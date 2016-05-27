jQuery(function ($) {
	"use strict";

	$.fn.toggleNovalidate = function (init) {
		if ($(this).hasClass('novalidate') && init > 0) {
			$(this).removeNovalidate();
		}
		else {
			$(this).setNovalidate();
		}
	};

	$.fn.setNovalidate = function () {
		if (!$(this).hasClass('novalidate')) {
			$(this).addClass('novalidate').attr('disabled', 'disabled');
		}
	};

	$.fn.removeNovalidate = function () {
		if ($(this).hasClass('novalidate')) {
			$(this).removeClass('novalidate').removeAttr('disabled');
		}
	};

	$('[data-showon]').each(function (index, elm) {
		var $elm = $(elm),
			$label = $elm.find('label'),
			$field = $('#' + $label.attr('for')),

			dataShowon = $.parseJSON($elm.attr('data-showon')),
			dataShowonId = dataShowon[0].field,
			dataShowonValues = dataShowon[0].values,

			$setter = $('[name="' + dataShowonId + '"]'),
			setterVal = $setter.val(),
			isCheckbox = $setter.is('[type="checkbox"]'),
			isRadio = $setter.is('[type="radio"]');

		if (isRadio) {
			setterVal = $setter.filter(':checked').val();
		}

		if (setterVal === undefined) {
			setterVal = 0;
		}

		$field.setNovalidate();

		if ($.inArray(setterVal.toString(), dataShowonValues) != -1) {
			if ((isCheckbox && $setter.prop('checked')) || isRadio) {
				$field.removeNovalidate();
			}
		}

		$setter.change(function () {
			$field.toggleNovalidate($(this).val());
		});

	});
});