<?php
/**
 * Ipaddress.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'ipaddress');

// Код формы
$iAdmin_Form_Id = 384;
$sAdminFormAction = '/admin/ipaddress/visitor/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Ipaddress_Visitor.title'))
	->pageTitle(Core::_('Ipaddress_Visitor.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Ipaddress_Visitor.filter_menu'))
		->icon('fa-solid fa-filter')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/ipaddress/visitor/filter/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/ipaddress/visitor/filter/index.php', NULL, NULL, '')
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Ipaddress.show_ip_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/ipaddress/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/ipaddress/index.php', NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Ipaddress_Visitor.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Ipaddress_Visitor')
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$aOptions = array(
	0 => Core::_('Ipaddress_Visitor.result0'),
	1 => Core::_('Ipaddress_Visitor.result1'),
	2 => Core::_('Ipaddress_Visitor.result2')
);

$oAdmin_Form_Dataset
	->changeField('result', 'type', 8)
	->changeField('result', 'list', $aOptions);

// Показ формы
$oAdmin_Form_Controller->execute();