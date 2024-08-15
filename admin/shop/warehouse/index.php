<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 181;
$sAdminFormAction = '/admin/shop/warehouse/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sFormTitle = Core::_('Shop_Warehouse.main_menu_warehouses_list');

// Идентификатор магазина
$shop_id = intval(Core_Array::getGet('shop_id'));

// Идентификатор группы товаров
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

$oAdmin_Form_Controller->showTopFilterTags = 'shop_warehouse';

if (!is_null(Core_Array::getPost('load_prices')))
{
	$aJSON = array();

	$shop_price_id = intval(Core_Array::getPost('shop_price_id'));
	$aItems = Core_Array::getPost('items', array(), 'array');

	$Shop_Item_Controller = new Shop_Item_Controller();

	foreach ($aItems as $shop_item_id)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item')->getById($shop_item_id);

		if (!is_null($oShop_Item))
		{
			$price = $oShop_Item->loadPrice($shop_price_id);
			$aPrices = $Shop_Item_Controller->calculatePriceInItemCurrency($price, $oShop_Item);

			$aJSON[$oShop_Item->id] = array(
				'price' => $aPrices['price_tax']
			);
		}

	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		// ->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warehouse.purchases'))
		->icon('fa-solid fa-cart-arrow-down')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.purchaseorders'))
				->icon('fa-solid fa-cart-plus')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/purchaseorder/index.php', NULL, NULL /* , $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}" */)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/purchaseorder/index.php', NULL, NULL /*, $additionalParams */)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.invoices'))
				->icon('fa-solid fa-fw fa-file-invoice-dollar')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/invoice/index.php', NULL, NULL /* , $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}" */)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/invoice/index.php', NULL, NULL /*, $additionalParams */)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.supplies'))
				->icon('fa-solid fa-cart-flatbed')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/supply/index.php', NULL, NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/supply/index.php', NULL, NULL)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.purchasereturns'))
				->icon('fa-solid fa-truck')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/purchasereturn/index.php', NULL, NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/purchasereturn/index.php', NULL, NULL)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warehouse.operations'))
		->icon('fa fa-calendar-check-o')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.incoming'))
				->icon('fa fa-calendar-plus-o')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/incoming/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/incoming/index.php', NULL, NULL, $additionalParams)
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.writeoff'))
				->icon('fa fa-calendar-minus-o')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/writeoff/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/writeoff/index.php', NULL, NULL, $additionalParams)
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.regrade'))
				->icon('fa fa-calendar-o')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/regrade/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/regrade/index.php', NULL, NULL, $additionalParams)
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.movement'))
				->icon('fa fa-arrows-h')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/movement/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/movement/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warehouse.inventory'))
				->icon('fa fa-calendar-check-o')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/inventory/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/inventory/index.php', NULL, NULL, $additionalParams)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warehouse.types'))
		->icon('fa fa-list')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/type/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/type/index.php', NULL, NULL, $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warehouse.entries'))
		->icon('fa fa-list-check')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/entry/index.php', NULL, NULL, $additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}")
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/entry/index.php', NULL, NULL, $additionalParams)
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
	} while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
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
	} while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);


// Действие "Загрузка списка местоположений"
$oAdminFormActionLoadCountryLocationsList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
->Admin_Form_Actions
->getByName('loadList2');

if ($oAdminFormActionLoadCountryLocationsList && $oAdmin_Form_Controller->getAction() == 'loadList2')
{
	$oStructureControllerCountryLocationsList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options',  $oAdminFormActionLoadCountryLocationsList
	);

	$oStructureControllerCountryLocationsList
		->model(Core_Entity::factory('Shop_Country_Location'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_country_id', '=', Core_Array::getGet('list_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerCountryLocationsList);
}

// Действие "Загрузка списка городов"
$oAdminFormActionLoadCountryLocationCitiesList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
->Admin_Form_Actions
->getByName('loadList3');

if ($oAdminFormActionLoadCountryLocationCitiesList && $oAdmin_Form_Controller->getAction() == 'loadList3')
{
	$oStructureControllerCountryLocationCitiesList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadCountryLocationCitiesList
	);

	$oStructureControllerCountryLocationCitiesList
		->model(Core_Entity::factory('Shop_Country_Location_City'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_country_location_id', '=', Core_Array::getGet('list_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerCountryLocationCitiesList);
}

// Действие "Загрузка списка районов"
$oAdminFormActionLoadCountryLocationCitiesList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
->Admin_Form_Actions
->getByName('loadList4');

if ($oAdminFormActionLoadCountryLocationCitiesList && $oAdmin_Form_Controller->getAction() == 'loadList4')
{
	$oStructureControllerCountryLocationCitiesList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadCountryLocationCitiesList
	);

	$oStructureControllerCountryLocationCitiesList
		->model(Core_Entity::factory('Shop_Country_Location_City_Area'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_country_location_city_id', '=', Core_Array::getGet('list_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerCountryLocationCitiesList);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Warehouse_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Warehouse_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Warehouse_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warehouse_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

$oAdminFormActionChangeDefaultStatus = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionChangeDefaultStatus && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$Admin_Form_Action_Controller_Type_Apply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionChangeDefaultStatus
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Apply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Warehouse')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('shop_warehouses.*'))
)->addCondition(
	array(
		'where' => array('shop_warehouses.shop_id', '=', $shop_id)
	)
);

if (isset($oAdmin_Form_Controller->request['topFilter_filter_tags'])
	&& is_array($oAdmin_Form_Controller->request['topFilter_filter_tags']))
{
	$aValues = $oAdmin_Form_Controller->request['topFilter_filter_tags'];
	$aValues = array_filter($aValues, 'strlen');

	if (count($aValues))
	{
		$oAdmin_Form_Dataset->addCondition(
			array('join' => array('tag_shop_warehouses', 'shop_warehouses.id', '=', 'tag_shop_warehouses.shop_warehouse_id'))
		)->addCondition(
			array('join' => array('tags', 'tags.id', '=', 'tag_shop_warehouses.tag_id'))
		)->addCondition(
			array('where' => array('tags.name', 'IN', $aValues))
		);
	}
}

$oAdmin_Form_Controller
	->addExternalReplace('{shop_group_id}', $shop_group_id)
	->addExternalReplace('{shop_id}', $shop_id)
	;

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

if (is_null(Core_Entity::factory('Shop', $oShop->id)->Shop_Warehouses->getDefault(FALSE)))
{
	$oAdmin_Form_Controller->addMessage(Core_Message::get(Core::_('Shop_Warehouse.warehouse_default_not_exist'), 'error'));
}

// Показ формы
$oAdmin_Form_Controller->execute();