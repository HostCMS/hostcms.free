<?php
/**
 * Constants.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'constant');

// Код формы
$iAdmin_Form_Id = 26;

$sAdminFormAction = '/admin/constant/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Constant.menu'))
	->pageTitle(Core::_('Constant.menu'));

$oConstant_Dir = Core_Entity::factory('Constant_Dir', Core_Array::getGet('constant_dir_id', 0));

if ($oConstant_Dir->id)
{
	$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

	$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Constant_Dir.root'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 'constant_dir_id=0'))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 'constant_dir_id=0')));
	$oConstant_Dir_Tmp = $oConstant_Dir;
	$aBreadcrumbs = array();
	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oConstant_Dir_Tmp->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 'constant_dir_id='.$oConstant_Dir_Tmp->id))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 'constant_dir_id='.$oConstant_Dir_Tmp->id));
	} while ($oConstant_Dir_Tmp = $oConstant_Dir_Tmp->getParent());
	$aBreadcrumbs = array_reverse($aBreadcrumbs);
	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}

	$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Constant.main_menu'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Constant_Dir.menu_group'))
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

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	if (!$oConstant_Dir->id)
	{
		$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');
		$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Constant_Dir.root'))
			->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 'constant_dir_id=0'))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 'constant_dir_id=0')));
	}

	$oAdmin_Form_Action_Controller_Type_Edit = Admin_Form_Action_Controller::factory(
		'Constant_Controller_Edit', $oAdmin_Form_Action
	);
	$oAdmin_Form_Action_Controller_Type_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Action_Controller_Type_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
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

$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Constant_Dir')
);

$oAdmin_Form_Dataset->addCondition(array('where' =>array('parent_id', '=', $oConstant_Dir->id)));
$oAdmin_Form_Dataset->changeField('active', 'type', 1);

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Constant')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset->addCondition(array('where' =>array('constant_dir_id', '=', $oConstant_Dir->id)));
$oAdmin_Form_Dataset->changeField('name', 'type', 1);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
