<?php
/**
 * Sites.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'site');

$iAdmin_Form_Id = 43;

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sAdminFormAction = '/{admin}/site/alias/index.php';
$sSitePath = '/{admin}/site/index.php';

$iSiteId = Core_Array::getRequest('site_id', 0, 'int');

$oSite = Core_Entity::factory('Site')->find($iSiteId);

$pageTitle = Core::_('Site_Alias.site_show_domen_title', $oSite->name, FALSE);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($pageTitle)
	->pageTitle($pageTitle);

// Меню
$oAdminFormEntityMenus = Admin_Form_Entity::factory('Menus');

$oAdminFormEntityMenus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Site_Alias.site_link_domens'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

$oAdmin_Form_Controller->addEntity($oAdminFormEntityMenus);

// Построение хлебных крошек
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая хлебная крошка будет всегда
$oAdminFormEntityBreadcrumbs
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('site.site_show_site_title_list'))
			->href($oAdmin_Form_Controller->getAdminLoadHref($sSitePath))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sSitePath)))
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name($pageTitle)
			->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath()))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath()))
		);

// Хлебные крошки добавляем контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);

// Действие редактирования
$oAdminFormAction = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdminFormAction && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSiteAliasControllerEdit = new Site_Alias_Controller_Edit($oAdminFormAction);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSiteAliasControllerEdit);

	// Крошки при редактировании
	$oSiteAliasControllerEdit->addEntity($oAdminFormEntityBreadcrumbs);
}

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

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Перенести"
$oAdminFormActionMove = $oAdmin_Form->Admin_Form_Actions->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$Site_Alias_Controller_Move = Admin_Form_Action_Controller::factory(
		'Site_Alias_Controller_Move', $oAdminFormActionMove
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Site_Alias_Controller_Move);
}

$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Site_Alias')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset->addCondition(
	array('where' => array('site_id', '=', $iSiteId))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Предупреждение
Core_Event::attach('Admin_Form_Controller.onBeforeShowContent', function($oAdmin_Form_Controller) {
	$iSiteId = Core_Array::getRequest('site_id', 0, 'int');
	if (is_null(Core_Entity::factory('Site', $iSiteId)->getCurrentAlias()))
	{
		$oAdmin_Form_Controller->addMessage(
			Core_Message::get(Core::_('Site_Alias.error_current_alias'), 'error')
		);
	}
});

// Показ формы
$oAdmin_Form_Controller->execute();