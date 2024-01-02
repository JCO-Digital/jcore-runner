const jcoreRunnerButtons = [];

function jcoreRunnerCallEndpoint(script, settings) {
  const data = Object.assign(
    {
      script,
      page: 1,
      clear: false,
      exportFile: "",
      input: {},
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
  fetch(wpApiSettings.root + "jcore_runner/v1/run/", options)
    .then((response) => response.json())
    .then((jsonData) => {
      jcoreRunnerRunnig(false);
      if (jsonData.output) {
        const shouldScroll =
          output.scrollTop + output.offsetHeight === output.scrollHeight;
        output.innerHTML += jsonData.output;
        if (shouldScroll) {
          output.scrollTop = output.scrollHeight;
        }
      }
      if (jsonData.return && typeof jsonData.return === "object") {
        Object.keys(jsonData.return).forEach((key) => {
          const value = jsonData.return[key];
          const status = document.getElementById(`jcore-runner-return-${key}`);
          if (value && status) {
            status.innerHTML = value;
          }
        });
      }
      if (jsonData.nextPage) {
        const settings = {
          page: jsonData.nextPage,
          exportFile: jsonData.exportFile,
          input: Object.assign(data.input, jsonData.input),
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

window.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("[data-jcore-script]").forEach((element, i) => {
    jcoreRunnerButtons.push(element);
    element.addEventListener("click", () => {
      const input = {};
      document
        .querySelectorAll(`[data-jcore-input="${element.dataset.jcoreScript}"]`)
        .forEach((field) => {
          input[field.name] = field.value;
        });
      jcoreRunnerCallEndpoint(element.dataset.jcoreScript, {
        input,
        clear: true,
      });
    });
  });
});
