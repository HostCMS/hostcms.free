<?php
/**
 * Templates.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'template');

// Код формы
$iAdmin_Form_Id = 202;
$sAdminFormAction = '/admin/template/section/lib/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Template_Section_Lib.form_title'))
	->pageTitle(Core::_('Template_Section_Lib.form_title'));

// Меню формы
$oAdminFormEntityMenus = Admin_Form_Entity::factory('Menus');

// Элементы меню
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

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityMenus);

// Строка навигации
$iTemplateSectionId = Core_Array::getRequest('template_section_id', 0);
$oTemplate_Section = Core_Entity::factory('Template_Section', $iTemplateSectionId);

$oTemplate = $oTemplate_Section->Template;

$pageTitle = Core::_('Template_Section.section_show_title', $oTemplate->name, FALSE);

// Элементы строки навигации
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Template.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$prevFormPath = '/admin/template/index.php';

$iTemplateDirId = $oTemplate->Template_Dir->id;
if ($iTemplateDirId)
{
	// Если передана родительская группа - строим хлебные крошки
	$oTemplate_Dir = Core_Entity::factory('Template_Dir')->find($iTemplateDirId);

	if (!is_null($oTemplate_Dir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'template_dir_id=' . intval($oTemplate_Dir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oTemplate_Dir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($prevFormPath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($prevFormPath, NULL, NULL, $additionalParams)
				);
		} while ($oTemplate_Dir = $oTemplate_Dir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdminFormEntityBreadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = "template_dir_id={$iTemplateDirId}&template_id={$oTemplate->id}";

$prevFormPath = '/admin/template/section/index.php';

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

$additionalParams = "template_section_id={$iTemplateSectionId}";

// Хлебные крошки на текущий элемент
$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Template_Section_Lib.current_breadcrumbs'))
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
$oAdminFormAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdminFormAction && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oAdminFormActionControllerTypeEdit = Admin_Form_Action_Controller::factory(
		'Template_Section_Lib_Controller_Edit', $oAdminFormAction
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

// Действие "Загрузка свойств типовых динамических страниц"
$oAdminFormActionLoadLibList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadLibProperties');

if ($oAdminFormActionLoadLibList && $oAdmin_Form_Controller->getAction() == 'loadLibProperties')
{
	$oTemplate_Section_Lib_Controller_Libproperties = Admin_Form_Action_Controller::factory(
		'Template_Section_Lib_Controller_Libproperties', $oAdminFormActionLoadLibList
	);

	$lib_id = intval(Core_Array::getGet('lib_id'));

	$oTemplate_Section_Lib_Controller_Libproperties->libId($lib_id);

	$oAdmin_Form_Controller->addAction($oTemplate_Section_Lib_Controller_Libproperties);
}

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Template_Section_Lib')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
$oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('template_section_id', '=', $iTemplateSectionId)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();