<?php defined('_JEXEC') or die; ?>
<br />
<p><?php echo JText::_( 'JT_FORMULATOR_EXTENDED_MESSAGE_INTRO' ); ?></p>
<br />
<table>
	<tbody>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_ANREDE_LABEL' ); ?>:
			</th>
			<td><?php echo $form->getValue('anrede'); ?></td>
		</tr>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_VNAME_LABEL' ); ?>:
			</th>
			<td><?php echo $form->getValue('vorname'); ?></td>
		</tr>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_NNAME_LABEL' ); ?>:
			</th>
			<td><?php echo $form->getValue('nachname'); ?></td>
		</tr>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_TEL_LABEL' ); ?>:
			</th>
			<td><?php echo $form->getValue('tel'); ?></td>
		</tr>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_EMAIL_LABEL' ); ?>:
			</th>
			<td><?php echo $form->getValue('email'); ?></td>
		</tr>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo JText::_( 'JT_FORMULATOR_EXTENDED_FORM_MESSAGE_LABEL' ); ?>:
			</th>
			<td><?php echo $form->getValue('message'); ?></td>
		</tr>
	</tbody>
</table>
