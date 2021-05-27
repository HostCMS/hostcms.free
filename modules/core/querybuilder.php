<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Query builder Database Abstraction Layer (DBAL). Implement a Builder pattern.
 *
 * http://en.wikipedia.org/wiki/Builder_pattern
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_QueryBuilder
{
	/**
	 * Constructor.
	 * @param string $type type of object
	 */
	protected function __construct($type) {}

	/**
	 * Create and return a Query builder object for type $type
	 * @param string $type
	 * @param array $args
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::factory('Select');
	 * </code>
	 * @return object
	 */
	static public function factory($type, $args)
	{
		$queryBuilderName = __CLASS__ . '_' . ucfirst($type);
		return new $queryBuilderName($args);
	}

	/**
	 * Create and return a SELECT Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select();
	 * </code>
	 * @return Core_QueryBuilder_Select
	 */
	static public function select()
	{
		return Core_QueryBuilder::factory('Select', func_get_args());
	}

	/**
	 * Create and return a INSERT Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert();
	 * </code>
	 * @return Core_QueryBuilder_Insert
	 */
	static public function insert()
	{
		return Core_QueryBuilder::factory('Insert', func_get_args());
	}

	/**
	 * Create and return a REPLACE Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace();
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	static public function replace()
	{
		return Core_QueryBuilder::factory('Replace', func_get_args());
	}

	/**
	 * Create and return an UPDATE Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update();
	 * </code>
	 * @return Core_QueryBuilder_Update
	 */
	static public function update()
	{
		return Core_QueryBuilder::factory('Update', func_get_args());
	}

	/**
	 * Create and return a DELETE Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete();
	 * </code>
	 * @return Core_QueryBuilder_Delete
	 */
	static public function delete()
	{
		return Core_QueryBuilder::factory('Delete', func_get_args());
	}

	/**
	 * Create and return a RENAME Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Rename = Core_QueryBuilder::rename();
	 * </code>
	 * @return Core_QueryBuilder_Rename
	 */
	static public function rename()
	{
		return Core_QueryBuilder::factory('Rename', func_get_args());
	}

	/**
	 * Create and return a DROP Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop();
	 * </code>
	 * @return Core_QueryBuilder_Drop
	 */
	static public function drop()
	{
		return Core_QueryBuilder::factory('Drop', func_get_args());
	}

	/**
	 * Create and return a TRUNCATE Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Truncate = Core_QueryBuilder::truncate();
	 * </code>
	 * @return Core_QueryBuilder_Truncate
	 */
	static public function truncate()
	{
		return Core_QueryBuilder::factory('Truncate', func_get_args());
	}

	/**
	 * Create and return a OPTIMIZE Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Optimize = Core_QueryBuilder::optimize();
	 * </code>
	 * @return Core_QueryBuilder_Optimize
	 */
	static public function optimize()
	{
		return Core_QueryBuilder::factory('Optimize', func_get_args());
	}

	/**
	 * Create and return a LOCK TABLES Database Abstraction Layer
	 * <code>
	 * $oCore_QueryBuilder_Lock = Core_QueryBuilder::lock();
	 * </code>
	 * @return Core_QueryBuilder_Lock
	 */
	static public function lock()
	{
		return Core_QueryBuilder::factory('Lock', func_get_args());
	}

	/**
	 * Create and return an Expression Database Abstraction Layer
	 * @return Core_QueryBuilder_Expression
	 * @see raw()
	 */
	static public function expression()
	{
		return Core_QueryBuilder::factory('Expression', func_get_args());
	}

	/**
	 * Create and return an Expression Database Abstraction Layer
	 * @return Core_QueryBuilder_Expression
	 * @see expression()
	 */
	static public function raw()
	{
		return Core_QueryBuilder::factory('Expression', func_get_args());
	}
}
