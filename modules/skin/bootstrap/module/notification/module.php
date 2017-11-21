<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Notification. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Notification_Module extends Notification_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'bootstrap';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'notification';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array();
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$type = intval($type);

		$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);

		switch ($type)
		{
			// Обновление списка уведомлений
			case 0:
				$oCurrent_User = Core_Entity::factory('User', 0)->getCurrent();

				Core_Session::close();

				$aModules = Core_Entity::factory('Module')->getAllByActive(1);

				// Для каждого модуля получаем актуальные на данный момент уведомления
				foreach($aModules as $oModule)
				{
					if (method_exists($oModule->Core_Module, 'callNotifications'))
					{
						$oModule->Core_Module->callNotifications();
					}
				}

				$aJson = array();

				$iRequestUserId = intval(Core_Array::getPost('currentUserId'));

				// Идентификатор последнего загруженного уведомления для пользователя
				$iLastNotificationId = intval(Core_Array::getPost('lastNotificationId'));

				if (!is_null($oCurrent_User) && $oCurrent_User->id == $iRequestUserId)
				{
					$aJson['userId'] = $oCurrent_User->id;
					//$aJson['sound'] = $oCurrent_User->sound;
					$aJson['newNotifications'] = $aJson['unreadNotifications'] = array();

					// Массив идентификаторов непрочитанных уведомлений
					//$aUnreadNotificationsId = Core_Array::getPost('unreadNotificationsId');

					$oNotifications = $oCurrent_User->Notifications;

					$oNotifications->queryBuilder()
						->select('notifications.*', array('notification_users.read', 'read'))
						->orderBy('notifications.id', 'ASC');
						//->orderBy('datetime');

					// При наличии ранее загруженных уведомлений загружаем новые и непрочитанные
					if ($iLastNotificationId)
					{
						$oNotifications->queryBuilder()
							//->where('notification_users.read', '=', 0)
							//->where('notifications.id', '>', $iLastNotificationId)
							->open()
							// Больше уже выведенного
							->where('notifications.id', '>', $iLastNotificationId)
							->setOr()
							// Непрочитанные
							->where('notification_users.read', '=', 0)
							->close();

					}
					else // При отсутствии ранее загруженных уведомлений загружаем непрочитанные
					{
						$oNotifications->queryBuilder()
							->where('notification_users.read', '=', 0);
					}

					$aNotifications = $oNotifications->findAll();

					// Уведомления пользователя
					foreach ($aNotifications as $oNotification)
					{
						$aNotification = array(
							'id' => $oNotification->id,
							'title' => $oNotification->title,
							'description' => $oNotification->description,
							'datetime' => Core_Date::sql2datetime($oNotification->datetime),
							'read' => $oNotification->read
						);

						//$oNotification_User = $oNotification->Notification_Users->getByUser_id($oCurrent_User->id);

						$aNotificationDecorations = array();
						if ($oNotification->module_id)
						{
							$oCore_Module = $oNotification->Module->Core_Module;

							if (!is_null($oCore_Module))
							{

								$aNotificationDecorations = $oCore_Module->getNotifications($oNotification->type, $oNotification->entity_id);

								$aNotification['href'] = Core_Array::get($aNotificationDecorations, 'href');
								$aNotification['onclick'] = Core_Array::get($aNotificationDecorations, 'onclick');
								$aNotification['icon'] = Core_Array::get($aNotificationDecorations, 'icon');
								$aNotification['notification'] = Core_Array::get($aNotificationDecorations, 'notification');
								$aNotification['extra'] = Core_Array::get($aNotificationDecorations, 'extra');
							}
						}

						// Новое сообщение
						if ($oNotification->id > $iLastNotificationId)
						{
							$aJson['newNotifications'][] = $aNotification;
						}
						// Непрочитанное ранее загруженное сообщение
						else/*if(!$oNotification->read)*/
						{
							$aJson['unreadNotifications'][] = $aNotification;
						}
					}
				}

				Core::showJson($aJson);
			break;

			// Делаем уведомления прочитанными
			case 1:
				Core_Session::close();

				$aNotificationsListId = Core_Array::getPost('notificationsListId');
				$iCurrentUserId = intval(Core_Array::getPost('currentUserId'));


				foreach ($aNotificationsListId as $iNotificationId)
				{
					$oNotification_User = Core_Entity::factory('Notification', $iNotificationId)->Notification_Users->getByUser_id($iCurrentUserId);

					$oNotification_User
						->read(1)
						->save();
				}

			break;
		}
	}
}