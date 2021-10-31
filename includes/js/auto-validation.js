import "es6-promise/auto";
import "fetch-ie8";

// closest for ie
// https://yoo-s.com/topic/detail/699
if (!Element.prototype.matches) {
	Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}
if (!Element.prototype.closest) {
	Element.prototype.closest = function (value) {
		var element = this;
		do {
			if (element.matches(value)) return element;
			element = element.parentelementement || element.parentNode;
		} while (element !== null && element.nodeType === 1);
		return null;
	};
}

document.addEventListener("DOMContentLoaded", () => {
	document.querySelectorAll(
		".wpcf7-form-control-wrap .wpcf7-form-control:not(.wpcf7-file):not(.wpcf7-quiz)"
	).forEach((input) => {
		input.addEventListener("blur", function fn() {
			validate(input);
			this.removeEventListener("blur", fn);
			input.addEventListener("change", () => {
				validate(input);
			});
		});
	});
});

const validate = (input) => {
	clearResponse(input);
	let form = input.closest("form");

	let formData = new FormData(form);
	formData.append("watts-validation-target", normalizeInputName(input.name));

	const setScreenReaderValidationError = (error) => {
		const li = document.createElement("li");

		li.setAttribute("id", error.error_id);

		if (error.idref) {
			li.insertAdjacentHTML(
				"beforeend",
				`<a href="#${error.idref}">${error.message}</a>`
			);
		} else {
			li.insertAdjacentText("beforeend", error.message);
		}

		form.wpcf7.parentNode
			.querySelector(".screen-reader-response ul")
			.appendChild(li);
	};

	const setVisualValidationError = (error) => {
		const wrap = form.querySelector(error.into);

		const control = wrap.querySelector(".wpcf7-form-control");
		control.classList.add("wpcf7-not-valid");
		control.setAttribute("aria-describedby", error.error_id);

		const tip = document.createElement("span");
		tip.setAttribute("class", "wpcf7-not-valid-tip");
		tip.setAttribute("aria-hidden", "true");
		tip.insertAdjacentText("beforeend", error.message);
		wrap.appendChild(tip);

		wrap.querySelectorAll("[aria-invalid]").forEach((elm) => {
			elm.setAttribute("aria-invalid", "true");
		});

		if (control.closest(".use-floating-validation-tip")) {
			control.addEventListener("focus", (event) => {
				tip.setAttribute("style", "display: none");
			});

			tip.addEventListener("mouseover", (event) => {
				tip.setAttribute("style", "display: none");
			});
		}
	};

	fetch(validateionEndpoint(form.wpcf7.id), {
		method: "POST",
		body: formData,
	})
		.then((response) => {
			return response.json();
		})
		.then((response) => {
			if (response.invalid_fields) {
				response.invalid_fields.forEach(setScreenReaderValidationError);
				response.invalid_fields.forEach(setVisualValidationError);
			}
		})
		.catch((error) => {
			console.log(error);
		});
};

const clearResponse = (input) => {
	clearScreenReaderResponse(input);

	let input_wrapper = input.closest(".wpcf7-form-control-wrap");

	input_wrapper.querySelectorAll(".wpcf7-not-valid-tip").forEach((span) => {
		span.remove();
	});

	input_wrapper.querySelectorAll("[aria-invalid]").forEach((elm) => {
		elm.setAttribute("aria-invalid", "false");
	});

	input_wrapper.querySelectorAll(".wpcf7-form-control").forEach((control) => {
		control.removeAttribute("aria-describedby");
		control.classList.remove("wpcf7-not-valid");
	});

	input_wrapper.querySelectorAll(".wpcf7-response-output").forEach((div) => {
		div.innerText = "";
	});
};

const clearScreenReaderResponse = (input) => {
	let form = input.closest("form");
	form.wpcf7.parentNode
		.querySelectorAll("#" + form.wpcf7.parent.id + "-ve-" + input.name)
		.forEach((li) => {
			li.parentNode.removeChild(li);
		});
};

const normalizeInputName = (name) => {
	let ret = name;
	if (name.indexOf("[") !== -1) {
		ret = name.substr(0, name.indexOf("["));
	}

	return ret;
};

const validateionEndpoint = (id) =>
	window.location.origin + "/wp-json/watts/v1/" + id + "/validation";
