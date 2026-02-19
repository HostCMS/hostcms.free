<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 239;
$sAdminFormAction = '/{admin}/shop/warehouse/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Идентификатор склада
$shop_warehouse_id = intval(Core_Array::getGet('shop_warehouse_id'));
// Идентификатор магазина
$shop_id = Core_Array::getGet('shop_id', 0, 'int');
// Идентификатор группы товаров
$shop_group_id = Core_Array::getGet('shop_group_id', 0, 'int');

// Текущий склад
$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse')->find($shop_warehouse_id);
// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);
// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Warehouse_Item.title', $oShop_Warehouse->name, FALSE);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warehouse_Item.export'))
		->icon('fa fa-upload')
		->target('_blank')
		->onclick('')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportItems', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Представитель класса хлебных крошек
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath = '/{admin}/shop/index.php', NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, ''))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Добавляем крошки для групп магазинов
if ($oShopDir->id)
{
	$aBreadcrumbs = array();

	$oShopBreadCrumbDir = $oShopDir;

	do
	{
		$additionalParams = "shop_dir_id={$oShopBreadCrumbDir->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopBreadCrumbDir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	}
	while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на форму списка групп товаров и товаров
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, $sAdditionalParams))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	$sShopItemFormPath = '/{admin}/shop/item/index.php';

	do
	{
		$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroup->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	}
	while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Warehouse.main_menu_warehouses_list'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams))
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_warehouse_id={$oShop_Warehouse->id}&shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);

// Действие экспорта
$oAdminFormActionExport = $oAdmin_Form->Admin_Form_Actions->getByName('exportItems');

if ($oAdminFormActionExport && $oAdmin_Form_Controller->getAction() == 'exportItems')
{
	$Shop_Warehouse_Item_Export_Controller = new Shop_Warehouse_Item_Export_Controller($oShop_Warehouse);
	$Shop_Warehouse_Item_Export_Controller->execute();
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Warehouse_Item')
);

$oShop_Producers = $oShop->Shop_Producers;
$oShop_Producers->queryBuilder()
	->distinct()
	->select('shop_producers.*')
	->join('shop_items', 'shop_producers.id', '=', 'shop_items.shop_producer_id')
	// ->where('shop_producers.active', '=', 1)
	->where('shop_items.modification_id', '=', 0)
	->where('shop_items.shortcut_id', '=', 0)
	//->groupBy('shop_producers.id')
	->clearOrderBy()
	->orderBy('shop_producers.sorting', 'ASC')
	->orderBy('shop_producers.name', 'ASC');

$aShop_Producers = $oShop_Producers->findAll(FALSE);

if (count($aShop_Producers))
{
	$options = '';

	foreach ($aShop_Producers as $oShop_Producer)
	{
		$options .= $oShop_Producer->id . "=" . $oShop_Producer->name . "\n";
	}

	$oAdmin_Form_Dataset
		->changeField('shop_items.shop_producer_id', 'list', $options)
		->addExternalField('shop_producer_id');
}

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array(
			'shop_warehouse_items.*',
			array('shop_items.price', 'adminPrice'),
			array('shop_items.weight', 'dataWeight')
		))
	)
	->addCondition(
		array('join' => array('shop_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id'))
	)
	->addCondition(
		array('where' => array('shop_warehouse_items.shop_warehouse_id', '=', $shop_warehouse_id))
	)
	->addCondition(
		array('where' => array('shop_items.shop_id', '=', $oShop->id))
	);

$oAdmin_Form_Controller
	->addExternalReplace('{shop_group_id}', $shop_group_id)
	->addExternalReplace('{shop_id}', $shop_id);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();