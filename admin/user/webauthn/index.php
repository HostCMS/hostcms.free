<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 396;
$sAdminFormAction = '/{admin}/user/webauthn/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$user_id = Core_Array::getGet('user_id', 0, 'int');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User_Webauthn.title'))
	->pageTitle(Core::_('User_Webauthn.title'));

$additionalParams = "user_id={$user_id}";

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.ua_show_users_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/user/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/user/index.php', NULL, NULL, '')
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User_Webauthn.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('User_Webauthn')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('where' => array('user_webauthns.user_id', '=', $user_id))
	);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();