<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Заказ в 1 шаг.
 *
 * Доступные методы:
 *
 * - couponText() купон
 * - itemsProperties(TRUE) выводить значения дополнительных свойств товаров, по умолчанию FALSE
 * - itemsPropertiesList(TRUE) выводить список дополнительных свойств товаров, по умолчанию TRUE
 * - taxes(TRUE|FALSE) выводить список налогов, по умолчанию FALSE
 * - shop_item_id() идентификатор заказываемого товара
 * - quantity() количество заказываемого товара, по умолчанию 1
 * - countries(TRUE|FALSE) выводить в XML данные о странах
 * - orderProperties(TRUE|FALSE|array()) выводить список дополнительных свойств заказа, по умолчанию TRUE
 * - paymentSystems(TRUE|FALSE) выводить в XML данные о платежных системах
 *
 * <code>
 * $Shop_Cart_Controller_Onestep = new Shop_Cart_Controller_Onestep(
 * 		Core_Entity::factory('Shop', 1)
 * 	);
 *
 * 	$Shop_Cart_Controller_Onestep
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('МагазинКупитьВОдинШаг')
 * 		)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Cart_Controller_Onestep extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'couponText',
		'itemsProperties',
		'itemsPropertiesList',
		'taxes',
		'shop_item_id',
		'quantity',
		'countries',
		'orderProperties',
		'paymentSystems',
	);

	/**
	 * List of properties for item
	 * @var array
	 */
	protected $_aItem_Properties = array();

	/**
	 * List of property directories for item
	 * @var array
	 */
	protected $_aItem_Property_Dirs = array();

	/**
	 * Current Siteuser
	 * @var Siteuser_Model|NULL
	 */
	protected $_oSiteuser = NULL;

	/**
	 * List of properties for order
	 * @var array
	 */
	protected $_aOrder_Properties = array();

	/**
	 * List of property directories for order
	 * @var array
	 */
	protected $_aOrder_Property_Dirs = array();

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
			$this->_oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if (!is_null($this->_oSiteuser))
			{
				$this->addEntity($this->_oSiteuser->clearEntities());
			}
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($this->_oSiteuser ? $this->_oSiteuser->id : 0)
		);

		$this->itemsPropertiesList = $this->itemsProperties = $this->taxes = FALSE;
		$this->countries = $this->orderProperties = $this->paymentSystems = TRUE;

		$this->quantity = 1;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Cart_Controller_Onestep.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();

		// Coupon text
		!is_null($this->couponText) && $this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('coupon_text')
				->value($this->couponText)
		);

		// Активность модуля "Пользователи сайта"
		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_exists')
				->value(Core::moduleIsActive('siteuser') ? 1 : 0)
		);

		// Список свойств товаров
		if ($this->itemsPropertiesList)
		{
			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = $oShop_Item_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$oProperty->clearEntities();

				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty;

				$oShop_Item_Property = $oProperty->Shop_Item_Property;
				$oShop_Item_Property->shop_measure_id && $oProperty->addEntity(
					$oShop_Item_Property->Shop_Measure
				);
			}

			$aProperty_Dirs = $oShop_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_item_properties');

			$this->addEntity($Shop_Item_Properties);

			$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
		}

		$totalDiscount = 0;
		$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

		if (!is_null($oShop_Item->id))
		{
			$this->itemsProperties && $oShop_Item->showXmlProperties(TRUE);

			$this->addEntity($oShop_Item->clearEntities());

			$aTotal = $this->calculatePrice($oShop_Item);

			// Скидки от суммы заказа
			if ($oShop_Item->apply_purchase_discount)
			{
				$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
				$oShop_Purchase_Discount_Controller
					->amount($aTotal['amount'])
					->quantity($this->quantity)
					->weight($aTotal['weight'])
					->couponText($this->couponText)
					->siteuserId($this->_oSiteuser ? $this->_oSiteuser->id : 0)
					->prices(array($aTotal['amount']));

				$aShop_Purchase_Discounts = $oShop_Purchase_Discount_Controller->getDiscounts();
				foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
				{
					$this->addEntity($oShop_Purchase_Discount->clearEntities());
					$totalDiscount += $oShop_Purchase_Discount->getDiscountAmount();
				}
			}

			// Скидка больше суммы заказа
			$totalDiscount > $aTotal['amount'] && $totalDiscount = $aTotal['amount'];

			// Total order amount
			$total_amount = $aTotal['amount'] - $totalDiscount;

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total_amount')
					->value($total_amount)
					->addAttribute('formatted', $oShop->Shop_Currency->format($total_amount))
					->addAttribute('formattedWithCurrency', $oShop->Shop_Currency->formatWithCurrency($total_amount))
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total_tax')
					->value($aTotal['tax'])
					->addAttribute('formatted', $oShop->Shop_Currency->format($aTotal['tax']))
					->addAttribute('formattedWithCurrency', $oShop->Shop_Currency->formatWithCurrency($aTotal['tax']))
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total_quantity')
					->value($this->quantity)
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total_weight')
					->value($aTotal['weight'])
			);
		}

		$this->taxes && $oShop->showXmlTaxes(TRUE);

		// Свойства заказа
		if ($this->orderProperties)
		{
			$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

			$aProperties = $oShop_Order_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$oProperty->clearEntities();

				$this->_aOrder_Properties[$oProperty->property_dir_id][] = $oProperty;

				$oShop_Order_Property = $oProperty->Shop_Order_Property;
				$oProperty
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('prefix')->value($oShop_Order_Property->prefix)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('display')->value($oShop_Order_Property->display)
					);
			}

			$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aOrder_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			// Список свойств товаров
			$Shop_Order_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_order_properties');

			$this->addEntity($Shop_Order_Properties);

			$this->_addOrdersPropertiesList(0, $Shop_Order_Properties);
		}

		$this->countries && $this->addEntities(
			Core_Entity::factory('Shop_Country')->findAll(FALSE)
		);

		if (!is_null($this->_oSiteuser) && $this->_oSiteuser->country != '')
		{
			$oCurrent_Shop_Country = Core_Entity::factory('Shop_Country')->getByName($this->_oSiteuser->country);
			if (!is_null($oCurrent_Shop_Country))
			{
				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('current_shop_country_id')
						->value($oCurrent_Shop_Country->id)
				);

				$oCurrent_Shop_Country_Location_Cities = Core_Entity::factory('Shop_Country_Location_City');
				$oCurrent_Shop_Country_Location_Cities->queryBuilder()
					->join('shop_country_locations', 'shop_country_locations.id', '=', 'shop_country_location_cities.shop_country_location_id')
					->where('shop_country_locations.shop_country_id', '=', $oCurrent_Shop_Country->id);

				$oCurrent_Shop_Country_Location_City = $oCurrent_Shop_Country_Location_Cities->getByName($this->_oSiteuser->city);

				if (!is_null($oCurrent_Shop_Country_Location_City))
				{
					// Области
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('current_shop_country_location_id')
							->value($oCurrent_Shop_Country_Location_City->shop_country_location_id)
					);

					$this->addEntities(
						$oCurrent_Shop_Country
							->Shop_Country_Locations
							->findAll()
					);

					// Города
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('current_shop_country_location_city_id')
							->value($oCurrent_Shop_Country_Location_City->id)
					);

					$this->addEntities(
						$oCurrent_Shop_Country_Location_City
							->Shop_Country_Location
							->Shop_Country_Location_Cities
							->findAll()
					);
				}
			}
		}

		// Платежные системы
		$this->paymentSystems && $this->addEntities(
			$oShop->Shop_Payment_Systems->getAllByActive(1)
		);

		return parent::show();
	}

	/**
	 * Add items properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addItemsPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aItem_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aItem_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addItemsPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aItem_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aItem_Properties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Add order's properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addOrdersPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aOrder_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aOrder_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addOrdersPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aOrder_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aOrder_Properties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Calculate amount and weight
	 * @param object Shop_Item_Model $oShop_Item
	 * @return array
	 */
	public function calculatePrice(Shop_Item_Model $oShop_Item)
	{
		$aTotal = array(
			'amount' => 0,
			'weight' => 0,
			'tax' => 0,
		);

		// Prices
		$oShop_Item_Controller = new Shop_Item_Controller();
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			$oSiteuser && $oShop_Item_Controller->siteuser($oSiteuser);
		}

		$oShop_Item_Controller->count($this->quantity);
		$aPrices = $oShop_Item_Controller->getPrices($oShop_Item);

		$amount = $aPrices['price_discount'] * $this->quantity;

		$tax = $aPrices['tax'] * $this->quantity;

		$weight = $oShop_Item->weight * $this->quantity;

		$aTotal = array(
			'amount' => $amount,
			'weight' => $weight,
			'tax' => $tax,
		);

		return $aTotal;
	}

	/**
	 * Show delivery
	 * @param int $shop_country_id
	 * @param int $shop_country_location_id
	 * @param int $shop_country_location_city_id
	 * @param int $shop_country_location_city_area_id
	 * @param int $weight
	 * @param int $amount
	 * @return array
	 */
	public function showDelivery($shop_country_id, $shop_country_location_id, $shop_country_location_city_id, $shop_country_location_city_area_id, $weight, $amount)
	{
		$aDelivery[0] = array(
			//'id' => 0,
			'name' => '...',
			'price' => 0,
			'shop_delivery_condition_id' => 0,
		);

		$oShop = $this->getEntity();

		// Выбираем все типы доставки для данного магазина
		$aShop_Deliveries = $oShop->Shop_Deliveries->getAllByActive(1);

		foreach ($aShop_Deliveries as $oShop_Delivery)
		{
			$aShop_Delivery_Condition = array();

			if ($oShop_Delivery->type == 0)
			{
				$oShop_Delivery_Condition_Controller = new Shop_Delivery_Condition_Controller();
				$oShop_Delivery_Condition_Controller
					->shop_country_id($shop_country_id)
					->shop_country_location_id($shop_country_location_id)
					->shop_country_location_city_id($shop_country_location_city_id)
					->shop_country_location_city_area_id($shop_country_location_city_area_id)
					->totalWeight($weight)
					->totalAmount($amount);

				// Условие доставки, подходящее под ограничения
				$aShop_Delivery_Condition = array(
					$oShop_Delivery_Condition_Controller->getShopDeliveryCondition($oShop_Delivery)
				);
			}

			if (count($aShop_Delivery_Condition))
			{
				foreach ($aShop_Delivery_Condition as $oShop_Delivery_Condition)
				{
					if (!is_null($oShop_Delivery_Condition))
					{
						$aDelivery[] = array(
							//'id' => $oShop_Delivery_Condition->Shop_Delivery->id,
							'name' => $oShop_Delivery_Condition->Shop_Delivery->name,
							'price' => $oShop_Delivery_Condition->price,
							'shop_delivery_condition_id' => $oShop_Delivery_Condition->id,
						);
					}
				}

				$aShop_Delivery_Condition = array();
			}
		}

		return $aDelivery;
	}

	/**
	 * Show payment system
	 * @param int $shop_delivery_id
	 * @return array
	 */
	public function showPaymentSystem($shop_delivery_id)
	{
		$aPaymentSystems[0] = array(
			'id' => 0,
			'name' => '...',
		);

		$oShop = $this->getEntity();

		$oShop_Payment_Systems = $oShop->Shop_Payment_Systems;

		if ($shop_delivery_id)
		{
			$oShop_Payment_Systems
				->queryBuilder()
				->select('shop_payment_systems.*')
				->join('shop_delivery_payment_systems', 'shop_delivery_payment_systems.shop_payment_system_id', '=', 'shop_payment_systems.id')
				->where('shop_delivery_payment_systems.shop_delivery_id', '=', $shop_delivery_id);
		}

		$aShop_Payment_Systems = $oShop_Payment_Systems->getAllByActive(1);
		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$aPaymentSystems[] = array(
				'id' => $oShop_Payment_System->id,
				'name' => $oShop_Payment_System->name,
			);
		}

		return $aPaymentSystems;
	}
}