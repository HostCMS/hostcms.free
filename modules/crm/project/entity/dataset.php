<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Project Entity Dataset.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		// Warning
		if (!is_null(Core_Array::getRequest('debug')))
		{
			echo '<p><b>Query FOUND_ROWS</b>.</p>';
		}

		return Core_QueryBuilder::select()->getFoundRows();
	}

	/**
	 * Load items
	 */
	protected function _loadItems()
	{
		$oQB = Core_QueryBuilder::select(array(2, 'type'), 'crm_notes.id', 'datetime')
			->sqlCalcFoundRows()
			->from('crm_notes')
			->leftJoin('crm_project_crm_notes', 'crm_notes.id', '=', 'crm_project_crm_notes.crm_note_id')
			->where('crm_project_crm_notes.crm_project_id', '=', $this->_crm_project->id)
			->where('crm_notes.deleted', '=', 0)
			->unionOrderBy('datetime', 'DESC')
			->unionOrderBy('id', 'DESC')
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

		$attachments = Core_QueryBuilder::select(array(3, 'type'), 'id', 'datetime')
			->from('crm_project_attachments')
			->where('crm_project_attachments.crm_project_id', '=', $this->_crm_project->id);

		$oQB->union($attachments);

		if (Core::moduleIsActive('dms'))
		{
			$dms_documents = Core_QueryBuilder::select(array(4, 'type'), 'id', array('created', 'datetime'))
				->from('dms_documents')
				->where('dms_documents.crm_project_id', '=', $this->_crm_project->id)
				->where('dms_documents.deleted', '=', 0);

			$oQB->union($dms_documents);
		}

		$queryBuilder = $oQB->execute();

		$this->_objects = $queryBuilder->asObject()->result();

		$this->_loaded = TRUE;
		$this->_count = $this->_getFoundRows();

		// Warning
		if (!is_null(Core_Array::getRequest('debug')))
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