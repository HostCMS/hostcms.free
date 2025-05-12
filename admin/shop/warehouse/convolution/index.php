<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$sAdminFormAction = '/{admin}/shop/warehouse/convolution/index.php';

// Идентификатор магазина
$shop_id = intval(Core_Array::getGet('shop_id'));

// Идентификатор группы товаров
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop_Warehouse_Convolution.title'));

$windowId = $oAdmin_Form_Controller->getWindowId();

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Shop_Warehouse_Convolution.title'));

$bStart = Core_Array::getRequest('_', FALSE) && $oAdmin_Form_Controller->getAction() == 'start';

if ($bStart)
{
	$iShopId = intval(Core_Array::getRequest('shop_id'));
	$iShopGroupId = intval(Core_Array::getRequest('shop_group_id'));

	$iShopWarehouseId = strval(Core_Array::getRequest('shop_warehouse_id'));
	$sDate = strval(Core_Array::getRequest('date'));
	$iLimit = intval(Core_Array::getRequest('limit'));

	if (!defined('DENY_INI_SET') || !DENY_INI_SET)
	{
		set_time_limit(1200);
		ini_set("max_execution_time", "1200");
		ini_set("memory_limit", "512M");
	}

	Core_Session::start();

	$Shop_Warehouse_Convolution_Controller = new Shop_Warehouse_Convolution_Controller();

	if (isset($_SESSION['Shop_Warehouse_Convolution_Controller']))
	{
		$Shop_Warehouse_Convolution_Controller = $_SESSION['Shop_Warehouse_Convolution_Controller'];
		unset($_SESSION['Shop_Warehouse_Convolution_Controller']);
	}
	else
	{
		$Shop_Warehouse_Convolution_Controller = new Shop_Warehouse_Convolution_Controller();
		$Shop_Warehouse_Convolution_Controller
			->shopId($iShopId)
			->shop_warehouse_id($iShopWarehouseId)
			->date($sDate)
			->limit($iLimit);
	}

	$bCompleted = $Shop_Warehouse_Convolution_Controller
		->execute();

	$sAdditionalParams = "shop_id={$iShopId}&shop_group_id={$iShopGroupId}&shop_warehouse_id={$iShopWarehouseId}&date={$sDate}&limit={$iLimit}";

	// Режим - импорт или проведение документов
	$mode = Core_Array::getRequest('mode', 'import');

	if ($mode == 'import')
	{
		switch ($bCompleted)
		{
			case 'finish':
				Core_Message::show(Core::_('Shop_Warehouse_Convolution.create_end_post_start'), 'warning');
				$mode = 'post';
			break;
			case 'error':
				Core_Message::show(Core::_('Shop_Warehouse_Convolution.error'), 'error');
			break;
			case 'continue':
				Core_Message::show(Core::_('Shop_Warehouse_Convolution.create_document', $Shop_Warehouse_Convolution_Controller->position), 'warning');
			break;
		}

		$_SESSION['Shop_Warehouse_Convolution_Controller'] = $Shop_Warehouse_Convolution_Controller;

		$sRedirectAction = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'start', NULL, 0, 0, $sAdditionalParams . '&mode=' . $mode);
	}
	elseif ($mode == 'post')
	{
		// Post step-by-step
		if ($Shop_Warehouse_Convolution_Controller->postNext())
		{
			$_SESSION['Shop_Warehouse_Convolution_Controller'] = $Shop_Warehouse_Convolution_Controller;

			$sRedirectAction = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'start', NULL, 0, 0, $sAdditionalParams . '&mode=' . $mode);

			Core_Message::show(Core::_('Shop_Warehouse_Convolution.post'), 'warning');
		}
		else
		{
			$sRedirectAction = "";

			Core_Message::show(Core::_('Shop_Warehouse_Convolution.create_end'));
		}
	}
	else
	{
		echo 'Unknown mode "' . htmlspecialchars($mode) . '"';
	}

	Core_Session::close();

	if ($sRedirectAction)
	{
		$iRedirectTime = 1000;
		Core_Html_Entity::factory('Script')
			->type('text/javascript')
			->value('var timeout = setTimeout(function (){ ' . $sRedirectAction . '}, ' . $iRedirectTime . ');')
			->execute();
	}
}

if (!$bStart || $bCompleted)
{
	$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

	$oMainTab
		->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

	$aOptions = array();

	$aShop_Warehouses = $oShop->Shop_Warehouses->findAll(FALSE);
	foreach ($aShop_Warehouses as $oShop_Warehouse)
	{
		$aOptions[$oShop_Warehouse->id] = $oShop_Warehouse->name;
	}

	$oDefaultWarehouse = $oShop->Shop_Warehouses->getDefault();

	$oMainRow1->add(
		Admin_Form_Entity::factory('Select')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			->caption(Core::_('Shop_Warehouse_Convolution.shop_warehouse'))
			->name('shop_warehouse_id')
			->options($aOptions)
			->value(!is_null($oDefaultWarehouse) ? $oDefaultWarehouse->id : 0)
	)->add(
		Admin_Form_Entity::factory('Date')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'))
			->caption(Core::_('Shop_Warehouse_Convolution.date'))
			->name('date')
			->value(Core_Date::date2sql(date('Y/m/d', strtotime('-1 days'))))
	)->add(
		Admin_Form_Entity::factory('Input')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'))
			->caption(Core::_('Shop_Warehouse_Convolution.limit'))
			->name('limit')
			->value(5000)
	)->add(
		Admin_Form_Entity::factory('Input')
			->type('hidden')
			->divAttr(array('class' => 'hidden'))
			->name('shop_id')
			->value(Core_Array::getGet('shop_id'))
	)->add(
		Admin_Form_Entity::factory('Input')
			->type('hidden')
			->divAttr(array('class' => 'hidden'))
			->name('shop_group_id')
			->value(Core_Array::getGet('shop_group_id'))
	)->add(Admin_Form_Entity::factory('Div')
		->class('form-group col-xs-12 col-sm-2 margin-top-21')
		->add(
			Admin_Form_Entity::factory('Button')
				->name('start')
				->type('submit')
				->value(Core::_('Shop_Warehouse_Convolution.start'))
				->class('applyButton btn btn-blue')
				->onclick($oAdmin_Form_Controller->getAdminSendForm('start', NULL, ''))
		)
	);

	// Элементы строки навигации
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
			->name(Core::_('Shop_Warehouse.main_menu_warehouses_list'))
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams))
	)->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Shop_Warehouse_Inventory.title'))
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/warehouse/inventory/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/warehouse/inventory/index.php', NULL, NULL, $sAdditionalParams))
	);

	// Элементы строки навигации
	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Shop_Warehouse_Convolution.title'))
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}")
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams)
		)
	);

	// Добавляем все хлебные крошки контроллеру
	$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

	Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action($sAdminFormAction)
		->add($oMainTab)
		->execute();
}

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('Shop_Warehouse_Convolution.title'))
	->module($sModule)
	->execute();