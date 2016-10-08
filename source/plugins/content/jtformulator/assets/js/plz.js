/**
* @package     Joomla.Plugin
* @subpackage  Content.jtformulator
*
* @author      Guido De Gobbis
* @copyright   (c) 2016 JoomTools.de - All rights reserved.
* @license     GNU General Public License version 3 or later
**/
jQuery(function ($) {
	document.formvalidator.setHandler('plz', function (value) {
		regex = /^\d{5}$/;
		return regex.test(value);
	});
});
