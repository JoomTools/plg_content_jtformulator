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

echo JText::_('JT_FORMULATOR_DEFAULT_FORM_NAME_LABEL') . ': ' . $form->getValue('name') . "\n\r";
echo JText::_('JT_FORMULATOR_DEFAULT_FORM_EMAIL_LABEL') . ': ' . $form->getValue('email') . "\n\r\n\r";
echo JText::_('JT_FORMULATOR_DEFAULT_FORM_MESSAGE_LABEL') . ":\n\r" . $form->getValue('message');
