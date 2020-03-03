<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Report_Controller
{
	// Basic
	static protected $_oDefault_Currency = NULL;
	static protected $_startDatetime = NULL;
	static protected $_endDatetime = NULL;
	static protected $_previousStartDatetime = NULL;
	static protected $_previousEndDatetime = NULL;

	// Orders
	static protected $_shop_id = NULL;
	static protected $_order_parameter_y = NULL;
	static protected $_order_parameter_x = NULL;
	static protected $_order_segment = NULL;
	static protected $_allow_delivery = NULL;
	static protected $_total_orders = NULL;
	static protected $_total_order_items = NULL;
	static protected $_previous_total_orders = NULL;
	static protected $_previous_total_order_items = NULL;

	// Popular
	static protected $_popular_limit = NULL;
	static protected $_popular_producers_limit = NULL;
	static protected $_popular_parameter_y = NULL;
	static protected $_popular_parameter_x = NULL;
	static protected $_popular_segment = NULL;
	static protected $_group_modifications = NULL;

	// Для отчета по параметрам
	static protected $_byParams = array();
	static protected $_byParamSegments = array();

	// Colors
	static protected $_aYAxisColors = array();
	static protected $_aColors = array(
		'#E75B8D',
		'#FB6E52',
		'#FFCE55',
		'#A0D468',
		'#2DC3E8',
		'#6F85BF',
		'#CC324B',
		'#65B045',
		'#5DB2FF',
		'#FFF1A8',
		'#E46F61',
		'#008CD2',
		'#af3161',
		'#1ad40e',
		'#bc35c4',
		'#6f52d2',
		'#db0f44',
		'#24dd29',
		'#cb92e1',
		'#06c19c',
		'#3c1cca',
		'#e4c651',
		'#c5c0c6',
		'#c87503',
		'#65b921',
		'#66837d',
		'#abbb87',
		'#2ef3bc',
		'#a99d7b',
		'#61c822',
		'#c25c96',
		'#7fa99d',
		'#1fe14b',
		'#a18d37',
		'#f21e92',
		'#81d0f1',
		'#879829',
		'#0ca6e4'
	);

	static protected $_debug = FALSE;
	static protected $_debugMessages = array();

	static protected function _selectOrders($startDatetime, $endDatetime, $fromId, $toId)
	{
		$oShop_Orders_QB = Core_QueryBuilder::select('shop_orders.*')
			->from('shop_orders')
			->straightJoin()
			// ->indexHint('shop_orders', array('USE INDEX', 'datetime'))
			->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
			->where('shop_orders.id', 'BETWEEN', array($fromId, $toId))
			->where('shops.site_id', '=', CURRENT_SITE)
			->where('shops.deleted', '=', 0)
			->where('shop_orders.datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			->clearOrderBy()
			->orderBy('id', 'ASC');

		return $oShop_Orders_QB;
	}

	static protected function _selectOrdersItems($startDatetime, $endDatetime, $fromId, $toId)
	{
		/*
		SELECT `shop_order_items`.*, t1.datetime
		FROM `shop_order_items`
		INNER JOIN (
			SELECT `shop_orders`.id, `shop_orders`.`datetime` FROM `shop_orders` INNER JOIN `shops` ON `shops`.`id` = `shop_orders`.`shop_id` WHERE `shops`.`site_id` = '2' AND `shops`.`deleted` = 0 AND `shop_orders`.`datetime` BETWEEN '2019-05-20 00:00:00' AND '2019-11-20 23:59:59' ORDER BY `id` ASC LIMIT 5000 OFFSET 0
		) as t1 ON `shop_order_items`.shop_order_id = t1.id AND `shop_order_items`.deleted = 0
		*/

		$oShop_Orders_Items_QB = Core_QueryBuilder::select('shop_order_items.*', 't1.*');
		$oShop_Orders_Items_QB
			->from('shop_order_items')
			->join(
				array(
					$oInnerQB = Core_QueryBuilder::select(
							'shop_orders.id',
							'shop_orders.shop_id',
							'shop_orders.datetime',
							'shop_orders.shop_country_location_id',
							'shop_orders.shop_country_location_city_id',
							'shop_orders.shop_delivery_id',
							'shop_orders.shop_payment_system_id',
							'shop_orders.source_id',
							'shop_orders.company_id',
							'shop_orders.shop_order_status_id',
							'shop_orders.paid'
						)
						->from('shop_orders')
						->straightJoin()
						// ->indexHint('shop_orders', array('USE INDEX', 'datetime'))
						->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
						->where('shops.site_id', '=', CURRENT_SITE)
						->where('shops.deleted', '=', 0)
						->where('shop_orders.id', 'BETWEEN', array($fromId, $toId))
						->where('shop_orders.datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
						->clearOrderBy()
						->orderBy('id', 'ASC')
						// ->offset($offset)
						// ->limit($limit)
				, 't1'),
				'shop_order_items.shop_order_id', '=', 't1.id', array(
					array('AND' => array('shop_order_items.deleted', '=', 0)
				)
			));

		self::$_shop_id && $oInnerQB->where('shops.id', '=', self::$_shop_id);

		return $oShop_Orders_Items_QB;
	}

	static protected function _selectPaidOrders($startDatetime, $endDatetime, $fromId, $toId)
	{
		$oShop_Orders_QB = Core_QueryBuilder::select('shop_orders.*')
			->from('shop_orders')
			->straightJoin()
			// ->indexHint('shop_orders', array('USE INDEX', 'datetime'))
			->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
			->where('shop_orders.id', 'BETWEEN', array($fromId, $toId))
			->where('shops.site_id', '=', CURRENT_SITE)
			->where('shops.deleted', '=', 0)
			->where('shop_orders.paid', '=', 1)
			->where('shop_orders.payment_datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			->clearOrderBy()
			->orderBy('id', 'ASC');

		return $oShop_Orders_QB;
	}

	static protected function _selectPaidOrdersItems($startDatetime, $endDatetime, $fromId, $toId)
	{
		$oShop_Orders_Items_QB = Core_QueryBuilder::select('shop_order_items.*', 't1.*');
		$oShop_Orders_Items_QB
			->from('shop_order_items')
			->join(
				array(
					$oInnerQB = Core_QueryBuilder::select(
							'shop_orders.id',
							'shop_orders.shop_id',
							'shop_orders.datetime',
							'shop_orders.shop_country_location_id',
							'shop_orders.shop_country_location_city_id',
							'shop_orders.shop_delivery_id',
							'shop_orders.shop_payment_system_id',
							'shop_orders.source_id',
							'shop_orders.company_id',
							'shop_orders.shop_order_status_id',
							'shop_orders.paid'
						)
						->from('shop_orders')
						->straightJoin()
						// ->indexHint('shop_orders', array('USE INDEX', 'datetime'))
						->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
						->where('shops.site_id', '=', CURRENT_SITE)
						->where('shops.deleted', '=', 0)
						->where('shop_orders.paid', '=', 1)
						->where('shop_orders.id', 'BETWEEN', array($fromId, $toId))
						->where('shop_orders.datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
						->clearOrderBy()
						->orderBy('id', 'ASC')
						// ->offset($offset)
						// ->limit($limit)
				, 't1'),
				'shop_order_items.shop_order_id', '=', 't1.id', array(
					array('AND' => array('shop_order_items.deleted', '=', 0)
				)
			));

		self::$_shop_id && $oInnerQB->where('shops.id', '=', self::$_shop_id);

		return $oShop_Orders_Items_QB;
	}

	static protected function _selectCanceledOrders($startDatetime, $endDatetime)
	{
		$oShop_Orders = Core_Entity::factory('Shop_Order');
		$oShop_Orders
			->queryBuilder()
			->straightJoin()
			->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
			->where('shops.site_id', '=', CURRENT_SITE)
			->where('shops.deleted', '=', 0)
			->where('shop_orders.canceled', '=', 1)
			->where('shop_orders.datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			->clearOrderBy()
			->orderBy('shop_orders.id', 'ASC');

		self::$_shop_id && $oShop_Orders
			->queryBuilder()
			->where('shops.id', '=', self::$_shop_id);

		return $oShop_Orders;
	}

	static protected function _selectPopularItems($startDatetime, $endDatetime)
	{
		$oShop_Order_Items = Core_Entity::factory('Shop_Order_Item');
		$oShop_Order_Items
			->queryBuilder()
			->select(array(Core_QueryBuilder::expression('SUM(shop_order_items.quantity)'), 'dataQuantityAmount'))
			->select(array(Core_QueryBuilder::expression('AVG(shop_order_items.price)'), 'dataAvgPrice'))
			->select(array(Core_QueryBuilder::expression('SUM(shop_order_items.quantity * shop_order_items.price)'), 'dataTotalAmount'))
			->join('shop_orders', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
			->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
			->where('shops.site_id', '=', CURRENT_SITE)
			->where('shops.deleted', '=', 0)
			->where('shop_order_items.type', '=', 0)
			->where('shop_order_items.price', '>', 0)
			->where('shop_orders.paid', '=', 1)
			->where('shop_orders.payment_datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			->where('shop_orders.deleted', '=', 0)
			->clearOrderBy()
			->orderBy('dataQuantityAmount', 'DESC');

		// Группировать модификации
		if (self::$_group_modifications)
		{
			$oShop_Order_Items
				->queryBuilder()
				->select(array(Core_QueryBuilder::expression('IF(shop_items.modification_id, shop_items.modification_id, shop_items.id)'), 'dataId'))
				->join('shop_items', 'shop_items.id', '=', 'shop_order_items.shop_item_id')
				->groupBy('dataId');
		}
		else
		{
			$oShop_Order_Items
				->queryBuilder()
				->groupBy('shop_order_items.shop_item_id');
		}

		return $oShop_Order_Items;
	}

	static protected function _selectPopularProducers($startDatetime, $endDatetime)
	{
		$oShop_Order_Items = Core_Entity::factory('Shop_Order_Item');
		$oShop_Order_Items
			->queryBuilder()
			->select(array(Core_QueryBuilder::expression('SUM(shop_order_items.quantity)'), 'dataQuantityAmount'))
			// ->select(array(Core_QueryBuilder::expression('shop_producers.name'), 'dataProducerName'))
			->join('shop_orders', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
			->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
			->join('shop_items', 'shop_items.id', '=', 'shop_order_items.shop_item_id')
			->where('shops.site_id', '=', CURRENT_SITE)
			->where('shops.deleted', '=', 0)
			->where('shop_order_items.type', '=', 0)
			->where('shop_order_items.price', '>', 0)
			->where('shop_orders.paid', '=', 1)
			->where('shop_orders.payment_datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			->where('shop_orders.deleted', '=', 0)
			->clearOrderBy()
			->orderBy('dataQuantityAmount', 'DESC');

		// Группировать модификации
		if (self::$_group_modifications)
		{
			$oShop_Order_Items
				->queryBuilder()
				->select(array(Core_QueryBuilder::expression('IF(shop_items.modification_id, mod.shop_producer_id, shop_items.shop_producer_id)'), 'dataId'))
				->leftJoin(array('shop_items', 'mod'), 'mod.id', '=', 'shop_items.modification_id')
				->groupBy('dataId');
		}
		else
		{
			$oShop_Order_Items
				->queryBuilder()
				->groupBy('shop_items.shop_producer_id');
		}

		return $oShop_Order_Items;
	}

	static protected function _selectNewSiteusers($startDatetime, $endDatetime)
	{
		$oSiteusers = Core_Entity::factory('Siteuser');
		$oSiteusers
			->queryBuilder()
			->where('siteusers.active', '=', 1)
			->where('siteusers.datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			->where('siteusers.deleted', '=', 0)
			/*->clearOrderBy()
			->orderBy('siteusers.id', 'DESC')*/;

		return $oSiteusers;
	}

	static protected function _selectOrderSiteusers($startDatetime, $endDatetime)
	{
		$oShop_Orders = Core_Entity::factory('Shop_Order');
		$oShop_Orders
			->queryBuilder()
			->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
			->where('shops.site_id', '=', CURRENT_SITE)
			->where('shops.deleted', '=', 0)
			->where('shop_orders.datetime', 'BETWEEN', array($startDatetime . ' 00:00:00', $endDatetime . ' 23:59:59'))
			// ->groupBy('shop_order1s.siteuser_id')
			;

		self::$_shop_id && $oShop_Orders
			->queryBuilder()
			->where('shops.id', '=', self::$_shop_id);

		return $oShop_Orders;
	}

	static protected function _getYAxisName($aShop_Order)
	{
		$color = NULL;

		// Ось Y
		switch (self::$_order_parameter_y)
		{
			case 'region':
				$yAxisName = $aShop_Order['shop_country_location_id']
					? Core_Entity::factory('Shop_Country_Location', $aShop_Order['shop_country_location_id'])->name
					: '—';
			break;
			case 'city':
				$yAxisName = $aShop_Order['shop_country_location_city_id']
					? Core_Entity::factory('Shop_Country_Location_City', $aShop_Order['shop_country_location_city_id'])->name
					: '—';
			break;
			case 'delivery':
				$yAxisName = $aShop_Order['shop_delivery_id']
					? Core_Entity::factory('Shop_Delivery', $aShop_Order['shop_delivery_id'])->name
					: '—';
			break;
			case 'paid':
				$yAxisName = $aShop_Order['shop_payment_system_id']
					? Core_Entity::factory('Shop_Payment_System', $aShop_Order['shop_payment_system_id'])->name
					: '—';
			break;
			case 'order_status':
			default:
				$yAxisName = $aShop_Order['shop_order_status_id']
					? Core_Entity::factory('Shop_Order_Status', $aShop_Order['shop_order_status_id'])->name
					: '—';

				$aShop_Order['shop_order_status_id']
					&& Core_Entity::factory('Shop_Order_Status', $aShop_Order['shop_order_status_id'])->color != ''
					&& $color = Core_Entity::factory('Shop_Order_Status', $aShop_Order['shop_order_status_id'])->color;
			break;
			case 'seller':
				$yAxisName = $aShop_Order['company_id']
					? Core_Entity::factory('Shop_Company', $aShop_Order['company_id'])->name
					: Core_Entity::factory('Shop', $aShop_Order['shop_id'])->Shop_Company->name;
			break;
			case 'utm_source':
				$yAxisName = $aShop_Order['source_id'] && !is_null(Core_Entity::factory('Source', $aShop_Order['source_id'])->source)
					? Core_Entity::factory('Source', $aShop_Order['source_id'])->source
					: '—';
			break;
			case 'utm_medium':
				$yAxisName = $aShop_Order['source_id'] && !is_null(Core_Entity::factory('Source', $aShop_Order['source_id'])->medium)
					? Core_Entity::factory('Source', $aShop_Order['source_id'])->medium
					: '—';
			break;
			case 'utm_campaign':
				$yAxisName = $aShop_Order['source_id'] && !is_null(Core_Entity::factory('Source', $aShop_Order['source_id'])->campaign)
					? Core_Entity::factory('Source', $aShop_Order['source_id'])->campaign
					: '—';
			break;
		}

		return array($yAxisName, $color);
	}

	static protected function _getSegmentName($aShop_Order)
	{
		// Сегментация
		switch (self::$_order_segment)
		{
			case 'none':
			default:
				$segmentName = NULL;
			break;
			case 'region':
				$segmentName = $aShop_Order['shop_country_location_id']
					? Core_Entity::factory('Shop_Country_Location', $aShop_Order['shop_country_location_id'])->name
					: '—';
			break;
			case 'city':
				$segmentName = $aShop_Order['shop_country_location_city_id']
					? Core_Entity::factory('Shop_Country_Location_City', $aShop_Order['shop_country_location_city_id'])->name
					: '—';
			break;
			case 'delivery':
				$segmentName = $aShop_Order['shop_delivery_id']
					? Core_Entity::factory('Shop_Delivery', $aShop_Order['shop_delivery_id'])->name
					: '—';
			break;
			case 'paid':
				$segmentName = $aShop_Order['shop_payment_system_id']
					? Core_Entity::factory('Shop_Payment_System', $aShop_Order['shop_payment_system_id'])->name
					: '—';
			break;
			case 'utm_source':
				$segmentName = $aShop_Order['source_id'] && !is_null(Core_Entity::factory('Source', $aShop_Order['source_id'])->source)
					? Core_Entity::factory('Source', $aShop_Order['source_id'])->source
					: '—';
			break;
			case 'utm_medium':
				$segmentName = $aShop_Order['source_id'] && !is_null(Core_Entity::factory('Source', $aShop_Order['source_id'])->medium)
					? Core_Entity::factory('Source', $aShop_Order['source_id'])->medium
					: '—';
			break;
			case 'utm_campaign':
				$segmentName = $aShop_Order['source_id'] && !is_null(Core_Entity::factory('Source', $aShop_Order['source_id'])->campaign)
					? Core_Entity::factory('Source', $aShop_Order['source_id'])->campaign
					: '—';
			break;
		}

		return $segmentName;
	}

	static protected function _getOrders($functionName, $startDatetime, $endDatetime, $groupDate, $groupInc)
	{
		self::$_debug && $fBeginTime = Core::getmicrotime();

		// Default zeros
		$aOrderedAmount
			= $byParams = $byParamSegments
			= $aTotalOrders = $aTotalOrderItems = array();

		for ($iTmp = Core_Date::sql2timestamp($startDatetime . ' 00:00:00'); $iTmp <= Core_Date::sql2timestamp($endDatetime . ' 23:59:59'); $iTmp = strtotime('+1 day', $iTmp))
		{
			$sDate = date($groupDate, $iTmp);
			if (!isset($aOrderedAmount[$sDate]))
			{
				$aOrderedAmount[$sDate] = $aTotalOrders[$sDate] = $aTotalOrderItems[$sDate] = 0;
			}
		}

		$aAvgCount = $yAxisColor = array();

		$oShop_Controller = Shop_Controller::instance();

		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'))
			->from('shop_orders');

		self::$_shop_id && $oCore_QueryBuilder_Select
			->where('shop_orders.shop_id', '=', self::$_shop_id);

		$aRow = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

		$iMaxId = $aRow['max_id'];

		$fromId = 0;
		$limit = 1000;

		do {
			// ---------------------
			self::$_debug && $fBeginTime2 = Core::getmicrotime();

			$oShop_Orders_QB = self::$functionName($startDatetime, $endDatetime, $fromId + 1, $fromId + $limit);

			self::$_shop_id && $oShop_Orders_QB
				->where('shops.id', '=', self::$_shop_id);

			$aShop_Orders = $oShop_Orders_QB->asAssoc()->execute()->result();

			self::$_debug && self::$_debugMessages[] = sprintf("{$functionName} Query as Assoc, fromId {$fromId}, limit {$limit}: %.5f", Core::getmicrotime() - $fBeginTime2);

			self::$_debug && $fBeginTime2 = Core::getmicrotime();

			foreach ($aShop_Orders as $aShop_Order)
			{
				$sDate = date($groupDate, Core_Date::sql2timestamp($aShop_Order['datetime']));

				// Количество заказов
				isset($aTotalOrders[$sDate])
					? $aTotalOrders[$sDate]++
					: $aTotalOrders[$sDate] = 1;

				// Количество товаров в заказе
				!isset($aTotalOrderItems[$sDate]) && $aTotalOrderItems[$sDate] = 0;

				list($yAxisName, $color) = self::_getYAxisName($aShop_Order);

				!isset($byParams[$yAxisName])
					&& $byParams[$yAxisName] = 0;

				!is_null($color) && !isset($yAxisColor[$yAxisName])
					&& $yAxisColor[$yAxisName] = $color;

				// Ось X
				if (in_array(self::$_order_parameter_x, array('orders_count', 'avg_amount')))
				{
					switch (self::$_order_parameter_x)
					{
						case 'orders_count':
						default:
							$byParams[$yAxisName] += 1;
						break;
						case 'avg_amount':
							!isset($aAvgCount[$yAxisName])
								? $aAvgCount[$yAxisName] = 1
								: $aAvgCount[$yAxisName]++;
						break;
					}

					!isset($byParamSegments[$yAxisName])
						&& $byParamSegments[$yAxisName] = array();

					$segmentName = self::_getSegmentName($aShop_Order);

					if (!is_null($segmentName))
					{
						!isset($byParamSegments[$yAxisName][$segmentName])
							&& $byParamSegments[$yAxisName][$segmentName] = 0;

						$byParamSegments[$yAxisName][$segmentName]++;
					}
				}
			}

			self::$_debug && self::$_debugMessages[] = sprintf("{$functionName} Process Data: %.5f", Core::getmicrotime() - $fBeginTime2);

			$fromId += $limit;
		}
		while ($fromId < $iMaxId);

		$fromId = 0;

		$itemsFunctionName = $functionName . 'Items';

		do {
			self::$_debug && $fBeginTime1 = Core::getmicrotime();

			$oShop_Order_Items_QB = self::$itemsFunctionName($startDatetime, $endDatetime, $fromId + 1, $fromId + $limit);

			$aShop_Order_Items = $oShop_Order_Items_QB->asAssoc()->execute()->result();

			self::$_debug && self::$_debugMessages[] = sprintf("{$itemsFunctionName} Query, offset {$fromId}, limit {$limit}: %.5f", Core::getmicrotime() - $fBeginTime1);

			self::$_debug && $fBeginTime2 = Core::getmicrotime();

			foreach ($aShop_Order_Items as $aShop_Order_Item)
			{
				$sDate = date($groupDate, Core_Date::sql2timestamp($aShop_Order_Item['datetime']));

				$tax = Shop_Controller::instance()->round($aShop_Order_Item['price'] * $aShop_Order_Item['rate'] / 100);

				$fAmount = self::$_allow_delivery || $aShop_Order_Item['type'] != 1
					? Shop_Controller::instance()->round(Shop_Controller::instance()->round($aShop_Order_Item['price'] + $tax) * $aShop_Order_Item['quantity'])
					: 0;

				// Доставка - не товар!
				$aShop_Order_Item['type'] != 1
					&& $aTotalOrderItems[$sDate] += 1;

				!isset($aOrderedAmount[$sDate]) && $aOrderedAmount[$sDate] = 0;
				$aOrderedAmount[$sDate] += $oShop_Controller->round($fAmount);

				list($yAxisName, $color) = self::_getYAxisName($aShop_Order_Item);

				!isset($byParams[$yAxisName])
					&& $byParams[$yAxisName] = 0;

				!is_null($color) && !isset($yAxisColor[$yAxisName])
					&& $yAxisColor[$yAxisName] = $color;

				// Ось X
				if (in_array(self::$_order_parameter_x, array('orders_amount', 'paid_amount', 'avg_amount')))
				{
					switch (self::$_order_parameter_x)
					{
						case 'orders_amount':
							$byParams[$yAxisName] += $oShop_Controller->round($fAmount);
						break;
						case 'paid_amount':
						case 'avg_amount':
							$aShop_Order_Item['paid']
								&& $byParams[$yAxisName] += $oShop_Controller->round($fAmount);
						break;
					}

					!isset($byParamSegments[$yAxisName])
						&& $byParamSegments[$yAxisName] = array();

					$segmentName = self::_getSegmentName($aShop_Order_Item);

					if (!is_null($segmentName))
					{
						!isset($byParamSegments[$yAxisName][$segmentName])
							&& $byParamSegments[$yAxisName][$segmentName] = 0;

						$byParamSegments[$yAxisName][$segmentName]++;
					}
				}
			}

			self::$_debug && self::$_debugMessages[] = sprintf("{$itemsFunctionName} Process Data: %.5f", Core::getmicrotime() - $fBeginTime2);

			$fromId += $limit;
		}
		while ($fromId < $iMaxId);

		// Расчет среднего чека
		if (self::$_order_parameter_x == 'avg_amount')
		{
			foreach ($byParams as $yAxisName => $amount)
			{
				$byParams[$yAxisName] = Shop_Controller::instance()->round($byParams[$yAxisName] / $aAvgCount[$yAxisName]);
			}
		}

		self::$_debug && self::$_debugMessages[] = sprintf('_getOrders(), ' . $functionName . ' %.5f', Core::getmicrotime() - $fBeginTime);

		return array(
			'orderedAmount' => $aOrderedAmount,
			'byParams' => $byParams,
			'yAxisColor' => $yAxisColor,
			'byParamSegments' => $byParamSegments,
			'totalOrders' => $aTotalOrders,
			'totalOrderItems' => $aTotalOrderItems
		);
	}

	static protected function _getPopularItems($functionName, $startDatetime, $endDatetime, $groupDate, $groupInc)
	{
		self::$_debug && $fBeginTime = Core::getmicrotime();

		$aPopularItems = array();

		$limit = 1000;
		$offset = 0;

		self::$_byParams = self::$_byParamSegments = array();

		do {
			$oShop_Order_Items = self::$functionName($startDatetime, $endDatetime);
			$oShop_Order_Items
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			self::$_shop_id && $oShop_Order_Items
				->queryBuilder()
				->where('shops.id', '=', self::$_shop_id);

			$aShop_Order_Items = $oShop_Order_Items->findAll(FALSE);

			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				$iId = self::$_group_modifications ? $oShop_Order_Item->dataId : $oShop_Order_Item->shop_item_id;

				$aPopularItems[$iId] = array(
					'name' => $oShop_Order_Item->name,
					'marking' => $oShop_Order_Item->marking,
					'avgPrice' => number_format($oShop_Order_Item->dataAvgPrice, 2, '.', ' '),
					'quantityAmount' => $oShop_Order_Item->dataQuantityAmount,
					'totalAmount' => number_format($oShop_Order_Item->dataTotalAmount, 2, '.', ' ')
				);

				$oShop_Item = $oShop_Order_Item->Shop_Item->modification_id
					? $oShop_Order_Item->Shop_Item->Modification
					: $oShop_Order_Item->Shop_Item;

				// Ось Y
				switch (self::$_popular_parameter_y)
				{
					case 'group':
					default:
						$yAxisName = $oShop_Order_Item->shop_item_id
							? ($oShop_Item->shop_group_id
								? $oShop_Item->Shop_Group->name
								: Core::_('Report.root')
							)
							: '—';
					break;
					case 'producer':
						$yAxisName = $oShop_Order_Item->shop_item_id
							? ($oShop_Item->shop_producer_id
								? $oShop_Item->Shop_Producer->name
								: '—'
							)
							: '—';
					break;
				}

				!isset(self::$_byParams[$yAxisName])
					&& self::$_byParams[$yAxisName] = 0;

				// Ось X
				switch (self::$_popular_parameter_x)
				{
					case 'count_positions':
					default:
						self::$_byParams[$yAxisName] += 1;
					break;
					case 'count_paid':
						$oShop_Order_Item->Shop_Order->paid
							&& self::$_byParams[$yAxisName] += $oShop_Order_Item->quantity;
					break;
					case 'total_amount':
						$fCurrencyCoefficient = $oShop_Order_Item->Shop_Order->Shop_Currency->id > 0 && self::$_oDefault_Currency->id > 0
							? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
								$oShop_Order_Item->Shop_Order->Shop_Currency, self::$_oDefault_Currency
							)
							: 0;

						$fAmount = $oShop_Order_Item->getAmount() * $fCurrencyCoefficient;

						self::$_byParams[$yAxisName] += Shop_Controller::instance()->round($fAmount);
					break;
				}

				!isset(self::$_byParamSegments[$yAxisName])
					&& self::$_byParamSegments[$yAxisName] = array();

				// Сегментация
				switch (self::$_popular_segment)
				{
					case 'none':
					default:
						$segmentName = NULL;
					break;
					case 'group':
						$segmentName = $oShop_Order_Item->shop_item_id
							? $oShop_Item->Shop_Group->name
							: '—';
					break;
					case 'producer':
						$segmentName = $oShop_Order_Item->shop_item_id
							? $oShop_Item->Shop_Producer->name
							: '—';
					break;
				}

				if (!is_null($segmentName))
				{
					!isset(self::$_byParamSegments[$yAxisName][$segmentName])
						&& self::$_byParamSegments[$yAxisName][$segmentName] = 0;

					self::$_byParamSegments[$yAxisName][$segmentName]++;
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Order_Items));

		self::$_debug && self::$_debugMessages[] = sprintf('_getPopularItems(), ' . $functionName . ' %.5f', Core::getmicrotime() - $fBeginTime);

		return $aPopularItems;
	}

	static protected function _getPriorityContent($aShop_Order_Items)
	{
		$class = count($aShop_Order_Items) > 10
			? 'table-wrapper-scroll-y report-table-scrollbar'
			: '';
		?>
		<div class="<?php echo $class?>">
			<table class="table table-hover">
				<thead>
					<tr>
						<th><?php echo Core::_('Report.item_group')?></th>
						<th><?php echo Core::_('Report.item_name')?></th>
						<th><?php echo Core::_('Report.item_marking')?></th>
						<th><?php echo Core::_('Report.item_ordered')?></th>
						<th><?php echo Core::_('Report.item_amount')?></th>
						<th><?php echo Core::_('Report.item_avg_price')?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($aShop_Order_Items as $shop_item_id => $aTmp)
					{
						$oShop_Item = Core_Entity::factory('Shop_Item', $shop_item_id);

						$oShop_Item = $oShop_Item->modification_id
							? $oShop_Item->Modification
							: $oShop_Item;

						$sItemUrl = '';
						$oSiteAlias = $oShop_Item->Shop->Site->getCurrentAlias();
						if ($oSiteAlias)
						{
							$sItemUrl = ($oShop_Item->Shop->Structure->https ? 'https://' : 'http://')
								. $oSiteAlias->name
								. $oShop_Item->Shop->Structure->getPath()
								. $oShop_Item->getPath();
						}

						$imgSrc = '';
						if (strlen($oShop_Item->image_small))
						{
							$imgSrc = htmlspecialchars($oShop_Item->getSmallFileHref());
						}
						?>
						<tr>
							<td><?php echo $oShop_Item->shop_group_id ? htmlspecialchars($oShop_Item->Shop_Group->name) : Core::_('Report.root')?></td>
							<td><a href="<?php echo $sItemUrl?>" target="_blank" data-container="body" data-titleclass="bordered-palegreen" data-toggle="popover-hover" data-placement="top" data-title="<?php echo htmlspecialchars($aTmp['name'])?>" data-content="<div class='text-align-center'><img src='<?php echo $imgSrc?>' /></div>"><?php echo htmlspecialchars($aTmp['name'])?></a></td>
							<td><?php echo htmlspecialchars($aTmp['marking'])?></td>
							<td><?php echo round($aTmp['quantityAmount'])?></td>
							<td><?php echo $aTmp['totalAmount']?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?></td>
							<td><?php echo $aTmp['avgPrice']?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}

	static protected $_aOrderedAmount = NULL;
	static protected $_aOrderedByParams = NULL;
	static protected $_aOrderedByParamSegments = NULL;

	static protected $_aOrderedPreviousAmount = NULL;

	static protected function _prepareOrders($functionName, $aOptions)
	{
		$isActive = Core_Session::isAcive();
		!$isActive && Core_Session::start();

		self::$_shop_id = isset($_SESSION['report']['shop_id'])
			? intval($_SESSION['report']['shop_id'])
			: 0;

		self::$_allow_delivery = isset($_SESSION['report']['allow_delivery'])
			? intval($_SESSION['report']['allow_delivery'])
			: 0;

		self::$_order_parameter_y = isset($_SESSION['report']['order_parameter_y'])
			? strval($_SESSION['report']['order_parameter_y'])
			: 'order_status';

		self::$_order_parameter_x = isset($_SESSION['report']['order_parameter_x'])
			? strval($_SESSION['report']['order_parameter_x'])
			: 'orders_count';

		self::$_order_segment = isset($_SESSION['report']['order_segment'])
			? strval($_SESSION['report']['order_segment'])
			: 'none';

		!$isActive && Core_Session::close();

		self::$_oDefault_Currency = Core_Entity::factory('Shop_Currency')->getDefault();

		self::$_startDatetime = isset($aOptions['start_datetime'])
			? $aOptions['start_datetime']
			: date('Y-m-d', strtotime("-6 month"));

		self::$_endDatetime = isset($aOptions['end_datetime'])
			? $aOptions['end_datetime']
			: date('Y-m-d', time());

		self::$_previousStartDatetime = isset($aOptions['previous_start_datetime'])
			? $aOptions['previous_start_datetime']
			: date('Y-m-d', strtotime("-6 month", Core_Date::sql2timestamp(self::$_startDatetime)));

		self::$_previousEndDatetime = isset($aOptions['previous_end_datetime'])
			? $aOptions['previous_end_datetime']
			: date('Y-m-d', strtotime("-1 day", Core_Date::sql2timestamp(self::$_startDatetime)));

		if (self::$_oDefault_Currency)
		{
			$group_by = Core_Array::get($aOptions, 'group_by', 1);

			switch ($group_by)
			{
				case 0: // day
					$groupDate = 'd.m';
					$groupInc = '+1 day';
				break;
				case 1: // week
				default:
					$groupDate = 'W';
					$groupInc = '+1 week';
				break;
				case 2: // month
					$groupDate = 'm.Y';
					$groupInc = '+1 month';
				break;
			}

			$aTmp = self::_getOrders($functionName, self::$_startDatetime, self::$_endDatetime, $groupDate, $groupInc);
			self::$_aOrderedAmount = $aTmp['orderedAmount'];
			self::$_aOrderedByParams = $aTmp['byParams'];
			arsort(self::$_aOrderedByParams);
			self::$_aOrderedByParamSegments = $aTmp['byParamSegments'];
			self::$_aYAxisColors = $aTmp['yAxisColor'];
			self::$_total_orders = $aTmp['totalOrders'];
			self::$_total_order_items = $aTmp['totalOrderItems'];

			if (Core_Array::get($aOptions, 'compare_previous_period', 0))
			{
				$aTmp = self::_getOrders($functionName, self::$_previousStartDatetime, self::$_previousEndDatetime, $groupDate, $groupInc);
				self::$_aOrderedPreviousAmount = $aTmp['orderedAmount'];

				self::$_previous_total_orders = $aTmp['totalOrders'];
				self::$_previous_total_order_items = $aTmp['totalOrderItems'];
			}
			else
			{
				self::$_aOrderedPreviousAmount = array();
			}
		}
	}

	static protected function _orders($functionName, $aOptions)
	{
		$compare_previous_period = Core_Array::get($aOptions, 'compare_previous_period', 0);

		$checked = self::$_allow_delivery
			? 'checked="checked"'
			: '';
		?>
		<div class="row">
			<div class="col-xs-12 col-sm-4">
				<?php
				$aShopOptions = array(0 => Core::_('Report.all_shops'));

				$aShops = Core_Entity::factory('Shop')->getAllBySite_id(CURRENT_SITE, FALSE);
				foreach ($aShops as $oShop)
				{
					$aShopOptions[$oShop->id] = $oShop->name;
				}

				Admin_Form_Entity::factory('Select')
					->id('shop_id')
					->options($aShopOptions)
					->value(self::$_shop_id)
					->name('shop_id')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {shop_id: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-4 margin-top-5">
				<div class="pull-left text margin-right-10"><?php echo Core::_('Report.allow_delivery')?></div>
				<label>
					<input class="checkbox-slider toggle colored-success" name="allow_delivery" onchange="$(this).val(+this.checked); sendRequest({tab: $('.report-tabs .nav-tabs li.active'), data: {allow_delivery: $(this).val()}});" type="checkbox" value="<?php echo self::$_allow_delivery?>" <?php echo $checked?>/>
					<span class="text"></span>
				</label>
			</div>
		</div>
		<?php
		if ($functionName == '_selectOrders')
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();

			$newSiteusersDeltaPercent = $avgOrdersDeltaPercent = $avgOrdersAmountDeltaPercent = $canceledDeltaPercent = $avgCommonOrdersAmountDeltaPercent = $avgCountOrdersItemDeltaPercent = '';

			// New siteusers
			$iCountNewSiteusers = Core::moduleIsActive('siteuser') ? self::_selectNewSiteusers(self::$_startDatetime, self::$_endDatetime)->getCount() : 0;

			$iCountTotalOrders = array_sum(self::$_total_orders);

			$iCountOrderSiteusers = self::_selectOrderSiteusers(self::$_startDatetime, self::$_endDatetime)->getCount(FALSE, 'siteuser_id', TRUE);

			$avgOrders = $iCountOrderSiteusers
				? $iCountTotalOrders / $iCountOrderSiteusers
				: 0;

			// Orders amount by siteuser
			$iCountTotalOrdersAmount = array_sum(self::$_aOrderedAmount);

			$avgOrdersAmount = $iCountOrderSiteusers
				? $iCountTotalOrdersAmount / $iCountOrderSiteusers
				: 0;

			// Canceled orders
			$iCountCanceledOrders = self::_selectCanceledOrders(self::$_startDatetime, self::$_endDatetime)->getCount();
			$canceledPercent = $iCountTotalOrders
				? round($iCountCanceledOrders * 100 / $iCountTotalOrders)
				: 0;

			// Avg orders amount
			$avgCommonOrdersAmount = $iCountTotalOrders
				? $iCountTotalOrdersAmount / $iCountTotalOrders
				: 0;

			$iCountTotalOrdersItem = array_sum(self::$_total_order_items);

			// Avg order items
			$avgCountOrdersItem = $iCountTotalOrders
				? $iCountTotalOrdersItem / $iCountTotalOrders
				: 0;

			if ($compare_previous_period)
			{
				// New siteusers
				$iPreviuosCountNewSiteusers = Core::moduleIsActive('siteuser') ? self::_selectNewSiteusers(self::$_previousStartDatetime, self::$_previousEndDatetime)->getCount() : 0;

				if ($iCountNewSiteusers && $iPreviuosCountNewSiteusers)
				{
					$percent = 100 - round($iPreviuosCountNewSiteusers * 100 / $iCountNewSiteusers);
					$newSiteusersDeltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}

				// Orders by siteuser
				$iPreviousCountTotalOrders = array_sum(self::$_previous_total_orders);

				$iPreviuosCountOrderSiteusers = self::_selectOrderSiteusers(self::$_previousStartDatetime, self::$_previousEndDatetime)->getCount(FALSE, 'siteuser_id', TRUE);

				$avgPreviousOrders = $iPreviuosCountOrderSiteusers
					? $iPreviousCountTotalOrders / $iPreviuosCountOrderSiteusers
					: 0;

				if ($avgOrders && $avgPreviousOrders)
				{
					$percent = 100 - round($avgPreviousOrders * 100 / $avgOrders);
					$avgOrdersDeltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}

				// Orders amount by siteuser
				$iPreviousCountTotalOrdersAmount = array_sum(self::$_aOrderedPreviousAmount);

				$avgPreviousOrdersAmount = $iPreviuosCountOrderSiteusers
					? $iPreviousCountTotalOrdersAmount / $iPreviuosCountOrderSiteusers
					: 0;

				if ($avgOrdersAmount && $avgPreviousOrdersAmount)
				{
					$percent = 100 - round($avgPreviousOrdersAmount * 100 / $avgOrdersAmount);
					$avgOrdersAmountDeltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}

				// Canceled orders
				$iPreviousCountCanceledOrders = self::_selectCanceledOrders(self::$_previousStartDatetime, self::$_previousEndDatetime)->getCount();
				$iPreviousCanceledPercent = $iPreviousCountTotalOrders
					? round($iPreviousCountCanceledOrders * 100 / $iPreviousCountTotalOrders)
					: 0;

				if ($canceledPercent && $iPreviousCanceledPercent)
				{
					$percent = 100 - round($iPreviousCanceledPercent * 100 / $canceledPercent);
					$canceledDeltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}

				// Avg orders amount
				$avgPreviousCommonOrdersAmount = $iPreviousCountTotalOrders
					? $iPreviousCountTotalOrdersAmount / $iPreviousCountTotalOrders
					: 0;

				if ($avgCommonOrdersAmount && $avgPreviousCommonOrdersAmount)
				{
					$percent = 100 - round($avgPreviousCommonOrdersAmount * 100 / $avgCommonOrdersAmount);
					$avgCommonOrdersAmountDeltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}

				// Avg order items
				$iPreviousCountTotalOrdersItem = array_sum(self::$_previous_total_order_items);

				// Avg order items
				$avgPreviuosCountOrdersItem = $iPreviousCountTotalOrders
					? $iPreviousCountTotalOrdersItem / $iPreviousCountTotalOrders
					: 0;

				if ($avgCountOrdersItem && $avgPreviuosCountOrdersItem)
				{
					$percent = 100 - round($avgPreviuosCountOrdersItem * 100 / $avgCountOrdersItem);
					$avgCountOrdersItemDeltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}
			}
			?>
			<div class="row siteusers-block margin-top-20">
				<div class="col-xs-12 col-sm-3">
					<div class="report-name"><?php echo Core::_('Report.widget_new_clients')?></div>
					<div class="report-description">
						<?php echo $iCountNewSiteusers?>
						<?php echo $newSiteusersDeltaPercent?>
					</div>
				</div>
				<div class="col-xs-12 col-sm-3">
					<div class="report-name"><?php echo Core::_('Report.widget_orders_by_client')?></div>
					<div class="report-description">
						<?php echo number_format($avgOrders, 2, '.', ' ')?>
						<?php echo $avgOrdersDeltaPercent?>
					</div>
				</div>
				<div class="col-xs-12 col-sm-3">
					<div class="report-name"><?php echo Core::_('Report.widget_client_avg_price')?></div>
					<div class="report-description">
						<?php echo number_format($avgOrdersAmount, 2, '.', ' ')?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?>
						<?php echo $avgOrdersAmountDeltaPercent?>
					</div>
				</div>
				<div class="col-xs-12 col-sm-3">
					<div class="report-name"><?php echo Core::_('Report.widget_canceled_orders')?></div>
					<div class="report-description">
						<?php echo $canceledPercent?>%
						<?php echo $canceledDeltaPercent?>
					</div>
				</div>
			</div>
			<div class="row siteusers-block margin-top-20">
				<div class="col-xs-12 col-sm-3">
					<div class="report-name"><?php echo Core::_('Report.widget_avg_price')?></div>
					<div class="report-description">
						<?php echo number_format($avgCommonOrdersAmount, 2, '.', ' ')?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?>
						<?php echo $avgCommonOrdersAmountDeltaPercent?>
					</div>
				</div>
				<div class="col-xs-12 col-sm-3">
					<div class="report-name"><?php echo Core::_('Report.widget_avg_order_items')?></div>
					<div class="report-description">
						<?php echo number_format($avgCountOrdersItem, 2, '.', ' ')?>
						<?php echo $avgCountOrdersItemDeltaPercent?>
					</div>
				</div>
			</div>
			<?php
			self::$_debug && self::$_debugMessages[] = sprintf('_orders(), _selectOrders %.5f', Core::getmicrotime() - $fBeginTime);
		}
		?>
		<div class="row">
			<div class="col-xs-12">
				<div id="bar-chart<?php echo $functionName?>" class="chart chart-lg margin-top-20"></div>
			</div>
		</div>
		<?php
		if (self::$_oDefault_Currency)
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();

			$group_by = Core_Array::get($aOptions, 'group_by', 1);

			switch ($group_by)
			{
				case 0: // day
					$delta = 'day';
				break;
				case 1: // week
				default:
					$delta = 'week';
				break;
				case 2: // month
					$delta = 'month';
				break;
			}
			?>
			<div class="row">
				<div class="col-xs-12">
					<div class="show-table-button margin-top-20 padding-10">
						<div class="row">
							<div class="col-xs-12 col-sm-3">
								<div class="report-name"><?php echo Core::_('Report.order_costs');?></div>
								<div class="amount"></div>
							</div>
							<div class="col-xs-12 col-sm-9 margin-top-10">
								<a onclick="$('.data-table').toggleClass('hidden')" class="btn btn-primary"><?php echo Core::_('Report.show_table')?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 margin-top-20">
					<div class="data-table hidden margin-bottom-20">
						<div class="table-scrollable">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<th><?php echo Core::_('Report.table_period')?></th>
										<th><?php echo Core::_('Report.table_orders')?></th>
										<th><?php echo Core::_('Report.table_items')?></th>
										<th><?php echo Core::_('Report.table_amount')?></th>
									</tr>
								</thead>
								<tbody>
								<?php
								switch ($delta)
								{
									case 'week':
									default:
										$label = Core::_('Report.table_week');
										$isWeek = 1;
									break;
									case 'day':
									case 'month':
										$label = '';
										$isWeek = 0;
									break;
								}

								$aDates = array();

								$end_date1 = date('Y-m-d', strtotime(self::$_endDatetime . ' + 7 days'));

								for ($date = self::$_startDatetime; $date <= $end_date1; $date = date('Y-m-d', strtotime($date. ' + 7 days')))
								{
									$week =  date('W', strtotime($date));
									$year =  date('Y', strtotime($date));

									$from = date("Y-m-d", strtotime("{$year}-W{$week}+1"));
									$from < self::$_startDatetime && $from = self::$_startDatetime;
									$to = date("Y-m-d", strtotime("{$year}-W{$week}-7"));
									$to > self::$_endDatetime && $to = self::$_endDatetime;

									$aDates[$week] = array(
										'from' => Core_Date::sql2date($from),
										'to' => Core_Date::sql2date($to)
									);
								}

								$totalPeriodAmount = $totalPeriodOrders = $totalPeriodOrderItems = 0;
								foreach (self::$_aOrderedAmount as $date => $amount)
								{
									$ordersCount = isset(self::$_total_orders[$date])? self::$_total_orders[$date] : 0;
									$orderItemsCount = isset(self::$_total_order_items[$date])? self::$_total_order_items[$date] : 0;
									?>
									<tr>
										<td>
											<b><?php echo $date?> <?php echo $label?></b><br/>
											<?php
											if ($isWeek)
											{
												if (isset($aDates[$date]))
												{
												?>
													<span><b><?php echo $aDates[$date]['from']?> — <?php echo $aDates[$date]['to']?></b></span>
												<?php
												}
											}
											?>
										</td>
										<td><?php echo $ordersCount?></td>
										<td><?php echo $orderItemsCount?></td>
										<td class="text-align-left"><?php echo number_format($amount, 2, '.', ' ')?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?></td>
									</tr>
									<?php

									$totalPeriodOrders += $ordersCount;
									$totalPeriodOrderItems += $orderItemsCount;
									$totalPeriodAmount += $amount;
								}
								?>
								<tr class="semi-bold">
									<td class="text-align-right">∑</td>
									<td><?php echo $totalPeriodOrders?></td>
									<td><?php echo $totalPeriodOrderItems?></td>
									<td class="text-align-left"><?php echo number_format($totalPeriodAmount, 2, '.', ' ')?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?></td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<?php
			self::$_debug && self::$_debugMessages[] = sprintf('_orders(), Table %.5f', Core::getmicrotime() - $fBeginTime);
		}
		?>
		<div class="row">
			<div class="display" id="break_page" style="page-break-before:always"></div>

			<div class="col-xs-12 margin-bottom-10">
				<div class="report-title"><?php echo Core::_('Report.parameters')?></div>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php
				Admin_Form_Entity::factory('Select')
					->id('order_parameter_y')
					->options(array(
						'region' => Core::_('Report.axis_region'),
						'city' => Core::_('Report.axis_city'),
						'delivery' => Core::_('Report.axis_delivery'),
						'paid' => Core::_('Report.axis_paid'),
						'order_status' => Core::_('Report.axis_order_status'),
						'seller' => Core::_('Report.axis_seller'),
						'utm_source' => Core::_('Report.axis_utm_source'),
						'utm_medium' => Core::_('Report.axis_utm_medium'),
						'utm_campaign' => Core::_('Report.axis_utm_campaign')
					))
					->value(self::$_order_parameter_y)
					->name('order_parameter_y')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {order_parameter_y: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php
				Admin_Form_Entity::factory('Select')
					->id('order_parameter_x')
					->options(array(
						'orders_count' => Core::_('Report.axis_orders_count'),
						'orders_amount' => Core::_('Report.axis_orders_amount'),
						'paid_amount' => Core::_('Report.axis_paid_amount'),
						'avg_amount' => Core::_('Report.axis_avg_amount')
					))
					->value(self::$_order_parameter_x)
					->name('order_parameter_x')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {order_parameter_x: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-6">
				<div class="segmentation pull-right">
					<div><?php echo Core::_('Report.segmentation')?></div>
					<div><?php
					Admin_Form_Entity::factory('Select')
						->id('order_segment')
						->options(array(
							'none' => Core::_('Report.axis_none'),
							'region' => Core::_('Report.axis_region'),
							'city' => Core::_('Report.axis_city'),
							'delivery' => Core::_('Report.axis_delivery'),
							'paid' => Core::_('Report.axis_paid'),
							'utm_source' => Core::_('Report.axis_utm_source'),
							'utm_medium' => Core::_('Report.axis_utm_medium'),
							'utm_campaign' => Core::_('Report.axis_utm_campaign')
						))
						->value(self::$_order_segment)
						->name('order_segment')
						->divAttr(array('class' => ''))
						->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {order_segment: $(this).val()}});')
						->execute();
					?></div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<?php
				$height = count(self::$_aOrderedByParams) ? count(self::$_aOrderedByParams) * 40 : 200;
				?>
				<div id="horizontal-chart<?php echo $functionName?>" class="chart chart-lg margin-top-20" style="height: <?php echo $height?>px;"></div>
			</div>
			<div class="col-xs-12">
				<div class="legend-container<?php echo $functionName?>"></div>
			</div>
		</div>
		<?php
		if (self::$_oDefault_Currency)
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();

			?><script>
				var dataArr = [], // Set up our data array, labels array
					tickLabelsX = [], // Setup labels for use on the Y-axis
					previousDataArr = [],
					totalOrdersArr = [],
					totalOrdersItemsArr = [];

				<?php
				$totalAmount = $previousTotalAmount = 0;

				$i = 0;
				foreach (self::$_aOrderedAmount as $date => $amount)
				{
					?>
					// Bar
					dataArr.push([<?php echo $i?>, <?php echo $amount?>]);
					tickLabelsX.push([<?php echo $i?>, '<?php echo $date?>']);
					<?php
					$totalAmount += $amount;
					$i++;
				}

				if ($compare_previous_period)
				{
					$i = 0;
					foreach (self::$_aOrderedPreviousAmount as $date => $amount)
					{
						?>
						// Bar
						previousDataArr.push([<?php echo $i?>, <?php echo $amount?>]);
						<?php
						$previousTotalAmount += $amount;
						$i++;
					}
				}

				$i = 0;
				foreach (self::$_total_order_items as $date => $count)
				{
					?>
					// Bar
					totalOrdersItemsArr.push([<?php echo $i?>, <?php echo $count?>]);
					<?php
					$i++;
				}

				$i = 0;
				foreach (self::$_total_orders as $date => $count)
				{
					?>
					// Bar
					totalOrdersArr.push([<?php echo $i?>, <?php echo $count?>]);
					<?php
					$i++;
				}

				$deltaPercent = '';
				if ($compare_previous_period && $totalAmount && $previousTotalAmount)
				{
					$percent = 100 - round($previousTotalAmount * 100 / $totalAmount);
					$deltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}
				?>

				var themeprimary = "#39c7ea",
					gridbordercolor = "#eee",
					data = [
					{ // amount
						color: themeprimary,
						data: dataArr,
						label: '<?php echo Core::_("Report.label_amount")?>',
						bars: {
							show: true,
							fillColor: { colors: [{ opacity: 0.8 }, { opacity: 1 }] },
							barWidth: 0.4,
							align: 'center',
							lineWidth: 1,
							fill: true,
							zero: true
						}
					},
					<?php
					if ($compare_previous_period)
					{
					?>
						{ // previous preiod amount
							color: "#fb7863",
							data: previousDataArr,
							label: '<?php echo Core::_("Report.label_previuos_amount")?>',
							lines: {
								show: true,
								fill: true,
								lineWidth: .1,
								fillColor: {
									colors: [{
										opacity: 0.2
									}, {
										opacity: 0.6
									}]
								}
							},
							points: {show: false},
							shadowSize: 0
						},
					<?php
					}
					?>
					{ // total order items
						color: "#a0d468",
						data: totalOrdersItemsArr,
						label: '<?php echo Core::_("Report.label_total_items")?>',
						lines: {
							show: true,
							fill: false,
							fillColor: {
								colors: [{
									opacity: 0.3
								}, {
									opacity: 0
								}]
							}
						},
						points: { show: true },
						yaxis: 2
					},
					{ // total orders
						color: "#ffcf54",
						data: totalOrdersArr,
						label: '<?php echo Core::_("Report.label_total_orders")?>',
						lines: {
							show: true,
							fill: false,
							fillColor: {
								colors: [{
									opacity: 0.3
								}, {
									opacity: 0
								}]
							}
						},
						points: { show: true },
						yaxis: 2
					}
				];

				var options = {
					yaxes: [
						{
							min: 0,
							tickDecimals: 0
						},
						{
							alignTicksWithAxis: 1,
							position: 'right',
							min: 0,
							tickDecimals: 0
						}
					],
					xaxis: {
						show: tickLabelsX.length < 30,
						ticks: tickLabelsX,
						tickDecimals: 0
					},
					selection: {
						mode: "x"
					},
					legend: {
						show: true,
						noColumns: 4,
						margin: -20
					},
					grid: {
						show: true,
						hoverable: true,
						clickable: false,
						tickColor: '#fff',
						borderWidth: 0,
						borderColor: '#fff',
						margin: 20
					}
				};

				// Horizontal chart
				var dataHorizontal = [],
					dataHorizontalTicksY = [];

				<?php
				if (self::$_order_segment == 'none')
				{
					$i = 0;
					foreach (self::$_aOrderedByParams as $yaxis => $xaxis)
					{
						$color = isset(self::$_aYAxisColors[$yaxis]) && !is_null(self::$_aYAxisColors[$yaxis])
							? self::$_aYAxisColors[$yaxis]
							: self::$_aColors[$i % count(self::$_aColors)];

						?>
						aData = [ [<?php echo $xaxis?>, <?php echo $i?>] ];
						dataHorizontal.push({label: '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($yaxis))?>', data: aData, color: '<?php echo $color?>'});
						dataHorizontalTicksY.push([<?php echo $i?>, '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($yaxis))?>']);
						<?php
						$i++;
					}
				}
				else
				{
					// Все варианты сегментов
					$aAllSegments = array();
					foreach (self::$_aOrderedByParamSegments as $yaxis => $aTmp)
					{
						foreach ($aTmp as $segmentName => $count)
						{
							if (!in_array($segmentName, $aAllSegments))
							{
								$aAllSegments[] = $segmentName;
							}
						}
					}

					$i = 0;
					foreach (self::$_aOrderedByParams as $yaxis => $xaxis)
					{
						?>
						dataHorizontalTicksY.push([<?php echo $i?>, '<?php echo $yaxis?>']);
						<?php
						$i++;
					}

					foreach ($aAllSegments as $key => $segmentName)
					{
						?>
						aData = [];
						<?php

						$i = 0;
						foreach (self::$_aOrderedByParams as $yaxis => $xaxis)
						{
							$maxValueByParams = array_sum(self::$_aOrderedByParamSegments[$yaxis]);

							$value = isset(self::$_aOrderedByParamSegments[$yaxis][$segmentName]) && $xaxis > 0
								? self::$_aOrderedByParamSegments[$yaxis][$segmentName] * ($xaxis / $maxValueByParams)
								: 0;
							?>
							aData.push([<?php echo $value?>, <?php echo $i?>]);
							<?php
							$i++;
						}
						?>
						dataHorizontal.push({
							label: '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($segmentName))?>',
							data: aData,
							color: '<?php echo self::$_aColors[$key % count(self::$_aColors)]?>'
						});
						<?php
					}
				}
				?>
				var optionsHorizontal = {
					series: {
						stack: true,
						bars: {
							show: true,
							order: 1,
							horizontal: true,
							fillColor: { colors: [{ opacity: 0.8 }, { opacity: 1 }] },
							barWidth: 0.8,
							align: 'center',
							lineWidth: 0,
							fill: true,
							zero: true
						},
					},
					grid: {
						show: true,
						hoverable: true,
						clickable: false,
						tickColor: '#fff',
						borderWidth: 0,
						borderColor: '#fff'
					},
					legend: {
						show: true,
						noColumns: dataHorizontal.length > 50 ? 4 : 2,
						container: dataHorizontal.length > 50 ? $('.legend-container<?php echo $functionName?>') : null
					},
					tooltip: true,
					tooltipOpts: {
						content: "<span>%s</span> : <span>%x</span>",
						defaultTheme: false
					},
					selection: { mode: "x" },
					xaxis: { min: 0, tickDecimals: 0 },
					yaxis: { ticks: dataHorizontalTicksY }
				};

				$(function() {
					var aScripts = [
						'jquery.flot.js',
						// 'jquery.flot.time.min.js',
						// 'jquery.flot.categories.min.js',
						'jquery.flot.tooltip.min.js',
						// 'jquery.flot.crosshair.min.js',
						// 'jquery.flot.selection.min.js',
						'jquery.flot.pie.min.js',
						// 'jquery.flot.resize.js',
						'jquery.flot.stack.min.js',
						'jquery.flot.axislabels.js'
						// 'jquery.flot.fillbetween.min.js',
						// 'jquery.flot.orderBars.js',
					];

					function showTooltip(x, y, contents, color) {
						$('<div id="flot-tooltip">' + contents + '</div>').css({
							position: 'absolute',
							display: 'none',
							top: y - 30,
							left: x + 30,
							border: '1px solid',
							padding: '5px',
							'background-color': '#FFF',
							opacity: 0.80,
							'border-color': color,
							'-moz-border-radius': '5px',
							'-webkit-border-radius': '5px',
							'-khtml-border-radius': '5px',
							'border-radius': '5px'
						}).appendTo("body").fadeIn(200);
					}

					$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
						if (data.length)
						{
							setTimeout(function() {
								if ($("#bar-chart<?php echo $functionName?>").width() > 0)
								{
									$.plot($("#bar-chart<?php echo $functionName?>"), data, options);

									var previousPoint = null,
										previousPointLabel = null;

									$("#bar-chart<?php echo $functionName?>").bind("plothover", function (event, pos, item) {
										if (item) {
											if ((previousPoint != item.dataIndex) || (previousLabel != item.series.label)) {
												previousPoint = item.dataIndex;
												previousLabel = item.series.label;

												$("#flot-tooltip").remove();

												var x = item.datapoint[0],
													y = item.datapoint[1],
													color = item.series.color,
													formattedY = y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$& ');

												showTooltip(item.pageX, item.pageY,
														"<b>" + item.series.label + "</b><br /> " + formattedY,
														color);
											}
										} else {
											$("#flot-tooltip").remove();
											previousPoint = null;
										}
									});
								}
							}, 10);
						}

						if (dataHorizontal.length)
						{
							setTimeout(function() {
								if ($("#horizontal-chart<?php echo $functionName?>").width() > 0)
								{
									plotHorizontal = $.plot($("#horizontal-chart<?php echo $functionName?>"), dataHorizontal, optionsHorizontal);

									var offset = [],
										leftBorder = [],
										amounts = [],
										plotData = plotHorizontal.getData();

									$.each(plotData, function(i, barObject){
										if (barObject.data.length > 1) {
											$.each(barObject.data, function (index, aData){
												if (typeof amounts[index] === 'undefined') {
													amounts[index] = 0;
												}
												amounts[index] += aData[0];
											});
										}
									});

									$.each(plotData, function(i, barObject){
										$.each(barObject.data, function (index, aData){
											var value = aData[0], segmentation = barObject.data.length > 1;

											if (typeof offset[index] === 'undefined') {
												offset[index] = 0;
												leftBorder[index] = plotHorizontal.getPlotOffset().left;
											}

											if (segmentation) {
												offset[index] += value;
											}
											else
											{
												offset[index] = value;
											}

											if (value)
											{
												var o = plotHorizontal.pointOffset({ x: offset[index], y: aData[1] });

												if (segmentation) {
													label = $.mathRound(value / amounts[index] * 100, 2) + '%';
												}
												else {
													label = value;
												}

												var textLength = String(label).length * 7.5;

												// Текст помещается на элемент бара
												if (o.left - leftBorder[index] > textLength)
												{
													$('<div class="data-point-label">' + label + '</div>').css({
														position: 'absolute',
														left: leftBorder[index] + (o.left - leftBorder[index] - textLength) / 2,
														top: o.top - 10,
														display: 'none',
														color: '#fff'
													}).appendTo(plotHorizontal.getPlaceholder()).slideToggle();
												}

												if (segmentation)
												{
													leftBorder[index] = o.left;
												}
											}
										});
									});
								}
							}, 10);
						}
					});

					var currentTab = $('.report-tabs .nav-tabs li.active'),
						currentContentId = currentTab.find('a').attr('href'),
						currentContent = $(currentContentId);

					currentContent.find('div.amount').text('<?php echo number_format($totalAmount, 0, '.', ' ')?> <?php echo htmlspecialchars(self::$_oDefault_Currency->name)?>');

					<?php
					if ($compare_previous_period && strlen($deltaPercent))
					{
						?>
						currentContent.find('div.amount').append('<?php echo $deltaPercent?>');
						<?php
					}
					?>
				});
			</script>
			<?php
			self::$_debug && self::$_debugMessages[] = sprintf('_orders(), Plots %.5f', Core::getmicrotime() - $fBeginTime);
		}

		if (self::$_debug)
		{
			?><div><h1>Debug:</h1><?php
			foreach (self::$_debugMessages as $debugMessage)
			{
				echo '<p>' . htmlspecialchars($debugMessage) . '</p>';
			}
			?></div><?php
		}
	}

	static protected function _preparePopularItems($functionName, $aOptions)
	{
		$isActive = Core_Session::isAcive();
		!$isActive && Core_Session::start();

		self::$_shop_id = isset($_SESSION['report']['shop_id'])
			? intval($_SESSION['report']['shop_id'])
			: 0;

		self::$_popular_limit = isset($_SESSION['report']['popular_limit'])
			? intval($_SESSION['report']['popular_limit'])
			: 50;

		self::$_group_modifications = isset($_SESSION['report']['group_modifications'])
			? intval($_SESSION['report']['group_modifications'])
			: 0;

		self::$_popular_parameter_y = isset($_SESSION['report']['popular_parameter_y'])
			? strval($_SESSION['report']['popular_parameter_y'])
			: 'group';

		self::$_popular_parameter_x = isset($_SESSION['report']['popular_parameter_x'])
			? strval($_SESSION['report']['popular_parameter_x'])
			: 'count_positions';

		self::$_popular_segment = isset($_SESSION['report']['popular_segment'])
			? strval($_SESSION['report']['popular_segment'])
			: 'none';

		self::$_oDefault_Currency = Core_Entity::factory('Shop_Currency')->getDefault();

		!$isActive && Core_Session::close();

		self::$_startDatetime = isset($aOptions['start_datetime'])
			? $aOptions['start_datetime']
			: date('Y-m-d', strtotime("-6 month"));

		self::$_endDatetime = isset($aOptions['end_datetime'])
			? $aOptions['end_datetime']
			: date('Y-m-d', time());
	}

	static protected function _popularItems($functionName, $aOptions)
	{
		$group_by = Core_Array::get($aOptions, 'group_by', 1);

		$checked = self::$_group_modifications
			? 'checked="checked"'
			: '';

		switch ($group_by)
		{
			case 0: // day
				$groupDate = 'd.m';
				$groupInc = '+1 day';
			break;
			case 1: // week
			default:
				$groupDate = 'W';
				$groupInc = '+1 week';
			break;
			case 2: // month
				$groupDate = 'm.Y';
				$groupInc = '+1 month';
			break;
		}

		$aPopularItems = self::_getPopularItems($functionName, self::$_startDatetime, self::$_endDatetime, $groupDate, $groupInc);

		$byParams = self::$_byParams;
		arsort($byParams);

		$byParams = array_slice($byParams, 0, self::$_popular_limit);

		$byParamSegments = self::$_byParamSegments;
		?>
		<div class="row">
			<div class="col-xs-12 col-sm-4">
				<?php
				$aShopOptions = array(0 => Core::_('Report.all_shops'));

				$aShops = Core_Entity::factory('Shop')->getAllBySite_id(CURRENT_SITE, FALSE);
				foreach ($aShops as $oShop)
				{
					$aShopOptions[$oShop->id] = $oShop->name;
				}

				Admin_Form_Entity::factory('Select')
					->id('shop_id')
					->options($aShopOptions)
					->value(self::$_shop_id)
					->name('shop_id')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {shop_id: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-4 margin-top-5">
				<div class="pull-left text margin-right-10"><?php echo Core::_('Report.group_modifications')?></div>
				<label>
					<input class="checkbox-slider toggle colored-success" name="group_modifications" onchange="$(this).val(+this.checked); sendRequest({tab: $('.report-tabs .nav-tabs li.active'), data: {group_modifications: $(this).val()}});" type="checkbox" value="<?php echo self::$_group_modifications?>" <?php echo $checked?>/>
					<span class="text"></span>
				</label>
			</div>
			<div class="col-xs-12 col-sm-4">
				<div class="segmentation pull-right">
					<div><?php echo Core::_('Report.popular_quantity')?></div>
					<div>
						<?php
						Admin_Form_Entity::factory('Select')
							->id('popular_limit')
							->options(array(
								10 => 10,
								20 => 20,
								30 => 30,
								40 => 40,
								50 => 50,
								100 => 100,
								500 => 500,
								1000 => 1000
							))
							->value(self::$_popular_limit)
							->name('popular_limit')
							->divAttr(array('class' => ''))
							->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {popular_limit: $(this).val()}});')
							->execute();
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 margin-top-20">
				<div class="tabbable report-popular-tab">
					<ul class="nav nav-tabs tabs-flat nav-justified" id="reportPopularTab">
						<?php
						$totalItemsAmount = $totalItemsModificationsCount = 0;
						$iCount = count($aPopularItems);
						foreach ($aPopularItems as $shop_item_id => $aTmp)
						{
							$oShop_Item = Core_Entity::factory('Shop_Item', $shop_item_id);

							$totalItemsAmount += $aTmp['quantityAmount'];

							$oShop_Item->modification_id && $totalItemsModificationsCount += 1;
						}
						?>
						<li class="active">
							<a data-toggle="tab" href="#all_items" aria-expanded="true">
								<?php echo Core::_('Report.popular_total_items')?>
								<div class="tab-data"><?php echo $iCount?></div>
								<div class="tab-additional-info">
									<?php
									if (!self::$_group_modifications)
									{
										?>
										<?php echo Core::_('Report.popular_total_modifications', number_format($totalItemsModificationsCount, 0, '.', ' '))?>
										<?php
									}
									else
									{
										?>
										—
										<?php
									}
									?>
								</div>
							</a>
						</li>
						<?php
						// High & Low importance
						$highImportanceCount = $totalItemsAmount * 0.25;
						$mediumImportanceCount = $totalItemsAmount * 0.75;

						$aHighPopularItems = $aMediumPopularItems = $aLowPopularItems = array();
						$totalHighItemsCount = $iCountHighImportanceItems = $iCountMediumImportanceItems = $iCountLowImportanceItems = 0;
						foreach ($aPopularItems as $shop_item_id => $aTmp)
						{
							if ($totalHighItemsCount <= $highImportanceCount)
							{
								$iCountHighImportanceItems++;
								$aHighPopularItems[$shop_item_id] = $aTmp;
							}
							elseif ($totalHighItemsCount <= $mediumImportanceCount)
							{
								$iCountMediumImportanceItems++;
								$aMediumPopularItems[$shop_item_id] = $aTmp;
							}
							else
							{
								$iCountLowImportanceItems++;
								$aLowPopularItems[$shop_item_id] = $aTmp;
							}

							$totalHighItemsCount += $aTmp['quantityAmount'];
						}

						$highImportancePercent = $mediumImportancePercent = $lowImportancePercent = 0;

						if ($iCount)
						{
							$highImportancePercent = round($iCountHighImportanceItems * 100 / $iCount, 2);
							$mediumImportancePercent = round($iCountMediumImportanceItems * 100 / $iCount, 2);
							$lowImportancePercent = round($iCountLowImportanceItems * 100 / $iCount, 2);
						}
						?>
						<li class="tab-darkorange">
							<a data-toggle="tab" href="#high_importance" aria-expanded="false">
								<?php echo Core::_('Report.popular_high_importance')?>
								<div class="tab-data"><?php echo $highImportancePercent?>%</div>
								<div class="tab-additional-info"><?php echo Core::_('Report.popular_count_items', $iCountHighImportanceItems)?></div>
							</a>
						</li>
						<li class="tab-yellow">
							<a data-toggle="tab" href="#medium_importance" aria-expanded="false">
								<?php echo Core::_('Report.popular_medium_importance')?>
								<div class="tab-data"><?php echo $mediumImportancePercent?>%</div>
								<div class="tab-additional-info"><?php echo Core::_('Report.popular_count_items', $iCountMediumImportanceItems)?></div>
							</a>
						</li>
						<li class="tab-palegreen">
							<a data-toggle="tab" href="#low_importance" aria-expanded="false">
								<?php echo Core::_('Report.popular_low_importance')?>
								<div class="tab-data"><?php echo $lowImportancePercent?>%</div>
								<div class="tab-additional-info"><?php echo Core::_('Report.popular_count_items', $iCountLowImportanceItems)?></div>
							</a>
						</li>
					</ul>
					<div class="tab-content tabs-flat">
						<div id="all_items" class="tab-pane active">
							<?php self::_getPriorityContent(array_slice($aPopularItems, 0, self::$_popular_limit))?>
						</div>
						<div id="low_importance" class="tab-pane">
							<?php self::_getPriorityContent(array_slice($aLowPopularItems, 0, self::$_popular_limit))?>
						</div>
						<div id="medium_importance" class="tab-pane">
							<?php self::_getPriorityContent(array_slice($aMediumPopularItems, 0, self::$_popular_limit))?>
						</div>
						<div id="high_importance" class="tab-pane">
							<?php self::_getPriorityContent(array_slice($aHighPopularItems, 0, self::$_popular_limit))?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<hr/>
		<div class="row">
			<div class="display" id="break_page" style="page-break-before:always"></div>

			<div class="col-xs-12 margin-bottom-10">
				<div class="report-title"><?php echo Core::_('Report.parameters')?></div>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php
				Admin_Form_Entity::factory('Select')
					->id('popular_parameter_y')
					->options(array(
						'group' => Core::_('Report.axis_group'),
						'producer' => Core::_('Report.axis_producer')
					))
					->value(self::$_popular_parameter_y)
					->name('popular_parameter_y')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {popular_parameter_y: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php
				Admin_Form_Entity::factory('Select')
					->id('popular_parameter_x')
					->options(array(
						'count_positions' => Core::_('Report.axis_count_positions'),
						'count_paid' => Core::_('Report.axis_count_paid'),
						'total_amount' => Core::_('Report.axis_total_amount')
					))
					->value(self::$_popular_parameter_x)
					->name('popular_parameter_x')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {popular_parameter_x: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-6">
				<div class="segmentation pull-right">
					<div><?php echo Core::_('Report.segmentation')?></div>
					<div><?php
					Admin_Form_Entity::factory('Select')
						->id('popular_segment')
						->options(array(
							'none' => Core::_('Report.axis_none'),
							'group' => Core::_('Report.axis_group'),
							'producer' => Core::_('Report.axis_producer')
						))
						->value(self::$_popular_segment)
						->name('popular_segment')
						->divAttr(array('class' => ''))
						->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {popular_segment: $(this).val()}});')
						->execute();
					?></div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<?php
				$height = count($aPopularItems) ? count($aPopularItems) * 40 : 200;
				?>
				<div id="horizontal-chart<?php echo $functionName?>" class="chart chart-lg margin-top-20" style="height: <?php echo $height?>"></div>
			</div>
		</div>

		<script>
		var dataHorizontal = [],
			dataHorizontalTicksY = [];

		<?php
		if (self::$_popular_segment == 'none')
		{
			$i = 0;
			foreach ($byParams as $yaxis => $xaxis)
			{
				?>
				aData = [ [<?php echo $xaxis?>, <?php echo $i?>] ];
				dataHorizontal.push({label: '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($yaxis))?>', data: aData, color: '<?php echo self::$_aColors[$i % count(self::$_aColors)]?>'});
				dataHorizontalTicksY.push([<?php echo $i?>, '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($yaxis))?>']);
				<?php
				$i++;
			}
		}
		else
		{
			// Все варианты сегментов
			$aAllSegments = array();
			$i = 0;
			foreach ($byParamSegments as $yaxis => $aTmp)
			{
				foreach ($aTmp as $segmentName => $count)
				{
					if (!in_array($segmentName, $aAllSegments))
					{
						$aAllSegments[] = $segmentName;
					}
				}
				?>
				dataHorizontalTicksY.push([<?php echo $i?>, '<?php echo $yaxis?>']);
				<?php
				$i++;
			}

			foreach ($aAllSegments as $key => $segmentName)
			{
				?>
				aData = [];
				<?php

				$i = 0;
				foreach ($byParams as $yaxis => $xaxis)
				{
					$maxValueByParams = array_sum($byParamSegments[$yaxis]);

					$value = isset($byParamSegments[$yaxis][$segmentName]) && $xaxis > 0
						? $byParamSegments[$yaxis][$segmentName] * ($xaxis / $maxValueByParams)
						: 0;
					?>
					aData.push([<?php echo $value?>, <?php echo $i?>]);
					<?php
					$i++;
				}
				?>
				dataHorizontal.push({
					label: '<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($segmentName))?>',
					data: aData,
					color: '<?php echo self::$_aColors[$key % count(self::$_aColors)]?>'
				});
				<?php
			}
		}
		?>

		var optionsHorizontal = {
			series: {
				stack: true,
				bars: {
					show: true,
					order: 1,
					horizontal: true,
					fillColor: { colors: [{ opacity: 0.8 }, { opacity: 1 }] },
					barWidth: 0.8,
					align: 'center',
					lineWidth: 0,
					fill: true,
					zero: true
				},
			},
			grid: {
				show: true,
				hoverable: true,
				clickable: false,
				tickColor: '#fff',
				borderWidth: 0,
				borderColor: '#fff'
			},
			legend: { show: true, noColumns: 2 },
			tooltip: true,
			tooltipOpts: {
				content: "<span>%s</span> : <span>%x</span>",
				defaultTheme: false
			},
			selection: { mode: "x" },
			xaxis: { min: 0, tickDecimals: 0 },
			yaxis: { ticks: dataHorizontalTicksY }
		};

		$(function() {
			var aScripts = [
				'jquery.flot.js',
				// 'jquery.flot.time.min.js',
				// 'jquery.flot.categories.min.js',
				'jquery.flot.tooltip.min.js',
				// 'jquery.flot.crosshair.min.js',
				// 'jquery.flot.selection.min.js',
				'jquery.flot.pie.min.js',
				// 'jquery.flot.resize.js',
				'jquery.flot.stack.min.js',
				'jquery.flot.axislabels.js'
				// 'jquery.flot.fillbetween.min.js',
				// 'jquery.flot.orderBars.js',
			];

			$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
				if (dataHorizontal.length)
				{
					setTimeout(function() {
						if ($("#horizontal-chart<?php echo $functionName?>").width() > 0)
						{
							plotHorizontal = $.plot($("#horizontal-chart<?php echo $functionName?>"), dataHorizontal, optionsHorizontal);

							var offset = [],
								leftBorder = [],
								amounts = [],
								plotData = plotHorizontal.getData();

							$.each(plotData, function(i, barObject){
								if (barObject.data.length > 1) {
									$.each(barObject.data, function (index, aData){
										if (typeof amounts[index] === 'undefined') {
											amounts[index] = 0;
										}
										amounts[index] += aData[0];
									});
								}
							});

							$.each(plotData, function(i, barObject){
								$.each(barObject.data, function (index, aData){
									var value = aData[0], segmentation = barObject.data.length > 1;

									if (typeof offset[index] === 'undefined') {
										offset[index] = 0;
										leftBorder[index] = plotHorizontal.getPlotOffset().left;
									}

									if (segmentation) {
										offset[index] += value;
									}
									else
									{
										offset[index] = value;
									}

									if (value)
									{
										var o = plotHorizontal.pointOffset({ x: offset[index], y: aData[1] });

										if (segmentation) {
											label = $.mathRound(value / amounts[index] * 100, 2) + '%';
										}
										else {
											label = value;
										}

										var textLength = String(label).length * 7.5;

										// Текст помещается на элемент бара
										if (o.left - leftBorder[index] > textLength)
										{
											$('<div class="data-point-label">' + label + '</div>').css({
												position: 'absolute',
												left: leftBorder[index] + (o.left - leftBorder[index] - textLength) / 2,
												top: o.top - 10,
												display: 'none',
												color: '#fff'
											}).appendTo(plotHorizontal.getPlaceholder()).slideToggle();
										}

										if (segmentation)
										{
											leftBorder[index] = o.left;
										}
									}
								});
							});
						}
					}, 10);
				}
			});
		});
		</script>
		<?php
	}

	static protected function _getPopularProducers($functionName, $startDatetime, $endDatetime, $groupDate, $groupInc)
	{
		$aPopularProducers = array();

		$limit = 1000;
		$offset = 0;

		self::$_byParams = self::$_byParamSegments = array();

		do {
			$oShop_Order_Items = self::$functionName($startDatetime, $endDatetime);
			$oShop_Order_Items
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			self::$_shop_id && $oShop_Order_Items
				->queryBuilder()
				->where('shops.id', '=', self::$_shop_id);

			$aShop_Order_Items = $oShop_Order_Items->findAll(FALSE);

			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				if ($oShop_Order_Item->type != 1)
				{
					$oShop_Item = $oShop_Order_Item->Shop_Item->modification_id
						? $oShop_Order_Item->Shop_Item->Modification
						: $oShop_Order_Item->Shop_Item;

					$iId = self::$_group_modifications ? $oShop_Order_Item->dataId : $oShop_Item->shop_producer_id;

					$aPopularProducers[$iId] = array(
						'name' => $oShop_Item->shop_producer_id ? Core_Entity::factory('Shop_Producer', $iId)->name : '—',
						'quantityAmount' => $oShop_Order_Item->dataQuantityAmount
					);
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Order_Items));

		return $aPopularProducers;
	}

	static protected function _preparePopularProducers($functionName, $aOptions)
	{
		$isActive = Core_Session::isAcive();
		!$isActive && Core_Session::start();

		self::$_shop_id = isset($_SESSION['report']['shop_id'])
			? intval($_SESSION['report']['shop_id'])
			: 0;

		self::$_popular_producers_limit = isset($_SESSION['report']['popular_producers_limit'])
			? intval($_SESSION['report']['popular_producers_limit'])
			: 10;

		self::$_group_modifications = isset($_SESSION['report']['group_modifications'])
			? intval($_SESSION['report']['group_modifications'])
			: 0;

		self::$_oDefault_Currency = Core_Entity::factory('Shop_Currency')->getDefault();

		!$isActive && Core_Session::close();

		self::$_startDatetime = isset($aOptions['start_datetime'])
			? $aOptions['start_datetime']
			: date('Y-m-d', strtotime("-6 month"));

		self::$_endDatetime = isset($aOptions['end_datetime'])
			? $aOptions['end_datetime']
			: date('Y-m-d', time());
	}

	static protected function _popularProducers($functionName, $aOptions)
	{
		$group_by = Core_Array::get($aOptions, 'group_by', 1);

		$checked = self::$_group_modifications
			? 'checked="checked"'
			: '';

		switch ($group_by)
		{
			case 0: // day
				$groupDate = 'd.m';
				$groupInc = '+1 day';
			break;
			case 1: // week
			default:
				$groupDate = 'W';
				$groupInc = '+1 week';
			break;
			case 2: // month
				$groupDate = 'm.Y';
				$groupInc = '+1 month';
			break;
		}

		$aPopularProducers = self::_getPopularProducers($functionName, self::$_startDatetime, self::$_endDatetime, $groupDate, $groupInc);
		?>
		<div class="row">
			<div class="col-xs-12 col-sm-4">
				<?php
				$aShopOptions = array(0 => Core::_('Report.all_shops'));

				$aShops = Core_Entity::factory('Shop')->getAllBySite_id(CURRENT_SITE, FALSE);
				foreach ($aShops as $oShop)
				{
					$aShopOptions[$oShop->id] = $oShop->name;
				}

				Admin_Form_Entity::factory('Select')
					->id('shop_id')
					->options($aShopOptions)
					->value(self::$_shop_id)
					->name('shop_id')
					->divAttr(array('class' => ''))
					->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {shop_id: $(this).val()}});')
					->execute();
				?>
			</div>
			<div class="col-xs-12 col-sm-4 margin-top-5">
				<div class="pull-left text margin-right-10"><?php echo Core::_('Report.group_modifications')?></div>
				<label>
					<input class="checkbox-slider toggle colored-success" name="group_modifications" onchange="$(this).val(+this.checked); sendRequest({tab: $('.report-tabs .nav-tabs li.active'), data: {group_modifications: $(this).val()}});" type="checkbox" value="<?php echo self::$_group_modifications?>" <?php echo $checked?>/>
					<span class="text"></span>
				</label>
			</div>
			<div class="col-xs-12 col-sm-4">
				<div class="segmentation pull-right">
					<div><?php echo Core::_('Report.popular_quantity')?></div>
					<div>
						<?php
						Admin_Form_Entity::factory('Select')
							->id('popular_producers_limit')
							->options(array(
								10 => 10,
								20 => 20,
								30 => 30,
								40 => 40,
								50 => 50,
								100 => 100,
								500 => 500,
								1000 => 1000
							))
							->value(self::$_popular_producers_limit)
							->name('popular_producers_limit')
							->divAttr(array('class' => ''))
							->onchange('sendRequest({tab: $(\'.report-tabs .nav-tabs li.active\'), data: {popular_producers_limit: $(this).val()}});')
							->execute();
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div id="pie-chart<?php echo $functionName?>" class="chart chart-lg margin-top-20" style="height: 400px;"></div>
			</div>
		</div>

		<script>
		var dataPie = [];

		<?php
		$aConfig = Core::$config->get('shop_order_config', array()) + array(
			'cutNames' => 20
		);

		$iCountColors = count(self::$_aColors);

		$i = 0;
		foreach ($aPopularProducers as $shop_producer_id => $aTmp)
		{
			?>
			dataPie.push({
				label:'<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars(Core_Str::cut($aTmp['name'], $aConfig['cutNames'])))?>',
				data: <?php echo $aTmp['quantityAmount']?>,
				color: '<?php echo $iCountColors
					? self::$_aColors[$i % $iCountColors]
					: '#E75B8D'?>'
			});
			<?php
			$i++;
		}
		?>

		$(function() {
			var aScripts = [
				'jquery.flot.js',
				// 'jquery.flot.time.min.js',
				// 'jquery.flot.categories.min.js',
				// 'jquery.flot.tooltip.min.js',
				// 'jquery.flot.crosshair.min.js',
				'jquery.flot.resize.js',
				// 'jquery.flot.selection.min.js',
				'jquery.flot.pie.min.js'
			];

			$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
				if (dataPie.length)
				{
					// all scripts loaded
					setTimeout(function() {
						var placeholderBrandsDiagram = $("#pie-chart<?php echo $functionName?>");

						if (placeholderBrandsDiagram.width() > 0)
						{
							$.plot(placeholderBrandsDiagram, dataPie, {
								series: {
									pie: {
										show: true,
										radius: 1,
										innerRadius: 0.5,
										label: {
											show: true,
											radius: 0,
											// formatter: function(label, series) {
												// return "<div style='font-size:8pt;'>" + label + "</div>";
											// }
										}
									}
								},

								legend: {
									labelFormatter: function (label, series) {
										return $.escapeHtml(label + ", " + series.data[0][1]);
									}
								}
								,
								grid: {
									hoverable: true,
								}
							});

							placeholderBrandsDiagram.bind("plothover", function (event, pos, obj) {
								if (!obj) {
									return;
								}

								$("#pie-chart<?php echo $functionName?> span[id ^= 'pieLabel']").hide();
								$("#pie-chart<?php echo $functionName?> span[id = 'pieLabel" + obj.seriesIndex + "']").show();
							});

							placeholderBrandsDiagram.resize(function(){$("#pie-chart<?php echo $functionName?> span[id ^= 'pieLabel']").hide();});

							$("#pie-chart<?php echo $functionName?> span[id ^= 'pieLabel']").hide();
						}
					}, 10);
				}
			});
		});
		</script>
		<?php
	}

	static public function ordersCost($aFields, $aOptions)
	{
		$aReturn = array();

		$functionName = '_selectOrders';

		if (in_array('caption', $aFields))
		{
			$aReturn['caption'] = Core::_('Report.order_costs');
		}

		if (in_array('captionHTML', $aFields) || in_array('content', $aFields))
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();
			self::_prepareOrders($functionName, $aOptions);
			self::$_debug && self::$_debugMessages[] = sprintf('ordersCost(captionHTML), _prepareOrders %.5f', Core::getmicrotime() - $fBeginTime);
		}

		if (in_array('captionHTML', $aFields))
		{
			$totalAmount = 0;
			foreach (self::$_aOrderedAmount as $date => $amount)
			{
				$totalAmount += $amount;
			}

			$deltaPercent = '';

			if (Core_Array::get($aOptions, 'compare_previous_period', 0))
			{
				$previousTotalAmount = 0;
				foreach (self::$_aOrderedPreviousAmount as $date => $amount)
				{
					$previousTotalAmount += $amount;
				}

				if ($totalAmount && $previousTotalAmount)
				{
					$percent = 100 - round($previousTotalAmount * 100 / $totalAmount);
					$deltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}
			}

			$aReturn['captionHTML'] = '<div class="tab-description">' . number_format($totalAmount, 0, '.', ' ') . ' ' . htmlspecialchars(self::$_oDefault_Currency->name) . $deltaPercent . '</div>';
		}

		if (in_array('content', $aFields))
		{
			ob_start();
			self::_orders($functionName, $aOptions);
			$aReturn['content'] = ob_get_clean();
		}

		$aReturn['color'] = 'primary';

		return $aReturn;
	}

	static public function ordersPaid($aFields, $aOptions)
	{
		$aReturn = array();

		$functionName = '_selectPaidOrders';

		if (in_array('caption', $aFields))
		{
			$aReturn['caption'] = Core::_('Report.order_paid');
		}

		if (in_array('captionHTML', $aFields) || in_array('content', $aFields))
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();
			self::_prepareOrders($functionName, $aOptions);
			self::$_debug && self::$_debugMessages[] = sprintf('ordersPaid(), _prepareOrders %.5f', Core::getmicrotime() - $fBeginTime);
		}

		if (in_array('captionHTML', $aFields))
		{
			$totalAmount = 0;
			foreach (self::$_aOrderedAmount as $date => $amount)
			{
				$totalAmount += $amount;
			}

			$deltaPercent = '';

			if (Core_Array::get($aOptions, 'compare_previous_period', 0))
			{
				$previousTotalAmount = 0;
				foreach (self::$_aOrderedPreviousAmount as $date => $amount)
				{
					$previousTotalAmount += $amount;
				}

				if ($totalAmount && $previousTotalAmount)
				{
					$percent = 100 - round($previousTotalAmount * 100 / $totalAmount);
					$deltaPercent = '<span class="' . ($percent > 0 ? 'palegreen' : 'darkorange') . '">' . ($percent > 0 ? '+' : '') . $percent . '%</span>';
				}
			}

			$aReturn['captionHTML'] = '<div class="tab-description">' . number_format($totalAmount, 0, '.', ' ') . ' ' . htmlspecialchars(self::$_oDefault_Currency->name) . $deltaPercent . '</div>';
		}

		if (in_array('content', $aFields))
		{
			ob_start();
			self::_orders($functionName, $aOptions);
			$aReturn['content'] = ob_get_clean();
		}

		$aReturn['color'] = 'info';

		return $aReturn;
	}

	static public function popularItems($aFields, $aOptions)
	{
		$aReturn = array();

		$functionName = '_selectPopularItems';

		if (in_array('caption', $aFields))
		{
			$aReturn['caption'] = Core::_('Report.popular_items');
		}

		if (in_array('captionHTML', $aFields) || in_array('content', $aFields))
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();
			self::_preparePopularItems($functionName, $aOptions);
			self::$_debug && self::$_debugMessages[] = sprintf('popularItems(), _preparePopularItems %.5f', Core::getmicrotime() - $fBeginTime);
		}

		if (in_array('captionHTML', $aFields))
		{
			$aReturn['captionHTML'] = '<div class="tab-description">ТОП-' . self::$_popular_limit . '</div>';
		}

		if (in_array('content', $aFields))
		{
			ob_start();
			self::_popularItems($functionName, $aOptions);
			$aReturn['content'] = ob_get_clean();
		}

		$aReturn['color'] = 'success';

		$aReturn['group'] = $aReturn['comparePrevious'] = $aReturn['previousTimeInterval'] = 0;

		return $aReturn;
	}

	static public function popularProducers($aFields, $aOptions)
	{
		$aReturn = array();

		$functionName = '_selectPopularProducers';

		if (in_array('caption', $aFields))
		{
			$aReturn['caption'] = Core::_('Report.popular_producers');
		}

		if (in_array('captionHTML', $aFields) || in_array('content', $aFields))
		{
			self::$_debug && $fBeginTime = Core::getmicrotime();
			self::_preparePopularProducers($functionName, $aOptions);
			self::$_debug && self::$_debugMessages[] = sprintf('popularProducers(), _preparePopularProducers %.5f', Core::getmicrotime() - $fBeginTime);
		}

		if (in_array('captionHTML', $aFields))
		{
			$aReturn['captionHTML'] = '<div class="tab-description">ТОП-' . self::$_popular_producers_limit . '</div>';
		}

		if (in_array('content', $aFields))
		{
			ob_start();
			self::_popularProducers($functionName, $aOptions);
			$aReturn['content'] = ob_get_clean();
		}

		$aReturn['color'] = 'danger';

		$aReturn['group'] = $aReturn['comparePrevious'] = $aReturn['previousTimeInterval'] = 0;

		return $aReturn;
	}
}