<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Project Entity Dataset.
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Timeline_Model object
	 * @var object
	 */
	protected $_timeline = NULL;

	/**
	 * Constructor.
	 * @param Timeline_Model $oTimeline entity
	 * @hostcms-event Timeline_Dataset.onAfterConstruct
	 */
	public function __construct(Timeline_Model $oTimeline)
	{
		$this->_timeline = $oTimeline;

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
	 * Get crm note
	 * @param int $id
	 * @return object
	 */
	protected function _getQb0($id = NULL)
	{
		// Load model columns BEFORE FOUND_ROWS()
		Core_Entity::factory('Crm_Note')->getTableColumns();

		$oQb = Core_QueryBuilder::select(array(0, 'type'), 'crm_notes.id', 'crm_notes.datetime')
			->from('crm_notes')
			->join('timelines', 'timelines.crm_note_id', '=', 'crm_notes.id')
			->where('crm_notes.parent_id', '=', 0)
			->where('crm_notes.deleted', '=', 0);

		$id && $oQb->where('crm_notes.id', '=', $id);

		return $oQb;
	}

	/**
	 * Get events
	 * @param int $id
	 * @return object
	 */
	protected function _getQb1($id = NULL)
	{
		// Load model columns BEFORE FOUND_ROWS()
		Core_Entity::factory('Event')->getTableColumns();

		$oUser = Core_Auth::getCurrentUser();

		$oQb = Core_QueryBuilder::select(array(1, 'type'), 'events.id', 'events.datetime')
			->from('events')
			->join('event_users', 'events.id', '=', 'event_users.event_id')
			->where('event_users.user_id', '=', $oUser->id)
			->where('events.deleted', '=', 0);

		$id && $oQb->where('events.id', '=', $id);

		return $oQb;
	}

	/**
	 * Get deals
	 * @param int $id
	 * @return object
	 */
	protected function _getQb2($id = NULL)
	{
		// Load model columns BEFORE FOUND_ROWS()
		Core_Entity::factory('Deal')->getTableColumns();

		$oUser = Core_Auth::getCurrentUser();

		$oQb = Core_QueryBuilder::select(array(2, 'type'), 'deals.id', array('deals.start_datetime', 'datetime'))
			->from('deals')
			->join('deal_template_step_access_users', 'deals.deal_template_step_id', '=', 'deal_template_step_access_users.deal_template_step_id',
				array(array('AND' => array('deal_template_step_access_users.user_id', '=', $oUser->id)))
			)
			->open()
				->where(Core_QueryBuilder::expression('`deal_template_step_access_users`.`access` & 4'), '!=', 0)
				->setOr()
				->where('deals.creator_id', '=', $oUser->id)
				->setOr()
				->where('deals.user_id', '=', $oUser->id)
				->setOr()
				->where('deals.user_id', '=', 0)
			->close()
			->where('deals.deleted', '=', 0);

		$id && $oQb->where('deals.id', '=', $id);

		return $oQb;
	}

	/**
	 * Get dms document
	 * @param int $id
	 * @return object
	 */
	protected function _getQb3($id = NULL)
	{
		// Load model columns BEFORE FOUND_ROWS()
		Core_Entity::factory('Dms_Document')->getTableColumns();

		$oUser = Core_Auth::getCurrentUser();

		$oQb = Core_QueryBuilder::select(array(3, 'type'), 'dms_documents.id', array('created', 'datetime'))
			->from('dms_documents')
			->leftJoin('dms_document_access_users', 'dms_documents.id', '=', 'dms_document_access_users.dms_document_id')
			->leftJoin(
				array(Core_QueryBuilder::select('dms_document_type_id')->from('company_department_post_users')
					->join('dms_document_type_department_accesses', 'dms_document_type_department_accesses.company_department_id', '=', 'company_department_post_users.company_department_id')
					->where('company_department_post_users.user_id', '=', $oUser->id)
					->where('company_department_post_users.head', '=', 1)
					->where('dms_document_type_department_accesses.head_access', '>', 0)
					->groupBy('dms_document_type_id')
				, 't1'),
				't1.dms_document_type_id', '=', 'dms_documents.dms_document_type_id'
			)
			->open()
				->where('dms_documents.user_id', '=', $oUser->id)
				->setOr()
				// Выданы права сотруднику на просмотр документа
				->where(Core_QueryBuilder::expression('`dms_document_access_users`.`access` | 14'), '!=', 0)
				->where('dms_document_access_users.user_id', '=', $oUser->id)
				->setOr()
				->where('dms_documents.user_id', '=', 0)
				->setOr()
				// Глава отдела имеет какое-либо право доступа к заданному типу документа
				->where('t1.dms_document_type_id', 'IS NOT', NULL)
			->close()
			->where('dms_documents.deleted', '=', 0)
			;

		$id && $oQb->where('dms_documents.id', '=', $id);

		return $oQb;
	}

	/**
	 * Load items
	 */
	protected function _loadItems()
	{
		if ($this->_limit)
		{
			$oQB = $this->_getQb0()
				->sqlCalcFoundRows()
				// ->union($this->_getQb1())
				// ->union($this->_getQb2())
				->unionOrderBy('datetime', 'DESC')
				->unionLimit($this->_limit)
				->unionOffset($this->_offset);

			Core::moduleIsActive('event')
				&& $oQB->union($this->_getQb1());

			Core::moduleIsActive('deal')
				&& $oQB->union($this->_getQb2());

			Core::moduleIsActive('dms')
				&& $oQB->union($this->_getQb3());

			$oCore_DataBase = $oQB->execute();

			$aObjects = $oCore_DataBase->asObject()->result();

			$oCore_DataBase->free();

			// Warning
			if (!is_null(Core_Array::getRequest('debug')))
			{
				echo '<p><b>Select Query</b>: <pre>', Core_DataBase::instance()->getLastQuery(), '</pre></p>';
			}

			foreach ($aObjects as $oObject)
			{
				//$oObject->id = $oObject->type . '-' . $oObject->id;
				$this->_objects[$oObject->type . '-' . $oObject->id] = $this->_getObjectByType($oObject);
			}

			$this->_loaded = TRUE;
			$this->_count = $this->_getFoundRows();
		}

		// Warning
		if (!is_null(Core_Array::getRequest('debug')))
		{
			echo '<p><b>Query</b>: sqlCalcFoundRows before FOUND_ROWS()</p>';
		}
	}

	/**
	 * Get object by type
	 * @param object $object
	 * @return object
	 */
	protected function _getObjectByType($object)
	{
		switch ($object->type)
		{
			case 0:
				return Core_Entity::factory('Crm_Note', $object->id)->dataDatetime($object->datetime);
			break;
			case 1:
				return Core_Entity::factory('Event', $object->id)->dataDatetime($object->datetime);
			break;
			case 2:
				return Core_Entity::factory('Deal', $object->id)->dataDatetime($object->datetime);
			break;
			case 3:
				return Core_Entity::factory('Dms_Document', $object->id)->dataDatetime($object->datetime);
			break;
			default:
				throw new Core_Exception('_getObjectByType(): Wrong type', array(), 0, FALSE);
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
		return $this->_timeline;
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