<?php defined('_JEXEC') or die;

echo "\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_MESSAGE_INTRO' );
echo "\n\r\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_FORM_ANREDE_LABEL' ).': '. $form->getValue('anrede');
echo "\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_FORM_VNAME_LABEL' ).': '.$form->getValue('vorname');
echo "\n\r\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_FORM_NNAME_LABEL' ).': '.$form->getValue('nachname');
echo "\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_FORM_TEL_LABEL' ).': '.$form->getValue('tel');
echo "\n\r\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_FORM_EMAIL_LABEL' ).': '.$form->getValue('email');
echo "\n\r".JText::_( 'JT_FORMULATOR_EXTENDED_FORM_MESSAGE_LABEL' ).': '."\n\r".$form->getValue('message');
