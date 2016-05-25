<?php
/**
 * @Copyright    (c) 2016 JoomTools.de - All rights reserved.
 * @package        JT - Formulator - Plugin for Joomla! 2.5.x and 3.x
 * @author         Guido De Gobbis
 * @link           http://www.joomtools.de
 *
 * @license        GPL v3
 **/

defined('_JEXEC') or die;

foreach ($form->getFieldsets() as $fieldset)
{
	$fieldsetLabel = $fieldset->label;
	$fields        = $form->getFieldset($fieldset->name);

	if (count($fields)) : ?>
		<?php if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
			<h1><?php echo $legend; ?></h1>
		<?php endif; ?>

		<table cellpadding="2" border="1">
			<tbody>
			<?php foreach ($fields as $field) :
				$label = trim(JText::_($form->getFieldAttribute($field->fieldname, 'label')));
				$value = $form->getValue($field->fieldname);

				if (is_array($value))
				{
					foreach ($value as $_value)
					{
						$values[] = trim(JText::_($_value));
					}
					$value = implode(", ", $values);
					unset($values);
				} ?>
				<tr>
					<th style="width:30%; text-align: left;">
						<?php echo strip_tags($label); ?>
					</th>
					<td><?php echo $value ? nl2br(strip_tags($value)) : '--'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;
}

