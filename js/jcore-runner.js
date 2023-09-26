const jcoreRunnerButtons = [];

function jcoreRunnerCallEndpoint(script, page = 1) {
  const data = { script, page };
  const output = document.getElementById("jcore-runner-output");
  if (page === 1) {
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
    .then((data) => {
      jcoreRunnerRunnig(false);
      if (data.output) {
        const shouldScroll =
          output.scrollTop + output.offsetHeight === output.scrollHeight;
        output.innerHTML += data.output;
        if (shouldScroll) {
          output.scrollTop = output.scrollHeight;
        }
      }
      if (data.nextPage) {
        jcoreRunnerCallEndpoint(script, data.nextPage);
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
      jcoreRunnerCallEndpoint(element.dataset.jcoreScript);
    });
  });
});
