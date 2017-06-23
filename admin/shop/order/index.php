<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 75;
$sAdminFormAction = '/admin/shop/order/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = intval(Core_Array::getGet('shop_id'));

// Идентификатор группы товаров
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Текущая группа магазинов
$oShopDir = Core_Entity::factory('Shop_Dir', $oShop->shop_dir_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle = Core::_('Shop_Order.show_order_title', $oShop->name))
	->pageTitle($sFormTitle);

// Shop Order Print Forms
$shop_print_form_id = intval(Core_Array::getGet('shop_print_form_id'));
if ($shop_print_form_id)
{
	$shop_order_id = intval(Core_Array::getGet('shop_order_id'));

	if ($shop_order_id)
	{
		$oShop_Print_Form = Core_Entity::factory('Shop_Print_Form', $shop_print_form_id);
		$oShop_Order = Core_Entity::factory('Shop_Order')->find($shop_order_id);

		Shop_Print_Form_Handler::factory($oShop_Print_Form)
			->shopOrder($oShop_Order)
			->execute();
	}
	exit();
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Order.shops_link_order'))
		->icon('fa fa-clipboard')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Order.shops_link_order_add'))
				->icon('fa fa-plus')
				->img('/admin/images/order_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Order.property_menu'))
				->icon('fa fa-gears')
				->img('/admin/images/page_gear.gif')
				->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$shop_group_id}")))
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href(
		$oAdmin_Form_Controller->getAdminLoadHref(
			$sShopItemFormPath = '/admin/shop/index.php', NULL, NULL, ''
		)
	)
	->onclick(
		$oAdmin_Form_Controller->getAdminLoadAjax(
			$sShopItemFormPath, NULL, NULL, ''
		)
	)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Добавляем крошки для групп магазинов
if($oShopDir->id)
{
	$aBreadcrumbs = array();

	$oShopBreadCrumbDir = $oShopDir;

	do
	{
		$additionalParams = "shop_dir_id={$oShopBreadCrumbDir->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopBreadCrumbDir->name)
			->href
			(
				$oAdmin_Form_Controller->getAdminLoadHref
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			)
			->onclick
			(
				$oAdmin_Form_Controller->getAdminLoadAjax
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			)
		;
	}while($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

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
		->href
		(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				'/admin/shop/item/index.php',
				NULL,
				NULL,
				$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0&shop_dir_id={$oShopDir->id}"
			)
		)
		->onclick
		(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				'/admin/shop/item/index.php',
				NULL,
				NULL,
				$sAdditionalParams
			)
		)
);

// Крошки строим только если: мы не в корне или идет редактирование
if($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	$sShopItemFormPath = '/admin/shop/item/index.php';

	do
	{
		$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_dir_id={$oShopDir->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroup->name)
			->href
			(
				$oAdmin_Form_Controller->getAdminLoadHref
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			)
			->onclick
			(
				$oAdmin_Form_Controller->getAdminLoadAjax
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			);
	} while($oShopGroup = $oShopGroup->getParent());

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
		->name(Core::_('Shop_Order.orders'))
		->href
		(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				$oAdmin_Form_Controller->getPath(),
				NULL,
				NULL,
				$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&shop_dir_id={$oShopDir->id}"
			)
		)
		->onclick
		(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				$oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams
			)
		)
);

 // Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Order_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Order_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Order_Controller_Edit);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Пересчет стоимости доставки"
$oAdminFormActionrecalcDelivery = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('recalcDelivery');

if ($oAdminFormActionrecalcDelivery && $oAdmin_Form_Controller->getAction() == 'recalcDelivery')
{
	$oControllerrecalcDelivery = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Recalc', $oAdminFormActionrecalcDelivery
	);

	$oAdmin_Form_Controller->addAction($oControllerrecalcDelivery);
}

// Действие "Загрузка списка условий доставки"
$oAdminFormActionloadDeliveryConditionsList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadDeliveryConditionsList');

if ($oAdminFormActionloadDeliveryConditionsList && $oAdmin_Form_Controller->getAction() == 'loadDeliveryConditionsList')
{
	$oControllerloadDeliveryConditionsList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionloadDeliveryConditionsList
	);
	$oControllerloadDeliveryConditionsList
		->model(Core_Entity::factory('Shop_Delivery_Condition'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_delivery_id', '=', Core_Array::getGet('delivery_id')))
		);

	$oAdmin_Form_Controller->addAction($oControllerloadDeliveryConditionsList);
}

// Действие "Загрузка списка местоположений"
$oAdminFormActionLoadCountryLocationsList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadList2');

if ($oAdminFormActionLoadCountryLocationsList && $oAdmin_Form_Controller->getAction() == 'loadList2')
{
	$oStructureControllerCountryLocationsList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadCountryLocationsList
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

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Удаление значения свойства"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deletePropertyValue');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oDeletePropertyValueController = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAction
	);

	$oDeletePropertyValueController
		->linkedObject(Core_Entity::factory('Shop_Order_Property_List', $oShop->id));

	$oAdmin_Form_Controller->addAction($oDeletePropertyValueController);
}

// Действие "Удаление файла большого изображения"
/*$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteLargeImage');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteLargeImage')
{
	$oDeleteLargeImageController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteLargeImageController
		->methodName('deleteLargeImage')
		->divId('control_large_image');

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oDeleteLargeImageController);
}

// Действие "Удаление файла малого изображения"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteSmallImage');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteSmallImage')
{
	$oDeleteSmallImageController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteSmallImageController
		->methodName('deleteSmallImage')
		->divId('control_small_image');

	$oAdmin_Form_Controller->addAction($oDeleteSmallImageController);
}*/

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Order')
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset
(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Dataset->addCondition(
	array(
		'where' => array('shop_id', '=', $oShop->id)
	)
);

// Список значений для фильтра и поля
$aShop_Order_Statuses = Core_Entity::factory('Shop_Order_Status')->findAll();
$sList = "0=…\n";
foreach ($aShop_Order_Statuses as $oShop_Order_Status)
{
	$sList .= "{$oShop_Order_Status->id}={$oShop_Order_Status->name}\n";
}

$oAdmin_Form_Dataset
	->changeField('shop_order_status_id', 'list', trim($sList));

$oAdmin_Form_Controller
	->addExternalReplace('{shop_group_id}', $shop_group_id)
	->addExternalReplace('{shop_dir_id}', $oShopDir->id);

// Показ формы
$oAdmin_Form_Controller->execute();