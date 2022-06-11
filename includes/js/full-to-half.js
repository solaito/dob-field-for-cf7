document.addEventListener("DOMContentLoaded", function () {
	document.querySelectorAll(".wpcf7-tel, .wpcf7-email, .wpcf7-confirm_email").forEach((input) => {
		input.addEventListener("change", function () {
			let val = input.value;
			val = val.replace(
				/[Ａ-Ｚａ-ｚ０-９－！”＃＄％＆’（）＝＜＞，．？＿［］｛｝＠＾～￥]/g,
				function (s) {
					return String.fromCharCode(s.charCodeAt(0) - 65248);
				}
			);
			input.value = val;
		});
	});
});
