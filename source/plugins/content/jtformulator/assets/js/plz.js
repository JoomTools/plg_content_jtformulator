/**
* @Copyright   (c) 2016 JoomTools.de - All rights reserved.
* @package     JT - Formulator - Plugin for Joomla! 3.5+
* @author      Guido De Gobbis
* @link        http://www.joomtools.de
* @license     GPL v3
**/
jQuery(function ($) {
	document.formvalidator.setHandler('plz', function (value) {
		regex = /^\d{5}$/;
		return regex.test(value);
	});
});
