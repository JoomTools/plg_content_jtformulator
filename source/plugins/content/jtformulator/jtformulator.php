<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      Content.jtformulator
 *
 * @author          Guido De Gobbis
 * @copyright   (c) 2016 JoomTools.de - All rights reserved.
 * @license         GNU General Public License version 3 or later
 **/

defined('_JEXEC') or die('Restricted access');

class plgContentJtformulator extends JPlugin
{
	/**
	 * Honeypot
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $honeypot;

	/**
	 * TODO Desctiption
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $issetCaptcha;

	/**
	 * TODO Desctiption
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $validCaptcha = true;

	/**
	 * JFormField validation
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $validField = true;

	/**
	 * Array with JFormField Names of submitted Files
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $fileFields = array();

	/**
	 * Array with submitted Files
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $submitedFiles = array();

	/**
	 * Array with JForm Objects
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $form = array();

	/**
	 * Array with User params
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $uParams = array();

	/**
	 * Mail
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $mail = array();

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $regex = "@(<(\w+)[^>]*>|){jtformulator(\s.*)?}(</\\2>|)@";

	/**
	 * plgContentJtformulator constructor.
	 *
	 * @param   object &$subject
	 * @param   array  $params
	 *
	 * @since   1.0
	 */
	public function __construct(&$subject, $params)
	{
		// Don't run in administration panel
		if (JFactory::getApplication()->isAdmin())
		{
			return;
		}

		parent::__construct($subject, $params);

		$this->resetUserParams();
	}

	/**
	 * Reset user Params to default
	 *
	 * @return  void
	 * @since   1.0
	 */
	protected function resetUserParams()
	{
		$this->uParams             = array();
		$version                   = new JVersion();
		$joomla_main_version       = substr($version->RELEASE, 0, strpos($version->RELEASE, '.'));
		$this->uParams['jversion'] = $joomla_main_version;
	}

	/**
	 * Plugin to generates Forms within content
	 *
	 * @param   string  $context  The context of the content being passed to the plugin.
	 * @param   object  &article  The article object.  Note $article->text is also available
	 * @param   mixed   &$params  The article params
	 * @param   integer $page     The 'page' number
	 *
	 * @return  void
	 * @since  1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$app = JFactory::getApplication();

		// Don't run in administration Panel or when the content is being indexed
		if ($app->isAdmin()
			|| $context == 'com_finder.indexer'
			|| strpos($article->text, '{jtformulator') === false
		)
		{
			return;
		}

		$msg             = '';
		$error_msg       = '';
		$cIndex          = 0;
		$checkThemeFiles = array('fields'        => 'xml',
		                         'form'          => 'php',
		                         'message_html'  => 'php',
		                         'message_plain' => 'php'
		);

		$template = $app->getTemplate();

		// Get all matches or return
		if (!preg_match_all($this->regex, $article->text, $matches))
		{
			return;
		}

		JLoader::register('JFormField', dirname(__FILE__) . '/assets/jformfield.php');

		// add form fields
		JFormHelper::addFieldPath(dirname(__FILE__) . '/assets/fields');
		// add form rules
		JFormHelper::addRulePath(dirname(__FILE__) . '/assets/rules');

		foreach ($matches[0] as $matchKey => $matchValue)
		{
			// Set default framework value
			$this->uParams['framework'] = $this->params->get('framework', 0);

			// Set default captcha value
			$this->uParams['captcha'] = count($matches[0]) ? $this->params->get('captcha') : false;

			// Clear html replace
			$html = '';

			// Set language tag
			$langTag = JFactory::getLanguage()->getTag();

			// Set default recipient
			$uParams['mailto'] = null;

			// User param pairs from plugin call
			$vars = array();

			if (!empty($matches[3][$matchKey]))
			{
				$vars = explode('|', $matches[3][$matchKey]);
			}

			// Get user params as assoc array
			$uParams = $this->setUserParams($vars);

			if (!empty($uParams['mailto']))
			{
				$uParams['mailto'] = str_replace('#', '@', $uParams['mailto']);
			}

			if (empty($uParams['theme']))
			{
				$uParams['theme'] = 'default';
			}

			// Merge user params width default params
			$this->uParams = array_merge($this->uParams, $uParams);

			// Set form counter as index
			$this->uParams['index'] = (int) $cIndex;

			// Define framework as layout suffix
			$layoutSuffix = array();

			if (!empty($this->uParams['framework']))
			{
				$layoutSuffix = array($this->uParams['framework']);
			}

			foreach ($checkThemeFiles as $chkFile => $chkType)
			{
				$_checkTheme[$chkFile . '.' . $chkType] = $this->_getTmplPath($chkFile, $chkType) ? true : false;
			}

			$checkTheme = true;

			if (in_array(true, $_checkTheme) && in_array(false, $_checkTheme))
			{
				$checkTheme = false;

				$app->enqueueMessage(
					JText::sprintf('PLG_JT_FORMULATOR_FILES_ERROR', $this->uParams['theme'])
					, 'error'
				);
			}
			elseif (in_array(false, $_checkTheme))
			{
				$checkTheme = false;

				$app->enqueueMessage(
					JText::sprintf('PLG_JT_FORMULATOR_THEME_ERROR', $this->uParams['theme'])
					, 'error'
				);
			}

			if ($checkTheme)
			{
				$this->honeypot = '<input type="text"';
				$this->honeypot .= ' fieldName="' . $uParams['theme'] . $cIndex . '[information_number]"';
				$this->honeypot .= ' style="position: absolute;top:-999em;left:-999em;height: 0;width: 0;"';
				$this->honeypot .= ' value="" />';

				$formLang = dirname(dirname(dirname(
					$this->_getTmplPath('language/' . $langTag . '/' . $langTag . '.' . $uParams['theme'] . '_form', 'ini')
				)));

				$this->loadLanguage($uParams['theme'] . '_form', $formLang);

				// Define Formfields
				$formXmlPath = $this->_getTmplPath('fields', 'xml');

				$field = new JForm($this->uParams['theme'] . $cIndex, array('control' => $uParams['theme'] . $cIndex));

				// Load Formfields
				$field->loadFile($formXmlPath);

				// Set Formfields
				$this->form[$uParams['theme'] . $cIndex] = $field;

				// Set Layouts override
				$this->form[$uParams['theme'] . $cIndex]->addLayoutsPath = array(
					JPATH_THEMES . '/' . $template . '/html/plg_content_jtformulator/layouts',
					JPATH_THEMES . '/' . $template . '/html/layouts',
					JPATH_PLUGINS . '/content/jtformulator/layouts'
				);

				// Set Framework as Layout->Suffix
				$this->form[$uParams['theme'] . $cIndex]->framework = $layoutSuffix;

				$issetCaptcha = $this->_issetCaptcha();

				if (!$issetCaptcha)
				{
					$setCaptcha   = $this->_setCaptcha();
					$issetCaptcha = $setCaptcha ? 'captcha' : false;
				}

				//$this->issetCaptcha = $issetCaptcha;

				// Remove Captcha if disabled by plugin
				if (!$this->uParams['captcha'] && $issetCaptcha)
				{
					$this->form[$uParams['theme'] . $cIndex]->removeField($issetCaptcha);
				}

				// Get form submit task
				$task = $app->input->get('task', false, 'post');

				if ($task == $this->uParams['theme'] . $cIndex . "_sendmail")
				{
					// Get Form values
					$submitValues = $app->input->get($uParams['theme'] . $cIndex, array(), 'post', 'array');

					foreach ($submitValues as $subKey => $_subValue)
					{
						if (is_array($_subValue))
						{
							$subValue = array();
							foreach ($_subValue as $sValue)
							{
								$subValue[] = JText::_($sValue);
							}
						}
						else
						{
							$subValue = JText::_($_subValue);
						}
						$submitValues[$subKey] = $subValue;
					}

					switch (true)
					{
						case isset($submitValues['subject']):
							$this->mail['subject'] = 'subject';
							break;

						case !isset($submitValues['subject'])
							&& isset($uParams['subject']):
							$submitValues['subject'] = $this->mail['subject'] = $uParams['subject'];
							break;

						default:
							$submitValues['subject'] = $this->mail['subject'] = '';
							break;
					}

					$this->form[$uParams['theme'] . $cIndex]->bind($submitValues);

					if (!$submitValues['information_number'])
					{
						$valid = $this->_validate();
					}
					else
					{
						$valid = false;
					}

				}

				$formHtmlPath = $this->_getTmplPath('form');

				$html .= $this->_getTmpl($formHtmlPath);

				if ($task == $this->uParams['theme'] . $cIndex . "_sendmail")
				{

					if ($valid)
					{
						if ($this->_sendemail())
						{
							$app->enqueueMessage(JText::_('PLG_JT_FORMULATOR_EMAIL_THANKS'), 'message');
							$app->redirect(JRoute::_('index.php', false));
						}
					}
					if ($submitValues['information_number'])
					{
						$app->redirect(JRoute::_('index.php', false));
					}

				}

			}

			$pos = strpos($article->text, $matchValue);
			$end = strlen($matchValue);

			$article->text = substr_replace($article->text, $html, $pos, $end);
			$cIndex++;
			$this->resetUserParams();
		}
	}

	/**
	 * Set user Params
	 * @param   array $vars  Params pairs from Plugin call
	 *
	 * @return  array
	 * @since   1.0
	 */
	protected function setUserParams($vars)
	{
		$uParams = array();

		if (!empty($vars))
		{
			foreach ($vars as $var)
			{
				list($key, $value) = explode('=', trim($var));
				$uParams[$key] = $value;
			}

		}

		return $uParams;
	}

	protected function _getTmplPath($filename, $type = 'php')
	{
		$template = JFactory::getApplication()->getTemplate();
		$file     = $filename . '.' . $type;
		$fileFw   = $filename . '.' . $this->uParams['framework'] . '.' . $type;

		// Build fallback path with default theme
		$dAbsPath = JPATH_PLUGINS . '/content/jtformulator/tmpl/default';

		// Build template override path for theme
		$tAbsPath = JPATH_THEMES . '/' . $template
			. '/html/plg_content_jtformulator/'
			. $this->uParams['theme'];

		// Build plugin path for theme
		$bAbsPath = JPATH_PLUGINS . '/content/jtformulator/tmpl/'
			. $this->uParams['theme'];

		if ($type != 'ini' && $fileFw)
		{
			// Set the theme path
			if (file_exists($tAbsPath . '/' . $fileFw))
			{
				return $tAbsPath . '/' . $fileFw;
			}
			elseif (file_exists($bAbsPath . '/' . $fileFw))
			{
				return $bAbsPath . '/' . $fileFw;
			}
			elseif (file_exists($dAbsPath . '/' . $fileFw))
			{
				return $dAbsPath . '/' . $fileFw;
			}
		}

		// Set the right theme path
		if (file_exists($tAbsPath . '/' . $file))
		{
			return $tAbsPath . '/' . $file;
		}
		elseif (file_exists($bAbsPath . '/' . $file))
		{
			return $bAbsPath . '/' . $file;
		}
		else
		{
			return $dAbsPath . '/' . $file;

			/*			if ($this->uParams['theme'] != 'default' && $type != 'ini')
						{
							return false;
						}*/
		}
	}

	protected function _issetCaptcha()
	{
		$form   = $this->form[$this->uParams['theme'] . $this->uParams['index']];
		$fields = $form->getFieldset();

		foreach ($fields as $field)
		{

			$type = (string) $field->getAttribute('type');

			if ($type == 'captcha')
			{
				return (string) $field->getAttribute('fieldName');
			}
		}

		return false;
	}

	protected function _setCaptcha()
	{
		$form = $this->form[$this->uParams['theme'] . $this->uParams['index']];
		$xml  = '<form><fieldset fieldName="submit"><field fieldName="captcha" type="captcha" validate="captcha" description="JTF_CAPTCHA_DESC" label="JTF_CAPTCHA_LABEL"></field></fieldset></form>';

		return $form->load($xml, false);
	}

	protected function _validate()
	{
		$token    = JSession::checkToken();
		$index    = (int) $this->uParams['index'];
		$fieldXML = $this->form[$this->uParams['theme'] . $index]->getXML();

		foreach ($fieldXML as $fieldset)
		{
			$count = count($fieldset->field);

			if ($count >= 1)
			{
				foreach ($fieldset->field as $field)
				{
					$this->_validateField($field);
				}
			}
		}

		$valid = ($token && $this->validField) ? true : false;

		if (!empty($this->fileFields))
		{
			if ($valid)
			{
				$this->_clearOldFiles();
				$this->_saveFiles();
			}
			else
			{
				foreach ($this->fileFields as $fileField)
				{
					$this->_invalidField($fileField);
				}
			}
		}

		if ($this->validCaptcha !== true)
		{
			$this->_invalidField($this->issetCaptcha);
			$valid = false;
		}

		return $valid;
	}

	protected function _validateField($field)
	{
		$index         = (int) $this->uParams['index'];
		$data          = $this->form[$this->uParams['theme'] . $index]->getData()->toArray();
		$rule          = false;
		$value         = '';
		$showon        = (string) $field['showon'];
		$showField     = true;
		$validateField = true;
		$valid         = false;
		$type          = strtolower((string) $field['type']);
		$validate      = (string) $field['validate'];
		$required      = (string) $field['required'];
		$fieldName     = (string) $field['fieldName'];

		if ($showon)
		{
			$_showon_value    = explode(':', $showon);
			$_showon_value[1] = JText::_($_showon_value[1]);
			$showon_value     = $this->form[$this->uParams['theme'] . $index]->getField($_showon_value[0])->value;

			if ($_showon_value[1] != $showon_value)
			{
				$showField = false;
				$valid     = true;
				$this->form[$this->uParams['theme'] . $index]->setValue($fieldName, null, '');

				if ($type == 'spacer')
				{
					$this->form[$this->uParams['theme'] . $index]->setFieldAttribute($fieldName, 'label', '');
				}
			}
		}

		if (isset($data[$fieldName]))
		{
			$value = $data[$fieldName];
		}

		if ($required && !$value)
		{
			if (!$showField)
			{
				$validateField = false;
			}
		}

		if ($validateField && $showField)
		{
			if ($type == 'file')
			{
				$value = $this->getSubmittedFiles($fieldName);
			}

			if ($field->option)
			{
				$oCount = count($field->option);

				for ($i = 0; $i < $oCount; $i++)
				{
					$_val = (string) $field->option[$i]->attributes()->value;
					if ($_val)
					{
						if (is_array($value))
						{
							$val = in_array(JText::_($_val), $value) ? JText::_($_val) : $_val;
						}
						else
						{
							$val = $value == JText::_($_val) ? $value : $_val;
						}

						$field->option[$i]->attributes()->value = $val;
					}
				}
			}

			if ($type == 'email')
			{
				$field->addAttribute('tld', 'tld');
			}

			if ($validate)
			{
				$rule = JFormHelper::loadRuleType($validate);
			}
			else
			{
				$rule = JFormHelper::loadRuleType($type);
			}

			if ($rule && !empty($value))
			{
				if ($type == 'captcha')
				{
					$valid = $rule->test($field, $value, null, null, $this->form[$this->uParams['theme'] . $index]);

					if ($valid !== true)
					{
						$this->validCaptcha = $valid;
						$this->issetCaptcha = $fieldName;
						$valid              = false;
					}
				}
				else
				{
					$valid = $rule->test($field, $value);
				}
			}
			else
			{
				$valid = $validateField;
			}

		}

		if (!$valid && $type != 'captcha')
		{
			$this->_invalidField($fieldName);
		}
	}

	/**
	 * Get submitted Files
	 *
	 * @param   string $fieldName JFormField Name
	 *
	 * @return  array
	 * @since   1.0
	 */
	protected function getSubmittedFiles($fieldName)
	{
		$value       = null;
		$index       = (int) $this->uParams['index'];
		$jinput      = JFactory::getApplication()->input;
		$submitFiles = $jinput->files->get($this->uParams['theme'] . $index);

		$issetFiles = false;

		if (!empty($submitFiles[$fieldName][0]['fieldName']))
		{
			$issetFiles = true;
			$files      = $submitFiles[$fieldName];
		}
		elseif (!empty($submitFiles[$fieldName]['fieldName']))
		{
			$issetFiles = true;
			$files      = array($submitFiles[$fieldName]);
		}

		if ($issetFiles)
		{
			$value                           = $files;
			$this->submitedFiles[$fieldName] = $files;
			$this->fileFields[]              = $fieldName;
		}

		return $value;
	}

	protected function _invalidField($fieldName)
	{
		$errorClass = $this->params->get('error_class', 'invalid');
		$formName   = $this->uParams['theme'] . $this->uParams['index'];
		$label      = $this->form[$formName]->getFieldAttribute($fieldName, 'label');
		$label      = JText::_($label);
		$class      = $this->form[$formName]->getFieldAttribute($fieldName, 'class');
		$labelClass = $this->form[$formName]->getFieldAttribute($fieldName, 'labelclass');

		if ($fieldName == $this->issetCaptcha)
		{
			$class = $class
				? trim(str_replace($errorClass, '', $class)) . ' '
				: '';

			$labelClass = $labelClass
				? trim(str_replace($errorClass, '', $labelClass)) . ' '
				: '';

			$this->form[$formName]->setFieldAttribute($fieldName, 'class', $class . $errorClass);
			$this->form[$formName]->setFieldAttribute($fieldName, 'labelclass', $labelClass . $errorClass);

			JFactory::getApplication()
				->enqueueMessage((string) $this->validCaptcha, 'error');
		}
		else
		{
			$class = $class
				? trim(str_replace($errorClass, '', $class)) . ' '
				: '';

			$labelClass = $labelClass
				? trim(str_replace($errorClass, '', $labelClass)) . ' '
				: '';

			$this->form[$formName]->setFieldAttribute($fieldName, 'class', $class . $errorClass);
			$this->form[$formName]->setFieldAttribute($fieldName, 'labelclass', $labelClass . $errorClass);

			JFactory::getApplication()
				->enqueueMessage(
					JText::sprintf('PLG_JT_FORMULATOR_FIELD_ERROR', $label), 'error'
				);
		}

		$this->validField = false;
	}

	protected function _clearOldFiles()
	{
		jimport('joomla.filesystem.folder');

		if (!$fileClear = (int) $this->params->get('file_clear'))
		{
			return;
		}

		$filePath   = !$this->params->get('file_path', 'uploads')
			? 'images/uploads'
			: 'images/' . $this->params->get('file_path');
		$uploadBase = JPATH_BASE . '/' . $filePath;

		if (!is_dir($uploadBase))
		{
			return;
		}

		$folders = JFolder::folders($uploadBase);
		$nowPath = date('Ymd');
		$now     = new DateTime($nowPath);

		foreach ($folders as $folder)
		{
			$date   = new DateTime($folder);
			$clrear = date_diff($now, $date)->days;

			if ($clrear >= $fileClear)
			{
				JFolder::delete($uploadBase . '/' . $folder);
			}
		}
	}

	protected function _saveFiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$submitedFiles = $this->submitedFiles;
		$nowPath       = date('Ymd');

		$filePath = !$this->params->get('file_path', 'uploads')
			? 'images/uploads'
			: 'images/' . $this->params->get('file_path');

		$uploadBase = JPATH_BASE . '/' . $filePath . '/' . $nowPath;
		$uploadURL  = rtrim(JUri::base(), '/') . '/' . $filePath . '/' . $nowPath;
		$formName   = $this->uParams['theme'] . $this->uParams['index'];

		if (!is_dir($uploadBase))
		{
			JFolder::create($uploadBase);
		}

		if (!file_exists(JPATH_BASE . '/' . $filePath . '/.htaccess'))
		{
			JFile::write(JPATH_BASE . '/' . $filePath . '/.htaccess', JText::_('PLG_JT_FORMULATOR_SET_ATTACHMENT'));
		}

		foreach ($submitedFiles as $fieldName => $files)
		{
			$value = array();

			foreach ($files as $file)
			{
				$save     = null;
				$fileName = JFile::stripExt($file['fieldName']);
				$fileExt  = JFile::getExt($file['fieldName']);
				$name     = JFilterOutput::stringURLSafe($fileName) . '.' . $fileExt;

				$save = JFile::copy($file['tmp_name'], $uploadBase . '/' . $name);

				if ($save)
				{
					$value[$name] = $uploadURL . '/' . $name;
				}
			}

			$this->form[$formName]->setValue($fieldName, null, $value);
		}
	}

	protected function _getTmpl($path)
	{

		$index = $this->uParams['index'];
		$id    = $this->uParams['theme'];
		$form  = $this->form[$id . $index];

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		// (this will execute the view logic).
		include($path);

		// Done with the requested template; get the buffer and
		// clear it.
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}

	protected function _sendemail()
	{
		$jConfig = JFactory::getConfig();
		$index   = $this->uParams['index'];
		$data    = $this->form[$this->uParams['theme'] . $index]->getData()->toArray();

		if ($this->mail)
		{

			foreach ($this->mail as $key => $field)
			{

				if (is_array($field))
				{

					foreach ($field as $value)
					{
						$_field[] = isset($data[$value]) ? $data[$value] : $value;
					}

					$field = implode(' ', $_field);

					unset($_field);
				}
				else
				{
					$field = isset($data[$field]) ? $data[$field] : $field;
				}

				$mail[$key] = $field;
			}

		}

		$replayToEmail = isset($mail['sender_email']) && !empty($mail['sender_email'])
			? $mail['sender_email']
			: '';

		$replayToName = isset($mail['sender_name']) && !empty($mail['sender_email'])
			? $mail['sender_name']
			: '';

		$recipient = $this->uParams['mailto']
			? $this->uParams['mailto']
			: $jConfig->get('mailfrom');

		$subject = (isset($mail['subject']) && !empty($mail['subject']))
			? $mail['subject']
			: JText::sprintf('PLG_JT_FORMULATOR_EMAIL_SUBJECT', $jConfig->get('sitename'));

		$mailer = JFactory::getMailer();
		$hBody  = $this->_getTmpl($this->_getTmplPath('message_html'));
		$pBody  = $this->_getTmpl($this->_getTmplPath('message_plain'));

		$mailer->setSender(array($jConfig->get('mailfrom'), $jConfig->get('fromname')));

		if (!empty($replayToEmail))
		{
			$mailer->addReplyTo($replayToEmail, $replayToName);
		}

		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->IsHTML(true);
		$mailer->setBody($hBody);
		$mailer->AltBody = $pBody;

		$send = $mailer->Send();

		return $send;
	}
}
