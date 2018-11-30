<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Dataset_Entity extends Admin_Form_Dataset
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

	/**
	 * Constructor.
	 * @param Core_Entity $oCore_Entity entity
	 * @hostcms-event Admin_Form_Dataset_Entity.onAfterConstruct
	 */
	public function __construct(Core_Entity $oCore_Entity)
	{
		$this->_entity = $oCore_Entity;

		Core_Event::notify(get_class($this) . '.onAfterConstruct', $this);
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
	 * Get FOUND_ROWS
	 * @return int
	 */
	protected function _getFoundRows()
	{
		$row = Core_QueryBuilder::select(array('FOUND_ROWS()', 'count'))->execute()->asAssoc()->current();

		// Warning
		if (Core_Array::getRequest('debug'))
		{
			echo '<p><b>Query FOUND_ROWS</b>.</p>';
		}

		return $row['count'];
	}

	/**
	 * Get total count by COUNT(*)
	 * @return int
	 */
	protected function _getTotalCountByCount()
	{
		$queryBuilder = $this->_entity->queryBuilder()
			->clearSelect()
			->clearOrderBy()
			->select(array('COUNT(*)', 'count'))
			->from($this->_entity->getTableName())
			->limit(1)
			->offset(0)
			->asAssoc();

		$Core_DataBase = $queryBuilder->execute();

		$row = $Core_DataBase->current();

		// Warning
		if (Core_Array::getRequest('debug'))
		{
			echo '<p><b>getCount Query</b>: <pre>', $Core_DataBase->getLastQuery(), '</pre></p>';
		}

		return $row['count'];
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

			$this->_entity->applyMarksDeleted();

			$issetHaving = $this->_issetHavingOrGroupBy();

			if (!$issetHaving)
			{
				$this->_count = $this->_getTotalCountByCount();
			}
			else
			{
				$queryBuilder = $this->_entity->queryBuilder()
					//->clearSelect()
					->clearOrderBy()
					->sqlCalcFoundRows()
					->from($this->_entity->getTableName())
					->limit(1)
					->offset(0)
					->asAssoc();

				$queryBuilder->execute();

				// Warning
				if (Core_Array::getRequest('debug'))
				{
					echo '<p><b>Query</b>: sqlCalcFoundRows before FOUND_ROWS()</p>';
				}

				$this->_count = $this->_getFoundRows();
			}

			//$this->_count = count($this->_entity->findAll());
		}

		return $this->_count;
	}

	/**
	 * Dataset objects list
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * Get objects
	 * @return array
	 */
	public function getObjects()
	{
		return $this->_objects;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
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

			$queryBuilder = $this->_entity->queryBuilder()
				->limit($this->_limit)
				->offset($this->_offset);

			if (is_null($this->_count))
			{
				$issetHaving = $this->_issetHavingOrGroupBy();

				if ($issetHaving)
				{
					$this->_entity->queryBuilder()->sqlCalcFoundRows();
				}
			}

			// Load columns
			$this->_entity->getTableColumns();

			$this->_objects = $this->_entity->findAll(FALSE);

			// Warning
			if (Core_Array::getRequest('debug'))
			{
				echo '<p><b>Select Query</b>: <pre>', Core_DataBase::instance()->getLastQuery(), '</pre></p>';
			}

			$this->_loaded = TRUE;

			// Расчет количества
			if (is_null($this->_count))
			{
				if ($issetHaving)
				{
					$this->_count = $this->_getFoundRows();
				}
				else
				{
					$this->_count = $this->_getTotalCountByCount();
				}
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
					$condition['where'][0] = $this->_entity->getTableName() . '.' . $condition['where'][0];
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
		$queryBuilder = $this->_entity->queryBuilder()
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

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		$this->_entity
			->queryBuilder()
			->clear();

		// При NULL применяются условия _setConditions() и находим первый в списке
		/*$primaryKey = ($primaryKey === 0)
			? NULL
			: $primaryKey;*/

		// Применение внесенных условий отбора, чтобы нельзя было получить элемент не из этой группы
		//$this->_setConditions();

		$newObject = clone $this->_entity;
		return $newObject->find($primaryKey, FALSE);
		//return $this->_entity->find($primaryKey, FALSE);
	}
}