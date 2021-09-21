<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Model
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Model extends Core_Entity
{

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'calendar_caldav_user' => array(),
		'user' => array('through' => 'calendar_caldav_user')
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

				$start = Core_Date::date2timestamp(date('Y-m-d H:i:s', strtotime('-1 month')));
				$end = Core_Date::date2timestamp(date('Y-m-d H:i:s', strtotime('+1 year')));

				$aCalendarEntities = Calendar_Controller::getCalendarEntities($start, $end);

				if (count($aCalendarEntities))
				{
					foreach ($aCalendarEntities as $oEntity)
					{
						$sContent = $this->_createICalendar($oEntity);

						$Calendar_Caldav_Controller->save($sContent);
					}
				}
			}

			$oCalendar_Caldav_User->data = $Calendar_Caldav_Controller->getData();
			$oCalendar_Caldav_User->synchronized_datetime = Core_Date::timestamp2sql(time());
			$oCalendar_Caldav_User->save();
		}
	}

	protected function _createICalendar($oEntity)
	{
		$start = Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp($oEntity->start));
		$end = isset($oEntity->end)
			? Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp($oEntity->end))
			: '';

		$date = Core_Date::gmdate("Ymd\THis\Z", time());

		$sContent = <<<EOD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:{$oEntity->id}
DTSTAMP:{$date}
DTSTART:{$start}
DTEND:{$end}
SUMMARY:{$oEntity->title}
END:VEVENT
END:VCALENDAR
EOD;

		return $sContent;
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

		return parent::delete($primaryKey);
	}
}