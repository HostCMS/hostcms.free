<?php
/**
 * Department.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'company');

// Код формы
$iAdmin_Form_Id = 265;
$sAdminFormAction = '/admin/company/department/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

$company_id = Core_Array::getRequest('company_id');
$oCompany = Core_Entity::factory('Company', $company_id);

// AJAX-загрузка отделов компаний
if (Core_Array::getRequest('_', FALSE) && !is_null(Core_Array::getGet('loadDepartments')))
{
	$aCompany_Departments = array(
		array('value' => 0, 'name' => '...')
	);

	if ($company_id)
	{
		$aDepartments = $oCompany->fillDepartments();

		foreach ($aDepartments as $company_department_id => $name)
		{
			$aCompany_Departments[] = array('value' => $company_department_id, 'name' => $name);
		}
	}

	Core::showJson($aCompany_Departments);
}

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->title(Core::_('Company_Department.title', $oCompany->name))
	->setUp()
	->path($sAdminFormAction)
	->pageTitle(Core::_('Company_Department.title', $oCompany->name))
	->viewList(array('list' => 'Company_Controller_Structure'));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$sAdditionalParams = 'company_id=' . $company_id;

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company.company_show_title2'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/company/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/company/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company_Department.title', $oCompany->name, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams)
		)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Добавить/редактировать отдел"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('editDepartment');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'editDepartment')
{
	$oCompany_Department_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Company_Department_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCompany_Department_Controller_Edit);
}

// Действие "Удалить отдел"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('deleteDepartment');

// Действие "Удалить отдел"
if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'deleteDepartment')
{
	$oCompany_Department_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Company_Department_Controller_Delete', $oAdmin_Form_Action
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCompany_Department_Controller_Delete);
}

// Действие "Добавить/редактировать должность сотрудника в отделе"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('editUserDepartment');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'editUserDepartment')
{
	$oCompany_Department_User_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Company_Department_User_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCompany_Department_User_Controller_Edit);
}

// Действие "Добавить/редактировать должность сотрудника в отделе"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('deleteUserFromDepartment');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'deleteUserFromDepartment')
{
	$oCompany_Department_User_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Company_Department_User_Controller_Delete', $oAdmin_Form_Action
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCompany_Department_User_Controller_Delete);
}

Core_Entity::factory('Company_Department')->queryBuilder()->clear();

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Company_Department')
);

$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' => array('company_id', '=', $company_id))
);

$oAdmin_Form_Controller->limit(1000);
// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('User')
);

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('users.*'))
	)
	->addCondition(
		array('join' => array('company_department_post_users', 'users.id', '=', 'company_department_post_users.user_id', array(array('AND' => array('company_id', '=', $company_id)))))
	)
	->orderBy('head', 'DESC')
	->orderBy(Core_QueryBuilder::expression('CONCAT(`surname`, `name`, `patronymic`)'));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
