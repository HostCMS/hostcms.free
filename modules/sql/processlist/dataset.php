<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Sql_Processlist_Dataset extends Admin_Form_Dataset
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
	 * @return array
	 */
	protected function _load()
	{
		/*$condition = NULL;

		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				if ($operator == 'where' && $args[0] == 'name')
				{
					$condition = $args[2];
				}
			}
		}*/

		$aProcesses = $this->_database->getProcesslist();

		$this->_count = count($aProcesses);

		$aProcesses = array_slice($aProcesses, $this->_offset, $this->_limit);
		$this->_objects = array();
		foreach ($aProcesses as $aProcess)
		{
			$oSql_Processlist_Entity = new Sql_Processlist_Entity();
			$oSql_Processlist_Entity->id = $aProcess['ID'];
			$oSql_Processlist_Entity->user = $aProcess['USER'];
			$oSql_Processlist_Entity->host = $aProcess['HOST'];
			$oSql_Processlist_Entity->db = $aProcess['DB'];
			$oSql_Processlist_Entity->command = $aProcess['COMMAND'];
			$oSql_Processlist_Entity->time = $aProcess['TIME'];
			$oSql_Processlist_Entity->state = $aProcess['STATE'];
			$oSql_Processlist_Entity->info = $aProcess['INFO'];

			$this->_objects[$oSql_Processlist_Entity->id] = $oSql_Processlist_Entity;
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
		return new Sql_Processlist_Entity();
	}
}