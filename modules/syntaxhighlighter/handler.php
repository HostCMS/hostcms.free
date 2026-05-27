<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter_Handler
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Syntaxhighlighter_Handler
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return string
	 */
	static protected function _getDriverName($driver)
	{
		return 'Syntaxhighlighter_Driver_' . ucfirst($driver) . '_Handler';
	}

	/**
	 * Create and return an object of syntaxhighlighter
	 * @param string $driveName
	 * @param mixed $primaryKey Primary key
	 */
	static public function factory($driverName/*, $primaryKey = NULL*/)
	{
		$driver = self::_getDriverName($driverName);
		
		if (!class_exists($driver))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $driver));
		}
		
		return new $driver();
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param Syntaxhighlighter_Model $oSyntaxhighlighter
	 * @return object
	 */
	static public function instance(Syntaxhighlighter_Model $oSyntaxhighlighter)
	{
		if (!is_object($oSyntaxhighlighter))
		{
			throw new Core_Exception('Wrong argument type (expected Syntaxhighlighter_Model)');
		}

		if (!isset(self::$instance[$oSyntaxhighlighter->id]))
		{
			$name = $oSyntaxhighlighter->driver;

			self::$instance[$oSyntaxhighlighter->id] = self::factory($name);
		}

		return self::$instance[$oSyntaxhighlighter->id];
	}

	/**
	 * Init
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Textarea
	 */
	abstract public function init($oAdmin_Form_Entity_Textarea);

	/**
	 * Get driver js list
	 * @return array
	 */
	abstract public function getJsList();

	/**
	 * Get driver css list
	 * @return array
	 */
	abstract public function getCssList();

	/**
	 * Get driver raw js
	 * @return array
	 */
	abstract public function getJs();

	/**
	 * Get driver syntaxhighlighter options config
	 * @return array|NULL
	 */
	abstract public function getConfig();
}