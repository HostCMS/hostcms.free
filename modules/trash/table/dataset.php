<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Trash Table Dataset.
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Trash_Table_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Database instance
	 * @var Core_DataBase
	 */
	protected $_dataBase = NULL;

	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = NULL;

	/**
	 * Constructor.
	 * @param string $tableName table name
	 */
	public function __construct($tableName)
	{
		$this->_dataBase = Core_DataBase::instance();

		$this->_tableName = $tableName;
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			$modelName = Core_Inflection::getSingular($this->_tableName);
			$objects = $this->_newObject($modelName)
				->setMarksDeleted(NULL);

			$objects->queryBuilder()
				->clear()
				->select(array('COUNT(*)', 'count'))
				->from($this->_tableName)
				->where('deleted', '=', 1)
				->limit(1)
				->offset(0)
				->asAssoc();

			$Core_DataBase = $objects->queryBuilder()->execute();

			$row = $Core_DataBase->current();
			$this->_count = $row['count'];
		}

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
			$this->_objects = array();

			$aObjects = $this->_getItems();

			foreach ($aObjects as $key => $oObject)
			{
				$this->_objects[$oObject->id] = $oObject;
			}

			$this->_loaded = TRUE;
		}

		return $this->_objects;
	}

	/**
	 * Load data
	 * @param int $id items ID filter
	 * @return self
	 */
	protected function _getItems($id = NULL)
	{
		$modelName = Core_Inflection::getSingular($this->_tableName);

		if (class_exists($modelName . '_Model'))
		{
			$objects = $this->_newObject($modelName)
				->setMarksDeleted(NULL);

			$objects->queryBuilder()
				->clear()
				->where('deleted', '=', 1)
				->clearOrderBy();

			if ($this->_limit)
			{
				$objects->queryBuilder()
					->limit($this->_limit)
					->offset($this->_offset);
			}

			!is_null($id) && $objects->queryBuilder()
				->where('id', '=', $id);

			$aObjects = $objects->findAll(FALSE);
		}
		else
		{
			$aObjects = array();
		}

		return $aObjects;
	}

	/**
	 * Get new object
	 * @param string $modelName model
	 * @return object
	 */
	protected function _newObject($modelName)
	{
		return Core_Entity::factory($modelName);
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		$modelName = Core_Inflection::getSingular($this->_tableName);
		return $this->_newObject($modelName);
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		$aObjects = $this->_getItems($primaryKey);

		$this->_count = NULL;

		return isset($aObjects[0])
			? $aObjects[0]
			: NULL;
	}

	/**
	 * Clear objects list
	 * @return self
	 */
	public function clear()
	{
		$this->_objects = NULL;
		return $this;
	}

	/**
	 * Get objects
	 * @return array
	 */
	public function getObjects()
	{
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			ini_set("memory_limit", "512M");
			ini_set("max_execution_time", "240");
		}

		!is_array($this->_objects) && $this->load();

		return $this->_objects;
	}
}