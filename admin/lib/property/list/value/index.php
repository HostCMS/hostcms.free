<?php
/**
 * Libs.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'lib');

// Код формы
$iAdmin_Form_Id = 3;
$sAdminFormAction = '/admin/lib/property/list/value/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Lib_Property_List_Value.form_title'))
	->pageTitle(Core::_('Lib_Property_List_Value.form_title'));

// Меню формы
$oAdminFormEntityMenus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdminFormEntityMenus->add(	
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Lib_Property_List_Value.menu_add'))
		->icon('fa fa-plus')
		->img('/admin/images/list_add.gif')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityMenus);

// Строка навигации
$iLibPropertyId = Core_Array::getRequest('property_id', 0);
$oLibProperty = Core_Entity::factory('Lib_Property', $iLibPropertyId);

$oLib = $oLibProperty->Lib;

$pageTitle = Core::_('lib_property.lib_property_show_title', $oLib->name, FALSE);

// Элементы строки навигации
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Lib.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$prevFormPath = '/admin/lib/index.php';

$iLibDirId = $oLib->Lib_Dir->id;
if ($iLibDirId)
{
	// Если передана родительская группа - строим хлебные крошки
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
					$oAdmin_Form_Controller->getAdminLoadHref($prevFormPath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($prevFormPath, NULL, NULL, $additionalParams)
				);
		} while ($oLibDir = $oLibDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdminFormEntityBreadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}

		// Добавляем все хлебные крошки контроллеру
		$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);
	}
}

$additionalParams = "lib_dir_id={$iLibDirId}&lib_id={$oLib->id}";

$prevFormPath = '/admin/lib/property/index.php';

$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($pageTitle)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($prevFormPath, NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($prevFormPath, NULL, NULL, $additionalParams)
		)
);

$additionalParams = "property_id={$iLibPropertyId}";

// Хлебные крошки на текущий элемент
$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Lib_Property_List_Value.current_breadcrumbs'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
);

// Действие редактирования
$oAdminFormAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdminFormAction && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oAdminFormActionControllerTypeEdit = Admin_Form_Action_Controller::factory(
		'Lib_Property_List_Value_Controller_Edit', $oAdminFormAction
	);

	// Хлебные крошки для контроллера редактирования
	$oAdminFormActionControllerTypeEdit
		->addEntity($oAdminFormEntityBreadcrumbs);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdminFormActionControllerTypeEdit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oLibPropertyListValueControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер "Применить" контроллеру формы
	$oAdmin_Form_Controller->addAction($oLibPropertyListValueControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
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

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lib_Property_List_Value')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('lib_property_id', '=', $iLibPropertyId)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();