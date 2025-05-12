<?php
/**
 * Libs.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'lib');

$iAdmin_Form_Id = 33;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Путь к контроллеру формы ЦА
$sAdminFormAction = '/{admin}/lib/property/index.php';

// Путь к контроллеру предыдущей формы
$sLibPath = '/{admin}/lib/index.php';

// Идентификатор ТДС
$iLibId = Core_Array::getRequest('lib_id', 0, 'int');
$oLib = Core_Entity::factory('Lib')->find($iLibId);

// Идентификатор группы ТДС
$iLibDirId = Core_Array::getRequest('lib_dir_id', 0, 'int');

$parent_id = Core_Array::getRequest('parent_id', 0, 'int');

$pageTitle = Core::_('lib_property.lib_property_show_title', $oLib->name, FALSE);

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
		->name(Core::_('Admin_Form.add'))
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
$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('lib.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sLibPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sLibPath, NULL, NULL, '')
		)
);

// Если передан идентификатор группы ТДС, тогда строим дополнительные хлебные крошки
if ($iLibDirId)
{
	$oLibDir = Core_Entity::factory('Lib_Dir')->find($iLibDirId);

	if (!is_null($oLibDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'lib_dir_id=' . intval($oLibDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
					->name($oLibDir->name)
					->href(
						$oAdmin_Form_Controller->getAdminLoadHref($sLibPath, NULL, NULL, $additionalParams)
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax($sLibPath, NULL, NULL, $additionalParams)
					);

		} while ($oLibDir = $oLibDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdminFormEntityBreadcrumb)
		{
			$oAdminFormEntityBreadcrumbs->add(
				$oAdminFormEntityBreadcrumb
			);
		}
	}
}

// Дополнительные параметры для хлебной крошки на эту же страницу
$additionalParams = "lib_dir_id={$iLibDirId}&lib_id={$iLibId}";

$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($pageTitle)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
);

// Хлебные крошки добавляем контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);

// Действие редактирования
$oAdminFormAction = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdminFormAction && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oLibPropertyControllerEdit = new Lib_Property_Controller_Edit
	(
		$oAdminFormAction
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLibPropertyControllerEdit);

	// Крошки при редактировании
	$oLibPropertyControllerEdit->addEntity($oAdminFormEntityBreadcrumbs);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oLibPropertyControllerApply = new Admin_Form_Action_Controller_Type_Apply
	(
		$oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLibPropertyControllerApply);
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

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lib_Property')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('parent_id', '=', $parent_id)))
	->addCondition(array('where' => array('lib_id', '=', $iLibId)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addExternalReplace('{lib_dir_id}', $iLibDirId);

// Показ формы
$oAdmin_Form_Controller->execute();
