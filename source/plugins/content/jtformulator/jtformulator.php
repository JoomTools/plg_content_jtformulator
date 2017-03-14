<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.jtformulator
 *
 * @author      Guido De Gobbis
 * @copyright   (c) 2017 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
**/

defined('_JEXEC') or die('Restricted access');

class plgContentJtformulator extends JPlugin
{
	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var     string
	 * @since   1.0
	 */
	const PLUGIN_REGEX = "@(<(\w+)[^>]*>|){jtformulator(\s.*)?}(</\\2>|)@";

	/**
	 * Honeypot
	 *
	 * @var     string
	 * @since   1.0
	 */
	protected $honeypot;

	/**
	 * TODO Desctiption
	 *
	 * @var     string
	 * @since   1.0
	 */
	protected $issetCaptcha;

	/**
	 * TODO Desctiption
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $validCaptcha = true;

	/**
	 * JFormField validation
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $validField = true;

	/**
	 * Array with JFormField Names of submitted Files
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $fileFields = array();

	/**
	 * Array with submitted Files
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $submitedFiles = array();

	/**
	 * Array with JForm Objects
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $form = array();

	/**
	 * Array with User params
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $uParams = array();

	/**
	 * Mail
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $mail = array();

	/**
	 * Pathlist for searching layouts
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected $addLayoutsPath = array();

	/**
	 * Debug
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	protected $debug = false;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Plugin to generates Forms within content
	 *
	 * @param    string   $context  The context of the content being passed to the plugin.
	 * @param    object   &article  The article object.  Note $article->text is also available
	 * @param    mixed    &$params  The article params
	 * @param    integer  $page     The 'page' number
	 *
	 * @return   void
	 * @since    1.6
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

		$msg       = '';
		$error_msg = '';
		$this->debug = (boolean) $this->params->get('debug', 0);
		$cIndex    = 0;
		$template  = $app->getTemplate();
		$lang      = JFactory::getLanguage();
		$langTag   = $lang->getTag();

		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX, $article->text, $matches))
		{
			return;
		}

		$pluginReplacements = $matches[0];
		$userParams         = $matches[3];

		//JLoader::register('JFormField', dirname(__FILE__) . '/assets/jformfield.php');

		// Add form fields
		JFormHelper::addFieldPath(dirname(__FILE__) . '/assets/fields');

		// Add form rules
		JFormHelper::addRulePath(dirname(__FILE__) . '/assets/rules');

		foreach ($pluginReplacements as $rKey => $replacement)
		{
			// Clear html replace
			$html = '';

			$this->resetUserParams();

			if (!empty($userParams[$rKey]))
			{
				$vars = explode('|', $userParams[$rKey]);

				// Set user params
				$this->setUserParams($vars);
			}

			// Set Layouts override
			$this->addLayoutsPath = array(
				JPATH_THEMES . '/' . $template . '/html/plg_content_jtformulator/' . $this->uParams['theme'],
				JPATH_THEMES . '/' . $template . '/html/layouts/plugin/content/jtformulator',
				JPATH_THEMES . '/' . $template . '/html/layouts',
				JPATH_PLUGINS . '/content/jtformulator/layouts/jtformulator',
				JPATH_PLUGINS . '/content/jtformulator/layouts'
			);

			// Set form counter as index
			$this->uParams['index'] = (int) $cIndex;

			$formTheme = $this->uParams['theme'] . $cIndex;

			$formXmlPath = $this->getFieldsFile();

			if (!empty($formXmlPath))
			{
				$this->honeypot = '<input type="text"';
				$this->honeypot .= ' name="' . $formTheme . '[information_number]"';
				$this->honeypot .= ' style="position: absolute;top:-999em;left:-999em;height: 0;width: 0;"';
				$this->honeypot .= ' value="" />';

				$formLang = dirname(
					dirname(
						dirname(
							$this->getLanguagePath('language/' . $langTag . '/' . $langTag . '.jtf_theme.ini')
						)
					)
				);

				$this->loadLanguage('jtf_global',JPATH_PLUGINS . '/content/jtformulator/assets');

				//$lang->load('JTF_theme', $formLang);
				$lang->load('jtf_theme', $formLang);

				$form = new JForm($formTheme, array('control' => $formTheme));

				// Load Formfields
				$form->loadFile($formXmlPath);

				// Set Formfields
				$this->form[$formTheme] = $form;

				// Define framework as layout suffix
				$layoutSuffix = array('');

				if (!empty($this->uParams['framework']))
				{
					$layoutSuffix = array($this->uParams['framework']);
				}

				// Set Framework as Layout->Suffix
				$this->form[$formTheme]->framework = $layoutSuffix;

				$this->setFrameworkFieldClass();

				$issetCaptcha = $this->issetCaptcha();

				if (!$issetCaptcha)
				{
					$setCaptcha   = $this->setCaptcha();
					$issetCaptcha = $setCaptcha ? 'captcha' : false;
				}

				$this->issetCaptcha = $issetCaptcha;

				// Remove Captcha if disabled by plugin
				if (!$this->uParams['captcha'] && $issetCaptcha)
				{
					$this->form[$formTheme]->removeField($issetCaptcha);
				}

				// Get form submit task
				$task = $app->input->get('task', false, 'post');

				if ($task == $formTheme . "_sendmail")
				{
					$submitValues = $this->getTranslatedSubmittedFormValues();

					switch (true)
					{
						case isset($submitValues['subject']):
							$this->mail['subject'] = 'subject';
							break;

						case !isset($submitValues['subject'])
							&& isset($this->uParams['subject']):
							$submitValues['subject'] = $this->mail['subject'] = $this->uParams['subject'];
							break;

						default:
							$submitValues['subject'] = $this->mail['subject'] = '';
							break;
					}

					$this->form[$formTheme]->bind($submitValues);

					if ($submitValues['information_number'] == '')
					{
						$valid = $this->validate();
					}
					else
					{
						$valid = false;
					}

				}

				$html .= $this->getTmpl('form');

				if ($task == $formTheme . "_sendmail")
				{
					$sendmail = $this->sendMail();
					if ($valid && $sendmail)
					{
						$app->enqueueMessage(JText::_('JTF_EMAIL_THANKS'), 'message');
						$app->redirect(JRoute::_('index.php', false));
					}

					if (!empty($submitValues['information_number']))
					{
						$app->redirect(JRoute::_('index.php', false));
					}

				}

			}

			$pos = strpos($article->text, $replacement);
			$end = strlen($replacement);

			$article->text = substr_replace($article->text, $html, $pos, $end);
			$cIndex++;
			$this->resetUserParams();
		}
	}

	/**
	 * Reset user Params to default
	 *
	 * @return   void
	 * @since    1.0
	 */
	protected function resetUserParams()
	{
		$this->uParams       = array();
		$version             = new JVersion();
		$joomla_main_version = substr($version->RELEASE, 0, strpos($version->RELEASE, '.'));

		// Set default captcha value
		$this->uParams['captcha'] = $this->params->get('captcha');

		// Set Joomla main version
		$this->uParams['jversion'] = $joomla_main_version;

		// Set default recipient
		$this->uParams['mailto'] = JFactory::getConfig()->get('mailfrom');

		// Set default theme
		$this->uParams['theme'] = 'default';

		// Set default framework value
		$this->uParams['framework'] = $this->params->get('framework', 0);
	}

	/**
	 * Set user Params
	 *
	 * @param   array $vars Params pairs from Plugin call
	 *
	 * @return   array
	 * @since    1.0
	 */
	protected function setUserParams($vars)
	{
		$uParams = array();

		if (!empty($vars))
		{
			foreach ($vars as $var)
			{
				list($key, $value) = explode('=', trim($var));
				$uParams[trim($key)] = trim($value);
			}

		}

		if (!empty($uParams['mailto']))
		{
			$uParams['mailto'] = str_replace('#', '@', $uParams['mailto']);
		}

		if (!empty($uParams['sender']))
		{
			$this->mail['sender'] = explode(' ', $uParams['sender']);
			unset($uParams['sender']);
		}

		// Merge user params width default params
		$this->uParams = array_merge($this->uParams, $uParams);

		return $uParams;
	}

	protected function getLanguagePath($filename)
	{
		$template = JFactory::getApplication()->getTemplate();

		// Build template override path for theme
		$tAbsPath = JPATH_THEMES . '/' . $template
			. '/html/plg_content_jtformulator/'
			. $this->uParams['theme'];

		// Build plugin path for theme
		$bAbsPath = JPATH_PLUGINS . '/content/jtformulator/tmpl/'
			. $this->uParams['theme'];

		// Set the right theme path
		if (file_exists($tAbsPath . '/' . $filename))
		{
			return $tAbsPath . '/' . $filename;
		}

		if (file_exists($bAbsPath . '/' . $filename))
		{
			return $bAbsPath . '/' . $filename;
		}

		return false;
	}

	protected function issetCaptcha()
	{
		$form   = $this->form[$this->uParams['theme'] . $this->uParams['index']];
		$fields = $form->getFieldset();

		foreach ($fields as $field)
		{
			$type = (string) $field->getAttribute('type');

			if ($type == 'captcha')
			{
				return (string) $field->getAttribute('name');
			}
		}

		return false;
	}

	protected function setCaptcha()
	{
		$form = $this->form[$this->uParams['theme'] . $this->uParams['index']];
		$xml  = '<form><fieldset name="submit"><field name="captcha" type="captcha" validate="captcha" description="JTF_CAPTCHA_DESC" label="JTF_CAPTCHA_LABEL"></field></fieldset></form>';

		return $form->load($xml, false);
	}

	/**
	 * Get and translate submitted Form values
	 *
	 * @param    array   $submittedValues
	 * @return   array
	 * @since    1.0
	 */
	protected function getTranslatedSubmittedFormValues($submittedValues = array())
	{
		$app       = JFactory::getApplication();
		$formTheme = $this->uParams['theme'] . $this->uParams['index'];

		// Get Form values
		if (empty($submittedValues))
		{
			$submittedValues = $app->input->get($formTheme, array(), 'post', 'array');
		}

		foreach ($submittedValues as $subKey => $_subValue)
		{
			if (is_array($_subValue))
			{
				$subValue = $this->getTranslatedSubmittedFormValues($_subValue);
			}
			else
			{
				$subValue = JText::_($_subValue);
			}

			$submittedValues[$subKey] = $subValue;
		}

		return $submittedValues;
	}

	protected function validate()
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
					$this->validateField($field);
				}
			}
		}

		$valid = ($token && $this->validField) ? true : false;

		if (!empty($this->fileFields))
		{
			if ($valid)
			{
				$this->clearOldFiles();
				$this->saveFiles();
			}
			else
			{
				foreach ($this->fileFields as $fileField)
				{
					$this->invalidField($fileField);
				}
			}
		}

		if ($this->validCaptcha !== true)
		{
			$this->invalidField($this->issetCaptcha);
			$valid = false;
		}

		return $valid;
	}

	protected function validateField($field)
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
		$fieldName     = (string) $field['name'];

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
				if ($required || !empty($value))
				{
					$this->mail['sender_email'] = 'email';
				}
			}

			if ($validate)
			{
				$rule = JFormHelper::loadRuleType($validate);
			}
			else
			{
				$rule = JFormHelper::loadRuleType($type);
			}

			if (!empty($rule) && $required)
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
			$this->invalidField($fieldName);
		}
	}

	/**
	 * Get submitted Files
	 *
	 * @param   string $fieldName JFormField Name
	 *
	 * @return   array
	 * @since    1.0
	 */
	protected function getSubmittedFiles($fieldName)
	{
		$value       = null;
		$index       = (int) $this->uParams['index'];
		$jinput      = JFactory::getApplication()->input;
		$submitFiles = $jinput->files->get($this->uParams['theme'] . $index);

		$issetFiles = false;

		if (!empty($submitFiles[$fieldName][0]['name']))
		{
			$issetFiles = true;
			$files      = $submitFiles[$fieldName];
		}
		elseif (!empty($submitFiles[$fieldName]['name']))
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

	protected function invalidField($fieldName)
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
					JText::sprintf('JTF_FIELD_ERROR', $label), 'error'
				);
		}

		$this->validField = false;
	}

	protected function clearOldFiles()
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

	protected function saveFiles()
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
			JFile::write(JPATH_BASE . '/' . $filePath . '/.htaccess', JText::_('JTF_SET_ATTACHMENT_HTACCESS'));
		}

		foreach ($submitedFiles as $fieldName => $files)
		{
			$value = array();

			foreach ($files as $file)
			{
				$save     = null;
				$fileName = JFile::stripExt($file['name']);
				$fileExt  = JFile::getExt($file['name']);
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

	protected function getTmpl($filename)
	{
		$index = $this->uParams['index'];
		$id    = $this->uParams['theme'];
		$form  = $this->form[$id . $index];

		$displayData = array(
			'id' => $id,
			'index' => (int) $index,
			'honeypot' => $this->honeypot,
			'fileClear' => $this->params->get('file_clear'),
			'form' => $form
		);

		$renderer = new JLayoutFile($filename);

		// Set Framwork as Layout->Suffix
		if (!empty($this->uParams['framework']))
		{
			if (!method_exists($renderer, 'setSuffixes'))
			{
				unset($renderer);
				JLoader::register('JTLayoutFile', dirname(__FILE__) . '/assets/file.php');
				$renderer = new JTLayoutFile($filename);
			}

			$renderer->setSuffixes(array($this->uParams['framework']));
		}

		$renderer->setIncludePaths($this->addLayoutsPath);
		$renderer->setDebug($this->debug);

		return $renderer->render($displayData);
	}

	protected function sendMail()
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

		$replayToEmail = !empty($mail['sender_email'])
			? $mail['sender_email']
			: '';

		$replayToName = !empty($mail['sender'])
			? $mail['sender']
			: '';

		$recipient = $this->uParams['mailto'];

		$subject = (!empty($mail['subject']))
			? $mail['subject']
			: JText::sprintf('JTF_EMAIL_SUBJECT', $jConfig->get('sitename'));

		$mailer = JFactory::getMailer();
		$hBody  = $this->getTmpl('message.html');
		$pBody  = $this->getTmpl('message.plain');

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

	/**
	 * Checks if all needed files for Forms are found
	 *
	 * @return   bool
	 * @since    1.0
	 */
	protected function getFieldsFile()
	{
		$app      = JFactory::getApplication();
		$template = $app->getTemplate();
		$framework = ($this->uParams['framework'] != 0) ? '.' . $this->uParams['framework'] : '';
		$file = 'fields' . $framework . '.xml';

		$formPath = array(
			JPATH_THEMES . '/' . $template . '/html/plg_content_jtformulator/' . $this->uParams['theme'],
			JPATH_PLUGINS . '/content/jtformulator/tmpl/' . $this->uParams['theme']
		);

		foreach ($formPath as $path)
		{
			if (file_exists($path . '/' . $file))
			{
				return $path . '/' . $file;
			}
		}

		$app->enqueueMessage(
			JText::sprintf('JTF_THEME_ERROR', $this->uParams['theme'])
			, 'error'
		);

		return false;
	}

	protected function setFrameworkFieldClass()
	{
		$theme   = $this->uParams['theme'] . (int) $this->uParams['index'];
		$form   = $this->form[$theme];
		$framework = 'default';

		if (!empty($this->form[$theme]->framework[0]))
		{
			$framework = $this->form[$theme]->framework[0];
		}

		$classes = array();

		$classes['default']['type'][] = 'checkbox';
		$classes['default']['type'][] = 'checkboxes';
		$classes['default']['class']['default']   = 'input';
		$classes['default']['class']['gridgroup'] = 'control-group';
		$classes['default']['class']['gridlabel'] = 'control-label';
		$classes['default']['class']['gridfield'] = 'controls';
		$classes['default']['class'][][]           = 'checkbox';
		$classes['default']['class'][][]            = 'checkbox';

		$classes['bs3']['type'][] = 'checkbox';
		$classes['bs3']['class']['default']   = 'input';
		$classes['bs3']['class']['gridgroup'] = 'control-group';
		$classes['bs3']['class']['gridlabel'] = 'control-label';
		$classes['bs3']['class']['gridfield'] = 'controls';
		$classes['bs3']['class'][][]           = 'checkbox';
		$classes['bs3']['class'][][]            = 'checkbox';

		$classes['bs4']['type'][] = 'checkbox';
		$classes['bs4']['class']['default']   = 'input';
		$classes['bs4']['class']['gridgroup'] = 'control-group';
		$classes['bs4']['class']['gridlabel'] = 'control-label';
		$classes['bs4']['class']['gridfield'] = 'controls';
		$classes['bs4']['class'][][]           = 'checkbox';
		$classes['bs4']['class'][][]            = 'checkbox';

		$classes['uikit']['type'][] = 'checkbox';
		$classes['uikit']['type'][] = 'checkboxes';
		$classes['uikit']['type'][] = 'radio';
		$classes['uikit']['type'][] = 'textarea';
		$classes['uikit']['type'][] = 'list';

		$classes['uikit']['class']['default']   = 'uk-input';
		$classes['uikit']['class']['gridgroup'] = 'uk-margin';
		$classes['uikit']['class']['gridlabel'] = 'uk-form-label';
		$classes['uikit']['class']['gridfield'] = 'uk-form-controls';
		$classes['uikit']['class'][]            = array('', 'option' => 'uk-checkbox');
		$classes['uikit']['class'][]            = array('', 'option' => 'uk-checkbox');
		$classes['uikit']['class'][]            = array('', 'option' => 'uk-radio');
		$classes['uikit']['class'][][]          = 'uk-textarea';
		$classes['uikit']['class'][][]          = 'uk-select';

		$classes['uikit3']['type'][] = 'checkbox';
		$classes['uikit3']['type'][] = 'checkboxes';
		$classes['uikit3']['type'][] = 'radio';
		$classes['uikit3']['type'][] = 'textarea';
		$classes['uikit3']['type'][] = 'list';

		$classes['uikit3']['class']['default']   = 'uk-input';
		$classes['uikit3']['class']['gridgroup'] = 'uk-margin';
		$classes['uikit3']['class']['gridlabel'] = 'uk-form-label';
		$classes['uikit3']['class']['gridfield'] = 'uk-form-controls';
		$classes['uikit3']['class'][]            = array('', 'option' => 'uk-checkbox');
		$classes['uikit3']['class'][]            = array('', 'option' => 'uk-checkbox');
		$classes['uikit3']['class'][]            = array('', 'option' => 'uk-radio');
		$classes['uikit3']['class'][][]          = 'uk-textarea';
		$classes['uikit3']['class'][][]          = 'uk-select';

		$fields = $form->getFieldset();

		foreach ($fields as $field)
		{
			$this->setFieldClass($field->fieldname, $classes[$framework]);
		}
	}

	protected function setFieldClass($fieldname, $classes)
	{
		$theme     = $this->uParams['theme'] . (int) $this->uParams['index'];
		$form      = $this->form[$theme];
		$gridgroup = $gridlabel = $gridfield = array();

		$field = $form->getField($fieldname);
		$type = strtolower((string) $field->getAttribute('type'));

		if (in_array($type, array('checkbox', 'checkboxes', 'radio')))
		{
			$field->setOptionsClass($classes);
		}


		$class = array((string) $form->getFieldAttribute($fieldname, 'class'));
		$key = array_search($type, $classes['type'], true);

		if ($key !== false)
		{
			$class[] = $classes['class'][$key][0];
		}
		else
		{
			$class[] = $classes['class']['default'];
		}

		$form->setFieldAttribute($fieldname, 'class', implode(' ', $class));


		$gridgroup = array((string) $form->getFieldAttribute($fieldname, 'gridgroup'), $classes['class']['gridgroup']);
		$gridlabel = array((string) $form->getFieldAttribute($fieldname, 'gridlabel'), $classes['class']['gridlabel']);
		$gridfield = array((string) $form->getFieldAttribute($fieldname, 'gridfield'), $classes['class']['gridfield']);

		$form->setFieldAttribute($fieldname, 'gridgroup', implode(' ', $gridgroup));
		$form->setFieldAttribute($fieldname, 'gridlabel', implode(' ', $gridlabel));
		$form->setFieldAttribute($fieldname, 'gridfield', implode(' ', $gridfield));

		return;
	}
}
