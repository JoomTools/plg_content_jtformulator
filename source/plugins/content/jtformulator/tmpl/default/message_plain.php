<?php
/**
* @Copyright	(c) 2016 JoomTools.de - All rights reserved.
* @package		JT - Formulator - Plugin for Joomla! 2.5.x and 3.x
* @author		Guido De Gobbis
* @link 		http://www.joomtools.de
*
* @license		GPL v3
**/

defined('_JEXEC') or die;

foreach ($form->getFieldsets() as $fieldset)
{
	$fieldsetLabel = $fieldset->label;
	$fields        = $form->getFieldset($fieldset->name);

	if (count($fields))
	{
		if (isset($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel))))
		{
			echo "====================" . "\n";
			echo $legend . "\n";
		}
		echo "====================" . "\n";

		foreach ($fields as $field)
		{
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
			}


			echo strip_tags($label) . ": ";
			echo $value ? strip_tags($value) : '--';
			echo "\n\r";
		}
	}
}
