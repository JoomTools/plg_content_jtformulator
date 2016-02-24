<?php defined('_JEXEC') or die;
/**
 * Mit Ausnahme von dem Kontainer-Element mit der Klasse "captcha-icons"
 * sind alle Klassen und ID's, sowie die Links, für die Funktion von
 * ReCaptcha nötig. Die Bilder können durch eigene Bilder, oder
 * durch Texte ersetzt werden. Auch die Anordnung kann geändert werden.
 */
$error = $errorClass ? ' '.$errorClass : '';
$classError = $errorClass ? ' class="'.$errorClass.'"' : '';
?>

<div id="recaptcha_image"></div>
<div class="recaptcha_only_if_incorrect_sol" style="color:red">
	<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_INCORRECT_TRY_AGAIN'); ?>
</div>
<br />

<div class="captcha-icons" style="float:left; width: 25px;margin-right: 20px;">
	<a href="javascript:Recaptcha.reload()" title="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_REFRESH_BTN'); ?>">
		<img class="noresize" width="25" height="18" alt="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_REFRESH_BTN'); ?>" id="recaptcha_reload" src="https://www.google.com/recaptcha/api/img/white/refresh.gif">
	</a>
	<a class="recaptcha_only_if_image" href="javascript:Recaptcha.switch_type('audio')" title="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_AUDIO_CHALLENGE'); ?>">
		<img class="noresize" width="25" height="15" alt="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_AUDIO_CHALLENGE'); ?>" id="recaptcha_switch_audio" src="https://www.google.com/recaptcha/api/img/white/audio.gif">
	</a>
	<a class="recaptcha_only_if_audio" href="javascript:Recaptcha.switch_type('image')" title="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_VISUAL_CHALLENGE'); ?>">
		<img class="noresize" width="25" height="15" alt="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_VISUAL_CHALLENGE'); ?>" id="recaptcha_switch_img" src="https://www.google.com/recaptcha/api/img/white/text.gif">
	</a>
	<a href="javascript:Recaptcha.showhelp()" title="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_HELP_BTN'); ?>">
		<img class="noresize" width="25" height="16" id="recaptcha_whatsthis" src="https://www.google.com/recaptcha/api/img/white/help.gif" alt="<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_HELP_BTN'); ?>">
	</a>
</div>

<label for="recaptcha_response_field" class="required recaptcha_only_if_image">
	<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_INSTRUCTIONS_VISUAL'); ?><br />
</label>
<label for="recaptcha_response_field" class="required recaptcha_only_if_audio">
	<?php echo JText::_('JT_FORMULATOR_DEFAULT_FORM_CAPTCHA_INSTRUCTIONS_AUDIO'); ?><br />
</label>
<input type="text" id="recaptcha_response_field" name="recaptcha_response_field"<?php echo $classError; ?> required
		aria-required />
<br />
<br />
