<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_Index_Dataset
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Sql_Table_Index_Dataset extends Admin_Form_Dataset
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

	protected function _getIndexes($name = NULL)
	{
		$aReturn = array();

		try {
			$aIndexes = $this->_database->asAssoc()->getIndexes($this->_tableName, $name);

			foreach ($aIndexes as $indexName => $aIndex)
			{
				$oSql_Table_Index_Entity = new Sql_Table_Index_Entity();
				$oSql_Table_Index_Entity->name = $indexName;
				$oSql_Table_Index_Entity->unique = $aIndex[0]['Non_unique'] == 0;
				$oSql_Table_Index_Entity->packed = $aIndex[0]['Packed'];
				$oSql_Table_Index_Entity->type = $aIndex[0]['Index_type'];
				$oSql_Table_Index_Entity->columns = $aIndex;
				$oSql_Table_Index_Entity->setTableName($this->_tableName);

				$aReturn[$indexName] = $oSql_Table_Index_Entity;
			}
		} catch (Exception $exc) {
			Core_Message::show($exc->getMessage(), "error");
		}

		return $aReturn;
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		if (!$this->_loaded)
		{
			$this->_objects = $this->_getIndexes();

			$this->_count = count($this->_objects);

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
		$aIndexes = $this->_getIndexes($primaryKey);

		if (isset($aIndexes[$primaryKey]))
		{
			return $aIndexes[$primaryKey];
		}

		return $this->getEntity();
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		$object = new Sql_Table_Index_Entity();
		$object->setTableName($this->_tableName);
		return $object;
	}
}