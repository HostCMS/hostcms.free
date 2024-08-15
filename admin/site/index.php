<?php
/**
 * Sites.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'site');

// Код формы
$iAdmin_Form_Id = 42;
$sAdminFormAction = '/admin/site/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Site.site_show_site_title_list'))
	->pageTitle(Core::_('Site.site_show_site_title_list'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Site.site_show_site_title'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Site.menu2_caption'))
		->icon('fa fa-gears')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Site.menu2_sub_caption'))
				->icon('fa fa-user')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'accountInfo', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'accountInfo', NULL, 0, 0)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Хлебные крошки доступны только форме редактирования
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Site.site_show_site_title_list'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, ''))
);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSite_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Site_Controller_Edit', $oAdmin_Form_Action
	);

	$oSite_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSite_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oSiteControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSiteControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Site_Controller_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Удалить ico-файл"
$oAdminFormActionDeleteIcoFile = $oAdmin_Form->Admin_Form_Actions->getByName('deleteFavicon');

if ($oAdminFormActionDeleteIcoFile && $oAdmin_Form_Controller->getAction() == 'deleteFavicon')
{
	$oSite_Favicon_Delete_File = Admin_Form_Action_Controller::factory(
		'Site_Favicon_Delete_File', $oAdminFormActionDeleteIcoFile
	);

	$oSite_Favicon_Delete_File->site_favicon_id(Core_Array::getGet('site_favicon_id', 0, 'int'));

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSite_Favicon_Delete_File);
}

// Действие "Отметить удаленным"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('markDeleted');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'markDeleted')
{
	$oSite_Controller_Markdeleted = Admin_Form_Action_Controller::factory(
		'Site_Controller_Markdeleted', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSite_Controller_Markdeleted);
}

// Действие "Регистрационные данные"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('accountInfo');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'accountInfo')
{
	$oSite_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Site_Controller_AccountInfo', $oAdmin_Form_Action
	);

	// Хлебные крошки
	$oSite_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSite_Controller_Edit);
}

// Действие "Добавить с шаблоном дизайна"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('addSiteWithTemplate');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'addSiteWithTemplate')
{
	$oSite_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Site_Controller_addSiteWithTemplate', $oAdmin_Form_Action
	);

	// Хлебные крошки
	$oSite_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSite_Controller_Edit);
}

$oAdminFormActionRollback = $oAdmin_Form->Admin_Form_Actions->getByName('rollback');

if ($oAdminFormActionRollback && $oAdmin_Form_Controller->getAction() == 'rollback')
{
	$oControllerRollback = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Rollback', $oAdminFormActionRollback
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerRollback);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Site')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();