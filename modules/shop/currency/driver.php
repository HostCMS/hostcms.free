<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Currency_Driver
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Shop_Currency_Driver
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Driver's configuration
	 */
	protected $_config = NULL;

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$aConfig = Core::$config->get('shop_currency_config', array());

			if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
			{
				throw new Core_Exception('Shop_Currency_Driver configuration doesn\'t defined');
			}

			$driver = self::_getDriverName($aConfig[$name]['driver']);

			self::$instance[$name] = new $driver($aConfig[$name]);
		}

		return self::$instance[$name];
	}

	/**
	 * Constructor.
	 * @param array $config config
	 */
	public function __construct(array $config)
	{
		$this->_config = $config;
	}
}