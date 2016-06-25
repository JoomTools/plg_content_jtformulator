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

	if (count($fieldset->field))
	{
		if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel))))
		{
			echo "\n" ."====================" . "\n";
			echo $legend . "\n";
		}
		echo "====================" . "\n";

		foreach ($fieldset->field as $field)
		{
			$label       = strip_tags(trim(JText::_((string) $field['label'])));
			$value       = $form->getValue((string) $field['name']);
			$type        = (string) $form->getFieldAttribute((string) $field['name'], 'type');
			$fileTimeOut = '';

			if ($type == 'file' && $this->params->get('file_clear'))
			{
				$fileTimeOut .= "========" . "\n";
				$fileTimeOut .= JText::sprintf('PLG_JT_FORMULATOR_FILE_TIMEOUT', $this->params->get('file_clear')) . "\n";
				$fileTimeOut .= "\n";
			}

			if ($type == 'spacer')
			{
				if ($label)
				{
					$value = "========" . "\n";
					$value .= $label;
					$label = '';
				}
			}

			if (is_array($value))
			{
				foreach ($value as $_key => $_value)
				{
					if ($type == 'file')
					{
						$values[] = strip_tags(trim($_key)) . ' *';
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
				$value = strip_tags(trim(JText::_($value)));
			}

			if (empty($value) || $type == 'captcha')
			{
				// Comment out 'continue', if you want to submit only filled fields
				continue;
			}

			echo !empty($label) ? $label . ": " : '';
			echo !empty($value) ? $value : '--';
			echo "\n";
			echo $fileTimeOut;
		}
	}
}
