<?php
/**
 * Printlayout.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'printlayout');

// File download
if (Core_Array::getGet('downloadFile'))
{
	$oPrintlayout = Core_Entity::factory('Printlayout')->find(intval(Core_Array::getGet('downloadFile')));
	if (!is_null($oPrintlayout->id))
	{
		Core_File::download($oPrintlayout->getFilePath(), $oPrintlayout->file_name, array('content_disposition' => 'inline'));
	}
	else
	{
		throw new Core_Exception('Access denied');
	}

	exit();
}

// Код формы
$iAdmin_Form_Id = 251;
$sAdminFormAction = '/admin/printlayout/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Printlayout.menu'))
	->pageTitle(Core::_('Printlayout.menu'));

if (!is_null(Core_Array::getPost('showEmails')) && !is_null(Core_Array::getPost('representative')))
{
	$aJSON = array();

	$representative = strval(Core_Array::getPost('representative'));

	$aData = explode('_', $representative);

	if (isset($aData[0]))
	{
		// Представитель
		if (isset($aData[1]))
		{
			$entity = $aData[0];
			$id = $aData[1];

			$oEntity = NULL;

			switch ($entity)
			{
				case 'person':
					$oEntity = Core_Entity::factory('Siteuser_Person')->getById($id, FALSE);
				break;
				case 'company':
					$oEntity = Core_Entity::factory('Siteuser_Company')->getById($id, FALSE);
				break;
			}

			if (!is_null($oEntity))
			{
				$aDirectory_Emails = $oEntity->Directory_Emails->findAll();

				foreach ($aDirectory_Emails as $oDirectory_Email)
				{
					$aJSON[] = array(
						'email' => $oDirectory_Email->value,
						'type' => $oDirectory_Email->Directory_Email_Type->name
					);
				}
			}
		}
		else
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getById(intval($aData[0]), FALSE);
			if (!is_null($oSiteuser))
			{
				$aJSON[] = array(
					'email' => $oSiteuser->email,
					'type' => NULL
				);
			}
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
	->name(Core::_('Printlayout_Dir.main_menu'))
	->icon('fa fa-plus')
	->href(
		$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
	)
	->onclick(
		$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
	)
)->add(
	Admin_Form_Entity::factory('Menu')
	->name(Core::_('Printlayout_Driver.model_name'))
	->icon('fa fa-gear')
	->href(
		$oAdmin_Form_Controller->getAdminActionLoadHref('/admin/printlayout/driver/index.php', NULL, NULL, 0, 0)
	)
	->onclick(
		$oAdmin_Form_Controller->getAdminActionLoadAjax('/admin/printlayout/driver/index.php', NULL, NULL, 0, 0)
	)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$printlayout_dir_id = intval(Core_Array::getGet('printlayout_dir_id', 0));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Printlayout.root_dir'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($printlayout_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oPrintlayout_Dir = Core_Entity::factory('Printlayout_Dir')->find($printlayout_dir_id);

	if (!is_null($oPrintlayout_Dir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'printlayout_dir_id=' . intval($oPrintlayout_Dir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oPrintlayout_Dir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oPrintlayout_Dir = $oPrintlayout_Dir->getParent());

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
	$oPrintlayout_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Printlayout_Controller_Edit', $oAdmin_Form_Action
	);

	$oPrintlayout_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oPrintlayout_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oPrintlayoutDirControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oPrintlayoutDirControllerApply);
}

// Действие "Удаление файла"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteFile');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteFile')
{
	$oDeleteFileController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteFileController
		->methodName('deleteFile')
		->divId(array('preview_large_file', 'delete_large_file'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oDeleteFileController);
}

// Действие "Настройка модулей"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('setModules');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'setModules')
{
	$Printlayout_Module_Controller_Set = Admin_Form_Action_Controller::factory(
		'Printlayout_Module_Controller_Set', $oAction
	);

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($Printlayout_Module_Controller_Set);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Printlayout_Dir')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('parent_id', '=', $printlayout_dir_id)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Printlayout')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('printlayout_dir_id', '=', $printlayout_dir_id)
	)
)->changeField('name', 'type', 1);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
