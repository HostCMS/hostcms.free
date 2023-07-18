<?php
/**
 * Shop.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$iAdmin_Form_Id = 354;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Путь к контроллеру формы ЦА
$sAdminFormAction = '/admin/shop/document/relation/index.php';

$document_id = Core_Array::getRequest('document_id', 0, 'int');
$shop_id = Core_Array::getGet('shop_id', 0, 'int');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction);

// $windowId = $oAdmin_Form_Controller->getWindowId();

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

if ($document_id)
{
	$addPath = '/admin/shop/document/relation/add/index.php';

	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Document_Relation.documents'))
			->icon('fa fa-plus')
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Shop_Document_Relation.add_purchaseorder'))
					->icon('fa fa-plus')
					->onclick(
						// $oAdmin_Form_Controller->getAdminActionModalLoad($addPath, NULL, 'modal', 0, 0, "shop_id={$shop_id}&document_id={$document_id}&type=6")
						$oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $addPath, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => "shop_id={$shop_id}&document_id={$document_id}&type=6", 'width' => '90%'))
					)
			)
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Shop_Document_Relation.add_shop_warehouse_invoice'))
					->icon('fa fa-plus')
					->onclick(
						// $oAdmin_Form_Controller->getAdminActionModalLoad($addPath, NULL, 'modal', 0, 0, "shop_id={$shop_id}&document_id={$document_id}&type=7")
						$oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $addPath, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => "shop_id={$shop_id}&document_id={$document_id}&type=7", 'width' => '90%'))
					)
			)
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Shop_Document_Relation.add_shop_warehouse_supply'))
					->icon('fa fa-plus')
					->onclick(
						// $oAdmin_Form_Controller->getAdminActionModalLoad($addPath, NULL, 'modal', 0, 0, "shop_id={$shop_id}&document_id={$document_id}&type=8")
						$oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $addPath, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => "shop_id={$shop_id}&document_id={$document_id}&type=8", 'width' => '90%'))
					)
			)
	);
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdminFormActionEdit = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdminFormActionEdit && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oShop_Document_Relation_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Document_Relation_Controller_Edit', $oAdminFormActionEdit
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShop_Document_Relation_Controller_Edit);
}

// Удаление модуля
$oAdminFormActionDelete = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('delete');

if ($oAdminFormActionDelete && $oAdmin_Form_Controller->getAction() == 'delete')
{
	$oShop_Document_Relation_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Document_Relation_Controller_Delete', $oAdminFormActionDelete
	);

	$oAdmin_Form_Controller->addAction($oShop_Document_Relation_Controller_Delete);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Document_Relation')
);

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('document_id', '=', $document_id)));

$oAdmin_Form_Controller->deleteAdminFormFieldById(2070);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();