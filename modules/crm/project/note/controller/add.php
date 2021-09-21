<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Note_Controller_Add extends Admin_Form_Action_Controller
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
			$crm_project_id = intval(Core_Array::getGet('crm_project_id', 0));
			$sCommentText = trim(strval(Core_Array::getRequest('text_note')));

			$oCrm_Project_Note = Core_Entity::factory('Crm_Project_Note');
			$oCrm_Project_Note->crm_project_id = $crm_project_id;
			$oCrm_Project_Note->text = $sCommentText;
			$oCrm_Project_Note->datetime = Core_Date::timestamp2sql(time());
			$oCrm_Project_Note->save();

			$oModule = Core_Entity::factory('Module')->getByPath('crm_project');

			$oCrm_Project = $oCrm_Project_Note->Crm_Project;

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(Core::_('Crm_Project_Note.add_notification', $oCrm_Project->name, FALSE))
				->description($sCommentText)
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type(1) // 1 - В сделку добавлена заметка
				->entity_id($oCrm_Project->id)
				->save();

			// Связываем уведомление с сотрудниками
			Core_Entity::factory('User', $oCrm_Project->user_id)->add($oNotification);
		}
	}
}