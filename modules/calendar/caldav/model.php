<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Model
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Calendar_Caldav_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'calendar_caldav_user' => array(),
		'user' => array('through' => 'calendar_caldav_user'),
		'event_calendar_caldav' => array()
	);

	/**
	 * Backend callback method
	 * @return string
	 */
	public function iconBackend()
	{
		return '<i class="' . htmlspecialchars($this->icon) . '"></i>';
	}

	/**
	 * Change caldav status
	 * @return self
	 * @hostcms-event calendar_caldav.onBeforeChangeActive
	 * @hostcms-event calendar_caldav.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Sync
	 * @param Calendar_Caldav_User_Model $oCalendar_Caldav_User object
	 * @return self
	 */
	public function sync($oCalendar_Caldav_User)
	{
		if (!is_null($oCalendar_Caldav_User)
			&& !is_null($oCalendar_Caldav_User->caldav_server)
			&& !is_null($oCalendar_Caldav_User->username)
			&& !is_null($oCalendar_Caldav_User->password)
		)
		{
			$Calendar_Caldav_Controller = Calendar_Caldav_Controller::instance($this->driver);

			$Calendar_Caldav_Controller
				->setUrl($oCalendar_Caldav_User->caldav_server)
				->setUsername($oCalendar_Caldav_User->username)
				->setPassword($oCalendar_Caldav_User->password)
				->setData($oCalendar_Caldav_User->data)
				->connect();

			$aCalendars = $Calendar_Caldav_Controller->findCalendars();

			if (count($aCalendars))
			{
				$Calendar_Caldav_Controller->setCalendar(array_shift($aCalendars));

				$last_modified = $oCalendar_Caldav_User->synchronized_datetime != '0000-00-00 00:00:00'
					? $oCalendar_Caldav_User->synchronized_datetime
					: date('Y-m-d H:i:s', strtotime('-6 month'));

				// Выгрузка событий во внешний календарь
				$aCalendarEntities = Calendar_Controller::getUploadCalendarEntities($last_modified, $this->id);

				if (count($aCalendarEntities))
				{
					foreach ($aCalendarEntities as $oEntity)
					{
						$sContent = $this->_createICalendar($oEntity);
						$Calendar_Caldav_Controller->save($sContent);
					}

					Core_Log::instance()->clear()
						->notify(FALSE) // avoid recursion
						->status(Core_Log::$MESSAGE)
						->write(sprintf('CalDav uploaded %d item(s) for %s', count($aCalendarEntities), $oCalendar_Caldav_User->User->login));
				}

				$oEvent_Type_Default = Core_Entity::factory('Event_Type')->getDefault();

				// Получение событий из внешнего календаря
				$aObjects = $Calendar_Caldav_Controller->getObjects();

				foreach ($aObjects as $aObject)
				{
					in_array($aObject['description'], array('Default Mozilla Description'))
						&& $aObject['description'] = '';

					$bFound = FALSE;

					if (Core::moduleIsActive('deal'))
					{
						$oDeal = Core_Entity::factory('Deal')->getByGuid($aObject['uid']);

						if (!is_null($oDeal))
						{
							$bFound = TRUE;
							if (!isset($aObject['modified']) || Core_Date::sql2timestamp($aObject['modified']) > Core_Date::sql2timestamp($oDeal->last_modified))
							{
								$oDeal->name = $aObject['summary'];
								$oDeal->description = $aObject['description'];
								$oDeal->start_datetime = $aObject['start_datetime'];
								$oDeal->deadline = $aObject['end_datetime'];
								isset($aObject['modified'])
									&& $oDeal->last_modified = $aObject['modified'];
								$oDeal->save();
							}
						}
					}

					if (!$bFound)
					{
						$oEvent = Core_Entity::factory('Event')->getByGuid($aObject['uid']);

						$bNewEvent = is_null($oEvent);

						if ($bNewEvent)
						{
							$oEvent = Core_Entity::factory('Event');
							$oEvent->guid = $aObject['uid'];
							$oEvent->datetime = $aObject['created'];
							$oEvent->last_modified = '0000-00-00 00:00:00';

							// Определение типа по тексту
							$aEvent_Types = Core_Entity::factory('Event_Type')->findAll();
							foreach ($aEvent_Types as $oEvent_Type)
							{
								if (mb_stripos($aObject['summary'], mb_substr($oEvent_Type->name, 0, -1)) !== FALSE)
								{
									$oEvent->event_type_id = $oEvent_Type->id;
									break;
								}
							}

							// Тип по умолчанию
							!$oEvent->event_type_id && $oEvent_Type_Default
								&& $oEvent->event_type_id = $oEvent_Type_Default->id;
						}

						if (!isset($aObject['modified']) || Core_Date::sql2timestamp($aObject['modified']) > Core_Date::sql2timestamp($oEvent->last_modified))
						{
							$oEvent->name = $aObject['summary'];
							$oEvent->description = $aObject['description'];
							$oEvent->start = $aObject['start_datetime'];
							$oEvent->deadline = $aObject['end_datetime'];
							$oEvent->place = $aObject['location'];
							$oEvent->all_day = intval($aObject['allDay']);
							isset($aObject['modified'])
								&& $oEvent->last_modified = $aObject['modified'];
							$oEvent->save();

							if (!$oEvent->Event_Users->getCountByuser_id($oCalendar_Caldav_User->user_id))
							{
								$oEvent_User = Core_Entity::factory('Event_User');
								$oEvent_User->event_id = $oEvent->id;
								$oEvent_User->user_id = $oCalendar_Caldav_User->user_id;
								$oEvent_User->creator = $bNewEvent ? 1 : 0;
								$oEvent_User->save();
							}
						}
					}
				}
			}

			$oCalendar_Caldav_User->data = $Calendar_Caldav_Controller->getData();
			$oCalendar_Caldav_User->synchronized_datetime = Core_Date::timestamp2sql(time());
			$oCalendar_Caldav_User->save();
		}
	}

	/**
	 * Create calendar entity
	 * @param object $oEntity
	 * @return string
	 */
	protected function _createICalendar($oEntity)
	{
		$start = Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp($oEntity->start));
		$end = isset($oEntity->end)
			? Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp($oEntity->end))
			: '';

		$date = Core_Date::gmdate("Ymd\THis\Z", time());

		$title = str_replace("\n", '\n', $oEntity->title);

		$description = str_replace("\n", '\n', $oEntity->description);

		$last_modified = Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp($oEntity->last_modified));

		$sContent = <<<EOD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VTIMEZONE
TZID:Europe/Moscow
BEGIN:STANDARD
TZOFFSETFROM:+0300
TZOFFSETTO:+0300
TZNAME:MSK
DTSTART:19700101T000000
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:{$oEntity->guid}
DTSTAMP:{$date}
DTSTART:{$start}
DTEND:{$end}
SUMMARY:{$title}
DESCRIPTION:{$description}
LAST-MODIFIED:{$last_modified}
LOCATION:{$oEntity->place}
END:VEVENT
END:VCALENDAR
EOD;

		return $sContent;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<a href="' . $link . '" onclick="' . $onclick . '">' . htmlspecialchars($this->name) . '</a>';
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event admin_form_action.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Calendar_Caldav_Users->deleteAll(FALSE);
		$this->Event_Calendar_Caldavs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}