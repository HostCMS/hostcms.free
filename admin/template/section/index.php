<?php
/**
 * Templates.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'template');

$iAdmin_Form_Id = 201;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Путь к контроллеру формы ЦА
$sAdminFormAction = '/admin/template/section/index.php';

// Путь к контроллеру предыдущей формы
$sTemplatePath = '/admin/template/index.php';

// Идентификатор макета
$iTemplateId = intval(Core_Array::getRequest('template_id', 0));

// Идентификатор группы макетов
$iTemplateDirId = intval(Core_Array::getRequest('template_dir_id', 0));

$oTemplate = Core_Entity::factory('Template')->find($iTemplateId);

$pageTitle = Core::_('Template_Section.section_show_title', $oTemplate->name);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
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
		->name(Core::_('Template.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sTemplatePath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sTemplatePath, NULL, NULL, '')
		)
);

// Если передан идентификатор группы макетов, тогда строим дополнительные хлебные крошки
if ($iTemplateDirId)
{
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
						$oAdmin_Form_Controller->getAdminLoadHref($sTemplatePath, NULL, NULL, $additionalParams)
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax($sTemplatePath, NULL, NULL, $additionalParams)
					);

		} while ($oTemplate_Dir = $oTemplate_Dir->getParent());

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
$additionalParams = "template_dir_id={$iTemplateDirId}&template_id={$iTemplateId}";

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
$oAdminFormAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdminFormAction && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oTemplateSectionControllerEdit = new Template_Section_Controller_Edit($oAdminFormAction);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTemplateSectionControllerEdit);

	// Крошки при редактировании
	$oTemplateSectionControllerEdit->addEntity($oAdminFormEntityBreadcrumbs);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oTemplateSectionApply = new Admin_Form_Action_Controller_Type_Apply($oAdminFormActionApply);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTemplateSectionApply);
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
	Core_Entity::factory('Template_Section')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('template_id', '=', $iTemplateId)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
