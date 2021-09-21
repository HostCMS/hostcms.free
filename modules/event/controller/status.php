<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Events.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller_Status extends Admin_Form_Action_Controller
{

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */

	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			$eventStatusId = Core_Array::getRequest('eventStatusId');

			if (is_null($eventStatusId))
			{
				throw new Core_Exception("eventStatusId is NULL");
			}

			$oOriginalEventStatus = $this->_object->Event_Status;

			if ($eventStatusId)
			{
				$oEvent_Status = Core_Entity::factory('Event_Status')->find(intval($eventStatusId));

				if (!is_null($oEvent_Status->id))
				{
					$eventStatusId = $oEvent_Status->id;
					$eventStatusIsFinal = $oEvent_Status->final;
				}
				else
				{
					throw new Core_Exception("eventStatusId is unknown");
				}

				$sNewEventStatusName = $oEvent_Status->name;
			}
			else
			{
				// Без статуса
				$eventStatusId = 0;
				$eventStatusIsFinal = 0;

				$sNewEventStatusName = Core::_('Event.notStatus');
			}

			$oEvent = $this->_object;
			$oEvent->event_status_id = $eventStatusId;
			$oEvent->completed = $eventStatusIsFinal;
			$oEvent->save();

			// Отправка уведомлений о смене статуса

			// Текущий сотрудник
			$oUser = Core_Auth::getCurrentUser();

			// Ответственные сотрудники
			$oEventUsers = $oEvent->Event_Users;

			$oEventUsers
				->queryBuilder()
				->where('user_id', '!=', $oUser->id);

			$aEventUsers = $oEventUsers->findAll();

			$sOriginalEventStatusName = !$oOriginalEventStatus->id ? Core::_('Event.notStatus') : $oOriginalEventStatus->name;

			$oModule = Core_Entity::factory('Module')->getByPath('event');

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(strip_tags($oEvent->name))
				->description(Core::_('Event.notificationDescriptionType6', strip_tags($sOriginalEventStatusName), strip_tags($sNewEventStatusName)))
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type(6) // 6 - статус дела изменен
				->entity_id($oEvent->id)
				->save();

			// Связываем уведомление с ответственными сотрудниками
			foreach ($aEventUsers as $oEventUser)
			{
				Core_Entity::factory('User', $oEventUser->user_id)
					->add($oNotification);
			}

			return TRUE;
		}
	}
}