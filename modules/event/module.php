<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Module
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2022-04-29';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'event';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 140,
				'block' => 3,
				'ico' => 'fa fa-tasks',
				'name' => Core::_('Event.model_name'),
				'href' => "/admin/event/index.php",
				'onclick' => "$.adminLoad({path: '/admin/event/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Get Notification Design
	 * @param int $type
	 * @param int $entityId
	 * @return array
	 */
	public function getNotificationDesign($type, $entityId)
	{
		switch ($type)
		{
			case 100: // Напоминание о событии
				$sIconIco = "fa-clock-o";
				$sIconColor = "white";
				$sBackgroundColor = "bg-warning";
				$sNotificationColor = 'darkorange';
			break;
			case 6: // В дело добавлена заметка
				$sIconIco = "fa-comment-o";
				$sIconColor = "white";
				$sBackgroundColor = "bg-azure";
				$sNotificationColor = 'azure';
			break;
			default:
				$sIconIco = "fa-info";
				$sIconColor = "white";
				$sBackgroundColor = "bg-themeprimary";
				$sNotificationColor = 'info';
		}

		return array(
			'icon' => array(
				'ico' => "fa {$sIconIco}",
				'color' => $sIconColor,
				'background-color' => $sBackgroundColor
			),
			'notification' => array(
				'ico' => $sIconIco,
				'background-color' => $sNotificationColor
			),
			'href' => "/admin/event/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][" . $entityId . "]=1",
			// $(this).parents('li.open').click();
			'onclick' => "$.adminLoad({path: '/admin/event/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][" . $entityId . "]=1'}); return false",
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			)
		);
	}

	/**
	 * Call new notifications
	 */
	public function callNotifications()
	{
		$oModule = Core::$modulesList['event'];

		$oQueryBuilder = Core_QueryBuilder::select(array('events.id', 'event_id'), 'event_users.user_id'
				//array('events.id', 'event_users.user_id')
				)
			->from('events')
			->join('event_users', 'events.id', '=', 'event_users.event_id')
			->leftJoin('notifications', 'events.id', '=', 'notifications.entity_id',
				array(
					array('AND' => array('notifications.module_id', '=', $oModule->id)),
					array('AND' => array('notifications.type', '=', 100)) // уведомления с типом 100 - напоминание о деле(событии)
				)
			)
			->leftJoin('notification_users', 'notifications.id', '=', 'notification_users.notification_id',
				array(
					array('AND' => array('event_users.user_id', '=', Core_QueryBuilder::expression('`notification_users`.`user_id`')))
				)
			)
			->where('events.completed', '=', 0)
			/*->open()
				->where('events.reminder_start', '>=', Core_QueryBuilder::expression("DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00')"))
				->where('events.reminder_start', '<', Core_QueryBuilder::expression("DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00'), INTERVAL 1 MINUTE)"))
				->setOr()
				->where('events.reminder_start', '<=', Core_QueryBuilder::expression("DATE_FORMAT(NOW(), '%Y-%m-%d %T')"))
			->close()*/
			->where('events.reminder_start', '<=', Core_Date::timestamp2sql(time()))
			->where('events.start', '>', Core_Date::timestamp2sql(time()))
			->where('notification_users.user_id', 'IS', NULL)
			->where('events.deleted', '=', 0);

		// Массив пар [id события, id сотрудника]
		$aResult = $oQueryBuilder->execute()->asAssoc()->result();

		// Массив
		$aEventUsers = array();

		foreach ($aResult as $aEventAndUser)
		{
			$aEventUsers[$aEventAndUser['event_id']][] = $aEventAndUser['user_id'];
		}

		foreach ($aEventUsers as $iEventId => $aUsersId)
		{
			$oEvent = Core_Entity::factory('Event', $iEventId);

			$oNotification = Core_Entity::factory('Notification');
			$oNotification
				->title(strip_tags($oEvent->name))
				->description($oEvent->start != '0000-00-00 00:00:00' ? (Core::_('Event.event_start') . Core_Date::sql2datetime($oEvent->start)) : '' )
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type(100) // 100 - напоминание о деле (событии)
				->entity_id($oEvent->id)
				->save();

			foreach ($aUsersId as $iUserId)
			{
				// Связываем уведомление с сотрудником
				Core_Entity::factory('User', $iUserId)
					->add($oNotification);
			}
		}
	}

	public function getCalendarContextMenuActions()
	{
		// Идентификатор формы "Дела"
		// $iAdmin_Form_Id = 220;
		// $oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

		return array('<a href="javascript:void(0);" onclick="$.modalLoad({path: \'/admin/event/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][0]=1&date=\' + $(this).parents(\'ul\').data(\'timestamp\'), windowId: \'id_content\'}); return false">' . Core::_('Event.add_event') . '</a>');
	}

	/**
	 * Get List of Events
	 * @param int $start timestamp of period start
	 * @param int $end timestamp of period end
	 * @return array
	 */
	public function getCalendarEvents($start, $end)
	{
		$oUser = Core_Auth::getCurrentUser();

		$oEvents = $oUser->Events;

		$start = date('Y-m-d H:i:s', $start);
		$end = date('Y-m-d H:i:s', $end);

		$oEvents
			->queryBuilder()
			->open()
				->where('start', 'BETWEEN', array($start, $end))
				->setOr()
				->where('deadline', 'BETWEEN', array($start, $end))
			->close()
			->where('completed', '=', 0);

		$aEvents = $oEvents->findAll();

		$aReturnEvents = array();

		$oModule = Core_Entity::factory('Module')->getByPath('event');

		foreach ($aEvents as $oEvent)
		{
			$oTmpEvent = new StdClass();

			$oEvent_User = $oEvent->Event_Users->getByUser_id($oUser->id);

			$oTmpEvent->id = $oEvent->id . '_' . $oModule->id;
			$oTmpEvent->title = $oEvent->name;
			$oTmpEvent->path = '/admin/event/index.php';

			$oTmpEvent->description = Core_Str::cut($oEvent->description, 100);
			$oTmpEvent->place = $oEvent->place;

			if (!$oEvent_User->creator)
			{
				$oTmpEvent->editable = FALSE;
				$oTmpEvent->droppable = FALSE;
			}

			if (!is_null($oEvent->Event_Type->id))
			{
				$oTmpEvent->textColor = '#262626'; //$oEvent->Event_Type->color;
				$oTmpEvent->borderColor = $oEvent->Event_Type->color;
			}
			else
			{
				$oTmpEvent->textColor = '#2dc3e8';
				$oTmpEvent->borderColor = '#2dc3e8';
			}

			// c - дата в формате стандарта ISO 8601 (добавлено в PHP 5), например 2004-02-12T15:19:21+00:00
			$oTmpEvent->start = date('c', Core_Date::sql2timestamp($oEvent->start));

			$oTmpEvent->allDay = $oEvent->all_day ? TRUE : FALSE;

			if (!is_null($oEvent->deadline) && $oEvent->deadline != '0000-00-00 00:00:00')
			{
				$oTmpEvent->end = $oEvent->all_day
					// Добавляем минуту потому как при показе в FullCalerndar события, продолжительностью весь день,
					// в качестве конечной даты используется начало суток, следующих за завершающим днем события
					? date('Y-m-dT00:00:00', Core_Date::sql2timestamp($oEvent->deadline) + 60)
					: date('c', Core_Date::sql2timestamp($oEvent->deadline));
			}

			$aReturnEvents[] = $oTmpEvent;
		}

		return $aReturnEvents;
	}

	/**
	 * Перемещение события на календаре
	 * @param int $entity_id идентификатор события
	 * @param int $startMilliseconds timestamp начала события
	 * @param int $allDay весь день
	 * @return array
	 */
	public function calendarEventDrop($entity_id, $startTimestamp, $allDay)
	{
		$oEvent = Core_Entity::factory('Event', $entity_id);
		$oUser = Core_Auth::getCurrentUser();
		$allDay = intval($allDay);

		$oEvent_User = $oEvent->Event_Users->getByUser_id($oUser->id);

		// Разрешаем менять параметры события только его создателю
		if (!$oEvent_User->creator)
		{
			return FALSE;
		}

		// Продолжительность события в секундах до переноса
		$eventDurationSeconds = ($oEvent->deadline != '0000-00-00 00:00:00' && !empty($oEvent->deadline))
			? Core_Date::sql2timestamp($oEvent->deadline) - Core_Date::sql2timestamp($oEvent->start)
			: 0;

		// Весь день
		if ($allDay)
		{
			$oEvent->start = date('Y-m-d 00:00:00', $startTimestamp);

			$oEvent->deadline = date('Y-m-d 23:59:59', Core_Date::sql2timestamp($oEvent->start) + $eventDurationSeconds);

			if (!$oEvent->all_day)
			{
				$oEvent->duration_type = 0; // минуты
				//$oEvent->duration = $eventDurationSeconds / 60;
				$oEvent->duration = (Core_Date::sql2timestamp($oEvent->deadline) - Core_Date::sql2timestamp($oEvent->start)) / 60;
			}
		}
		else
		{
			$oEvent->start = Core_Date::timestamp2sql($startTimestamp);

			if ($eventDurationSeconds)
			{
				$oEvent->deadline = Core_Date::timestamp2sql($startTimestamp + $eventDurationSeconds);
			}
		}

		$oEvent->all_day = intval($allDay);
		$oEvent->save();

		return TRUE;
	}

	/**
	 * Изменяет продолжительность события, связанного с календарем
	 * @param int $entity_id идентификатор события
	 * @param int $deltaSeconds размер изменения продолжительности в секундах
	 * @return array
	 */
	public function calendarEventResize($entity_id, $deltaSeconds)
	{
		$oEvent = Core_Entity::factory('Event', $entity_id);
		$oUser = Core_Auth::getCurrentUser();

		$oEvent_User = $oEvent->Event_Users->getByUser_id($oUser->id);

		// Разрешаем менять параметры события, в частности продолжительность, только его создателю
		// Менять продолжительность можно событию, которому задана продолжительность
		if (!$oEvent_User->creator)
		{
			return FALSE;
		}

		// Продолжительность события в секундах до изменения
		$eventDurationSeconds = ($oEvent->deadline != '0000-00-00 00:00:00' && !empty($oEvent->deadline))
			? Core_Date::sql2timestamp($oEvent->deadline) - Core_Date::sql2timestamp($oEvent->start)
			: 0;

		// Измененная продолжительности события в секундах
		$newEventDurationSeconds = $eventDurationSeconds + intval(Core_Array::getRequest('deltaSeconds'));

		// Новая продолжительность события в минутах
		$durationValue = floor($newEventDurationSeconds / 60);

		// Для событий с установленным параметром "Весь день" продолжительность измеряем в минутах
		// потому как для таких событий продолжительность равна количеству дней минус 1 минута
		if (!$oEvent->all_day)
		{
			// $durationType - тип интервала
			if ($durationValue < 60)
			{
				$oEvent->duration_type = 0; // Минуты
			}
			elseif (($durationValue = floor($newEventDurationSeconds / 60 / 60) ) && $durationValue < 24)
			{
				$oEvent->duration_type = 1; // Часы
			}
			else
			{
				$durationValue = floor($newEventDurationSeconds / 60 / 60 / 24);
				$oEvent->duration_type = 2; // Дни
			}
		}

		$oEvent->deadline = Core_Date::timestamp2sql( Core_Date::sql2timestamp($oEvent->start) + $newEventDurationSeconds);
		//$oEvent->deadline = Core_Date::timestamp2sql( Core_Date::sql2timestamp($oEvent->deadline) + $deltaSeconds);
		$oEvent->duration = $durationValue;
		$oEvent->save();

		return TRUE;
	}

	/**
	 * Показ формы добавления/редактирования события, связанного с календарем
	 * @param int $entity_id идентификатор события
	 * @return array
	 */
	public function calendarAddEvent($entity_id = 0)
	{
		$entity_id = intval($entity_id);

		Core_Session::close();

		$iAdmin_Form_Id = 220;
		$sAdminFormEntityAction = '/admin/event/index.php';
		$sModule = 'event';

		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

		$hostcmsParams = Core_Array::getRequest('hostcms');

		$windowId = !is_null($hostcmsParams) && isset($hostcmsParams['window']) && !empty($hostcmsParams['window'])
			? $hostcmsParams['window']
			: 'id_content';

		// Контроллер формы
		$oAdmin_Form_Entity_Controller = Admin_Form_Controller::create($oAdmin_Form);
		$oAdmin_Form_Entity_Controller
			->module(Core_Module::factory($sModule))
			->ajax(TRUE)
			//->setUp()
			->checked(array(0 => array($entity_id)))
			->path($sAdminFormEntityAction)
			->window($windowId)
			->action('edit');

		// Действие редактирования
		$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
			->Admin_Form_Actions
			->getByName('edit');

		$oAdmin_Form_Action_Controller_Type_Edit = Admin_Form_Action_Controller::factory(
			'Event_Controller_Edit', $oAdmin_Form_Action
		);

		$oAdmin_Form_Action_Controller_Type_Edit
			->controller($oAdmin_Form_Entity_Controller)
			->setDatasetId(0)
			->setObject(Core_Entity::factory('Event', $entity_id))
			->execute($operation = '');

		ob_start();

		echo $oAdmin_Form_Action_Controller_Type_Edit->getContent();

		$oAdmin_Answer = Core_Skin::instance()->answer();
		$oAdmin_Answer
			->ajax(TRUE)
			->content(
				ob_get_clean()
			)
			->execute();

		exit();
	}

	/**
	 * Удаление события, связанного с календарем
	 * @param int $entity_id идентификатор события
	 * @return array
	 */
	public function calendarEventDelete($entity_id)
	{
		$oEvent = Core_Entity::factory('Event', $entity_id);
		$oUser = Core_Auth::getCurrentUser();

		$oEvent_User = $oEvent->Event_Users->getByUser_id($oUser->id);

		// Разрешаем менять параметры события, в частности продолжительность, только его создателю
		// Менять продолжительность можно событию, которому задана продолжительность
		if (!$oEvent_User->creator)
		{
			return FALSE;
		}

		$oEvent->markDeleted();

		return TRUE;
	}
}