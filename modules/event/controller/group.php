<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Events.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Controller_Group extends Admin_Form_Action_Controller
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
			$eventGroupId = Core_Array::getRequest('eventGroupId');

			if (is_null($eventGroupId))
			{
				throw new Core_Exception("eventGroupId is NULL");
			}

			$oOriginalEventGroup = $this->_object->Event_Group;

			if ($eventGroupId)
			{
				$oEvent_Group = Core_Entity::factory('Event_Group')->find(intval($eventGroupId));

				if (!is_null($oEvent_Group->id))
				{
					$eventGroupId = $oEvent_Group->id;
				}
				else
				{
					throw new Core_Exception("eventGroupId is unknown");
				}

				$sNewEventGroupName = $oEvent_Group->name;
			}
			else
			{
				// Без статуса
				$eventGroupId = 0;
				$sNewEventGroupName = Core::_('Event.notGroup');
			}

			$oEvent = $this->_object;
			$oEvent->event_group_id = intval($eventGroupId);
			$oEvent->save();

			// Отправка уведомлений о смене группы

			// Текущий сотрудник
			$oUser = Core_Auth::getCurrentUser();

			// Ответственные сотрудники
			$oEventUsers = $oEvent->Event_Users;

			$oEventUsers
				->queryBuilder()
				->where('user_id', '!=', $oUser->id);

			$aEventUsers = $oEventUsers->findAll();

			$sOriginalEventGroupName = !$oOriginalEventGroup->id ? Core::_('Event.notGroup') : $oOriginalEventGroup->name;

			$oModule = Core_Entity::factory('Module')->getByPath('event');

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(strip_tags($oEvent->name))
				->description(Core::_('Event.notificationDescriptionType7', strip_tags($sOriginalEventGroupName), strip_tags($sNewEventGroupName)))
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