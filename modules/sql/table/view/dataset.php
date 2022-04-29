<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Dataset_View
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_View_Dataset extends Admin_Form_Dataset
{
	/**
	 * Entity of dataset
	 * @var object
	 */
	protected $_entity = NULL;

	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	protected $_tableName = NULL;

	public function table($tableName)
	{
		$this->_tableName = $tableName;
		return $this;
	}

	public function __construct()
	{
		$this->_entity = new Sql_Table_View_Entity;
	}

	/**
	 * Check if entity conditions consist of having/groupBy
	 * @return boolean
	 */
	protected function _issetHavingOrGroupBy()
	{
		$issetHaving = FALSE;
		foreach ($this->_conditions as $condition)
		{
			if (isset($condition['having']) || isset($condition['groupBy']))
			{
				$issetHaving = TRUE;
				break;
			}
		}

		return $issetHaving;
	}

	/**
	 * Check if entity conditions consist of where
	 * @return boolean
	 */
	protected function _issetWhere()
	{
		$issetWhere = FALSE;
		foreach ($this->_conditions as $condition)
		{
			if (isset($condition['where']))
			{
				$issetWhere = TRUE;
				break;
			}
		}

		return $issetWhere;
	}

	/**
	 * Get FOUND_ROWS
	 * @return int
	 */
	protected function _getFoundRows()
	{
		// Warning
		if (!is_null(Core_Array::getRequest('debug')))
		{
			echo '<p><b>Query FOUND_ROWS</b>.</p>';
		}

		return Core_QueryBuilder::select()->getFoundRows();
	}

	/**
	 * Get total count by COUNT(*)
	 * @return int
	 */
	protected function _getTotalCountByCount()
	{
		$aSql_Tables = Core_DataBase::instance()->asObject('Sql_Table_Entity')->getTablesSchema($this->_tableName);

		if ($aSql_Tables[0]->table_rows < 100000 || $this->_issetWhere())
		{
			$queryBuilder = $this->_entity
				->queryBuilder()
				->clearSelect()
				->clearOrderBy()
				->select(array('COUNT(*)', 'count'))
				->from($this->_tableName)
				->limit(1)
				->offset(0)
				->asAssoc();

			$Core_DataBase = $queryBuilder->execute();

			$row = $Core_DataBase->current();

			// Warning
			if (!is_null(Core_Array::getRequest('debug')))
			{
				echo '<p><b>getCount Query</b>: <pre>', $Core_DataBase->getLastQuery(), '</pre></p>';
			}

			$count = $row['count'];
		}
		else
		{
			$count = $aSql_Tables[0]->table_rows;
		}

		return $count;
	}

	/**
	 * Get items count
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			// Apply conditions
			$this->_setConditions();

			$this->_count = $this->_getTotalCountByCount();
		}

		return $this->_count;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		$this->_entity->setTableName($this->_tableName);
		return $this->_entity;
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		if (!$this->_loaded)
		{
			// Применение внесенных условий отбора
			$this->_setConditions();

			$queryBuilder = $this->_entity
				->queryBuilder()
				->clearFrom()
				->from($this->_tableName);

			!is_null($this->_limit) && $queryBuilder
				->limit($this->_limit)
				->offset($this->_offset);

			if (is_null($this->_count))
			{
				$issetHaving = $this->_issetHavingOrGroupBy();

				if ($issetHaving)
				{
					$queryBuilder->sqlCalcFoundRows();
				}
			}

			$this->_objects = $this->_entity
				->queryBuilder()
				->asObject('Sql_Table_View_Entity')
				->execute()
				->result();

			// Warning
			if (!is_null(Core_Array::getRequest('debug')))
			{
				echo '<p><b>Select Query</b>: <pre>', Core_DataBase::instance()->getLastQuery(), '</pre></p>';
			}

			$this->_loaded = TRUE;

			// Расчет количества
			if (is_null($this->_count))
			{
				$this->_count = $issetHaving
					? $this->_getFoundRows()
					: $this->_getTotalCountByCount();
			}
		}
		return $this->_objects;
	}

	/**
	 * Add condition for the selection of elements
	 * @param array $condition condition
	 * @return Admin_Form_Dataset
	 */
	public function addCondition($condition)
	{
		// Уточнение таблицы при поиске WHERE
		if (isset($condition['where']))
		{
			if (isset($condition['where'][0]))
			{
				if (is_string($condition['where'][0])
					&& strpos($condition['where'][0], '.') === FALSE
				)
				{
					$condition['where'][0] = $this->_tableName . '.' . $condition['where'][0];
				}
			}
		}

		return parent::addCondition($condition);
	}

	/**
	 * Apply conditions for the selection of elements
	 */
	protected function _setConditions()
	{
		$queryBuilder = $this->_entity
			->queryBuilder()
			->clear();

		// Conditions
		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				call_user_func_array(array($queryBuilder, $operator), $args);
			}
		}

		// Orders
		foreach ($this->_orders as $order)
		{
			call_user_func_array(array($queryBuilder, 'orderBy'), $order);
		}
	}

	protected $_primaryKeyName = NULL;

	protected function _getPrimaryKeyName()
	{
		if (is_null($this->_primaryKeyName))
		{
			$this->_primaryKeyName = 'id';
			$aFileds = Core_DataBase::instance()->getColumns($this->_tableName);
			foreach ($aFileds as $key => $aRow)
			{
				// Set temporary key and order field for the Admin_Form
				if ($aRow['key'] == 'PRI')
				{
					$this->_primaryKeyName = $aRow['name'];
					break;
				}
			}
		}

		return $this->_primaryKeyName;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		// Определяем имя первичного ключа
		$queryBuilder = $this->_entity
			->queryBuilder()
			->clearFrom()
			->clearWhere()
			->from($this->_tableName)
			->where($this->_getPrimaryKeyName(), '=', $primaryKey);

		$object = $this->_entity
			->queryBuilder()
			->asObject('Sql_Table_View_Entity')
			->execute()
			->current();

		if (!is_object($object))
		{
			$object = new Sql_Table_View_Entity();
		}

		$object->setTableName($this->_tableName);

		return $object;
	}
}