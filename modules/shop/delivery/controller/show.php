<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Выбор способа доставки.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'paymentSystems',
		'applyDiscounts',
		'applyDiscountCards',
	);

	/**
	 * Shop_Deliveries object
	 * @var Shop_Delivery_Model
	 */
	protected $_Shop_Deliveries = NULL;

	/**
	 * Current Siteuser
	 * @var Siteuser_Model|NULL
	 */
	protected $_oSiteuser = NULL;

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
			$this->_oSiteuser && $this->addEntity($this->_oSiteuser->clearEntities());
		}

		$this->paymentSystems = FALSE;

		$this->applyDiscounts = $this->applyDiscountCards = TRUE;

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

		$quantityPurchaseDiscount = $amountPurchaseDiscount = $this->totalAmount = $quantity = $this->totalWeight = $this->volume = 0;

		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$aDiscountPrices = array();

		// Есть скидки на N-й товар, доступные для текущей даты
		$bPositionDiscount = $oShop->Shop_Purchase_Discounts->checkAvailableWithPosition();

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
						$this->_oSiteuser && $oShop_Item_Controller->siteuser($this->_oSiteuser);
					}

					$oShop_Item_Controller->count($oShop_Cart->quantity);

					$aPrices = $oShop_Item_Controller->getPrices($oShop_Cart->Shop_Item);

					$this->totalAmount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					if ($bPositionDiscount)
					{
						// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
						for ($i = 0; $i < $oShop_Cart->quantity; $i++)
						{
							$aDiscountPrices[] = $aPrices['price_discount'];
						}
					}

					// Сумма для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					$quantity += $oShop_Cart->quantity;

					// Количество для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $quantityPurchaseDiscount += $oShop_Cart->quantity;

					$this->totalWeight += $oShop_Cart->Shop_Item->weight * $oShop_Cart->quantity;

					// Расчет единицы измерения ведется в милиметрах
					$this->volume += Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->length, $oShop->size_measure, 0)
						* Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->width, $oShop->size_measure, 0)
						* Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->height, $oShop->size_measure, 0);
				}
			}
		}

		// Дисконтная карта
		$bApplyMaxDiscount = $bApplyShopPurchaseDiscounts = FALSE;
		$fDiscountcard = $fAppliedDiscountsAmount = 0;

		if ($this->applyDiscountCards && Core::moduleIsActive('siteuser') && $this->_oSiteuser)
		{
			$oShop_Discountcard = $this->_oSiteuser->Shop_Discountcards->getByShop_id($oShop->id);
			if (!is_null($oShop_Discountcard) && $oShop_Discountcard->shop_discountcard_level_id)
			{
				$oShop_Discountcard_Level = $oShop_Discountcard->Shop_Discountcard_Level;

				$bApplyMaxDiscount = $oShop_Discountcard_Level->apply_max_discount == 1;

				// Сумма скидки по дисконтной карте
				$fDiscountcard = $this->totalAmount * ($oShop_Discountcard_Level->discount / 100);
			}
		}

		if ($this->applyDiscounts)
		{
			// Скидки от суммы заказа
			$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
			$oShop_Purchase_Discount_Controller
				->amount($amountPurchaseDiscount)
				->quantity($quantityPurchaseDiscount)
				->couponText($this->couponText)
				->prices($aDiscountPrices);

			$aShop_Purchase_Discounts = $oShop_Purchase_Discount_Controller->getDiscounts();

			// Если применять только максимальную скидку, то считаем сумму скидок по скидкам от суммы заказа
			if ($bApplyMaxDiscount)
			{
				$totalPurchaseDiscount = 0;

				foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
				{
					$totalPurchaseDiscount += $oShop_Purchase_Discount->getDiscountAmount();
				}

				$bApplyShopPurchaseDiscounts = $totalPurchaseDiscount > $fDiscountcard;
			}
			else
			{
				$bApplyShopPurchaseDiscounts = TRUE;
			}

			// Если решили применять скидку от суммы заказа
			if ($bApplyShopPurchaseDiscounts)
			{
				foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
				{
					$fAppliedDiscountsAmount += $oShop_Purchase_Discount->getDiscountAmount();
				}
			}

			// Скидка больше суммы заказа
			$fAppliedDiscountsAmount > $this->totalAmount && $fAppliedDiscountsAmount = $this->totalAmount;
		}

		// Не применять максимальную скидку или сумму по карте больше, чем скидка от суммы заказа
		if (!$bApplyMaxDiscount || !$bApplyShopPurchaseDiscounts)
		{
			if ($fDiscountcard)
			{
				$fAmountForCard = $this->totalAmount - $fAppliedDiscountsAmount;

				if ($fAmountForCard > 0)
				{
					$oShop_Discountcard->discountAmount(
						Shop_Controller::instance()->round($fAmountForCard * ($oShop_Discountcard_Level->discount / 100))
					);

					$fAppliedDiscountsAmount += $oShop_Discountcard->getDiscountAmount();
				}
			}
		}

		// Скидка больше суммы заказа
		$fAppliedDiscountsAmount > $this->totalAmount
			&& $fAppliedDiscountsAmount = $this->totalAmount;

		// Применяем скидку от суммы заказа
		$this->totalAmount -= $fAppliedDiscountsAmount;

		if ($this->_oSiteuser)
		{
			// Применяемые бонусы
			if (isset($_SESSION['hostcmsOrder']['bonuses']) && $_SESSION['hostcmsOrder']['bonuses'] > 0)
			{
				$aSiteuserBonuses = $this->_oSiteuser->getBonuses($oShop);

				$max_bonus = Shop_Controller::instance()->round($this->totalAmount * ($oShop->max_bonus / 100));

				$available_bonuses = $aSiteuserBonuses['total'] <= $max_bonus
					? $aSiteuserBonuses['total']
					: $max_bonus;

				if ($_SESSION['hostcmsOrder']['bonuses'] > $available_bonuses)
				{
					$_SESSION['hostcmsOrder']['bonuses'] = $available_bonuses;
				}

				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('apply_bonuses')
						->value($_SESSION['hostcmsOrder']['bonuses'])
				);

				// Вычитаем бонусы
				$this->totalAmount -= $_SESSION['hostcmsOrder']['bonuses'];
			}
		}

		return $this;
	}

}