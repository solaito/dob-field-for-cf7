document.addEventListener("DOMContentLoaded", () => {
	document.querySelectorAll(
		'.wpcf7-form'
	).forEach((form) => {
		form.querySelectorAll(
			'.wpcf7-form-control-wrap .wpcf7-form-control:not(.wpcf7-file):not(.wpcf7-quiz):not(.wpcf7-validates-as-required)'
		).forEach((input) => {
			input.addEventListener("change", () => {
				addEventListenerToEmailAddressConfirmationoTarget(form, input);
				validate(input);
			});
		});
		// 必須項目の場合、入力されなかったときにバリデーションする必要があるためblurで発火
		form.querySelectorAll(
			'.wpcf7-form-control-wrap .wpcf7-form-control:not(.wpcf7-file):not(.wpcf7-quiz).wpcf7-validates-as-required'
		).forEach((input) => {
			input.addEventListener("blur", function fn() {
				addEventListenerToEmailAddressConfirmationoTarget(form, input);
				validate(input);
				// blurとchangeで2回発火するのを防ぐためにblurは削除
				this.removeEventListener("blur", fn);
				input.addEventListener("change", () => {
					validate(input);
				});
			});
		});
	});
});

const addEventListenerToEmailAddressConfirmationoTarget = (form, input) => {
	if( input.classList.contains('wpcf7-confirm_email') && input.dataset.targetName != null && input.dataset.targetStatus != 'eventAdded') {
		form.querySelectorAll(
			'.wpcf7-form-control-wrap .wpcf7-email[name="' + input.dataset.targetName + '"]'
		).forEach((target) => {
			target.addEventListener('change', () => {
				validate(input);
			});
			input.dataset.targetStatus = 'eventAdded';
		});
	};
};

const validate = (input) => {
	clearResponse(input);
	let form = input.closest("form");

	let formData = new FormData(form);
	formData = deleteFile(formData);
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

		form.wpcf7.parent
			.querySelector(".screen-reader-response ul")
			.appendChild(li);
	};

	const setVisualValidationError = (error) => {
		const wrap = form.querySelector(error.into);

		const controls = wrap.querySelectorAll(".wpcf7-form-control");
		controls.forEach((control) => {
			control.classList.add("wpcf7-not-valid");
			control.setAttribute("aria-describedby", error.error_id);
		});

		const tip = document.createElement("span");
		tip.setAttribute("class", "wpcf7-not-valid-tip");
		tip.setAttribute("aria-hidden", "true");
		tip.insertAdjacentText("beforeend", error.message);
		wrap.appendChild(tip);

		wrap.querySelectorAll("[aria-invalid]").forEach((elm) => {
			elm.setAttribute("aria-invalid", "true");
		});

		controls.forEach((control) => {
			if (control.closest(".use-floating-validation-tip")) {
				control.addEventListener("focus", (event) => {
					tip.setAttribute("style", "display: none");
				});

				tip.addEventListener("mouseover", (event) => {
					tip.setAttribute("style", "display: none");
				});
			}
		});
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
	form.wpcf7.parent
		.querySelectorAll("#" + form.wpcf7.parent.id + "-ve-" + input.name)
		.forEach((li) => {
			li.remove();
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
	watts.api.root + watts.api.namespace + "/" + id + "/validation";

const deleteFile = (formData) => {
	for (const item of formData) {
		if(File.prototype.isPrototypeOf(item[1]))
		{
			formData.delete(item[0]);
		}
	}

	return formData;
}
