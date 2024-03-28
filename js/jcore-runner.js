const jcoreRunnerButtons = [];

function jcoreRunnerCallEndpoint(script, settings) {
  const data = Object.assign(
    {
      script,
      page: 1,
      clear: false,
      exportFile: "",
      input: {},
      data: {},
    },
    settings,
  );
  const output = document.getElementById("jcore-runner-output");
  if (data.clear) {
    output.innerHTML = "";
  }
  jcoreRunnerRunnig(data);
  const options = {
    method: "POST", // *GET, POST, PUT, DELETE, etc.
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": wpApiSettings.nonce,
    },
    body: JSON.stringify(data), // body data type must match "Content-Type" header
  };
  fetch(`${wpApiSettings.root}jcore_runner/v1/run/`, options)
    .then((response) => response.json())
    .then((jsonData) => {
      jcoreRunnerRunnig(false);
      if (jsonData.output) {
        const shouldScroll =
          output.scrollTop + output.offsetHeight === output.scrollHeight;
        output.innerHTML += `\n${jsonData.output}`;
        if (shouldScroll) {
          output.scrollTop = output.scrollHeight;
        }
      }
      if (jsonData.return && typeof jsonData.return === "object") {
        for (const key of Object.keys(jsonData.return)) {
          const value = jsonData.return[key];
          const status = document.getElementById(`jcore-runner-return-${key}`);
          if (value && status) {
            status.innerHTML = value;
          }
        }
      }
      if (jsonData.nextPage) {
        const settings = {
          page: jsonData.nextPage,
          exportFile: jsonData.exportFile,
          input: data.input,
          data: jsonData.data,
        };
        jcoreRunnerCallEndpoint(script, settings);
      }
    })
    .catch((error) => {
      jcoreRunnerRunnig(false);
    });
}

function jcoreRunnerRunnig(run = false) {
  const progress = document.getElementById("jcore-runner-progress");
  const spinner = document.getElementById("jcore-runner-spinner");
  for (const element of jcoreRunnerButtons) {
    element.disabled = run !== false;
  }
  if (run === false) {
    progress.innerHTML = "Done";
    spinner.style.display = "none";
  } else {
    progress.innerHTML = `Running ${run.script}, page: ${run.page}`;
    spinner.style.display = "block";
  }
}

window.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-jcore-script]").forEach((element, i) => {
    jcoreRunnerButtons.push(element);
    element.addEventListener("click", () => {
      const input = {};
      for (const field of document.querySelectorAll(
        `[data-jcore-input="${element.dataset.jcoreScript}"]`,
      )) {
        if (field.tagName === "SELECT") {
          input[field.name] = Array.from(field.options)
            .filter((option) => option.selected)
            .map((option) => option.value);
          continue;
        }
        input[field.name] = field.value;
      }
      jcoreRunnerCallEndpoint(element.dataset.jcoreScript, {
        input,
        clear: true,
      });
    });
  });
});
