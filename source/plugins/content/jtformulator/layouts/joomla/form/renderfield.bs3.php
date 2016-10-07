<?php
/**
* @Copyright   (c) 2016 JoomTools.de - All rights reserved.
* @package     JT - Formulator - Plugin for Joomla! 3.5+
* @author      Guido De Gobbis
* @link        http://www.joomtools.de
* @license     GPL v3
**/

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 * 	$options         : (array)  Optional parameters
 * 	$label           : (string) The html code for the label (not required if $options['hiddenLabel'] is true)
 * 	$input           : (string) The input field html code
 */

if (!empty($options['showonEnabled']))
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'jui/cms.js', false, true);
	JFactory::getDocument()->addScript(JUri::root(true) . '/plugins/content/jtformulator/assets/js/showon.js');
}

$gridgroup = empty($options['gridgroup']) ? '': ' ' . $options['gridgroup'];
$gridlabel = empty($options['gridlabel']) ? '': ' ' . $options['gridlabel'];
$gridfield = empty($options['gridfield']) ? '': ' ' . $options['gridfield'];
?>

<div class="form-group<?php echo $gridgroup; ?>" <?php echo $options['rel']; ?>>
	<?php if (empty($options['hiddenLabel'])) : ?>
		<div class="form-label<?php echo $gridlabel; ?>"><?php echo $label; ?></div>
	<?php endif; ?>
	<div class="form-controls<?php echo $gridfield; ?>"><?php echo $input; ?></div>
</div>
