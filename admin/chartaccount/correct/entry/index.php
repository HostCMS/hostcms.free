<?php
/**
 * Chartaccount.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'chartaccount');

// Код формы
$iAdmin_Form_Id = 359;
$sAdminFormAction = '/admin/chartaccount/correct/entry/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$chartaccount_id = Core_Array::getGet('chartaccount_id', 0, 'int');

$oChartaccount = Core_Entity::factory('Chartaccount', $chartaccount_id);

$name = $oChartaccount->code . ' ' . $oChartaccount->name;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Chartaccount_Correct_Entry.title', $name))
	->pageTitle(Core::_('Chartaccount_Correct_Entry.title', $name));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
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
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/chartaccount/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/chartaccount/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount_Correct_Entry.title', $name))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "chartaccount_id={$chartaccount_id}")
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "chartaccount_id={$chartaccount_id}")
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oChartaccount_Correct_Entry_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Chartaccount_Correct_Entry_Controller_Edit', $oAdmin_Form_Action
	);

	$oChartaccount_Correct_Entry_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oChartaccount_Correct_Entry_Controller_Edit);
}

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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Chartaccount_Correct_Entry')
);

$oAdmin_Form_Dataset
	->addCondition(array('open' => array()))
		->addCondition(array('where' => array('debit', '=', $chartaccount_id)))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('credit', '=', $chartaccount_id)))
	->addCondition(array('close' => array()));

// Доступ только к своим
// $oUser = Core_Auth::getCurrentUser();
// !$oUser->superuser && $oUser->only_access_my_own
// 	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

$aChartaccounts = Core_Entity::factory('Chartaccount')->findAll(FALSE);
$aList = array();
foreach ($aChartaccounts as $oChartaccount)
{
	$aList[$oChartaccount->id] = array('value' => $oChartaccount->code);
}

$oAdmin_Form_Dataset
	->changeField('debit', 'type', 8)
	->changeField('debit', 'list', $aList)
	->changeField('credit', 'type', 8)
	->changeField('credit', 'list', $aList);

// Показ формы
$oAdmin_Form_Controller->execute();