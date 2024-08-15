<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Dataset_Entity extends Admin_Form_Dataset
{
	/**
	 * Entity of dataset
	 * @var object
	 */
	protected $_entity = NULL;

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

		Core_Event::notify(get_class($this) . '.onAfterConstruct', $this);
	}

	/**
	 * Add user condition
	 * @return self
	 */
	public function addUserConditions()
	{
		$oUser = Core_Auth::getCurrentUser();

		$tableName = $this->_entity->getTableName();

		$this
			->addCondition(array('open' => array()))
			->addCondition(array('where' => array('user_id', '=', $oUser->id)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('user_id', '=', 0)))
			->addCondition(array('close' => array()));

		return $this;
	}

	/**
	 * Check if entity conditions consist of having/groupBy
	 * @return boolean
	 */
	protected function _issetHavingOrGroupBy()
	{
		$issetHavingOrGroupBy = FALSE;
		foreach ($this->_conditions as $condition)
		{
			if (isset($condition['having']) || isset($condition['groupBy']))
			{
				$issetHavingOrGroupBy = TRUE;
				break;
			}
		}

		return $issetHavingOrGroupBy;
	}

	/**
	 * Check if entity conditions consist of having
	 * @return boolean
	 */
	protected function _issetHaving()
	{
		$issetHaving = FALSE;
		foreach ($this->_conditions as $condition)
		{
			if (isset($condition['having']))
			{
				$issetHaving = TRUE;
				break;
			}
		}

		return $issetHaving;
	}

	/**
	 * Check if entity conditions consist of groupBy
	 * @return boolean
	 */
	protected function _issetGroupBy()
	{
		$issetGroupBy = FALSE;
		foreach ($this->_conditions as $condition)
		{
			if (isset($condition['groupBy']))
			{
				$issetGroupBy = TRUE;
				break;
			}
		}

		return $issetGroupBy;
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
			echo '<p><b>Call getFoundRows()</b>.</p>';
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
		$bDebug = !is_null(Core_Array::getRequest('debug'));

		$bDebug
			&& $fBeginTime = Core::getmicrotime();

		$queryBuilder = $this->_entity->queryBuilder()
			->clearSelect()
			->clearOrderBy()
			->select(array('COUNT(*)', 'count'))
			->from($this->_entity->getTableName())
			->limit(1)
			->offset(0)
			->asAssoc();

		//$this->_applyRestrictAccess($queryBuilder);

		$oCore_DataBase = $queryBuilder->execute();

		$row = $oCore_DataBase->current();

		$oCore_DataBase->free();

		if ($bDebug)
		{
			echo '<p><b>getCount Query</b> (' . sprintf('%.3f', Core::getmicrotime() - $fBeginTime) . ' s.): <pre>', $oCore_DataBase->getLastQuery(), '</pre></p>';
		}

		return $row['count'];
	}

	/**
	 * Check if not use join or use without where conditions
	 * @return boolean
	 */
	protected function _allJoinsNotUsed()
	{
		$issetHaving = $this->_issetHaving();

		if ($issetHaving)
		{
			return FALSE;
		}

		$aJoins = array();
		foreach ($this->_conditions as $condition)
		{
			// При наличии прямого объединения простой подсчет количества невозможен
			if (isset($condition['join']))
			{
				return FALSE;
			}
			elseif (isset($condition['leftJoin']))
			{
				//if (count($condition['leftJoin']) == 3)
				$aJoins[] = $condition['leftJoin'][0];
			}
			elseif (isset($condition['rightJoin']))
			{
				$aJoins[] = $condition['rightJoin'][0];
			}
		}

		foreach ($aJoins as $join)
		{
			foreach ($this->_conditions as $condition)
			{
				if (isset($condition['where']))
				{
					// $join может быть как именем таблицы, так и массивом с псевдонимом
					if (isset($condition['where'][0]) && (!is_scalar($join) || strpos($condition['where'][0], $join) !== FALSE))
					{
						return FALSE;
					}
				}
			}
		}

		return TRUE;
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

			$issetHavingOrGroupBy = $this->_issetHavingOrGroupBy();

			// Нет смысла считать, если !$issetHavingOrGroupBy
			$allJoinsNotUsed = $issetHavingOrGroupBy
				? $this->_allJoinsNotUsed()
				: NULL;

			//var_dump('_allJoinsNotUsed', $allJoinsNotUsed);

			if (!$issetHavingOrGroupBy || $allJoinsNotUsed)
			{
				$allJoinsNotUsed
					&& $this->_entity->queryBuilder()
						->clearJoin()
						->clearGroupBy(); // без удаления groupBy() количество найдет 1

				$this->_count = $this->_getTotalCountByCount();
			}
			else
			{
				$bDebug = !is_null(Core_Array::getRequest('debug'));

				$bDebug
					&& $fBeginTime = Core::getmicrotime();

				$queryBuilder = $this->_entity->queryBuilder()
					//->clearSelect() // see below
					->clearOrderBy()
					->sqlCalcFoundRows()
					->from($this->_entity->getTableName())
					->limit(1)
					->offset(0)
					->asAssoc();

				!$this->_issetHaving()
					&& $this->_entity->queryBuilder()
						->clearSelect();

				//$this->_applyRestrictAccess($queryBuilder);

				$oCore_DataBase = $queryBuilder->execute();
				$oCore_DataBase->free();

				if ($bDebug)
				{
					echo '<p><b>Query sqlCalcFoundRows() before FOUND_ROWS()</b> (' . sprintf('%.3f', Core::getmicrotime() - $fBeginTime) . ' s.): <pre>', $oCore_DataBase->getLastQuery(), '</pre></p>';
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
			$bDebug = !is_null(Core_Array::getRequest('debug'));

			$bDebug
				&& $fBeginTime = Core::getmicrotime();

			if (!is_null($this->_limit) && $this->_limit == 0 && $this->_offset == 0)
			{
				$this->_objects = array();
				$this->_count = 0;
			}
			else
			{
				// Применение внесенных условий отбора
				$this->_setConditions();

				$queryBuilder = $this->_entity->queryBuilder();

				!is_null($this->_limit) && $queryBuilder
					->limit($this->_limit)
					->offset($this->_offset);

				if (is_null($this->_count))
				{
					$issetHavingOrGroupBy = $this->_issetHavingOrGroupBy();

					if ($issetHavingOrGroupBy)
					{
						$queryBuilder->sqlCalcFoundRows();
					}
				}

				// Load columns
				$this->_entity->getTableColumns();

				$this->_objects = $this->_entity->findAll(FALSE);

				if ($bDebug)
				{
					echo '<p><b>Select Query</b> (' . sprintf('%.3f', Core::getmicrotime() - $fBeginTime) . ' s.): <pre>', Core_DataBase::instance()->getLastQuery(), '</pre></p>';
				}
			}

			$this->_loaded = TRUE;

			// Расчет количества
			if (is_null($this->_count))
			{
				$this->_count = $issetHavingOrGroupBy
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