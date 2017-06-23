<?php
/**
 * Templates.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'template');

// Код формы
$iAdmin_Form_Id = 6;
$sAdminFormAction = '/admin/template/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Template.title'))
	->pageTitle(Core::_('Template.title'));

$template_dir_id = intval(Core_Array::getGet('template_dir_id', 0));
$template_id = intval(Core_Array::getGet('template_id', 0));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Template.menu1'))
		->icon('fa fa-th')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Template.menu_add'))
				->icon('fa fa-plus')
				->img('/admin/images/template_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
		)
);

if (!$template_id)
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
		->name(Core::_('Template_Dir.menu'))
		->icon('fa fa-folder-open')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Template_Dir.menu_add'))
				->icon('fa fa-plus')
				->img('/admin/images/folder_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Template_dir.root'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($template_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oTemplateDir = Core_Entity::factory('Template_Dir')->find($template_dir_id);

	if (!is_null($oTemplateDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'template_dir_id=' . intval($oTemplateDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oTemplateDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while($oTemplateDir = $oTemplateDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

if ($template_id)
{
	$oParentTemplate = Core_Entity::factory('Template')->find($template_id);

	if (!is_null($oParentTemplate->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'template_dir_id=' . intval($oParentTemplate->template_dir_id)
				. '&template_id=' . $oParentTemplate->id;

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name(Core::_('Template.breadCrumb', $oParentTemplate->name))
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while($oParentTemplate = $oParentTemplate->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

if ($template_dir_id || $template_id)
{
	// Добавляем все хлебные крошки контроллеру
	$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oTemplate_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Template_Controller_Edit', $oAdmin_Form_Action
	);

	$oTemplate_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTemplate_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oTemplateDirControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTemplateDirControllerApply);
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
	Core_Entity::factory('Template_Dir')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
)->addCondition(
	array('where' =>
		array('parent_id', '=', $template_id == 0 ? $template_dir_id : NULL)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Template')
);

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
)
//->changeField('name', 'type', 1)
->changeField('name', 'link', '/admin/template/index.php?template_dir_id={template_dir_id}&template_id={id}')
->changeField('name', 'onclick', "$.adminLoad({path: '/admin/template/index.php',additionalParams: 'template_dir_id={template_dir_id}&template_id={id}', windowId: '{windowId}'}); return false");
;

if ($template_id == 0)
{
	$oAdmin_Form_Dataset
		->addCondition(
			array('where' =>
				array('template_dir_id', '=', $template_dir_id)
			)
		)
		->addCondition(
			array('where' =>
				array('template_id', '=', 0)
			)
		);
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(
			array('where' =>
				array('template_id', '=', $template_id)
			)
		);
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
