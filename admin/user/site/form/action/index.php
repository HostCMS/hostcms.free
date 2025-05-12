<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 139;
$sAdminFormAction = '/{admin}/user/site/form/action/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$company_department_id = Core_Array::getGet('company_department_id');
$oCompany_Department = Core_Entity::factory('Company_Department', $company_department_id);

$site_id = Core_Array::getGet('site_id');
$oSite = Core_Entity::factory('Site', $site_id);

// Проверка возможности доступа пользователя к сайту
$oUser = Core_Auth::getCurrentUser();

if ($oUser->superuser == 0
	&& !$oUser->checkSiteAccess($oSite))
{
	throw new Core_Exception("Access denied");
}

$admin_form_id = Core_Array::getGet('admin_form_id');

$oAdmin_Language = Core_Entity::factory('Admin_Language')
	->getByShortname(Core_I18n::instance()->getLng());

$oAdmin_Word_Value = Core_Entity::factory('Admin_Form', $admin_form_id)
	->Admin_Word
	->getWordByLanguage($oAdmin_Language->id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Company_Department_Action_Access.ua_show_user_form_events_access_title', $oAdmin_Word_Value->name, $oCompany_Department->name))
	->pageTitle(Core::_('Company_Department_Action_Access.ua_show_user_form_events_access_title', $oAdmin_Word_Value->name, $oCompany_Department->name));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Путь к контроллеру формы групп пользователей
// $sUserGroupsPath = '/{admin}/user/index.php';

// Путь к контроллеру формы пользователей определенной группы
//$sUsersPath = '/{admin}/company/index.php';
//$sAdditionalParamsUser = 'company_department_id=' . $company_department_id;

$sCompaniesPath = '/{admin}/company/index.php';

$sCompanyPath = '/{admin}/company/department/index.php';
$sAdditionalCompanyParams = 'company_id=' . $oCompany_Department->Company->id;


$sChoosingSitePath = '/{admin}/user/site/index.php';
$sAdditionalChoosingSite = 'company_department_id=' . $company_department_id . '&mode=action';

$sFormActionsPath = '/{admin}/user/site/form/index.php';
$sAdditionalFormActions = 'company_department_id=' . $company_department_id . '&site_id=' . $site_id;

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company.company_show_title2'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sCompaniesPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompaniesPath, NULL, NULL, '')
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department.title', $oCompany_Department->Company->name, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sCompanyPath, NULL, NULL, $sAdditionalCompanyParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanyPath, NULL, NULL, $sAdditionalCompanyParams)
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.choosing_site'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sChoosingSitePath, NULL, NULL, $sAdditionalChoosingSite)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sChoosingSitePath, NULL, NULL, $sAdditionalChoosingSite)
	)
)
->add( // Список пользователей группы
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department_Module.ua_show_user_access_action_title', $oCompany_Department->name, $oSite->name, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sFormActionsPath, NULL, NULL, $sAdditionalFormActions)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sFormActionsPath, NULL, NULL, $sAdditionalFormActions)
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department_Action_Access.ua_show_user_form_events_access_title', $oAdmin_Word_Value->name, $oCompany_Department->name, FALSE))
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
	$oUserFormActionControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserFormActionControllerApply);
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

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Company_Department_Site_Form_Action')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('admin_form_actions.*', array('admin_word_values.name', 'text_name'),
	array('admin_form_actions.name', 'action_name'), array('company_department_action_accesses.admin_form_action_id', 'access')))
)->addCondition(
	array('leftJoin' => array('company_department_action_accesses', 'admin_form_actions.id', '=', 'company_department_action_accesses.admin_form_action_id',
		array(
			array('AND' => array('company_department_action_accesses.company_department_id', '=', $company_department_id)),
			array('AND' => array('company_department_action_accesses.site_id', '=', $site_id))
		)
	))
)->addCondition(
	array('join' => array('admin_word_values', 'admin_word_values.admin_word_id', '=', 'admin_form_actions.admin_word_id'))
)->addCondition(
	array('where' => array('admin_form_actions.admin_form_id', '=', $admin_form_id))
)->addCondition(
	array('where' => array('admin_word_values.admin_language_id', '=', $oAdmin_Language->id))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
