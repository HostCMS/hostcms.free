<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event Timeline Dataset.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Timeline_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Event_Model object
	 * @var object
	 */
	protected $_event = NULL;

	/**
	 * Constructor.
	 * @param Event_Model $oEvent entity
	 * @hostcms-event Event_Timeline_Dataset.onAfterConstruct
	 */
	public function __construct(Event_Model $oEvent)
	{
		$this->_event = $oEvent;

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

	protected function _getQb0($id = NULL)
	{
		$oQb = Core_QueryBuilder::select(array(0, 'type'), 'id', 'datetime')
			->from('event_histories')
			->where('event_histories.event_id', '=', $this->_event->id);

		$id && $oQb->where('event_histories.id', '=', $id);

		return $oQb;
	}

	protected function _getQb1($id = NULL)
	{
		$oQb = Core_QueryBuilder::select(array(1, 'type'), 'crm_notes.id', 'datetime')
			->from('crm_notes')
			->leftJoin('event_crm_notes', 'crm_notes.id', '=', 'event_crm_notes.crm_note_id')
			->where('event_crm_notes.event_id', '=', $this->_event->id)
			->where('crm_notes.deleted', '=', 0);

		$id && $oQb->where('crm_notes.id', '=', $id);

		return $oQb;
	}

	protected function _getQb2($id = NULL)
	{
		$oQb = Core_QueryBuilder::select(array(2, 'type'), 'id', 'datetime')
			->from('events')
			->where('events.parent_id', '=', $this->_event->id)
			->where('events.deleted', '=', 0);

		$id && $oQb->where('events.id', '=', $id);

		return $oQb;
	}

	protected function _loadItems()
	{
		if ($this->_limit)
		{
			$oQB = $this->_getQb0()
				->sqlCalcFoundRows()
				->union($this->_getQb1())
				->union($this->_getQb2())
				->unionOrderBy('datetime', 'DESC')
				->unionLimit($this->_limit)
				->unionOffset($this->_offset);

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

	protected function _getObjectByType($object)
	{
		switch ($object->type)
		{
			case 0:
				return Core_Entity::factory('Event_History', $object->id);
			break;
			case 1:
				return Core_Entity::factory('Crm_Note', $object->id);
			break;
			case 2:
				return Core_Entity::factory('Event', $object->id);
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
		is_null($this->_count) && $this->_loadItems();

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
		return new stdClass();
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		$this->load();

		if (isset($this->_objects[$primaryKey]))
		{
			return $this->_objects[$primaryKey];
		}
		elseif (strpos($primaryKey, '-') !== FALSE)
		{
			list($type, $id) = explode('-', $primaryKey);

			$functionName = '_getQb' . intval($type);

			if (method_exists($this, $functionName))
			{
				$oQb = $this->$functionName($id);
				$oDataBase = $oQb->execute();

				$oObject = $oDataBase->asObject()->current();

				if (!$oObject)
				{
					$oObject = new stdClass();
					$oObject->type = $type;
					$oObject->id = 0;
				}

				return $this->_getObjectByType($oObject);
			}
		}

		return NULL;
	}
}