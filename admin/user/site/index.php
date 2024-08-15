<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 182;
$sAdminFormAction = '/admin/user/site/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User.choosing_site'))
	->pageTitle(Core::_('User.choosing_site'));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$company_department_id = Core_Array::getGet('company_department_id');

$oCompany_Department = Core_Entity::factory('Company_Department', $company_department_id);

// Путь к контроллеру формы пользователей определенной группы
$sUsersPath = '/admin/company/index.php';
$sAdditionalCompanyParams = 'company_id=' . $oCompany_Department->Company->id;

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company.company_show_title2', $oCompany_Department->name, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sUsersPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sUsersPath, NULL, NULL, '')
	)
)
->add( // Отдел
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department.title', $oCompany_Department->Company->name, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/company/department/index.php', NULL, NULL, $sAdditionalCompanyParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/company/department/index.php', NULL, NULL, $sAdditionalCompanyParams)
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.choosing_site'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Site')
);

$oUser = Core_Auth::getCurrentUser();

// Ограничение списка сайтов для непривилегированного пользователя
if ($oUser->superuser == 0)
{
	$oAdmin_Form_Dataset->addCondition(
		array('select' => array('sites.*'))
	)->addCondition(
		array('join' => array('company_department_modules', 'sites.id', '=', 'company_department_modules.site_id'))
	)->addCondition(
		array('join' => array('company_department_post_users', 'company_department_modules.company_department_id', '=', 'company_department_post_users.company_department_id'))
	)->addCondition(
		array('where' => array('company_department_post_users.user_id', '=', $oUser->id))
	)->addCondition(
		array('groupBy' => array('sites.id'))
	);
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Внешняя заменя для onclick и href
$oAdmin_Form_Controller->addExternalReplace('{company_department_id}', $company_department_id);

// Change links to other form
if (Core_Array::getGet('mode') == 'action')
{
	$oAdmin_Form_Dataset->changeField('name', 'link', '/admin/user/site/form/index.php?company_department_id={company_department_id}&site_id={id}&mode=action');
	$oAdmin_Form_Dataset->changeField('name', 'onclick', "$.adminLoad({path: '/admin/user/site/form/index.php', additionalParams: 'company_department_id={company_department_id}&site_id={id}&mode=action', windowId: '{windowId}'}); return false");

	$oAdmin_Form_Controller->addExternalReplace('{mode}', 'action');
}
else
{
	$oAdmin_Form_Controller->addExternalReplace('{mode}', 'module');
}

$oAdmin_Form_Controller->execute();
