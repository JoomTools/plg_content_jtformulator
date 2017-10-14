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
 * @var JForm   $tmpl             The Empty form for template
 * @var array   $forms            Array of JForm instances for render the rows
 * @var bool    $multiple         The multiple state for the form field
 * @var int     $min              Count of minimum repeating in multiple mode
 * @var int     $max              Count of maximum repeating in multiple mode
 * @var string  $fieldname        The field name
 * @var string  $control          The forms control
 * @var string  $label            The field label
 * @var string  $description      The field description
 * @var array   $buttons          Array of the buttons that will be rendered
 * @var bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
extract($displayData);

// Add script
if ($multiple)
{
	JHtml::_('jquery.ui', array('core', 'sortable'));
	JHtml::_('script', 'plugins/content/jtformulator/assets/js/system/subform-repeatable.js', array('version' => 'auto'));
}

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';
?>

<div class="row-fluid">
	<div class="subform-repeatable-wrapper subform-layout">
		<div class="subform-repeatable uk-grid"
			data-bt-add="a.group-add" data-bt-remove="a.group-remove" data-bt-move="a.group-move"
			data-repeatable-element="div.subform-repeatable-group" data-minimum="<?php echo $min; ?>" data-maximum="<?php echo $max; ?>">
			<div class="btn-toolbar uk-width-1-1">
				<div class="btn-group">
					<a class="group-add btn btn-mini button btn-success"><span class="icon-plus"></span> </a>
				</div>
			</div>
		<?php
		foreach ($forms as $k => $form) :
			echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons));
		endforeach;
		?>
		<?php if ($multiple) : ?>
		<script type="text/subform-repeatable-template-section" class="subform-repeatable-template-section">
		<?php echo $this->sublayout($sublayout, array('form' => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 'X', 'buttons' => $buttons)); ?>
		</script>
		<?php endif; ?>
		</div>
	</div>
</div>
<script>
	jQuery(document).on('subform-row-add', function(event, row){
		console.log('ROW: ', row);
		document.formvalidator = new JFormValidator();
	})
</script>