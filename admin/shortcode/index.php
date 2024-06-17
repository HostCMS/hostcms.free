<?php
/**
 * Hostcms Shortcode
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'shortcode');

// Код формы
$iAdmin_Form_Id = 204;
$sAdminFormAction = '/admin/shortcode/index.php';

$oShortcode_Dir = Core_Entity::factory('Shortcode_Dir', Core_Array::getGet('shortcode_dir_id', 0));

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shortcode.title'))
	->pageTitle(Core::_('Shortcode.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$additionalParams = "shortcode_dir_id={$oShortcode_Dir->id}";

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shortcode.add_shortcode'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shortcode.add_shortcode_dir'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shortcode.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, ''))
);

// Крошки по группам
if ($oShortcode_Dir->id)
{
	$oShortcode_Dir_Breadcrumbs = $oShortcode_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShortcode_Dir_Breadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shortcode/index.php', NULL, NULL, "shortcode_dir_id={$oShortcode_Dir_Breadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shortcode/index.php', NULL, NULL, "shortcode_dir_id={$oShortcode_Dir_Breadcrumbs->id}"));
	}
	while ($oShortcode_Dir_Breadcrumbs = $oShortcode_Dir_Breadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oShortcode_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shortcode_Controller_Edit', $oAdmin_Form_Action
	);

	$oShortcode_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShortcode_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

$oAdminFormActionRollback = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('rollback');

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
	Core_Entity::factory('Shortcode_Dir')
);

$oAdmin_Form_Dataset->changeField('name', 'class', 'semi-bold');
$oAdmin_Form_Dataset
	->addCondition(array('where' => array('parent_id', '=', $oShortcode_Dir->id)));

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shortcode')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset
	->addCondition(array('where' => array('shortcode_dir_id', '=', $oShortcode_Dir->id)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->addExternalReplace('{shortcode_dir_id}', $oShortcode_Dir->id);

// Показ формы
$oAdmin_Form_Controller->execute();