<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Restrict access to entities
	 * @var FALSE|int
	 */
	//protected $_restrictAccess = FALSE;

	/**
	 * Constructor.
	 * @param Core_Entity $oCore_Entity entity
	 * @hostcms-event Admin_Form_Dataset_Entity.onAfterConstruct
	 */
	public function __construct(Core_Entity $oCore_Entity)
	{
		$this->_entity = $oCore_Entity;

		/*$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser) && !$oUser->superuser && $oUser->only_access_my_own)
		{
			$this->_restrictAccess = $oUser->id;
		}*/

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
		// Warning
		if (Core_Array::getRequest('debug'))
		{
			echo '<p><b>Query FOUND_ROWS</b>.</p>';
		}

		return Core_QueryBuilder::select()->getFoundRows();
	}

	/*protected function _applyRestrictAccess($queryBuilder)
	{
		// Restrict access
		if ($this->_restrictAccess && isset($this->_entity->user_id))
		{
			$queryBuilder->where($this->_entity->getTableName() . '.user_id', '=', $this->_restrictAccess);
		}

		return $this;
	}*/

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

		//$this->_applyRestrictAccess($queryBuilder);

		$Core_DataBase = $queryBuilder->execute();

		$row = $Core_DataBase->current();
		
		$Core_DataBase->free();

		// Warning
		if (Core_Array::getRequest('debug'))
		{
			echo '<p><b>getCount Query</b>: <pre>', $Core_DataBase->getLastQuery(), '</pre></p>';
		}

		return $row['count'];
	}

	/**
	 * Set items count
	 * @param int $count
	 * @return self
	 */
	public function setCount($count)
	{
		$this->_count = $count;
		return $this;
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

				//$this->_applyRestrictAccess($queryBuilder);

				$oCore_DataBase = $queryBuilder->execute();
				$oCore_DataBase->free();

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

			$queryBuilder = $this->_entity->queryBuilder();

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

		// Needs to use object watcher
		return $newObject->find($primaryKey/*, FALSE*/);
		//return $this->_entity->find($primaryKey, FALSE);
	}
}