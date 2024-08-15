<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Note_Controller_Add extends Crm_Note_Controller_Add
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		parent::execute();

		$iEventId = Core_Array::getGet('event_id', 0, 'int');

		$result = Core_Array::getPost('result', 0, 'int');

		$completed = Core_Array::getPost('completed', 0, 'int');

		$oCrm_Note = $this->_object;

		$oEvent = Core_Entity::factory('Event', $iEventId);
		$oEvent->add($oCrm_Note);

		if ($result)
		{
			$oEvent->completed = $completed;
			$oEvent->save();
		}

		$aFiles = Core_Array::getFiles('file', array());

		if (is_array($aFiles) && isset($aFiles['name']))
		{
			$oCrm_Note->dir = $oEvent->getHref();
			$oCrm_Note->save();

			$iCount = count($aFiles['name']);

			for ($i = 0; $i < $iCount; $i++)
			{
				$aFile = array(
					'name' => $aFiles['name'][$i],
					'tmp_name' => $aFiles['tmp_name'][$i],
					'size' => $aFiles['size'][$i]
				);

				if (intval($aFile['size']) > 0)
				{
					$oCrm_Note_Attachment = Core_Entity::factory('Crm_Note_Attachment');
					$oCrm_Note_Attachment->crm_note_id = $oCrm_Note->id;

					$oCrm_Note_Attachment
						->setDir(CMS_FOLDER . $oCrm_Note->dir)
						->setHref($oEvent->getHref())
						->saveFile($aFile['tmp_name'], $aFile['name']);
				}
			}
		}

		$oModule = Core_Entity::factory('Module')->getByPath('event');

		$aEvent_Users = $oEvent->Event_Users->findAll();
		foreach ($aEvent_Users as $oEvent_User)
		{
			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(Core::_('Event_Note.add_notification', $oEvent->name, FALSE))
				->description(
					html_entity_decode(strip_tags($oCrm_Note->text), ENT_COMPAT, 'UTF-8')
				)
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type(6) // 6 - В дело добавлена заметка
				->entity_id($oEvent->id)
				->save();

			// Связываем уведомление с сотрудниками
			Core_Entity::factory('User', $oEvent_User->user_id)->add($oNotification);
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aExplodeWindowId = explode('-', $windowId);

		if (strpos($windowId, '-event-notes') !== FALSE)
		{
			$this->addMessage("<script>$(function() {
				$.adminLoad({ path: '/admin/event/timeline/index.php', additionalParams: 'event_id={$oEvent->id}', windowId: '{$aExplodeWindowId[0]}-event-timeline' });
			});</script>");
		}
		elseif (strpos($windowId, '-event-timeline') !== FALSE)
		{
			$this->addMessage("<script>$(function() {
				$.adminLoad({ path: '/admin/event/note/index.php', additionalParams: 'event_id={$oEvent->id}', windowId: '{$aExplodeWindowId[0]}-event-notes' });
			});</script>");
		}
	}
}