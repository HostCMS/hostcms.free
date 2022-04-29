<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Status_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Status_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'event' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<span class="editable" id="apply_check_0_' . $this->id . '_fv_1151">' . htmlspecialchars($this->name) . '</span>';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBadge()
	{
		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core_Entity::factory('Module')->getByPath('event');

			$aBot_Modules = Bot_Controller::getBotModules($oModule->id, 0, $this->id);

			foreach ($aBot_Modules as $oBot_Module)
			{
				$oBot = $oBot_Module->Bot;

				$sParents = $oBot->bot_dir_id
					? $oBot->Bot_Dir->dirPathWithSeparator() . ' → '
					: '';

				Core_Html_Entity::factory('Span')
					->class('badge badge-square badge-hostcms')
					->value('<i class="fa fa-android"></i> ' . $sParents . htmlspecialchars($oBot->name))
					->execute();
			}
		}
	}

	/**
	 * Change event status final
	 * @hostcms-event event_status.onBeforeChangeActive
	 * @hostcms-event event_status.onAfterChangeActive
	 * @return self
	 */
	public function changeFinal()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeFinal', $this);

		$this->final = 1 - $this->final;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeFinal', $this);

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event event_status.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		Core_QueryBuilder::update('events')
			->set('event_status_id', 0)
			->where('event_status_id', '=', $this->id)
			->execute();

		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core_Entity::factory('Module')->getByPath('event');

			if ($oModule)
			{
				$aBot_Modules = Bot_Controller::getBotModules($oModule->id, 0, $this->id);

				foreach ($aBot_Modules as $oBot_Module)
				{
					$oBot_Module->delete();
				}
			}
		}

		return parent::delete($primaryKey);
	}
}