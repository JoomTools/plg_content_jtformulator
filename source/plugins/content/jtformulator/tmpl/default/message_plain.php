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
	$fileTimeOut   = JText::sprintf('PLG_JT_FORMULATOR_FILE_TIMEOUT', $this->params->get('file_clear'));
	$fileFieldSet  = false;

	if (count($fieldset->field))
	{
		if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel))))
		{
			echo "====================" . "\n";
			echo $legend . "\n";
		}

		if ($fileFieldSet)
		{
			echo $fileTimeOut . "\n";
		}

		echo "====================" . "\n";

		foreach ($fieldset->field as $field)
		{
			$label = trim(JText::_((string) $field['label']));
			$value = $form->getValue((string) $field['name']);
			$type  = (string) $form->getFieldAttribute((string) $field['name'], 'type');

			if ($type == 'file' && $this->params->get('file_clear'))
			{
				$fileFieldSet = true;
			}

			if ($type == 'spacer')
			{
				$label = '&nbsp;';
				$value = trim(JText::_((string) $field['label']));
			}

			if (empty($value))
			{
				// Comment out 'continue', if you want to submit only filled fields
				// continue;
			}

			if (is_array($value))
			{
				if ($type != 'subform')
				{

					foreach ($value as $_value)
					{
						$values[] = trim(JText::_($_value));
					}
					$value = implode(", ", $values);
					unset($values);
				}
			}
			else
			{
				$value = trim(JText::_($value));
			}


			echo strip_tags($label) . ": ";

			if (!is_array($value))
			{
				echo $value ? strip_tags($value) : '--';
			}

			echo "\n\r";

			if (!empty($value))
			{

				if ($type == 'subform')
				{
					$formname   = $form->getFormControl();
					$fieldname  = (string) $field['name'];
					$control    = $formname . '[' . $fieldname . ']' . '[' . $fieldname . 'X]';
					$formsource = (string) $field['formsource'];

					foreach ($value as $valuesKey => $subValues)
					{
						if ($valuesKey >= 1)
						{
							echo "---------------------------\n\r";
						}
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
								// continue;
							}

							if (is_array($subFormValue))
							{
								$subFormValue = trim($subFormValue[0]);
							}

							echo strip_tags(JText::_($subFormLabel)) . ": ";
							echo $subFormValue ? strip_tags(JText::_($subFormValue)) : '--';
							echo "\n\r";
						}
					}
				}
			}
		}
	}
}
