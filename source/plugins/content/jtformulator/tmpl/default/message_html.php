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

$fieldsets = $form->getXML();

foreach ($fieldsets->fieldset as $fieldset)
{
	$fieldsetLabel = (string) $fieldset['label'];

	if (count($fieldset->field)) : ?>
		<?php if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
			<h1><?php echo $legend; ?></h1>
		<?php endif; ?>

		<table cellpadding="2" border="1">
			<tbody>
			<?php foreach ($fieldset->field as $field) :
				$label = trim(JText::_((string) $field['label']));
				$value = $form->getValue((string) $field['name']);
				$type = (string) $form->getFieldAttribute((string) $field['name'], 'type');
				$fileTimeOut = '';

				if ($type == 'file' && $this->params->get('file_clear'))
				{
					$fileTimeOut .= '<tr><td colspan="2">';
					$fileTimeOut .= JText::sprintf('PLG_JT_FORMULATOR_FILE_TIMEOUT', $this->params->get('file_clear'));
					$fileTimeOut .= '</td></tr>';
				}

				if ($type == 'spacer')
				{
					$label = '&nbsp;';
					$value = trim(JText::_((string) $field['label']));
				}

				if (empty($value))
				{
					// Comment out 'continue', if you want to submit only filled fields
					continue;
				}

				if (is_array($value))
				{
					foreach ($value as $_key => $_value)
					{
						if ($type == 'file')
						{
							$values[] = '<a href="' . $_value . '" download>' . $_key . '</a> *';
						}
						else
						{
							$values[] = strip_tags(trim(JText::_($_value)));
						}
					}

					$value = implode(", ", $values);
					unset($values);
				}
				else
				{
					$value = trim(JText::_($value));
				} ?>
				<tr>
					<th style="width:30%; text-align: left;">
						<?php echo strip_tags($label); ?>
					</th>
					<td><?php echo $value ? nl2br(strip_tags($value)) : '--'; ?></td>
				</tr>
				<?php echo $fileTimeOut; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;
}
