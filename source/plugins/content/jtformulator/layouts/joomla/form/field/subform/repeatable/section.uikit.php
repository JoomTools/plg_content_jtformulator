<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);

?>

<div class="subform-repeatable-group uk-margin-large-bottom uk-width-1-1 uk-width-medium-1-4"
	 data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">

<?php foreach ($form->getGroup('') as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
	<?php if (!empty($buttons)) : ?>
		<div class="uk-margin-top uk-width-1-1 uk-text-right">
			<div class="uk-button-group">
				<?php if (!empty($buttons['add'])) : ?><a class="group-add uk-button uk-button-small uk-button-success"><span class="uk-icon-plus"></span> </a><?php endif; ?>
				<?php if (!empty($buttons['remove'])) : ?><a class="group-remove uk-button uk-button-small uk-button-danger"><span class="uk-icon-minus"></span> </a><?php endif; ?>
				<?php if (!empty($buttons['move'])) : ?><a class="group-move uk-button uk-button-small uk-button-primary"><span class="uk-icon-move"></span> </a><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
