<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Implement an Identity Map pattern
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_ObjectWatcher
{
	/**
	 * Cache
	 * @var array
	 */
	private $_cache = array();

	/**
	 * The singleton instance.
	 * @var mixed
	 */
	static private $_instance;

	/**
	 * Maximum count of objects
	 * Максимальное количество объектов
	 * @var array
	 */
	static protected $_maxObjects = NULL;

	/**
	 * ObjectWatcher config
	 * @var mixed
	 */
	static public $config = NULL;

	/**
	 * Constructor.
	 */
	private function __construct(){}

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (!isset(self::$_instance))
		{
			self::$_instance = new Core_ObjectWatcher();

			self::$config = Core::$config->get('core_objectwatcher', array()) + array(
				'maxObjects' => 512
			);

			self::$_maxObjects = self::$config['maxObjects'];
		}
		return self::$_instance;
	}

	/**
	 * Get primary key of model
	 * @param Core_Entity $model object
	 * @return string
	 */
	public function getKey(Core_Entity $model)
	{
		return get_class($model) . '.' . $model->getPrimaryKey();
	}

	/**
	 * Add instance of $model to cahce
	 * @param Core_Entity $model
	 */
	static public function add(Core_Entity $model)
	{
		$instance = self::instance();
		$databaseDriver = Core_ORM::getDatabaseDriver();

		// Delete old items
		if (/*rand(0, self::$_maxObjects) == 0 && */isset($instance->_cache[$databaseDriver]) && count($instance->_cache[$databaseDriver]) > self::$_maxObjects)
		{
			$instance->reduce();
		}

		$instance->_cache[$databaseDriver][$instance->getKey($model)] = $model;
	}

	/**
	 * Reduce cache
	 */
	public function reduce()
	{
		$databaseDriver = Core_ORM::getDatabaseDriver();
		
		if (isset($this->_cache[$databaseDriver]))
		{
			$this->_cache[$databaseDriver] = array_slice($this->_cache[$databaseDriver], floor(self::$_maxObjects / 4));

			// Forces collection of any existing garbage cycles
			function_exists('gc_collect_cycles') && gc_collect_cycles();
		}
	}

	/**
	 * Clear all instances
	 */
	static public function clear()
	{
		$instance = self::instance();

		$instance->_cache = array();

		// Forces collection of any existing garbage cycles
		function_exists('gc_collect_cycles') && gc_collect_cycles();
	}

	/**
	 * Delete instance of $model from cahce
	 * @param Core_Entity $model
	 */
	static public function delete(Core_Entity $model)
	{
		$instance = self::instance();
		$databaseDriver = Core_ORM::getDatabaseDriver();

		$key = $instance->getKey($model);

		if (isset($instance->_cache[$databaseDriver]) && array_key_exists($key, $instance->_cache[$databaseDriver]))
		{
			unset($instance->_cache[$databaseDriver][$key]);
		}
	}

	/**
	 * Checks if instance of $classname already exist
	 * @param string $classname class name
	 * @param string $primaryKey primary key
	 * @return mixed
	 */
	static public function exists($classname, $primaryKey)
	{
		$instance = self::instance();
		$databaseDriver = Core_ORM::getDatabaseDriver();

		$key = $classname . '.' . $primaryKey;

		return isset($instance->_cache[$databaseDriver][$key])
			? $instance->_cache[$databaseDriver][$key]
			: NULL;
	}
}
