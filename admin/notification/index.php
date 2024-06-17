<?php
/**
 * Notifications.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'notification');

// Код формы
$iAdmin_Form_Id = 216;
$sAdminFormAction = '/admin/notification/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Notification.notifications_title'))
	->pageTitle(Core::_('Notification.notifications_title'));

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Notification.notifications_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity
(
	Core_Entity::factory('Notification')
);

$oCurrentUser = Core_Auth::getCurrentUser();

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('notifications.*', array('notification_users.user_id', 'userId'), array(Core_QueryBuilder::expression('CONCAT_WS(" ", `notifications`.`title`, `notifications`.`description`)'), 'titleDescription'))
		)
	)
	->addCondition(
		array('join' => array('notification_users', 'notifications.id', '=', 'notification_users.notification_id'))
	)
	->addCondition(
		array('where' => array('notification_users.user_id', '=', $oCurrentUser->id) )
	)
	->addCondition(
		array('groupBy' => array('notifications.id'))
	)

	/*
	->addCondition(
		array('orderBy' => array('completed'))
	)*/;


// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset
(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();