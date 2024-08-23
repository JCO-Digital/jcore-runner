const jcoreRunnerButtons = [];

/**
 * Checks if a value is a plain object.
 * (i.e., not an array or null, or a File/Date/etc.)
 * @param {any} value The value to check.
 * @returns {boolean} True if the value is a plain object, false otherwise.
 */
function isPlainObject(value) {
	return (
		typeof value === "object" &&
		value !== null &&
		value.constructor === Object &&
		Object.prototype.toString.call(value) === "[object Object]"
	);
}

/**
 * Recursively converts an object to a FormData object.
 *
 * This handles arrays and (one level) nested objects.
 * It is not the most efficient way to do this, but it works.
 *
 * @param {Object} obj The object to convert.
 * @param {string} parentKey The key of the parent object. Used for nested objects.
 * @returns {FormData} The converted FormData object.
 */
function objectToFormData(obj, parentKey = "") {
	const formData = new FormData();

	for (const [key, value] of Object.entries(obj)) {
		const fullKey = parentKey ? `${parentKey}[${key}]` : key;

		if (Array.isArray(value)) {
			for (const item of value) {
				const arrayKey = `${fullKey}[]`;
				if (isPlainObject(item)) {
					objectToFormData(item, arrayKey).forEach((value, key) =>
						formData.append(key, value),
					);
				} else {
					formData.append(arrayKey, item);
				}
			}
		} else if (isPlainObject(value)) {
			objectToFormData(value, fullKey).forEach((nestedValue, nestedKey) =>
				formData.append(nestedKey, nestedValue),
			);
		} else {
			formData.append(fullKey, value);
		}
	}
	return formData;
}

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
	jcoreRunnerRunning(data);
	const formData = objectToFormData(data);
	const options = {
		method: "POST",
		headers: {
			"X-WP-Nonce": wpApiSettings.nonce,
		},
		body: formData,
	};
	fetch(`${wpApiSettings.root}jcore_runner/v1/run/`, options)
		.then((response) => response.json())
		.then((jsonData) => {
			jcoreRunnerRunning(false);
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
					exportFileExtension: jsonData.exportFileExtension ?? "json",
					input: data.input,
					data: jsonData.data ?? {},
				};
				jcoreRunnerCallEndpoint(script, settings);
			} else if (jsonData.exportFile) {
				if (document.getElementById("jcore-runner-export-download")) {
					document.getElementById("jcore-runner-export-download").remove();
				}
				const a = document.createElement("a");
				const url = `${jcore_export_url}${jsonData.exportFile}.${
					jsonData.exportFileExtension ?? "json"
				}`;
				a.id = "jcore-runner-export-download";
				a.href = url;
				a.download = "";
				a.textContent = "Download export";
				const container = document.getElementById("jcore-runner-return");
				if (container) {
					container.appendChild(a);
				}
			}
		})
		.catch((error) => {
			jcoreRunnerRunning(false);
		});
}

function jcoreRunnerRunning(run = false) {
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
				if (
					field.tagName === "INPUT" &&
					["checkbox", "radio"].includes(field.type)
				) {
					input[field.name] = field.checked;
					continue;
				}
				if (field.tagName === "INPUT" && field.type === "file") {
					input[field.name] = field.files[0];
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
