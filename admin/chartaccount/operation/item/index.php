<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'chartaccount');

// Код формы
$iAdmin_Form_Id = 363;
$sAdminFormAction = '/admin/chartaccount/operation/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sFormTitle = Core::_('Chartaccount_Operation_Item.title');

$chartaccount_operation_id = Core_Array::getGet('chartaccount_operation_id', 0, 'int');
$company_id = Core_Array::getGet('company_id', 0, 'int');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => '/admin/chartaccount/operation/item/index.php', 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => "chartaccount_operation_id={$chartaccount_operation_id}&company_id={$company_id}"))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => '/admin/chartaccount/operation/item/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => "chartaccount_operation_id={$chartaccount_operation_id}&company_id={$company_id}", 'width' => '90%'))
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oChartaccount_Operation_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Chartaccount_Operation_Item_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oChartaccount_Operation_Item_Controller_Edit);
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

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Chartaccount_Operation_Item')
);

$oAdmin_Form_Dataset->addCondition(array('where' => array('chartaccount_operation_id', '=', $chartaccount_operation_id)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();