<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 234;
$sFormAction = '/admin/shop/item/warehouse/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', intval(Core_Array::getRequest('shop_id', 0)));
$oShop_Group = Core_Entity::factory('Shop_Group', intval(Core_Array::getRequest('shop_group_id', 0)));
$oShop_Dir = $oShop->Shop_Dir;

$sFormTitle = Core::_("Shop_Item.item_warehouse_title", $oShop->name);

$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShop_Group->id}";

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Хлебные крошки
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php'))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php'))
);

// Крошки по директориям магазинов
if ($oShop_Dir->id)
{
	$oShopDirBreadcrumbs = $oShop_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oShopDirBreadcrumbs->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		));
	} while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
	->name($oShop->name)
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
);

// Крошки по группам товаров
if ($oShop_Group->id)
{
	$oShopGroupBreadcrumbs = $oShop_Group;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShop_Group->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShop_Group->id}"));
	} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Item')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('shop_items.*', array('shop_items.price', 'adminPrice'), array('SUM(shop_warehouse_items.count)', 'adminRest')))
	)
	->addCondition(
		array('leftJoin' => array('shop_warehouse_items', 'shop_items.id', '=', 'shop_warehouse_items.shop_item_id'))
	)
	// ->addCondition(array('where' => array('shop_group_id', '=', $oShop_Group->id)))
	->addCondition(array('where' => array('shop_id', '=', $oShop->id)))
	// ->addCondition(array('where' => array('modification_id', '=', 0)))
	->addCondition(array('where' => array('shortcut_id', '=', 0)))
	->addCondition(array('groupBy' => array('shop_items.id')))
;

$aShop_Warehouses = $oShop->Shop_Warehouses->getAllByActive(1);

if (count($aShop_Warehouses))
{
	$options = '';

	foreach ($aShop_Warehouses as $oShop_Warehouse)
	{
		$options .= $oShop_Warehouse->id . "=" . $oShop_Warehouse->name . "\n";
	}

	$oAdmin_Form_Dataset
		->changeField('shop_warehouse_items.shop_warehouse_id', 'list', $options)
	;
}

// Change field type
if (Core_Entity::factory('Shop', $oShop->id)->Shop_Warehouses->getCount() == 1)
{
	$oAdmin_Form_Dataset->changeField('adminRest', 'type', 2);
}

// Change field type
$oAdmin_Form_Dataset
	->changeField('img', 'type', 10)
	->addExternalField('shop_warehouse_id');

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();