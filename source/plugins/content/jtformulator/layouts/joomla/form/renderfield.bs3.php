<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      Content.jtformulator
 *
 * @author          Guido De Gobbis
 * @copyright   (c) 2017 JoomTools.de - All rights reserved.
 * @license         GNU General Public License version 3 or later
 **/

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 *    $options         : (array)  Optional parameters
 *    $label           : (string) The html code for the label (not required if $options['hiddenLabel'] is true)
 *    $input           : (string) The input field html code
 */

if (!empty($options['showonEnabled']))
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
	JFactory::getDocument()->addScript(JUri::root(true) . '/plugins/content/jtformulator/assets/js/showon.js');
}

$rel       = empty( $options['rel'] ) ? '' : ' ' . $options['rel'];
$gridgroup = !empty( $options['gridgroup'] ) ? ' class="' . $options['gridgroup'] . '"' : '';
?>

<?php if (!empty($gridgroup) || !empty($rel)) : ?>
<div<?php echo $gridgroup; ?><?php echo $rel; ?>>
<?php endif; ?>

    <?php if (empty($options['hiddenLabel'])) : ?>

        <?php if (!empty($options['gridlabel'])) : ?>
        <div class="<?php echo $options['gridlabel']; ?>">
        <?php endif; ?>

            <?php echo $label; ?>

        <?php if (!empty($options['gridlabel'])) : ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!empty($options['gridfield'])) : ?>
    <div class="<?php echo $options['gridfield']; ?>">
    <?php endif; ?>

        <?php if (!empty($options['icon'])) : ?>
        <div class="input-group">
            <span class="input-group-addon">
                <i class="<?php echo $options['icon']; ?>"></i>
            </span>
        <?php endif; ?>

            <?php echo $input; ?>

        <?php if (!empty($options['icon'])) : ?>
        </div>
        <?php endif; ?>

    <?php if (!empty($options['gridfield'])) : ?>
    </div>
    <?php endif; ?>

<?php if (!empty($gridgroup) || !empty($rel)) : ?>
</div>
<?php endif; ?>