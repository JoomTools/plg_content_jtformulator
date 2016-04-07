<?php defined('_JEXEC') or die;

echo JText::_( 'JT_FORMULATOR_DEFAULT_FORM_NAME_LABEL' ).': '.$form->getValue('name')."\n\r";
echo JText::_( 'JT_FORMULATOR_DEFAULT_FORM_EMAIL_LABEL' ).': '.$form->getValue('email')."\n\r\n\r";
echo JText::_( 'JT_FORMULATOR_DEFAULT_FORM_MESSAGE_LABEL' ).":\n\r".$form->getValue('message');
