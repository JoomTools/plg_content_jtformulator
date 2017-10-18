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

$fieldsets = $form->getXML(); ?>

<table cellpadding="8" cellspacing="0" border="1">
	<tbody>
	<?php
	foreach ($fieldsets->fieldset as $fieldset)
	{
	$fieldsetLabel = (string) $fieldset['label'];

	if (count($fieldset->field)) : ?>
	<?php if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
	</tbody>
</table>
<h1><?php echo $legend; ?></h1>
<table cellpadding="8" cellspacing="0" border="1">
	<tbody>
	<?php endif; ?>
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

		if ($type == 'captcha')
		{
			// Comment out 'continue', if you want to submit only filled fields
			continue;
		}

		if (empty($value))
		{
			// Comment out 'continue', if you want to submit only filled fields
			//continue;
		}

		if (is_array($value))
		{
			if ($type != 'subform')
			{
				foreach ($value as $_key => $_value)
				{
					if ($type == 'file')
					{
						$values[] = '<a href="' . strip_tags(trim(JText::_($_value))) . '" download>' . $_key . '</a> *';
					}
					else
					{
						$values[] = strip_tags(trim(JText::_($_value)));
					}
				}

				$value = implode(", ", $values);
				unset($values);
			}
		}
		else
		{
			$value = strip_tags(trim(JText::_($value)));
		} ?>
		<tr>
			<th style="width:30%; text-align: left;">
				<?php echo strip_tags($label); ?>
			</th>
			<td><?php if (!is_array($value))
				{
					echo $value ? nl2br($value) : '--';
				}

				if (!empty($value))
				{
					if ($type == 'subform')
					{
						$formname   = $form->getFormControl();
						$fieldname  = (string) $field['name'];
						$control    = $formname . '[' . $fieldname . ']' . '[' . $fieldname . 'X]';
						$formsource = (string) $field['formsource'];
						$setTable   = false;
						$counter    = count($value) - 1;

						$setTable = true; ?>
						<table cellpadding="8" cellspacing="0" border="1">
						<tbody>
						<?php

						foreach ($value as $valuesKey => $subValues)
						{
							$subForm = $form::getInstance(
								'subform.' . $fieldname . $valuesKey,
								$formsource,
								array('control' => $control)
							);

							foreach ($subForm->getGroup('') as $subFormField)
							{
								$subFormLabel = $subFormField->getAttribute('label');
								$subFormValue = $subValues[$subFormField->fieldname];

								if (empty($subFormValue))
								{
									// Comment out 'continue', if you want to submit only filled fields
									//continue;
								}

								if (is_array($subFormValue))
								{
									$subFormValue = trim($subFormValue[0]);
								} ?>
								<tr>
									<th style="width:30%; text-align: left;">
										<?php echo strip_tags(JText::_($subFormLabel)); ?>
									</th>
									<td><?php echo $subFormValue
											? nl2br(strip_tags(JText::_($subFormValue)))
											: '--'; ?>
									</td>
								</tr>
								<?php
								//unset($subFormValue);
							}

							if ($valuesKey < $counter)
							{
								?>
								<tr>
									<td colspan="2">---------------------------</td>
								</tr>
								<?php
							}

						}

						if ($setTable)
						{
							?>
							</tbody>
							</table>
							<?php
						}
					}
				}
				?></td>
		</tr>
		<?php echo $fileTimeOut; ?>
	<?php endforeach; ?>
	<?php endif;
	} ?>
	</tbody>
</table>

