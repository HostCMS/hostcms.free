<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Project Entity Dataset.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Entity_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Crm_Project_Model object
	 * @var object
	 */
	protected $_crm_project = NULL;

	/**
	 * Constructor.
	 * @param Crm_Project_Model $oCrm_Project entity
	 * @hostcms-event Crm_Project_Entity_Dataset.onAfterConstruct
	 */
	public function __construct(Crm_Project_Model $oCrm_Project)
	{
		$this->_crm_project = $oCrm_Project;

		Core_Event::notify(get_class($this) . '.onAfterConstruct', $this);
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

	protected function _loadItems()
	{
		$oQB = Core_QueryBuilder::select(array(2, 'type'), 'id', 'datetime')
			->sqlCalcFoundRows()
			->from('crm_project_notes')
			->where('crm_project_notes.crm_project_id', '=', $this->_crm_project->id)
			->where('crm_project_notes.deleted', '=', 0)
			->unionOrderBy('datetime', 'DESC')
			->unionLimit($this->_limit)
			->unionOffset($this->_offset);

		if (Core::moduleIsActive('event'))
		{
			$events = Core_QueryBuilder::select(array(0, 'type'), 'id', 'datetime')
				->from('events')
				->where('events.crm_project_id', '=', $this->_crm_project->id)
				->where('events.deleted', '=', 0);

			$oQB->union($events);
		}

		if (Core::moduleIsActive('deal'))
		{
			$deals = Core_QueryBuilder::select(array(1, 'type'), 'id', array('start_datetime', 'datetime'))
				->from('deals')
				->where('deals.crm_project_id', '=', $this->_crm_project->id)
				->where('deals.deleted', '=', 0);

			$oQB->union($deals);
		}

		$queryBuilder = $oQB->execute();

		$this->_objects = $queryBuilder->asObject()->result();

		$this->_loaded = TRUE;
		$this->_count = $this->_getFoundRows();

		// Warning
		if (Core_Array::getRequest('debug'))
		{
			echo '<p><b>Query</b>: sqlCalcFoundRows before FOUND_ROWS()</p>';
		}
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (!$this->_count)
		{
			$this->_loadItems();
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
			$this->_loadItems();

			$this->_loaded = TRUE;
		}

		return $this->_objects;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_crm_project;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		return $this->getEntity();
	}
}