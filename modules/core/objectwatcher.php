<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Implement an Identity Map pattern
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

			self::$config = Core::$config->get('core_objectwatcher') + array(
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

		// Delete old items
		if (/*rand(0, self::$_maxObjects) == 0 && */count($instance->_cache) > self::$_maxObjects)
		{
			$instance->_cache = array_slice($instance->_cache, floor(self::$_maxObjects / 4));

			// Forces collection of any existing garbage cycles
			function_exists('gc_collect_cycles') && gc_collect_cycles();
		}

		$instance->_cache[$instance->getKey($model)] = $model;
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

		$key = $instance->getKey($model);

		if (array_key_exists($key, $instance->_cache))
		{
			unset($instance->_cache[$key]);
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

		$key = $classname . '.' . $primaryKey;

		return isset($instance->_cache[$key])
			? $instance->_cache[$key]
			: NULL;
	}
}
