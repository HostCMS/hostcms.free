<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_Dataset extends Admin_Form_Dataset
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
	 * Dataset objects list
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		if (!$this->_loaded)
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

			$aSql_Tables = $this->_database->asObject('Sql_Table_Entity')->getTablesSchema($condition);

			$this->_count = count($aSql_Tables);

			$aSql_Tables = array_slice($aSql_Tables, $this->_offset, $this->_limit);
			foreach ($aSql_Tables as $oSql_Table)
			{
				$this->_objects[$oSql_Table->name] = $oSql_Table;
			}

			$this->_loaded = TRUE;
		}

		return $this->_objects;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		if (isset($this->_objects[$primaryKey]))
		{
			return $this->_objects[$primaryKey];
		}
		else
		{
			$aSql_Tables = $this->_database->asObject('Sql_Table_Entity')->getTablesSchema($primaryKey);
			if (count($aSql_Tables) == 1)
			{
				return $aSql_Tables[0];
			}
		}

		return NULL;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		$object = new Sql_Table_Entity();
		//$object->Table = $object->Op = $object->Msg_type = $object->Msg_text = '';
		return $object;
	}
}