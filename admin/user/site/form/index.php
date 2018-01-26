<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 138;
$sAdminFormAction = '/admin/user/site/form/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$company_department_id = Core_Array::getGet('company_department_id');
$oCompany_Department = Core_Entity::factory('Company_Department', $company_department_id);

$site_id = Core_Array::getGet('site_id');
$oSite = Core_Entity::factory('Site', $site_id);

// Проверка возможности доступа пользователя к сайту
$oUser = Core_Entity::factory('User')->getCurrent();

if ($oUser->superuser == 0
	&& !$oUser->checkSiteAccess($oSite))
{
	throw new Core_Exception("Access denied");
}

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Company_Department_Action_Access.ua_show_user_form_access_title', $oCompany_Department->name, $oSite->name))
	->pageTitle(Core::_('Company_Department_Action_Access.ua_show_user_form_access_title', $oCompany_Department->name, $oSite->name));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Путь к контроллеру формы пользователей определенной группы
$sUsersPath = '/admin/company/index.php';
$sAdditionalUsersParams = 'company_id=' . $oCompany_Department->Company->id;

$sChoosingSitePath = '/admin/user/site/index.php';
$sAdditionalChoosingSiteParams = 'company_department_id=' . $company_department_id;

$form_mode = Core_Array::getGet('mode');
if (!is_null($form_mode))
{
	$sAdditionalChoosingSiteParams .= '&mode=' . $form_mode;
}

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company.company_show_title2'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sUsersPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sUsersPath, NULL, NULL, '')
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department.title', $oCompany_Department->Company->name))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/company/department/index.php', NULL, NULL, $sAdditionalUsersParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/company/department/index.php', NULL, NULL, $sAdditionalUsersParams)
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
		->name(Core::_('Company_Department_Module.ua_show_user_access_action_title', $oCompany_Department->name, $oSite->name))
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

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Company_Department_Site_Form')
);

$oAdmin_Language = Core_Entity::factory('Admin_Language')
	->getByShortname(Core_I18n::instance()->getLng());

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('admin_forms.*', array('admin_word_values.name', 'name')))
)->addCondition(
	array('leftJoin' => array('admin_words', 'admin_forms.admin_word_id', '=', 'admin_words.id'))
)
->addCondition(
	array('leftJoin' => array('admin_word_values', 'admin_word_values.admin_word_id', '=', 'admin_words.id'))
)
->addCondition(array('open' => array()))
->addCondition(array('where' => array('admin_word_values.admin_language_id', '=', $oAdmin_Language->id)))
->addCondition(array('setOr' => array()))
->addCondition(array('where' => array('admin_forms.admin_word_id', '=', 0)))
->addCondition(array('close' => array()));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Внешняя заменя для onclick и href
$oAdmin_Form_Controller->addExternalReplace('{company_department_id}', $company_department_id);
$oAdmin_Form_Controller->addExternalReplace('{site_id}', $site_id);

// Показ формы
$oAdmin_Form_Controller->execute();
