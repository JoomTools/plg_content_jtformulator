<?php

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');

// Set Fieldname or own value
$this->mail['sender_name'] = 'name';

// If you have to concatenate more fields, use an array like this
//$this->mail['sender_name'] = array('anrede', 'vname', 'nname');

$this->mail['sender_email'] = 'email';
?>
{emailcloak=off}
<div class="contact-form">
	<form name="<?php echo $id; ?>_form" id="<?php echo $id.$index; ?>_form" action="<?php echo JRoute::_("index.php"); ?>" method="post" class="form-validate">

		<fieldset>
			<legend><?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_LABEL'); ?></legend>
			<dl>

				<dt><?php echo $form->getLabel('name') ;?></dt>
				<dd><?php echo $form->getInput('name') ;?></dd>
				<br />
				<dt><?php echo $form->getLabel('email') ;?></dt>
				<dd><?php echo $form->getInput('email') ;?></dd>
				<br />
				<dt><?php echo $form->getLabel('subject') ;?></dt>
				<dd><?php echo $form->getInput('subject') ;?></dd>
				<br />
				<dt><?php echo $form->getLabel('message') ;?></dt>
				<dd><?php echo $form->getInput('message') ;?></dd>
				<br />
				<dd><?php echo $this->captcha; ?></dd>
				<br />
				<dd><button class="button validate" type="submit"><?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_SEND'); ?></button></dd>

			</dl>
		</fieldset>

		<input type="hidden" name="option" value="<?php echo JFactory::getApplication()->input->get('option'); ?>" />
		<input type="hidden" name="task" value="<?php echo $id; ?>_sendmail" />
		<input type="hidden" name="view" value="<?php echo JFactory::getApplication()->input->get('view'); ?>" />
		<input type="hidden" name="itemid" value="<?php echo JFactory::getApplication()->input->get('idemid'); ?>" />
		<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->get('id'); ?>" />
		<?php echo JHtml::_( 'form.token' ); ?>

	</form>
</div>
