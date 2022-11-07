<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_Field_Dataset extends Admin_Form_Dataset
{
	/**
	 * Database instance
	 * @var Core_DataBase
	 */
	protected $_database = NULL;

	/**
	 * Table name
	 * @var mixed
	 */
	protected $_tableName = NULL;

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

	public function table($tableName)
	{
		$this->_tableName = $tableName;
		return $this;
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
			try {
				$aSql_Table_Field_Entities = $this->_database->asObject('Sql_Table_Field_Entity')->getFullColumns($this->_tableName);

				$this->_count = count($aSql_Table_Field_Entities);

				$aSql_Table_Field_Entities = array_slice($aSql_Table_Field_Entities, $this->_offset, $this->_limit);
				foreach ($aSql_Table_Field_Entities as $oSql_Table_Field_Entity)
				{
					$this->_objects[$oSql_Table_Field_Entity->Field] = $oSql_Table_Field_Entity->setTableName($this->_tableName);
				}
			} catch (Exception $exc) {
				Core_Message::show($exc->getMessage(), "error");
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
			if ($primaryKey)
			{
				$aSql_Table_Field_Entities = $this->_database->asObject('Sql_Table_Field_Entity')->getFullColumns($this->_tableName, $primaryKey);
				if (count($aSql_Table_Field_Entities) == 1)
				{
					return $aSql_Table_Field_Entities[0]->setTableName($this->_tableName);
				}
			}
		}

		return $this->getEntity();
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		$object = new Sql_Table_Field_Entity();
		$object->setTableName($this->_tableName);
		return $object;
	}
}