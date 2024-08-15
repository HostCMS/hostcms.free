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
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
	 * Set unlimitedProperties
	 * @param boolean $value default TRUR
	 * @return self
	 */
	public function setUnlimitedProperties($value = TRUE)
	{
		$this->_unlimitedProperties = $value;
		return $this;
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
		return $this->__set($property, $value);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 * @ignore
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

		throw new Core_Exception("The property '%property' does not exist in the '%class'.",
			array('%property' => $property, '%class' => get_class($this))
		);
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 * @ignore
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

		throw new Core_Exception("The property '%property' does not exist in the '%class'",
			array('%property' => $property, '%class' => get_class($this))
		);
	}

	/**
	 * Triggered when invoking inaccessible methods in an object context
	 * @param string $name method name
	 * @param array $arguments arguments
	 * @return mixed
	 * @ignore
	 */
	public function __call($name, $arguments)
	{
		// Add new property for 'unlimitedProperties' mode
		if ($this->_unlimitedProperties && !isset($this->_allowedProperties[$name]))
		{
			$this->_allowedProperties[$name] = $name;
			$this->_propertiesValues[$name] = NULL;
		}

		if (array_key_exists($name, $this->_propertiesValues))
		{
			if (array_key_exists(0, $arguments))
			{
				$this->$name = $arguments[0];
				return $this;
			}
			throw new Core_Exception("The argument for method '%methodName' does not exist in the '%class'. Available methods: %methods.",
				array('%methodName' => $name, '%class' => get_class($this), '%methods' => implode(', ', array_keys($this->_propertiesValues)))
			);
		}

		throw new Core_Exception("The method '%methodName' does not exist in '%class'",
			array('%methodName' => $name, '%class' => get_class($this)));
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 * @ignore
	 */
	public function __isset($property)
	{
		return array_key_exists($property, $this->_propertiesValues);
	}

	/**
	 * Convert object to string
	 * @return string
	 * @ignore
	 */
	public function __toString()
	{
		$return = array();

		if (!empty($this->_allowedProperties))
		{
			foreach ($this->_allowedProperties as $propertyName)
			{
				// before is_array() cause array() may be static class method call array('MyClass', 'myCallbackMethod')
				if (is_callable($this->$propertyName, FALSE, $callableName))
				{
					$value = $callableName;
				}
				elseif (is_array($this->$propertyName))
				{
					$value = Core_Array::implode('', $this->$propertyName);
				}
				elseif (is_resource($this->$propertyName))
				{
					$value = get_resource_type($this->$propertyName);
				}
				else
				{
					$value = $this->$propertyName;
				}

				$return[] = $propertyName . '=' . $value;
			}

			return implode(",", $return);
		}

		return '';
	}
}