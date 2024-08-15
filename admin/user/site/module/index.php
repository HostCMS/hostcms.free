<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 10;
$sAdminFormAction = '/admin/user/site/module/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$company_department_id = Core_Array::getGet('company_department_id');
$oCompany_Department = Core_Entity::factory('Company_Department', $company_department_id);

$site_id = Core_Array::getGet('site_id');
$oSite = Core_Entity::factory('Site', $site_id);

// Проверка возможности доступа пользователя к сайту__halt_compiler
$oUser = Core_Auth::getCurrentUser();

if ($oUser->superuser == 0
	&& !$oUser->checkSiteAccess($oSite))
{
	throw new Core_Exception("Access denied");
}

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Company_Department_Module.ua_show_user_access_module_title', $oCompany_Department->name, $oSite->name))
	->pageTitle(Core::_('Company_Department_Module.ua_show_user_access_module_title', $oCompany_Department->name, $oSite->name));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Путь к контроллеру формы пользователей определенной группы
$sUsersPath = '/admin/company/index.php';
$sAdditionalCompanyParams = 'company_id=' . $oCompany_Department->Company->id;

$sChoosingSitePath = '/admin/user/site/index.php';
$sAdditionalChoosingSiteParams = 'company_department_id=' . $company_department_id;

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company.company_show_title2'))
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
			$oAdmin_Form_Controller->getAdminLoadHref($sChoosingSitePath, NULL, NULL, $sAdditionalChoosingSiteParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sChoosingSitePath, NULL, NULL, $sAdditionalChoosingSiteParams)
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department_Module.ua_show_user_access_module_title', $oCompany_Department->name, $oSite->name, FALSE))
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
	$oUserGroupAccessModuleControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserGroupAccessModuleControllerApply);
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
	Core_Entity::factory('Company_Department_Site_Module')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('modules.*', array('company_department_modules.id', 'access'), array('modules.id', 'key_field')))
)
->addCondition(
	array('leftJoin' => array('company_department_modules', 'modules.id', '=', 'company_department_modules.module_id',
		array(
			array('AND' => array('company_department_modules.company_department_id', '=', $company_department_id)),
			array('AND' => array('company_department_modules.site_id', '=', $site_id))
		)
	))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();