<?php
/**
 * Shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$iAdmin_Form_Id = 354;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Путь к контроллеру формы ЦА
$sAdminFormAction = '/admin/shop/document/relation/add/index.php';

$document_id = Core_Array::getRequest('document_id', 0, 'int');
$type = Core_Array::getRequest('type', 0, 'int');
$shop_id = Core_Array::getGet('shop_id', 0, 'int');

$model = Shop_Controller::getDocumentModel($type);
$oModel = Core_Entity::factory($model);

// var_dump($model);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction);

method_exists($oModel, 'getTitle')
	&& $oAdmin_Form_Controller->title($oModel->getTitle());

if (!is_null(Core_Array::getPost('add_shop_document')))
{
	$aJson = array();

	$id = Core_Array::getPost('id', 0, 'int');
	$type = Core_Array::getPost('type', 0, 'int');

	if ($id && $type)
	{
		$related_document_id = Shop_Controller::getDocumentId($id, $type);

		$oShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation');
		$oShop_Document_Relations->queryBuilder()
			->where('document_id', '=', $document_id)
			->where('related_document_id', '=', $related_document_id);

		$oShop_Document_Relation = $oShop_Document_Relations->getLast(FALSE);

		if (is_null($oShop_Document_Relation))
		{
			$oShop_Document_Relation = Core_Entity::factory('Shop_Document_Relation');
			$oShop_Document_Relation->document_id = $document_id;
			$oShop_Document_Relation->related_document_id = $related_document_id;

			$oObject = $oModel->getById($id);

			!is_null($oObject) && method_exists($oModel, 'getAmount')
				&& $oShop_Document_Relation->paid = $oObject->getAmount();

			$oShop_Document_Relation->save();

			$aJson['related_document_id'] = $related_document_id;
		}
	}

	Core::showJson($aJson);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	$oModel
);

if (isset($oModel->shop_id))
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('shop_id', '=', $shop_id)));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('clearSelect' => array()))
		->addCondition(array('select' => array($oModel->getTableName() . '.*')))
		->addCondition(
			array('join' => array('shop_warehouses', $oModel->getTableName() . '.shop_warehouse_id', '=', 'shop_warehouses.id',
				array(
					array('AND' => array('shop_warehouses.shop_id', '=', $shop_id))
				))
			)
		);
}

$oAdmin_Form_Controller->deleteAdminFormFieldById(2071);
$oAdmin_Form_Controller->deleteAdminFormFieldById(2075);
$oAdmin_Form_Controller->deleteAdminFormActionById(1436);

!method_exists($oModel, 'getAmount')
	&& $oAdmin_Form_Controller->deleteAdminFormFieldById(2073);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();