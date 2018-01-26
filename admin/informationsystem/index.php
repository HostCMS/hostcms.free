<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'informationsystem');

// Код формы
$iAdmin_Form_Id = 11;
$sAdminFormAction = '/admin/informationsystem/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Informationsystem.information_systems_add_form_link'))
	->pageTitle(Core::_('Informationsystem.information_systems_add_form_link'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
		->name(Core::_('Informationsystem.show_comments_link3'))
		->icon('fa fa-tasks')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem.show_information_systems_link'))
				->img('/admin/images/folder_page_add.gif')
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Informationsystem_Dir.dir_add'))
		->icon('fa fa-folder-open')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->img('/admin/images/folder_page_add.gif')
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$informationsystem_dir_id = intval(Core_Array::getGet('informationsystem_dir_id', 0));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Informationsystem.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($informationsystem_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oInformationsystemDir = Core_Entity::factory('Informationsystem_Dir')->find($informationsystem_dir_id);

	if (!is_null($oInformationsystemDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'informationsystem_dir_id=' . intval($oInformationsystemDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oInformationsystemDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oInformationsystemDir = $oInformationsystemDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}

		// Добавляем все хлебные крошки контроллеру
		$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	}
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oInformationsystem_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Informationsystem_Controller_Edit', $oAdmin_Form_Action
	);

	$oInformationsystem_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystem_Controller_Edit);
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Informationsystem_Dir')
);
$oAdmin_Form_Dataset->changeField('name', 'class', 'semi-bold');
// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('parent_id', '=', $informationsystem_dir_id)
	)
)->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Informationsystem')
);

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
		array('where' =>
			array('informationsystem_dir_id', '=', $informationsystem_dir_id)
		)
	)->addCondition(
		array('where' =>
			array('site_id', '=', CURRENT_SITE)
		)
	)
	->changeField('name', 'link', '/admin/informationsystem/item/index.php?informationsystem_id={id}')
	->changeField('name', 'onclick', "$.adminLoad({path: '/admin/informationsystem/item/index.php', additionalParams: 'informationsystem_id={id}', windowId: '{windowId}'}); return false");

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Действие "Удалить файл watermark"
$oAdminFormActionDeleteWatermarkFile = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteWatermarkFile');

if ($oAdminFormActionDeleteWatermarkFile && $oAdmin_Form_Controller->getAction() == 'deleteWatermarkFile')
{
	$oInformationsystemControllerDeleteWatermarkFile = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteWatermarkFile
	);

	$oInformationsystemControllerDeleteWatermarkFile
		->methodName('deleteWatermarkFile')
		->divId(array('preview_large_watermark_file', 'delete_large_watermark_file'));

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystemControllerDeleteWatermarkFile);
}

// Показ формы
$oAdmin_Form_Controller->execute();