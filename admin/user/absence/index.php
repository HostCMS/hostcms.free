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
$iAdmin_Form_Id = 247;
$sAdminFormAction = '/admin/user/absence/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oUser = Core_Auth::getCurrentUser();

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User_Absence.title'))
	->pageTitle(Core::_('User_Absence.title'));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.ua_show_users_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/user/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/index.php', NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.timesheet_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/user/timesheet/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/timesheet/index.php', NULL, NULL, '')
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User_Absence.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oUser_Absence_Controller_Edit = Admin_Form_Action_Controller::factory(
		'User_Absence_Controller_Edit', $oAdmin_Form_Action
	);

	$oUser_Absence_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUser_Absence_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oUserAbsenceTypeControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserAbsenceTypeControllerApply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('User_Absence')
);

$aCompany_Departments = $oUser->getAllDepartmentsHeadedBy();
if (count($aCompany_Departments))
{
	$aDepartmentIDs = array();
	foreach ($aCompany_Departments as $oCompany_Department)
	{
		$aDepartmentIDs[] = $oCompany_Department->id;
	}
}
else
{
	$aDepartmentIDs = array(-1);
}

// Ограничение источника 0
$oAdmin_Form_Dataset
	->addCondition(array('select' => array('user_absences.*')))
	->addCondition(array('join' => array('company_department_post_users', 'company_department_post_users.user_id', '=', 'user_absences.employee_id')))
	->addCondition(array('where' => array('company_department_post_users.company_department_id', 'IN', $aDepartmentIDs)))
	->addCondition(array('groupBy' => array('user_absences.id')));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
