<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Implement a Registry pattern
 *
 * <code>
 * $value = 1;
 * Core_Registry::instance()->set('My_Key', $value);
 * </code>
 *
 * <code>
 * echo Core_Registry::instance()->get('My_Key');
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Registry {

	/**
	 * The singleton instance.
	 * @var mixed
	 */
	static private $_instance = NULL;

	/**
	 * List of values
	 * @var array
	 */
	private $_values = array();

	/**
	 * Constructor.
	 */
	protected function __construct() { }

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (!isset(self::$_instance))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get value for key
	 *
	 * <code>
	 * $oCore_Registry = Core_Registry::instance();
	 * $value = $oCore_Registry->get('My_Key');
	 * </code>
	 *
	 * @param string $key Key
	 * @param string $defaultValue Default value
	 * @return mixed value or NULL
	 */
	public function get($key, $defaultValue = NULL)
	{
		return isset($this->_values[$key])
			? $this->_values[$key]
			: $defaultValue;
	}

	/**
	 * Set value for key
	 *
	 * <code>
	 * $oCore_Registry = Core_Registry::instance();
	 * $value = 1;
	 * $oCore_Registry->set('My_Key', $value);
	 * </code>
	 * @param string $key
	 * @param mixed $value
	 * @return Core_Registry
	 */
	public function set($key, $value)
	{
		$this->_values[$key] = $value;
		return $this;
	}

	/**
	 * Delete value for key
	 *
	 * <code>
	 * $oCore_Registry = Core_Registry::instance();
	 * $oCore_Registry->delete('My_Key');
	 * </code>
	 *
	 * @param string $key
	 * @return Core_Registry
	 */
	public function delete($key)
	{
		if (isset($this->_values[$key]))
		{
			unset($this->_values[$key]);
		}

		return $this;
	}
}
