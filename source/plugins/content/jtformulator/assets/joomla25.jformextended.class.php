<?php
/**
* @Copyright	(c) 2016 JoomTools.de - All rights reserved.
* @package		JT - Formulator - Plugin for Joomla! 2.5.x and 3.x
* @author		Guido De Gobbis
* @link 		http://www.joomtools.de
*
* @license		GPL v3
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormExtended extends JForm
{
	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since   11.1
	 */
	public function __construct($name, array $options = array())
	{
		parent::__construct( $name, $options );
	}

	/**
	 * Returns the value of an attribute of the form itself
	 *
	 * @param   string  $name     Name of the attribute to get
	 * @param   mixed   $default  Optional value to return if attribute not found
	 *
	 * @return  mixed             Value of the attribute / default
	 *
	 * @since   3.2
	 */
	public function getAttribute($name, $default = null)
	{
		if ($this->xml instanceof SimpleXMLElement)
		{
			$attributes = $this->xml->attributes();

			// Ensure that the attribute exists
			if (property_exists($attributes, $name))
			{
				$value = $attributes->$name;

				if ($value !== null)
				{
					return (string) $value;
				}
			}
		}

		return $default;
	}

	/**
	 * Getter for the form data
	 *
	 * @return   JRegistry  Object with the data
	 *
	 * @since    3.2
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Method to get the XML form object
	 *
	 * @return  SimpleXMLElement  The form XML object
	 *
	 * @since   3.2
	 */
	public function getXml()
	{
		return $this->xml;
	}
}