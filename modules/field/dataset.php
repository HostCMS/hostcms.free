<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field Dataset.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Dataset extends Admin_Form_Dataset
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
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_dataBase = Core_DataBase::instance();
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			!$this->_loaded && $this->fillTables();

			$this->_count = count($this->_objects);
		}

		return $this->_count;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject()
	{
		return new Field_Entity();
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		!$this->_loaded && $this->fillTables();

		return array_slice($this->_objects, $this->_offset, $this->_limit);
	}

	/**
	 * Load data
	 * @return self
	 */
	public function fillTables()
	{
		$this->_loaded = TRUE;

		$this->_objects = array();

		//$aTables = $this->_dataBase->getTables();
		$aTables = $this->_dataBase->query('SHOW TABLE STATUS')->asAssoc()->result();

		$aDbConfig = $this->_dataBase->getConfig();

		$condition = NULL;

		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				if ($operator == 'where' && $args[0] == 'table_name')
				{
					$condition = $args[2];
				}
			}
		}

		$query = 'SELECT `TABLE_NAME`, `COLUMN_NAME` FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_schema` = ' . $this->_dataBase->quote($aDbConfig['database']);

		!is_null($condition)
			&& $query .= ' AND `TABLE_NAME` LIKE ' . $this->_dataBase->quote($condition);

		// information about all columns in all tables
		$aAllColumns = $this->_dataBase
			->query($query)
			->asAssoc()
			->result();

		$aTableColumns = array();
		foreach ($aAllColumns as $aColumn)
		{
			$aTableColumns[$aColumn['TABLE_NAME']][] = $aColumn['COLUMN_NAME'];
		}

		foreach ($aTables as $key => $aTableRow)
		{
			$sEngine = strtoupper(Core_Array::get($aTableRow, 'Engine', '', 'str'));
			$sComment = Core_Array::get($aTableRow, 'Comment');

			if ($sEngine != '' && $sComment != 'VIEW')
			{
				$name = Core_Array::get($aTableRow, 'Name');

				$id = $key + 1;

				if (isset($aTableColumns[$name]) && in_array('deleted', $aTableColumns[$name]) && strpos($name, '~') !== 0)
				{
					$oField_Entity = $this->_objects[$id] = $this->_newObject();

					$singular = Core_Inflection::getSingular($name);

					$oField_Entity->setTableColums(array(
						'id' => array(),
						'table_name' => array(),
						'model' => array(),
						'name' => array(),
						'count' => array(),
					));

					$oField_Entity->id = $id;
					$oField_Entity->table_name = $name;
					$oField_Entity->model = $singular;

					try {
						$oField_Entity->name = Core::_($singular . '.model_name');
					}
					catch (Exception $e) {
						$oField_Entity->name = 'Unknown';
					}

					$oField_Entity->count = Core_Entity::factory('Field')->getCountByModel($oField_Entity->model);
				}
			}
		}

		return $this;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_newObject();
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		!$this->_loaded && $this->fillTables();

		return isset($this->_objects[$primaryKey])
			? $this->_objects[$primaryKey]
			: $this->_newObject();
	}
}