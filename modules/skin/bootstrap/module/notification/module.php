<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Notification. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
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

		$oCurrent_User = Core_Auth::getCurrentUser();
		$iRequestUserId = intval(Core_Array::getPost('currentUserId'));

		switch ($type)
		{
			// Обновление списка уведомлений
			case 0:
				Core_Session::close();

				$aModules = Core_Entity::factory('Module')->getAllByActive(1);

				// Для каждого модуля получаем актуальные на данный момент уведомления
				foreach ($aModules as $oModule)
				{
					$oModule->loadModule();
					if (method_exists($oModule->Core_Module, 'callNotifications'))
					{
						$oModule->Core_Module->callNotifications();
					}
				}

				$aJson = array();

				// Идентификатор последнего загруженного уведомления для пользователя
				$iLastNotificationId = intval(Core_Array::getPost('lastNotificationId'));

				//$iLocalStorage = intval(Core_Array::getPost('localStorage'));

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
						->orderBy('notifications.id', 'DESC')
						->limit(30);

					// При наличии ранее загруженных уведомлений загружаем новые и непрочитанные
					/* if ($iLastNotificationId)
					{
						$oNotifications->queryBuilder()
							->where('notifications.id', '>', $iLastNotificationId)
							->where('notification_users.read', '=', 0);
					}
					else // При отсутствии ранее загруженных уведомлений загружаем непрочитанные
					{
						$oNotifications->queryBuilder()
							->where('notification_users.read', '=', 0);
					} */

					$oNotifications->queryBuilder()
						->where('notification_users.read', '=', 0);

					$current = 0;
					$steps = 4;

					do {
						$aNotifications = array_reverse($oNotifications->findAll(FALSE));

						// Уведомления пользователя
						foreach ($aNotifications as $oNotification)
						{
							$aNotification = array(
								'id' => $oNotification->id,
								'title' => strval($oNotification->title), // NULL => ''
								'description' => strval($oNotification->description), // NULL => ''
								'datetime' => Core_Date::sql2datetime($oNotification->datetime),
								'read' => $oNotification->read,
								'timeout' => '7000',
								'ajaxUrl' => '',
								'show' => 1,
							);

							$aNotificationDecorations = array();
							if ($oNotification->module_id)
							{
								$oCore_Module = $oNotification->Module->loadModule()->Core_Module;

								if (!is_null($oCore_Module))
								{
									$aNotificationDecorations = $oCore_Module->getNotificationDesignByNotification($oNotification);

									$aNotification['href'] = Core_Array::get($aNotificationDecorations, 'href');
									$aNotification['onclick'] = "$(this).parents('li.open').click(); " . Core_Array::get($aNotificationDecorations, 'onclick');
									$aNotification['icon'] = Core_Array::get($aNotificationDecorations, 'icon');
									$aNotification['notification'] = Core_Array::get($aNotificationDecorations, 'notification');
									$aNotification['extra'] = Core_Array::get($aNotificationDecorations, 'extra');
									$aNotification['site'] = Core_Array::get($aNotificationDecorations, 'site');
									$aNotification['timeout'] = Core_Array::get($aNotificationDecorations, 'timeout', $aNotification['timeout']);
									$aNotification['ajaxUrl'] = Core_Array::get($aNotificationDecorations, 'ajaxUrl');
									$aNotification['show'] = Core_Array::get($aNotificationDecorations, 'show', $aNotification['show']);
								}
							}

							// Новое сообщение
							if ($oNotification->id > $iLastNotificationId)
							{
								$aJson['newNotifications'][] = $aNotification;
							}
							// Непрочитанное ранее загруженное сообщение
							else/*if (!$oNotification->read)*/
							{
								$aJson['unreadNotifications'][] = $aNotification;
							}
						}

						if (count($aNotifications))
						{
							break;
						}

						sleep(3);

						$current++;

					} while ($current <= $steps);

					$aJson['lastNotificationId'] = count($aJson['newNotifications'])
						? intval($aJson['newNotifications'][count($aJson['newNotifications']) - 1]['id'])
						//? intval($aJson['newNotifications'][0]['id'])
						: $iLastNotificationId;

					// Данные о продолжительности рабочего дня
					$aJson['workdayDuration'] = $oCurrent_User->getWorkdayDuration(Core_Date::timestamp2sqldate(time()));
					$aJson['workdayStatus'] = $oCurrent_User->getStatusWorkday(Core_Date::timestamp2sqldate(time()));
				}

				Core::showJson($aJson);
			break;

			// Делаем уведомления прочитанными
			case 1:
				Core_Session::close();

				$aNotificationsListId = Core_Array::getPost('notificationsListId');

				if (!is_null($oCurrent_User) && $oCurrent_User->id == $iRequestUserId)
				{
					if (is_array($aNotificationsListId) && count($aNotificationsListId))
					{
						Core_QueryBuilder::update('notification_users')
							->set('read', 1)
							->where('user_id', '=', $iRequestUserId)
							->where('notification_id', 'IN', $aNotificationsListId)
							->execute();
					}

					/*foreach ($aNotificationsListId as $iNotificationId)
					{
						$oNotification_User = Core_Entity::factory('Notification', $iNotificationId)->Notification_Users->getByUser_id($iCurrentUserId);

						if (!is_null($oNotification_User))
						{
							$oNotification_User
								->read(1)
								->save();
						}
					}*/
				}

			break;
		}
	}
}