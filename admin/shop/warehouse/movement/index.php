<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 258;
$sAdminFormAction = '/admin/shop/warehouse/movement/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Идентификатор магазина
$shop_id = intval(Core_Array::getGet('shop_id'));
// Идентификатор группы товаров
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

$printlayout_id = intval(Core_Array::getGet('printlayout_id', 0));

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);
// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Warehouse_Movement.title');

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
		->img('/admin/images/company_add.gif')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Представитель класса хлебных крошек
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath = '/admin/shop/index.php', NULL, NULL, ''))
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
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	$sShopItemFormPath = '/admin/shop/item/index.php';

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
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams))
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "&shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Warehouse_Movement_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Warehouse_Movement_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Warehouse_Movement_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warehouse_Movement_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$Admin_Form_Action_Controller_Type_Apply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Apply);
}

// Удаление товаров из комплекта
$oAdminFormActionDeleteShopItem = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteShopItem');

if ($oAdminFormActionDeleteShopItem && $oAdmin_Form_Controller->getAction() == 'deleteShopItem')
{
	$Shop_Warehouse_Movement_Item_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Warehouse_Movement_Item_Controller_Delete', $oAdminFormActionDeleteShopItem
	);

	$oAdmin_Form_Controller->addAction($Shop_Warehouse_Movement_Item_Controller_Delete);
}

$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('print');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'print')
{
	$Shop_Warehouse_Movement_Controller_Print = Admin_Form_Action_Controller::factory(
		'Shop_Warehouse_Movement_Controller_Print', $oAdmin_Form_Action
	);

	$Shop_Warehouse_Movement_Controller_Print
		->title(Core::_('Shop_Warehouse_Movement.title'))
		->printlayout($printlayout_id);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warehouse_Movement_Controller_Print);
}

$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('sendMail');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'sendMail')
{
	$Shop_Warehouse_Movement_Controller_Print = Admin_Form_Action_Controller::factory(
		'Shop_Warehouse_Movement_Controller_Print', $oAdmin_Form_Action
	);

	$Shop_Warehouse_Movement_Controller_Print
		->printlayout($printlayout_id)
		->send(TRUE);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warehouse_Movement_Controller_Print);
}

$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('post');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'post')
{
	$Admin_Form_Action_Controller_Type_Post = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Post', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Post);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Warehouse_Movement')
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('shop_warehouse_movements.*'))
)
->addCondition(
	array(
		'leftJoin' => array('shop_warehouses', 'shop_warehouses.id', '=', 'shop_warehouse_movements.source_shop_warehouse_id')
	)
)
->addCondition(
	array(
		'where' => array('shop_warehouses.shop_id', '=', $shop_id)
	)
);

$oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

// Список значений для фильтра и поля
$aShop_Warehouses_Source = Core_Entity::factory('Shop_Warehouse')->getAllByShop_id($shop_id);
$sList = "0=…\n";
foreach ($aShop_Warehouses_Source as $oShop_Warehouse)
{
	$sList .= "{$oShop_Warehouse->id}={$oShop_Warehouse->name}\n";
}

$oAdmin_Form_Dataset
	->changeField('source_shop_warehouse_id', 'type', 8)
	->changeField('source_shop_warehouse_id', 'list', trim($sList));

// Список значений для фильтра и поля
$aShop_Warehouses_Destination = Core_Entity::factory('Shop_Warehouse')->getAllByShop_id($shop_id);
$sList = "0=…\n";
foreach ($aShop_Warehouses_Destination as $oShop_Warehouse)
{
	$sList .= "{$oShop_Warehouse->id}={$oShop_Warehouse->name}\n";
}

$oAdmin_Form_Dataset
	->changeField('destination_shop_warehouse_id', 'type', 8)
	->changeField('destination_shop_warehouse_id', 'list', trim($sList));

// Показ формы
$oAdmin_Form_Controller->execute();