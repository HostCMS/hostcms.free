<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User Module.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.8';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2019-05-27';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'user';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 10,
				'block' => 2,
				'ico' => 'fa fa-user',
				'name' => Core::_('User.menu'),
				'href' => "/admin/user/index.php",
				'onclick' => "$.adminLoad({path: '/admin/user/index.php'}); return false",
				'submenu' => array(
					array(
						'sorting' => 10,
						'ico' => 'fa fa-battery-3',
						'name' => Core::_('User.timesheet_title'),
						'href' => "/admin/user/timesheet/index.php",
						'onclick' => "$.adminLoad({path: '/admin/user/timesheet/index.php'}); return false"
					)
				)
			)
		);

		return parent::getMenu();
	}

	/**
	 * Call new notifications
	 */
	public function callNotifications()
	{
		$oUser = Core_Entity::factory('User')->getCurrent();

		if (!is_null($oUser))
		{
			$oModule = Core::$modulesList['user'];

			// $oUser_Workday = $oUser->User_Workdays->getByDate($currentDay, FALSE);
			$oUser_Workday = $oUser->User_Workdays->getLast();

			if (!is_null($oUser_Workday))
			{
				$currentDay = Core_Date::timestamp2sqldate(time());
				$workdayStatus = $oUser->getStatusWorkday($currentDay);

				switch ($workdayStatus)
				{
					case 1:
						// Сотрудник не завершил рабочий день, нотификация еще не отправлялась
						if (!$oUser_Workday->notify_day_expired)
						{
							$oUser_Workday->notify_day_expired = 1;
							$oUser_Workday->save();

							$oNotification = Core_Entity::factory('Notification');
							$oNotification
								->title(Core::_('User_Workday.notification_expired_day_title'))
								->description(Core::_('User_Workday.notification_expired_day_description'))
								->datetime(Core_Date::timestamp2sql(time()))
								->module_id($oModule->id)
								->type(2) // 2 - Сотрудник не звершил рабочий день
								->entity_id($oUser->id)
								->save();

							// Связываем уведомление с сотрудником
							$oUser->add($oNotification);
						}
					break;
					case 5:
						// Нотификация об окончании дня еше не отправлялась
						if (!$oUser_Workday->notify_day_end)
						{
							$oUser_Workday->notify_day_end = 1;
							$oUser_Workday->save();

							$oNotification = Core_Entity::factory('Notification');
							$oNotification
								->title(Core::_('User_Workday.notification_end_day_title'))
								->description(Core::_('User_Workday.notification_end_day_description'))
								->datetime(Core_Date::timestamp2sql(time()))
								->module_id($oModule->id)
								->type(0) // 0 - напоминание о завершении дня
								->entity_id($oUser->id)
								->save();

							// Связываем уведомление с сотрудником
							$oUser->add($oNotification);
						}
					break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Get Notification Design
	 * @param int $type
	 * @param int $entityId
	 * @return array
	 */
	public function getNotificationDesign($type, $entityId)
	{
		// Идентификатор формы "Учет рабочего времени"
		$iAdmin_Form_Id = 246;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

		// Контроллер формы
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
		$oAdmin_Form_Controller
			->path('/admin/user/timesheet/index.php')
			->window('id_content');

		switch ($type)
		{
			// Завершение рабочего дня
			case 0:
				$sIconIco = "fa-child";
				$sIconColor = "white";
				$sBackgroundColor = "bg-success";
				$sNotificationColor = 'success';
			break;
			// Уведомление руководителя о завершениии дня с другим временем
			case 1:
				$sIconIco = "fa-clock-o";
				$sIconColor = "white";
				$sBackgroundColor = "bg-warning";
				$sNotificationColor = 'warning';
			break;
			// Сотрудник не завершил предыдущий рабочий день
			case 2:
				$sIconIco = "fa-exclamation-circle";
				$sIconColor = "white";
				$sBackgroundColor = "bg-danger";
				$sNotificationColor = 'danger';
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
			'href' => $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 0, 0),
			'onclick' => $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 0, 0),
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			)
		);
	}
}