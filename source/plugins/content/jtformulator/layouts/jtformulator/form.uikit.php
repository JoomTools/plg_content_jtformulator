<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.jtformulator
 *
 * @author      Guido De Gobbis
 * @copyright   (c) 2017 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
**/

defined('_JEXEC') or die;

extract($displayData);
$renderOptions              = array();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<style>
    .invalid { border-color: #ff0000 !important; color: #ff0000 !important; }
    label.invalid { color: #ff0000 !important; }
    .uk-form-icon{ display: block; }
    .uk-form-icon:not(.uk-form-icon-flip)>select { padding-left: 40px !important; }
</style>
<div class="contact-form">
	<p><strong><?php echo JText::_('JTF_REQUIRED_FIELDS_LABEL'); ?></strong></p>
	<form name="<?php echo $id . $index; ?>_form"
	      id="<?php echo $id . $index; ?>_form"
	      action="<?php echo JRoute::_("index.php"); ?>"
	      method="post"
	      class="uk-form form-validate"
	      enctype="multipart/form-data"
	>
		<?php

		$fieldsets         = $form->getXML();
		$countFieldsets    = 1;
		$sumFieldsets      = count($fieldsets->fieldset);
		$submitSet         = false;

		foreach ($fieldsets->fieldset as $fieldset) :

			$fieldsetName  = (string) $fieldset['name'];
			$fieldsetLabel = (string) $fieldset['label'];
			$fieldsetDesc  = (string) $fieldset['description'];
			$sumFields     = count($fieldset->field);
			$fieldsetClass = !empty((string) $fieldset['class'])
				? ' class="' . (string) $fieldset['class'] . '"'
				: '';

			if ($fieldsetName == 'submit' && $sumFields == 0)
			{
				$sumFields = 1;
			}

			if ($sumFields) :
				if ($countFieldsets % 2) : ?>
					<!--<div class="row">-->
				<?php endif; ?>

				<fieldset<?php echo $fieldsetClass; ?>>
					<?php if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
						<legend class="uk-legend"><?php echo $legend; ?></legend>
					<?php endif; ?>
					<?php if (isset($fieldsetDesc) && strlen($desc = trim(JText::_($fieldsetDesc)))) : ?>
						<p><?php echo $desc; ?></p>
					<?php endif; ?>
					<?php foreach ($fieldset->field as $field)
					{

						$fieldName = (string) $field['name'];

						$renderOptions['gridgroup'] = (string) $field['gridgroup'];
						$renderOptions['gridlabel'] = (string) $field['gridlabel'];
						$renderOptions['gridfield'] = (string) $field['gridfield'];
						$renderOptions['icon'] = (string) $field['icon'];

						echo $form->renderField($fieldName, null, null, $renderOptions);
					}

					if ($fieldsetName == 'submit') :
						$submitSet = true; ?>
						<div class="uk-form-group uk-margin-large-top">
							<button class="uk-button uk-button-default"
							        type="submit"><?php echo JText::_('JSUBMIT'); ?></button>
						</div>
					<?php endif; ?>

				</fieldset>
			<?php endif;

			$countFieldsets++;
			if ($countFieldsets % 2 || $countFieldsets > $sumFieldsets) : ?>
				<!--</div>-->
			<?php endif;

		endforeach;

		if ($submitSet === false) : ?>
			<div class="uk-form-group uk-margin-large-top">
				<button class="uk-button uk-button-default"
				        type="submit"><?php echo JText::_('JSUBMIT'); ?></button>
			</div>
		<?php endif; ?>

		<?php echo $honeypot; ?>
		<input type="hidden" name="option" value="<?php echo JFactory::getApplication()->input->get('option'); ?>"/>
		<input type="hidden" name="task" value="<?php echo $id . $index; ?>_sendmail"/>
		<input type="hidden" name="view" value="<?php echo JFactory::getApplication()->input->get('view'); ?>"/>
		<input type="hidden" name="itemid" value="<?php echo JFactory::getApplication()->input->get('idemid'); ?>"/>
		<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->get('id'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
