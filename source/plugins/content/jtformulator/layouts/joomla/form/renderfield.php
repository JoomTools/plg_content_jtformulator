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
	JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
	JFactory::getDocument()->addScript(JUri::root(true) . '/plugins/content/jtformulator/assets/js/showon.js');
}

$rel   = empty($options['rel']) ? '' : ' ' . $options['rel'];
?>

<div class="<?php echo $options['gridgroup']; ?>"<?php echo $rel; ?>>
	<?php if (empty($options['hiddenLabel'])) : ?>
        <div class="<?php echo $options['gridlabel']; ?>"><?php echo $label; ?></div>
	<?php endif; ?>
    <div class="<?php echo $options['gridfield']; ?>">
		<?php if (!empty($options['icon'])) : ?>
            <span class="input-group-addon">
            <i class="<?php echo $options['icon']; ?>"></i>
        </span>
		<?php endif; ?>
		<?php echo $input; ?>
    </div>
</div>
