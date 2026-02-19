<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Sql_Variable_Dataset extends Admin_Form_Dataset
{
	/**
	 * Database instance
	 * @var Core_DataBase
	 */
	protected $_database = NULL;

	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_database = Core_DataBase::instance();
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		return $this->_count;
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		if (!$this->_loaded)
		{
			$this->_load();

			$this->_loaded = TRUE;
		}

		return $this->_objects;
	}

	/**
	 * Load objects
	 * @return self
	 */
	protected function _load()
	{
		$condition = NULL;

		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				if ($operator == 'where' && $args[0] == 'name')
				{
					$condition = $args[2];
				}
			}
		}

		$aVariables = $this->_database->getVariables($condition);

		$this->_count = count($aVariables);

		$aVariables = array_slice($aVariables, $this->_offset, $this->_limit);
		$this->_objects = array();
		foreach ($aVariables as $aVariable)
		{
			$oSql_Variable_Entity = new Sql_Variable_Entity();
			$oSql_Variable_Entity->name = $aVariable['name'];
			$oSql_Variable_Entity->value = $aVariable['value'];

			$this->_objects[$oSql_Variable_Entity->name] = $oSql_Variable_Entity;
		}

		return $this;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		$this->_load();

		if (isset($this->_objects[$primaryKey]))
		{
			return $this->_objects[$primaryKey];
		}

		return NULL;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return new Sql_Variable_Entity();
	}
}