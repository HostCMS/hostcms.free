<?php
// Disable bf-cache
// define('SET_CACHE_CONTROL', FALSE);
// $oCore_Response
	// ->header("Cache-Control", "private, no-store, no-cache, must-revalidate")
	// ->header('Pragma', 'no-cache, no-store')
	// ->header("Expires", 0)
	// ->sendHeaders();

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

// Проверять остаток на складе при добавлении в корзину
$bCheckStock = TRUE;

Shop_Payment_System_Handler::checkBeforeContent($oShop);

Shop_Delivery_Handler::checkBeforeContent($oShop);

$oShop_Cart_Controller = Shop_Cart_Controller::instance();
$oShop_Cart_Controller->checkStock($bCheckStock);

// Добавление товара в корзину
if (Core_Array::getRequest('add'))
{
	// Core_Session::start();
	// Core_Session::setMaxLifeTime(86400, TRUE);

	// Запрещаем индексацию страницы корзины
	Core_Page::instance()->response
		->header('X-Robots-Tag', 'none');

	$add = Core_Array::getRequest('add');
	!is_array($add) && $add = array($add);

	$count = Core_Array::getRequest('count', 1);
	!is_array($count) && $count = array($count);

	//$oShop_Cart_Controller = Shop_Cart_Controller::instance();

	foreach ($add as $key => $shop_item_id)
	{
		$oShop_Cart_Controller
			->clear()
			//->checkStock($bCheckStock)
			->shop_item_id(intval($shop_item_id))
			->quantity(floatval(Core_Array::get($count, $key, 1)))
			->add();
	}
}

// Ajax
if (Core_Array::getRequest('_', FALSE)
	&& (Core_Array::getRequest('add') || Core_Array::getRequest('loadCart')))
{
	ob_start();

	// Краткая корзина
	$Shop_Cart_Controller_Show = new Shop_Cart_Controller_Show(
		$oShop
	);
	$Shop_Cart_Controller_Show
		->xsl(
			Core_Entity::factory('Xsl')->getByName(
				Core_Array::get(Core_Page::instance()->libParams, 'littleCartXsl')
			)
		)
		->couponText(isset($_SESSION) ? Core_Array::get($_SESSION, 'coupon_text') : NULL)
		->show();

	echo json_encode(ob_get_clean());
	exit();
}

// Быстрый заказ в 1 клик
if (!is_null(Core_Array::getRequest('oneStepCheckout')))
{
	$shop_item_id = intval(Core_Array::getRequest('shop_item_id'));

	$Shop_Cart_Controller_Onestep = new Shop_Cart_Controller_Onestep($oShop);

	// Генерация окна с заказом в 1 клик
	if (!is_null(Core_Array::getRequest('showDialog'))
		&& Core_Array::getRequest('_', FALSE)
		&& $shop_item_id
	)
	{
		ob_start();

		$oneStepXslName = Core_Array::get(Core_Page::instance()->libParams, 'oneStepXsl');

		$iQuantity = floatval(Core_Array::getRequest('count', 1));

		$Shop_Cart_Controller_Onestep
			->xsl(
				Core_Entity::factory('Xsl')->getByName($oneStepXslName)
			)
			->shop_item_id($shop_item_id)
			->quantity($iQuantity)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('count')->value($iQuantity)
			)
			->show();

		Core::showJson(
			array(
				'html' => ob_get_clean(),
				'id' => $shop_item_id
			)
		);
	}

	// Список доставок
	if (!is_null(Core_Array::getRequest('showDelivery')))
	{
		$shop_country_id = Core_Array::getRequest('shop_country_id', 0);
		$shop_country_location_id = Core_Array::getRequest('shop_country_location_id', 0);
		$shop_country_location_city_id = Core_Array::getRequest('shop_country_location_city_id', 0);
		$shop_country_location_city_area_id = Core_Array::getRequest('shop_country_location_city_area_id', 0);

		$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id);
		if (!is_null($oShop_Item->id))
		{
			$aTotal = $Shop_Cart_Controller_Onestep->quantity(Core_Array::getRequest('count', 1))->calculatePrice($oShop_Item);

			$aDelivery = $Shop_Cart_Controller_Onestep->showDelivery($shop_country_id, $shop_country_location_id, $shop_country_location_city_id, $shop_country_location_city_area_id, $aTotal['weight'], $aTotal['amount']);

			Core::showJson(
				array('delivery' => $aDelivery)
			);
		}
	}

	// Список платежных систем
	if (!is_null(Core_Array::getRequest('showPaymentSystem')))
	{
		$shop_delivery_condition_id = strval(Core_Array::getGet('shop_delivery_condition_id', 0));

		$aPaymentSystems = array();
		if (is_numeric($shop_delivery_condition_id))
		{
			$oShop_Delivery_Condition = Core_Entity::factory('Shop_Delivery_Condition', $shop_delivery_condition_id);

			$aPaymentSystems = $Shop_Cart_Controller_Onestep->showPaymentSystem($oShop_Delivery_Condition->shop_delivery_id);
		}

		Core::showJson(array('payment_systems' => $aPaymentSystems));
	}
}

if (Core_Array::getGet('action') == 'repeat')
{
	$guid = Core_Array::getGet('guid');
	if (strlen($guid))
	{
		$oShop_Order = $oShop->Shop_Orders->getByGuid($guid);

		if (!is_null($oShop_Order))
		{
			$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll();

			//$oShop_Cart_Controller = Shop_Cart_Controller::instance();

			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				$oShop_Order_Item->shop_item_id && $oShop_Cart_Controller
					->clear()
					//->checkStock($bCheckStock)
					->shop_item_id($oShop_Order_Item->shop_item_id)
					->quantity($oShop_Order_Item->quantity)
					->marking($oShop_Order_Item->marking)
					->add();
			}
		}
	}
}

if (!is_null(Core_Array::getGet('ajaxLoad')))
{
	$aObjects = array();

	if (Core_Array::getGet('shop_country_id'))
	{
		$oShop_Country_Location = Core_Entity::factory('Shop_Country_Location');
		$oShop_Country_Location
			->queryBuilder()
			->where('shop_country_id', '=', intval(Core_Array::getGet('shop_country_id')));
		$aObjects = $oShop_Country_Location->getAllByActive(1);
	}
	elseif (Core_Array::getGet('shop_country_location_id'))
	{
		$oShop_Country_Location_City = Core_Entity::factory('Shop_Country_Location_City');
		$oShop_Country_Location_City
			->queryBuilder()
			->where('shop_country_location_id', '=', intval(Core_Array::getGet('shop_country_location_id')));
		$aObjects = $oShop_Country_Location_City->getAllByActive(1);
	}
	elseif (Core_Array::getGet('shop_country_location_city_id'))
	{
		$oShop_Country_Location_City_Area = Core_Entity::factory('Shop_Country_Location_City_Area');
		$oShop_Country_Location_City_Area
			->queryBuilder()
			->where('shop_country_location_city_id', '=', intval(Core_Array::getGet('shop_country_location_city_id')));
		$aObjects = $oShop_Country_Location_City_Area->getAllByActive(1);
	}

	$aArray = array('…');
	foreach ($aObjects as $Object)
	{
		//$aArray['_' . $Object->id] = $Object->name;
		$aArray['_' . $Object->id] = $Object->getName();
	}

	Core::showJson($aArray);
}

// Удаляение товара из корзины
if (Core_Array::getGet('delete'))
{
	$shop_item_id = intval(Core_Array::getGet('delete'));

	if ($shop_item_id)
	{
		//$oShop_Cart_Controller = Shop_Cart_Controller::instance();
		$oShop_Cart_Controller
			->clear()
			->shop_item_id($shop_item_id)
			->delete();
	}
}

// Запоминаем купон
if (!is_null(Core_Array::getRequest('coupon_text')))
{
	Core_Session::start();
	$_SESSION['hostcmsOrder']['coupon_text'] = trim(strval(Core_Array::getRequest('coupon_text')));
}

// Запоминаем количество списываемых бонусов
if (!is_null(Core_Array::getRequest('bonuses')))
{
	Core_Session::start();
	if (!is_null(Core_Array::getRequest('apply_bonuses')))
	{
		$_SESSION['hostcmsOrder']['bonuses'] = trim(strval(Core_Array::getRequest('bonuses')));
	}
	elseif (isset($_SESSION['hostcmsOrder']['bonuses']))
	{
		unset($_SESSION['hostcmsOrder']['bonuses']);
	}
}

if (Core_Array::getPost('recount') || Core_Array::getPost('step') == 1)
{
	//$oShop_Cart_Controller = Shop_Cart_Controller::instance();
	$aCart = $oShop_Cart_Controller->getAll($oShop);

	// Склад по умолчанию
	$oShop_Warehouse = $oShop->Shop_Warehouses->getDefault();

	foreach ($aCart as $oShop_Cart)
	{
		$quantity = Core_Array::getPost('quantity_' . $oShop_Cart->shop_item_id);

		// Количество было передано
		if (!is_null($quantity))
		{
			$oShop_Cart_Controller
				->clear()
				//->checkStock($bCheckStock)
				->shop_item_id($oShop_Cart->shop_item_id)
				->quantity($quantity)
				->postpone(is_null(Core_Array::getPost('postpone_' . $oShop_Cart->shop_item_id)) ? 0 : 1)
				->shop_warehouse_id(
					Core_Array::getPost('warehouse_' . $oShop_Cart->shop_item_id, !is_null($oShop_Warehouse) ? $oShop_Warehouse->id : 0)
				)
				->update();
		}
	}
}

$Shop_Cart_Controller_Show = new Shop_Cart_Controller_Show($oShop);

Core_Page::instance()->object = $Shop_Cart_Controller_Show;