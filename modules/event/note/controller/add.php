<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Note_Controller_Add extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null(Core_Array::getRequest('text_note')) && strlen(Core_Array::getRequest('text_note')))
		{
			$iEventId = intval(Core_Array::getGet('event_id', 0));
			$sCommentText = trim(strval(Core_Array::getRequest('text_note')));

			$oEvent_Note = Core_Entity::factory('Event_Note');
			$oEvent_Note->event_id = $iEventId;
			$oEvent_Note->text = $sCommentText;
			$oEvent_Note->datetime = Core_Date::timestamp2sql(time());
			$oEvent_Note->save();

			$oModule = Core_Entity::factory('Module')->getByPath('event');

			$oEvent = $oEvent_Note->Event;

			$aEvent_Users = $oEvent->Event_Users->findAll();
			foreach ($aEvent_Users as $oEvent_User)
			{
				// Добавляем уведомление
				$oNotification = Core_Entity::factory('Notification')
					->title(Core::_('Event_Note.add_notification', $oEvent->name, FALSE))
					->description($sCommentText)
					->datetime(Core_Date::timestamp2sql(time()))
					->module_id($oModule->id)
					->type(6) // 6 - В сделку добавлена заметка
					->entity_id($oEvent->id)
					->save();

				// Связываем уведомление с сотрудниками
				Core_Entity::factory('User', $oEvent_User->user_id)->add($oNotification);
			}
		}
	}
}