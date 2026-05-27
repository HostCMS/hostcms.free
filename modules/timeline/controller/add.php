<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_Controller_Add
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_Controller_Add extends Crm_Note_Controller_Add
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		parent::execute();

		$oCrm_Note = $this->_object;

		$oTimeline = Core_Entity::factory('Timeline');
		$oTimeline->add($oCrm_Note);

		// $parent_id = Core_Array::getRequest('parent_timeline_id', 0, 'int');
		// $oTimeline->parent_id = $parent_id;
		// $oTimeline->save();

		$aUserIDs = array();

		$oUser = Core_Auth::getCurrentUser();

		$oTimeline_User = Core_Entity::factory('Timeline_User');
		$oTimeline_User->user_id = $oUser->id;
		$oTimeline->add($oTimeline_User);

		// Ищем тег <span, внутри которого есть атрибут data-user-id="число"
		$pattern = '/<span\s+[^>]*data-user-id=["\'](\d+)["\'][^>]*>/i';

		if (preg_match_all($pattern, $oCrm_Note->text, $matches))
		{
			if (isset($matches[1]))
			{
				foreach ($matches[1] as $user_id)
				{
					if ($user_id)
					{
						$oTimeline_User = Core_Entity::factory('Timeline_User');
						$oTimeline_User->user_id = $user_id;
						$oTimeline->add($oTimeline_User);

						$aUserIDs[] = $user_id;
					}
				}
			}
		}

		if ($oCrm_Note->parent_id)
		{
			$oParent_Timeline = $oCrm_Note->Crm_Note->Timeline;

			if ($oParent_Timeline->id)
			{
				$aTimeline_Users = $oParent_Timeline->Timeline_Users->findAll();
				foreach ($aTimeline_Users as $oTimeline_User)
				{
					$aUserIDs[] = $oTimeline_User->user_id;
				}
			}
		}

		$aUserIDs = array_unique($aUserIDs);
		if (count($aUserIDs))
		{
			$oModule = Core_Entity::factory('Module')->getByPath('timeline');

			$bRemoveEmoji = strtolower(Core_Array::get(Core_DataBase::instance()->getConfig(), 'charset')) != 'utf8mb4';

			$description = Core_Str::cut(html_entity_decode(strip_tags($oCrm_Note->text), ENT_COMPAT, 'UTF-8'), 150);

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(Core::_('Timeline_Note.add_notification', $oCrm_Note->subject, FALSE))
				->description(
					$bRemoveEmoji
						? Core_Str::removeEmoji($description)
						: $description
				)
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type(0) // 0 - В ленту добавлена заметка
				->entity_id($oCrm_Note->id)
				->save();

			foreach ($aUserIDs as $user_id)
			{
				// Связываем уведомление с сотрудниками
				Core_Entity::factory('User', $user_id)->add($oNotification);
			}
		}

		$aFiles = Core_Array::getFiles('file', array());

		if (is_array($aFiles) && isset($aFiles['name']))
		{
			$oCrm_Note->dir = $oTimeline->getHref();
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
						->setHref($oTimeline->getHref())
						->saveFile($aFile['tmp_name'], $aFile['name']);
				}
			}
		}

		$this->addMessage("<script>$(function() {
			$.adminLoad({ path: hostcmsBackend + '/timeline/index.php', additionalParams: '', windowId: 'id_content' });
		});</script>");
	}
}