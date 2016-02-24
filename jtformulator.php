<?php
/**
 * @Copyright	(c) JoomTools.de - All rights reserved.
 * @package		JT - Formulator - Plugin for Joomla! 2.5.x and 3.x
 * @author		Guido De Gobbis
 * @link		http://www.joomtools.de
 *
 * @license		JTL-NN-AE-KW (http://www.joomtools.de/lizenzen.html)
 *
 * You should have received a copy of the JoomTools.de License
 * along with this program. If not, see <http://www.joomtools.de/lizenzen.html>.
 **/

defined( '_JEXEC' ) or die( 'Restricted access' );

class plgContentJtformulator extends JPlugin {

	// Debug-Object
	protected $FB = false;

	// Captcha
	protected $captcha;
	protected $validCaptcha = true;

	// Formular
	protected $form = array();

	// Userparams
	protected $uParams = array();

	// Mail
	protected $mail = array();

	public function __construct( &$subject, $params )
	{
		$app = JFactory::getApplication();
		if($app->isAdmin()) return;

		parent::__construct( $subject, $params );

		if(defined('JFIREPHP') && $this->params->get('debug', 0)) {
			$this->FB = FirePHP::getInstance(TRUE);
		}
		else {
			$this->FB = FALSE;
		}

		$version = new JVersion();
		$joomla_main_version = substr($version->RELEASE, 0, strpos($version->RELEASE, '.'));
		$this->uParams['jversion'] = $joomla_main_version;

		$this->loadLanguage();
 	}

	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		if(!preg_match('@{jtformulator(\s.*)?}@Us', $row->text)) return;

		$fb =& $this->FB;
		if($fb) $fb->group('JT - Formulator -> ' . __FUNCTION__ );
		if($fb) $fb->info('Classen-Objekt vor beginn der Arbeit');
		if($fb) $fb->log($this,'$this');

		$msg = '';
		$error_msg = '';
        $cIndex = 0;
		$checkThemeFiles = array('fields'=>'xml', 'form'=>'php', 'message_html'=>'php', 'message_plain'=>'php');

		$this->key['publickey'] = $this->params->get('publickey','6LfusfASAAAAAEH0QnEZrHjR6xYCG0Z68xVnj2bv');
		$this->key['privatekey']  = $this->params->get('privatekey','6LfusfASAAAAAH_yfd1UnO3wD-YANEId9aPQLOsS');

		$regex = "@(<(\w+)[^>]*>|){jtformulator(\s.*)?}(</[^>]+>|)@Uis";
		if (@preg_match_all( $regex, $row->text, $matches ) <= 0) {
			if($fb) $fb->error($matches,'ABBRUCH! Pluginaufruf ist fehlerhaft -> $matches');
			return;
		}
		if($fb) $fb->info('Pluginaufruf gefunden:');
		if($fb) $fb->log($matches,'Pluginaufruf');

		$this->uParams['captcha'] = (count($matches[0]) < 1) ? false : $this->params->get('captcha');

		foreach ($matches[0] as $matchKey=>$matchValue)
		{
			$html = '';

			if($fb) $fb->group($matchValue);
			if($fb) $fb->info('Verarbeite Parameter aus dem Pluginaufruf, und erzeuge ein assoziatives Array aus den Werten.');

			$vars = ($matches[3][$matchKey]) ? explode('|', $matches[3][$matchKey]) : array();

			if($fb) $fb->log($vars,'$vars');

			foreach ($vars as $var) {
				list($key, $value) = explode('=', trim($var));
				$uParams[$key] = $value;
			}

			$lang = JFactory::getLanguage();
			$tag = $lang->getTag();

			$this->uParams['mailto'] = isset($uParams['mailto'])
                ? str_replace('#', '@', $uParams['mailto'])
                : null;
			$uParams['theme'] = isset($uParams['theme'])
                ? $uParams['theme']
                : 'default';
			$this->uParams['theme'] = $uParams['theme'];
			$this->uParams['index'] = $cIndex;
            if(isset($uParams['subject'])) {
                $this->uParams['subject'] = $uParams['subject'];
            }

            if($fb) $fb->log($this->uParams,'Werte');

			if($fb) $fb->group('Prüfe ob alle Dateien vorhanden sind');

			foreach($checkThemeFiles as $chkFile=>$chkType) {
				$_checkTheme[$chkFile.'.'.$chkType] = $this->_getTmplPath($chkFile,$chkType) ? true : false;
			}

			if($fb) $fb->log($_checkTheme,'$_checkTheme');

			$checkTheme = true;

			if (in_array(true, $_checkTheme) && in_array(false, $_checkTheme)) {
				$checkTheme = false;
				JFactory::getApplication()->enqueueMessage(sprintf(JText::_('PLG_JT_FORMULATOR_FILES_ERROR'),$this->uParams['theme']), 'error');
			}
			elseif (!in_array(true, $_checkTheme) && in_array(false, $_checkTheme)) {
					$checkTheme = false;
					JFactory::getApplication()->enqueueMessage(sprintf(JText::_('PLG_JT_FORMULATOR_THEME_ERROR'),$this->uParams['theme']), 'error');
			}

			if($fb) $fb->groupEnd();

			if ($checkTheme)
            {
				$this->captcha = '<input type="text" name="'.$uParams['theme'].'[information_number]" style="position: absolute;top:-999em;left:-999em;height: 0;width: 0;" value="" />';
				$formLang = dirname(dirname(dirname($this->_getTmplPath('language/'.$tag.'/'.$tag.'.'.$uParams['theme'].'_form', 'ini'))));
				$this->loadLanguage($uParams['theme'].'_form', $formLang);

				if($fb) $fb->log($lang,'$lang');
				if($fb) $fb->info('Lade XML-Formularfelder');

				$formXmlPath = $this->_getTmplPath('fields', 'xml');

				if ($this->uParams['jversion'] >= '3') {
					$field = new JForm($this->uParams['theme'], array('control'=>$uParams['theme']));
				}
				else {
					require_once('assets/joomla25.jformextended.class.php');
					$field = new JFormExtended($this->uParams['theme'], array('control'=>$uParams['theme']));
				}

				if($fb) $fb->log($formXmlPath, '$formXmlPath');

				$field->loadFile($formXmlPath);
				$this->form[$uParams['theme']] = $field;

				if($fb) $fb->log($this->form,'Formular');

				$task = JFactory::getApplication()->input->get('task', false, 'post');

				if($fb) $fb->info($task, '$task');

				if ($task==$this->uParams['theme']."_sendmail")
                {

					if($fb) $fb->info('Formular wurde Abgeschickt');
					$submitValues = JFactory::getApplication()->input->get($uParams['theme'], array(), 'post', 'array');
					foreach ($submitValues as $subKey=>$subValue) {
						$submitValues[$subKey] = JText::_($subValue);
					}

                    switch(true)
                    {
                        case isset($submitValues['subject']):
                            if($fb) $fb->info('Case 1');
                            $this->mail['subject'] = 'subject';
                            break;
                        case !isset($submitValues['subject'])
                            && isset($uParams['subject']):
                            if($fb) $fb->info('Case 2');
                            $submitValues['subject'] = $this->mail['subject'] = $uParams['subject'];
                            break;
                        default:
                            if($fb) $fb->info('Default');
                            $submitValues['subject'] = $this->mail['subject'] = '';
                            break;
                    }

					$this->form[$uParams['theme']]->bind($submitValues);
					if($fb) $fb->log($submitValues, 'Formularwerte');
					if($fb) $fb->log($this->form[$uParams['theme']], 'Formular mit Werte');

					if($fb) $fb->info('Validiere Formularwerte');
					if (!$submitValues['information_number']) {
						$valid = $this->_validate();
					}
					else {
						$valid = false;
					}

				}

				if($fb) $fb->info($this->uParams['captcha'], 'Erzeuge Quelltext für Captcha');
				if ($this->uParams['captcha']) { $this->_getCaptcha(); }

				if($fb) $fb->info('Lade Formular-Quelltext');
				$formHtmlPath = $this->_getTmplPath('form');
				if($fb) $fb->log($formHtmlPath, '$formHtmlPath');

				$html .= $this->_getTmpl($formHtmlPath);

				if ($task==$this->uParams['theme']."_sendmail") {

					if ($valid) {
						if ($this->_sendemail()) {
							JFactory::getApplication()->enqueueMessage(JText::_('PLG_JT_FORMULATOR_EMAIL_THANKS'), 'success');
							JFactory::getApplication()->redirect(JRoute::_('index.php', false));
						}
					}
					if ($submitValues['information_number']) JFactory::getApplication()->redirect(JRoute::_('index.php', false));

				}

			}

			if($fb) $fb->groupEnd();
			if($fb) $fb->info($this,'$this');
			$row->text = str_replace($matchValue, $html, $row->text);
            $cIndex++;
		}

		if($fb) $fb->groupEnd();
		if($fb) $fb->log($this,'$this');
	}

	protected function _getCaptcha()
	{
		$fb =& $this->FB;
        $captcha = $captcha_val = '';
        $index = $this->uParams['index'];
		if($fb) $fb->group('JT - Formulator -> ' . __FUNCTION__ );

		if ($this->uParams['captcha'] == 'custom') {

			$errorClass = $this->validCaptcha ? '' : $this->params->get('error_class', 'invalid');
			if($fb) $fb->warn($this->validCaptcha, '$errorClass');
			if($fb) $fb->warn(RECAPTCHA_API_SECURE_SERVER, '$errorClass');

			$lang = JFactory::getLanguage();
			$tag = explode('-', $lang->getTag());
			$tag = $tag[0];

			$js = "var RecaptchaOptions = {  theme : 'custom', custom_theme_widget: 'recaptcha_widget', lang : '$tag' }; " ;
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( $js );

			require_once(dirname(__FILE__).'/assets/recaptchalib.php');

			$recaptchaPath = $this->_getTmplPath('recaptcha');

            $captcha_val = '<div id="recaptcha_widget" style="display:none">';
			// Start capturing output into a buffer
			ob_start();

			// Include the requested template filename in the local scope
			// (this will execute the view logic).
			include($recaptchaPath);

			// Done with the requested template; get the buffer and
			// clear it.
            $captcha_val .= ob_get_contents();
			ob_end_clean();
            $captcha_val .= '<script type="text/javascript" src="'. RECAPTCHA_API_SECURE_SERVER . '/challenge?k=' . $this->key['publickey'] . '"></script>';
            $captcha_val .= '</div>';
		}
		elseif ($this->uParams['captcha'] == 'joomla') {
			$jcaptcha = JFactory::getConfig()->get('captcha');

			if ($jcaptcha)
            {
                $captcha = JCaptcha::getInstance($jcaptcha);
                if($fb) $fb->log($captcha,'$captcha');
                if($fb) $fb->log($captcha,'$captcha');
                $captcha_val = $captcha ? $captcha->display('recaptcha', 'recaptcha'.$index, 'g-recaptcha') : '';
			}

		}

        $captcha_val .= $captcha ? '<noscript style="position:relative;">'.JText::_('PLG_JT_FORMULATOR_CAPTCHA_NOSCRIPT').'</noscript>' : '';
		$this->captcha = $captcha_val . $this->captcha;

		if($fb) $fb->log($this->captcha,'Quelltext');
		if($fb) $fb->groupEnd();
	}

	protected function _validate()
	{
		$fb =& $this->FB;
		if($fb) $fb->group('JT - Formulator -> ' . __FUNCTION__);

		$valid = JSession::checkToken();
		if($fb) $fb->info($valid, 'JSession::checkToken()');

		$valid_captcha = true;
		$errorClass = $this->params->get('error_class', 'invalid');

		if($fb) $fb->info('Formularwerte');
		$data = $this->form[$this->uParams['theme']]->getData()->toArray();
		if($fb) $fb->log($data, 'Werte');

		if($fb) $fb->info('Feldattribute');
		$fieldXML = $this->form[$this->uParams['theme']]->getXML()->fieldset->children();
		if($fb) $fb->log($fieldXML, 'Attribute');

		if($fb) $fb->group('Validierung der einzelnen Felder');
		foreach ($fieldXML as $field) {

			if($fb) $fb->info((string) $field['name'], 'Feldname');
			if($fb) $fb->log($field, 'Attribute');

			$rule = '';
			$fieldClass = '';
			$fieldvalidation = true;
			$type = (string) $field['type'];
			$validate = (string) $field['validate'];
			$required = (string) $field['required'];
			$name = (string) $field['name'];
			$value = $data[(string) $field['name']];

            if($field->option) {
                foreach($field->option as $option) {
                    $option->attributes()->value = JText::_($option->attributes()->value);
                }
            }

            if (($required == 'true' || $required == 'required') && !$value) {
				$fieldvalidation = false;
			}

			if ($type == 'email' || $validate == 'email') {
				$validate = $validate ? $validate : 'email';
				$emailName = $name;
			}

			if ($validate && $fieldvalidation) {

				if ($validate == 'email') {
					$field->addAttribute('tld', 'tld');
				}

				$rule = JFormHelper::loadRuleType($validate);
				if($fb) $fb->log($rule,'Regex für Feldvalidierung');

			}

			$test = ($rule && $fieldvalidation)? $rule->test($field, $value) : $fieldvalidation;

			if ($test == false) {
				$fieldClass = $this->form[$this->uParams['theme']]->getFieldAttribute($name, 'class');
				$fieldClass = $fieldClass ? trim(str_replace($errorClass, '', $fieldClass)).' ' : '';
				if($fb) $fb->log($fieldClass,'$fieldClasas');
				$this->form[$this->uParams['theme']]->setFieldAttribute($name, 'class', $fieldClass.$errorClass);
				$fieldClass = $this->form[$this->uParams['theme']]->getFieldAttribute($name, 'class');
				if($fb) $fb->log($fieldClass,'$fieldClasas');
				$valid = false;
			}

			if($fb) $fb->log($test,'Feldprüfung');

		} // end foreach
		if($fb) $fb->groupEnd();

		if($fb) $fb->info('Validierung der Captcha-Eingabe');
		if ($this->uParams['captcha'] == 'custom') {
			require_once('assets/recaptchalib.php');
			$resp = recaptcha_check_answer ($this->key['privatekey'],
			                                $_SERVER["REMOTE_ADDR"],
			                                $_POST["recaptcha_challenge_field"],
			                                $_POST["recaptcha_response_field"]);
			$valid_captcha = $resp->is_valid;
		}
		elseif ($this->uParams['captcha'] == 'joomla') {
			$jcaptcha = JFactory::getConfig()->get('captcha');

			if ($jcaptcha) {
				$valid_captcha = JCaptcha::getInstance($jcaptcha);

                if($fb) $fb->log($valid_captcha,'$valid_captcha');
                if(!$valid_captcha->checkAnswer(true)) {
                    if($fb) $fb->log($valid_captcha,'$valid_captcha');
                    if($fb) $fb->log($valid_captcha->getErrors(),'$valid_captcha->getErrors()');
                    JFactory::getApplication()->enqueueMessage(implode('<br />', $valid_captcha->getErrors()), 'error');
                    $valid_captcha = false;
                }
                if($fb) $fb->log($valid_captcha,'$valid_captcha');
			}

		}
		if($fb) $fb->log($valid_captcha,'Captcha-Eingabe');

		$this->validCaptcha = $valid_captcha;

		$valid = ($valid && $valid_captcha)? true : false;

		if (!$valid) {
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_JT_FORMULATOR_FIELD_ERROR'), 'error');
			if ($this->uParams['jversion'] <= '2') {
				$this->form[$this->uParams['theme']]->setValue($emailName, null, '');
				$fieldClass = $this->form[$this->uParams['theme']]->getFieldAttribute($emailName, 'class');
				$fieldClass = $fieldClass ? trim(str_replace($errorClass, '', $fieldClass)).' ' : '';
				$this->form[$this->uParams['theme']]->setFieldAttribute($emailName, 'class', $fieldClass.$errorClass);
				$fieldClass = $this->form[$this->uParams['theme']]->getFieldAttribute($emailName, 'class');
				if($fb) $fb->log($fieldClass,'$fieldClasas');
			}
		}

		if($fb) $fb->log($valid,'Validierung');
		if($fb) $fb->groupEnd();

		return $valid;
	}

	protected function _sendemail()
	{
		$fb =& $this->FB;
		if($fb) $fb->group('JT - Formulator -> ' . __FUNCTION__);

		$jConfig = JFactory::getConfig();
		if($fb) $fb->log($jConfig, '$jConfig');
		$data = $this->form[$this->uParams['theme']]->getData()->toArray();

		if ($this->mail){

			if($fb) $fb->log($this->mail, '$this->mail');
			foreach ($this->mail as $key=>$field) {

				if (is_array($field)) {
					foreach ($field as $value) {
						$_field[] = isset($data[$value])? $data[$value] : $value;
					}
					$field = implode(' ', $_field);
					unset($_field);
				}
				else {
					$field =  isset($data[$field])? $data[$field]: $field;
				}

				$mail[$key] = $field;
			}

		}

		$sender_email = isset($mail['sender_email']) ? $mail['sender_email'] : $jConfig->get('mailfrom');
		$sender_name = isset($mail['sender_name']) ? $mail['sender_name'] : $jConfig->get('fromname');
		$recipient = $this->uParams['mailto'] ? $this->uParams['mailto'] : $jConfig->get('mailfrom');
		$subject = (isset($mail['subject']) && !empty($mail['subject'])) ? $mail['subject'] : JText::sprintf('PLG_JT_FORMULATOR_EMAIL_SUBJECT', $jConfig->get('sitename'));

		if($fb) $fb->log($sender_email, '$sender_email');
		if($fb) $fb->log($sender_name, '$sender_name');
		if($fb) $fb->log($subject, '$subject');

		$mailer = JFactory::getMailer();

		if($fb) $fb->info('Lade Quelltext für HTML-E-Mail-Antwort');
		$hBody = $this->_getTmpl($this->_getTmplPath('message_html'));

		if($fb) $fb->info('Lade Quelltext für Plain-E-Mail-Antwort');
		$pBody = $this->_getTmpl($this->_getTmplPath('message_plain'));

		$mailer->setSender(array($sender_email, $sender_name));
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->IsHTML(true);
		$mailer->setBody($hBody);
		$mailer->AltBody = $pBody;

		$send = $mailer->Send();
		if($fb) $fb->log($mailer, '$mailer');
		if($fb) $fb->log($send, '$send');

		if($fb) $fb->groupEnd();
		return $send;

	}

	protected function _getTmplPath($filename, $type ='php')
	{
		$fb =& $this->FB;
		if($fb) $fb->group('JT - Formulator -> ' . __FUNCTION__);

		$template = JFactory::getApplication()->getTemplate();

		// Build the template and base path for the layout
		$tAbsPath = JPATH_THEMES.'/'. $template.'/html/plg_'.$this->_type.'_'.$this->_name.'/'.$this->uParams['theme'].'/'.$filename.'.'.$type;
		if($fb) $fb->log($tAbsPath,'$tAbsPath');
		$bAbsPath = JPATH_BASE.'/plugins/'.$this->_type.'/'.$this->_name.'/tmpl/'.$this->uParams['theme'].'/'.$filename.'.'.$type;
		if($fb) $fb->log($bAbsPath,'$bAbsPath');
		$dAbsPath = JPATH_BASE.'/plugins/'.$this->_type.'/'.$this->_name.'/tmpl/default/'.$filename.'.'.$type;
		if($fb) $fb->log($dAbsPath,'$dAbsPath');

		// If the template has a layout override use it
		if (file_exists($tAbsPath))
		{
			$absReturn = $tAbsPath;
		}
		elseif (file_exists($bAbsPath))
		{
			$absReturn = $bAbsPath;
		}
		else
		{
			$absReturn = $dAbsPath;

			if ($this->uParams['theme'] != 'default' && $type != 'ini' && $filename != 'recaptcha') {
				if($fb) $fb->groupEnd();
				return false;
			}

		}

		if($fb) $fb->groupEnd();

		return $absReturn;

	}

	protected function _getTmpl($path)
	{
		$fb =& $this->FB;
		if($fb) $fb->group('JT - Formulator -> ' . __FUNCTION__ );

		$form = $this->form[$this->uParams['theme']];
        $index = $this->uParams['index'];
		$id = $this->uParams['theme'];

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		// (this will execute the view logic).
		include($path);

		// Done with the requested template; get the buffer and
		// clear it.
		$return = ob_get_contents();
		ob_end_clean();

		if($fb) $fb->log($return, 'Quelltext');
		if($fb) $fb->groupEnd();

		return $return;
	}

}
