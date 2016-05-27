jQuery(function ($) {
	document.formvalidator.setHandler('plz', function (value) {
		regex = /^\d{5}$/;
		return regex.test(value);
	});
});
