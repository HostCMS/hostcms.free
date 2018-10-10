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
$iAdmin_Form_Id = 76;

$sAdminFormAction = '/admin/shop/order/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = intval(Core_Array::getGet('shop_id'));
$shop_group_id = intval(Core_Array::getGet('shop_group_id'));
$shop_dir_id = intval(Core_Array::getGet('shop_dir_id'));
$shop_order_id = intval(Core_Array::getGet('shop_order_id'));

$oShopDir = Core_Entity::factory('Shop_Dir', $shop_dir_id);
$oShop = Core_Entity::factory('Shop')->find($shop_id);
$oShop_Order = Core_Entity::factory('Shop_Order', $shop_order_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle = Core::_('Shop_Order_Item.show_order_items_title', $oShop_Order->invoice))
	->pageTitle($sFormTitle);

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_warehouse'))
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$iShopId = intval(Core_Array::getGet('shop_id'));
	$oShop = Core_Entity::factory('Shop', $iShopId);

	$aJSON = array(
		'id' => 0,
		'label' => '[0] ...'
	);

	if (strlen($sQuery))
	{
		$aTmp = Shop_Order_Item_Controller_Edit::fillWarehousesList($oShop, $sQuery);

		foreach ($aTmp as $key => $value)
		{
			$key && $aJSON[] = array(
				'id' => $key,
				'label' => $value
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
		->name(Core::_('Shop_Order_Item.links_items_add'))
		->icon('fa fa-plus')
		->img('/admin/images/add.gif')
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
	} while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

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
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				'/admin/shop/item/index.php', NULL, NULL,$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0&shop_dir_id={$oShopDir->id}"
			)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				'/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams
			)
		)
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
	} while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на форму списка заказов
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.orders'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(
				$sShopOrderFormPath = '/admin/shop/order/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&shop_dir_id={$oShopDir->id}"
			)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(
				$sShopOrderFormPath, NULL, NULL, $sAdditionalParams
			)
		)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.order_edit', $oShop_Order->invoice))
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref('/admin/shop/order/index.php', 'edit', NULL, 0, $oShop_Order->id)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax('/admin/shop/order/index.php', 'edit', NULL, 0, $oShop_Order->id)
		)
);

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				$oAdmin_Form_Controller->getPath(),
				NULL,
				NULL,
				$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&shop_dir_id={$oShopDir->id}&shop_order_id={$shop_order_id}"
			)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				$oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams
			)
		)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Order_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Item_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Order_Item_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Order_Item_Controller_Edit);
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

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Order_Item')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset
	->addCondition
	(
		array('select' => array('shop_order_items.*', array(Core_QueryBuilder::expression('ROUND((`price` +  ROUND(`price` * `rate` / 100, 2)) * `quantity`, 2)'), 'sum')))
	)
	->addCondition(
		array('where' =>
			array('shop_order_id', '=', $shop_order_id)
		)
	)
;

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();