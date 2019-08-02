<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Выбор способа доставки.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Delivery_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_country_id',
		'shop_country_location_id',
		'shop_country_location_city_id',
		'shop_country_location_city_area_id',
		'totalWeight',
		'totalAmount',
		'couponText',
		'postcode',
		'volume',
		'paymentSystems'
	);

	/**
	 * Shop_Deliveries object
	 * @var Shop_Delivery_Model
	 */
	protected $_Shop_Deliveries = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		if (Core::moduleIsActive('siteuser'))
		{
			// Если есть модуль пользователей сайта, $siteuser_id равен 0 или ID авторизованного
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($oSiteuser)
			{
				$this->addEntity($oSiteuser->clearEntities());
			}
		}

		$this->paymentSystems = FALSE;

		$this->_setShopDeliveries();
		
		if (Core_Session::hasSessionId())
		{
			Core_Session::start();
			if (isset($_SESSION['hostcmsOrder']['coupon_text']))
			 {
				 Shop_Item_Controller::coupon($_SESSION['hostcmsOrder']['coupon_text']);
			 }
		}
	}

	/**
	 * Prepare Shop_Deliveries for showing
	 * @return self
	 */
	protected function _setShopDeliveries()
	{
		$oShop = $this->getEntity();

		$this->_Shop_Deliveries = $oShop->Shop_Deliveries;

		$this->_Shop_Deliveries
			->queryBuilder()
			->where('shop_deliveries.active', '=', 1);

		return $this;
	}

	/**
	 * Get Shop_Deliveries
	 * @return Shop_Delivery_Model
	 */
	public function shopDeliveries()
	{
		return $this->_Shop_Deliveries;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Delivery_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_weight')
				->value($this->totalWeight)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_amount')
				->value($this->totalAmount)
		);

		Core_Session::start();

		// Выбираем все типы доставки для данного магазина
		$aShop_Deliveries = $this->_Shop_Deliveries->findAll();

		foreach ($aShop_Deliveries as $oShop_Delivery)
		{
			$aShop_Delivery_Conditions = $this->getShopDeliveryConditions($oShop_Delivery);

			if ($oShop_Delivery->type == 1)
			{
				foreach ($aShop_Delivery_Conditions as $key => $object)
				{
					$_SESSION['hostcmsOrder']['deliveries'][$object->id] = array(
						'shop_delivery_id' => $oShop_Delivery->id,
						'price' => $object->price,
						'rate' => isset($object->rate) ? intval($object->rate) : 0,
						'name' => $object->description
					);

					$oShop_Delivery_Condition = Core::factory('Core_Xml_Entity')
						->name('shop_delivery_condition')
						->addAttribute('id', $object->id . '#')
						->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('shop_delivery_id')
								->value($object->shop_delivery_id)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('shop_currency_id')
								->value($object->shop_currency_id)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('price')
								->value($object->price)
						)->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('description')
								->value($object->description)
						);

					// Replace $oShop_Delivery_Condition
					$aShop_Delivery_Conditions[$key] = $oShop_Delivery_Condition;
				}
			}

			if (count($aShop_Delivery_Conditions))
			{
				foreach ($aShop_Delivery_Conditions as $oShop_Delivery_Condition)
				{
					if (!is_null($oShop_Delivery_Condition))
					{
						$oShop_Delivery_Clone = clone $oShop_Delivery;

						$this->paymentSystems && $oShop_Delivery_Clone->showXmlShopPaymentSystems($this->paymentSystems);

						$this->addEntity(
							$oShop_Delivery_Clone
								->id($oShop_Delivery->id)
								->clearEntities()
								->addEntity($oShop_Delivery_Condition)
						);
					}
				}

				$aShop_Delivery_Conditions = array();
			}
		}

		return parent::show();
	}

	public function getShopDeliveryConditions(Shop_Delivery_Model $oShop_Delivery)
	{
		$aShop_Delivery_Conditions = array();

		if ($oShop_Delivery->type == 0)
		{
			$oShop_Delivery_Condition_Controller = new Shop_Delivery_Condition_Controller();
			$oShop_Delivery_Condition_Controller
				->shop_country_id($this->shop_country_id)
				->shop_country_location_id($this->shop_country_location_id)
				->shop_country_location_city_id($this->shop_country_location_city_id)
				->shop_country_location_city_area_id($this->shop_country_location_city_area_id)
				->totalWeight($this->totalWeight)
				->totalAmount($this->totalAmount);

			$oShop_Delivery_Condition = $oShop_Delivery_Condition_Controller->getShopDeliveryCondition($oShop_Delivery);

			// Условие доставки, подходящее под ограничения
			!is_null($oShop_Delivery_Condition)
				&& $aShop_Delivery_Conditions[] = $oShop_Delivery_Condition;
		}
		else
		{
			try
			{
				$aPrice = Shop_Delivery_Handler::factory($oShop_Delivery)
					->country($this->shop_country_id)
					->location($this->shop_country_location_id)
					->city($this->shop_country_location_city_id)
					->weight($this->totalWeight)
					->amount($this->totalAmount)
					->postcode($this->postcode)
					->volume($this->volume)
					->execute();

				if (!is_null($aPrice))
				{
					!is_array($aPrice) && $aPrice = array($aPrice);

					foreach ($aPrice as $key => $oShop_Delivery_Condition)
					{
						if (!is_object($oShop_Delivery_Condition))
						{
							$tmp = $oShop_Delivery_Condition;
							$oShop_Delivery_Condition = new StdClass();
							$oShop_Delivery_Condition->price = $tmp;
							$oShop_Delivery_Condition->rate = 0;
							$oShop_Delivery_Condition->description = NULL;
						}

						$oShop_Delivery_Condition->id = $oShop_Delivery->id . '-' . $key;
						$oShop_Delivery_Condition->shop_delivery_id = $oShop_Delivery->id;
						$oShop_Delivery_Condition->shop_currency_id = $oShop_Delivery->Shop->shop_currency_id;

						$aShop_Delivery_Conditions[] = $oShop_Delivery_Condition;
					}
				}
			}
			catch (Exception $e)
			{
				// Show error message just for backend users
				Core_Auth::logged()
					&& Core_Message::show($e->getMessage(), 'error');

				$aShop_Delivery_Conditions = array();
			}
		}

		return $aShop_Delivery_Conditions;
	}

	/**
	 * Calculate total amount and weight
	 * @return self
	 */
	public function setUp()
	{
		$oShop = $this->getEntity();

		$Shop_Cart_Controller = Shop_Cart_Controller::instance();

		$quantityPurchaseDiscount = $amountPurchaseDiscount = $amount = $quantity = $weight = $this->volume = 0;

		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$aDiscountPrices = array();

		$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);
		foreach ($aShop_Cart as $oShop_Cart)
		{
			$oShop_Item = $oShop_Cart->Shop_Item;
			if ($oShop_Item->id)
			{
				if ($oShop_Cart->postpone == 0)
				{
					// Prices
					$oShop_Item_Controller = new Shop_Item_Controller();
					if (Core::moduleIsActive('siteuser'))
					{
						$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
						$oSiteuser && $oShop_Item_Controller->siteuser($oSiteuser);
					}

					$oShop_Item_Controller->count($oShop_Cart->quantity);

					$aPrices = $oShop_Item_Controller->getPrices($oShop_Cart->Shop_Item);

					$amount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
					for ($i = 0; $i < $oShop_Cart->quantity; $i++)
					{
						$aDiscountPrices[] = $aPrices['price_discount'];
					}

					// Сумма для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					$quantity += $oShop_Cart->quantity;

					// Количество для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $quantityPurchaseDiscount += $oShop_Cart->quantity;

					$weight += $oShop_Cart->Shop_Item->weight * $oShop_Cart->quantity;

					// Расчет единицы измерения ведется в милиметрах
					$this->volume += Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->length, $oShop->size_measure, 0) * Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->width, $oShop->size_measure, 0) * Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->height, $oShop->size_measure, 0);
				}
			}
		}

		// Скидки от суммы заказа
		$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
		$oShop_Purchase_Discount_Controller
			->amount($amountPurchaseDiscount)
			->quantity($quantityPurchaseDiscount)
			->couponText($this->couponText)
			->prices($aDiscountPrices);

		$totalDiscount = 0;
		$aShop_Purchase_Discounts = $oShop_Purchase_Discount_Controller->getDiscounts();
		foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
		{
			$totalDiscount += $oShop_Purchase_Discount->getDiscountAmount();
		}

		$this->totalWeight = $weight;
		$this->totalAmount = $amount - $totalDiscount;

		return $this;
	}

}