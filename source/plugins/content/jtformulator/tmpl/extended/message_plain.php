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

$fieldsets = $form->getXML();

foreach ($fieldsets->fieldset as $fieldset)
{
	$fieldsetLabel = (string) $fieldset['label'];

	if (count($fieldset->field))
	{
		if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel))))
		{
			echo "====================" . "\n";
			echo $legend . "\n";
		}
		echo "====================" . "\n";

		foreach ($fieldset->field as $field)
		{
			$label = trim(JText::_((string) $field['label']));
			$value = $form->getValue((string) $field['name']);
			$type  = (string) $form->getFieldAttribute((string) $field['name'], 'type');

			if ($type == 'spacer')
			{
				$label = '&nbsp;';
				$value = trim(JText::_((string) $field['label']));
			}

			if (empty($value))
			{
				// Comment out 'continue', if you want to submit only filled fields
				//continue;
			}

			if (is_array($value))
			{
				foreach ($value as $_value)
				{
					$values[] = trim(JText::_($_value));
				}
				$value = implode(", ", $values);
				unset($values);
			}
			else
			{
				$value = trim(JText::_($value));
			}


			echo strip_tags($label) . ": ";
			echo $value ? strip_tags($value) : '--';
			echo "\n\r";
		}
	}
}
