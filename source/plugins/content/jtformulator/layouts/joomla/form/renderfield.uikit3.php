<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.jtformulator
 *
 * @author      Guido De Gobbis
 * @copyright   (c) 2017 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
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

$gridgroup = !empty($options['gridgroup']) ? ' class="' . $options['gridgroup'] . '"' : '';
$gridlabel = !empty($options['gridlabel']) ? ' class="' . $options['gridlabel'] . '"' : '';
$gridfield = !empty($options['gridfield']) ? ' class="' . $options['gridfield'] . '"' : '';
?>

<div<?php echo $gridgroup; ?><?php echo $options['rel']; ?>>
	<?php if (empty($options['hiddenLabel'])) : ?>
        <div<?php echo $gridlabel; ?>><?php echo $label; ?></div>
	<?php endif; ?>
    <div<?php echo $gridfield; ?>><?php echo $input; ?></div>
</div>
