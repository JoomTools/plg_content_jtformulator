<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @link   http://www.w3.org/TR/html-markup/input.tel.html
 * @see    JFormRuleTel for telephone number validation
 * @see    JHtmlTel for rendering of telephone numbers
 * @since  11.1
 */
class JFormFieldSubmit extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Submit';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $layout = 'joomla.form.field.submit';


	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
	{
		if ($this->hidden)
		{
			return '';
		}

		$data  = parent::getLayoutData();
		$label = $data['label'] == $this->fieldname ? 'JSUBMIT' : $data['label'];

		// Forcing the Alias field to display the tip below
		$position = $this->element['name'] == 'alias' ? ' data-placement="bottom" ' : '';

		// Here mainly for B/C with old layouts. This can be done in the layouts directly
		$extraData = array(
			'text'        => JText::_($label),
			'for'         => $this->id,
			'classes'     => explode(' ', $data['labelclass']),
			'position'    => $position,
		);

		$extraData['classes'][] = 'hidden';

		return $this->getRenderer($this->renderLabelLayout)->render(array_merge($data, $extraData));
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// Trim the trailing line in the layout file
		return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.7.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$label    = $data['label'] == $this->fieldname ? 'JSUBMIT' : $data['label'];

		$extraData = array(
			'label' => JText::_($label),
		);

		return array_merge($data, $extraData);
	}
}
