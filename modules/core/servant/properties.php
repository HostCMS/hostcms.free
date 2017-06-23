<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Object properties. Implement a design pattern Servant.
 *
 * <code>
 * class Foo extends Core_Servant_Properties
 * {
 * 	protected $_allowedProperties = array(
 * 		'name',
 * 		'value'
 * 	);
 * }
 * $oFoo = new Foo();
 * $oFoo
 * 	->name(123)
 * 	->value('bar');
 * // Property
 * var_dump($oFoo->value);
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array();

	/**
	 * Properties values
	 * @var array
	 */
	protected $_propertiesValues = array();

	/**
	 * Object has unlimited number of properties
	 * @var boolean
	 */
	protected $_unlimitedProperties = FALSE;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (!empty($this->_allowedProperties))
		{
			$this->_allowedProperties = array_combine($this->_allowedProperties, $this->_allowedProperties);
		}

		$this->_propertiesValues = array_fill_keys(array_keys($this->_allowedProperties), NULL);
	}

	/**
	 * Add additional allowed property
	 * @param string $property property name
	 * @return self
	 */
	public function addAllowedProperty($property)
	{
		if (!isset($this->_allowedProperties[$property]))
		{
			$this->_allowedProperties[$property] = $property;
			$this->_propertiesValues[$property] = NULL;
		}

		return $this;
	}

	/**
	 * Add additional allowed properties
	 * @param array $array array of properties' names
	 * @return self
	 */
	public function addAllowedProperties(array $array)
	{
		foreach ($array as $property)
		{
			$this->addAllowedProperty($property);
		}

		return $this;
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function set($property, $value)
	{
		/*if (array_key_exists($property, $this->_propertiesValues))
		{
			$this->_propertiesValues[$property] = $value;
			return $this;
		}

		throw new Core_Exception("The property '%property' does not exist in the entity",
			array('%property' => $property));*/
		return $this->__set($property, $value);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if (array_key_exists($property, $this->_propertiesValues))
		{
			return $this->_propertiesValues[$property];
		}
		elseif ($this->_unlimitedProperties)
		{
			return NULL;
		}

		throw new Core_Exception("The property '%property' does not exist in '%class'.",
			array('%property' => $property, '%class' => get_class($this)));
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function __set($property, $value)
	{
		// Add new property for 'unlimitedProperties' mode
		if ($this->_unlimitedProperties && !isset($this->_allowedProperties[$property]))
		{
			$this->_allowedProperties[$property] = $property;
		}

		if (array_key_exists($property, $this->_propertiesValues) || $this->_unlimitedProperties)
		{
			$this->_propertiesValues[$property] = $value;
			return $this;
		}

		throw new Core_Exception("The property '%property' does not exist in the entity",
			array('%property' => $property));
	}

	/**
	 * Triggered when invoking inaccessible methods in an object context
	 * @param string $name method name
	 * @param array $arguments arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if (array_key_exists($name, $this->_propertiesValues))
		{
			if (array_key_exists(0, $arguments))
			{
				$this->$name = $arguments[0];
				return $this;
			}
			throw new Core_Exception("The argument for method '%methodName' does not exist in '%class'",
				array('%methodName' => $name, '%class' => get_class($this)));
		}

		throw new Core_Exception("The method '%methodName' does not exist in '%class'",
			array('%methodName' => $name, '%class' => get_class($this)));
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		return array_key_exists($property, $this->_propertiesValues);
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		$return = array();

		if (!empty($this->_allowedProperties))
		{
			foreach ($this->_allowedProperties as $key => $value)
			{
				$return[] = $key . '=' . (is_array($this->$value)
					? Core_Array::implode('', $this->$value)
					: $this->$value);
			}

			return implode(",", $return);
		}

		return '';
	}
}