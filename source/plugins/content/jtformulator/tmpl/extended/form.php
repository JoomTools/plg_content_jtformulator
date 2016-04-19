<?php
/**
* @Copyright	(c) JoomTools.de - All rights reserved.
* @package		JT - Formulator - Plugin for Joomla! 2.5.x and 3.x
* @author		Guido De Gobbis
* @link 		http://www.joomtools.de
*
* @license		GPL v3
**/

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');

// Set Fieldname or own value
$this->mail['sender_name'] = array('anrede', 'vorname', 'nachname');
?>
{emailcloak=off}
<div class="extended-form">
	<form name="<?php echo $id; ?>_form" id="<?php echo $id.$index; ?>_form" action="<?php echo JRoute::_("index.php"); ?>" method="post" class="form-validate">

		<p>
			<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_LABEL' ); ?>
		</p>

		<p>&nbsp;</p>

		<p>
			<?php echo $form->getLabel('anrede') ;?>
			<br /><?php echo $form->getInput('anrede') ;?>
		</p>
		<br />

		<p>
			<?php echo $form->getLabel('vorname') ;?>
			<br /><?php echo $form->getInput('vorname') ;?>
		</p>
		<br />

		<p>
			<?php echo $form->getLabel('nachname') ;?>
			<br /><?php echo $form->getInput('nachname') ;?>
		</p>
		<br />

		<p>
			<?php echo $form->getLabel('tel') ;?>
			<br /><?php echo $form->getInput('tel') ;?>
		</p>
		<br />

		<p>
			<?php echo $form->getLabel('email') ;?>
			<br /><?php echo $form->getInput('email') ;?>
		</p>
		<br />

		<p>
			<?php echo $form->getLabel('subject') ;?>
			<br /><?php echo $form->getInput('subject') ;?>
		</p>
		<br />

		<p>
			<?php echo $form->getLabel('message') ;?>
			<br /><?php echo $form->getInput('message') ;?>
		</p>

		<p>&nbsp;</p>
		<?php echo $this->captcha; ?>
		<p>&nbsp;</p>

		<button class="button validate" type="submit"><?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_SEND' ); ?></button>

		<input type="hidden" name="option" value="<?php echo JFactory::getApplication()->input->get('option'); ?>" />
		<input type="hidden" name="task" value="<?php echo $id; ?>_sendmail" />
		<input type="hidden" name="view" value="<?php echo JFactory::getApplication()->input->get('view'); ?>" />
		<input type="hidden" name="itemid" value="<?php echo JFactory::getApplication()->input->get('idemid'); ?>" />
		<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->get('id'); ?>" />
		<?php echo JHtml::_( 'form.token' ); ?>

	</form>
</div>
