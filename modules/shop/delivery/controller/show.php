<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Выбор способа доставки.
 *
 * Доступные методы:
 *
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_delivery' Доставка
 * - '/shop/shop_delivery_condition' Условие доставки
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
		'timeFrom',
		'timeTo',
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
			->select('shop_deliveries.*')
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
	 * @hostcms-event Shop_Delivery_Controller_Show.onAfterAddShopDeliveryCondition
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
				->addAttribute('formatted', $oShop->Shop_Currency->format($this->totalAmount))
				->addAttribute('formattedWithCurrency', $oShop->Shop_Currency->formatWithCurrency($this->totalAmount))
		);

		Core_Session::start();

		$aSiteuser_Group_IDs = array(0);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($oSiteuser)
			{
				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$aSiteuser_Group_IDs[] = $oSiteuser_Group->id;
				}
			}
		}

		$this->_Shop_Deliveries
			->queryBuilder()
			->join('shop_delivery_siteuser_groups', 'shop_delivery_siteuser_groups.shop_delivery_id', '=', 'shop_deliveries.id')
			->where('shop_delivery_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs);

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

						$this->paymentSystems
							&& $oShop_Delivery_Clone->showXmlShopPaymentSystems($this->paymentSystems);

						$oShop_Delivery_Clone
							->id($oShop_Delivery->id)
							->clearEntities()
							->addEntity($oShop_Delivery_Condition);

						$this->applyForbiddenAllowedTags('/shop/shop_delivery', $oShop_Delivery_Clone);
						$this->applyForbiddenAllowedTags('/shop/shop_delivery_condition', $oShop_Delivery_Condition);

						$this->addEntity($oShop_Delivery_Clone);

						Core_Event::notify(get_class($this) . '.onAfterAddShopDeliveryCondition', $this, array($oShop_Delivery_Condition));
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
				->timeFrom($this->timeFrom)
				->timeTo($this->timeTo)
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
					->timeFrom($this->timeFrom)
					->timeTo($this->timeTo)
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
		$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);

		$this->totalAmount = $Shop_Cart_Controller->totalAmount;
		$quantityPurchaseDiscount = $Shop_Cart_Controller->totalQuantityForPurchaseDiscount;
		$amountPurchaseDiscount = $Shop_Cart_Controller->totalAmountForPurchaseDiscount;
		$this->totalWeight = $Shop_Cart_Controller->totalWeight;
		$this->volume = $Shop_Cart_Controller->totalVolume;
		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$aDiscountPrices = $Shop_Cart_Controller->totalDiscountPrices;

		// Дисконтная карта
		$bApplyMaxDiscount = $bApplyShopPurchaseDiscounts = FALSE;
		$fDiscountcard = $fAppliedDiscountsAmount = 0;

		if ($this->applyDiscountCards && Core::moduleIsActive('siteuser') && $this->_oSiteuser)
		{
			$oShop_Discountcard = $this->_oSiteuser->Shop_Discountcards->getByShop_id($oShop->id);
			if (!is_null($oShop_Discountcard)
				&& $oShop_Discountcard->active
				&& $oShop_Discountcard->shop_discountcard_level_id
			)
			{
				$oShop_Discountcard_Level = $oShop_Discountcard->Shop_Discountcard_Level;

				$bApplyMaxDiscount = $oShop_Discountcard_Level->apply_max_discount == 1;

				// Сумма скидки по дисконтной карте
				$fDiscountcard = $amountPurchaseDiscount * ($oShop_Discountcard_Level->discount / 100);
			}
		}

		if ($this->applyDiscounts)
		{
			// Скидки от суммы заказа
			$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
			$oShop_Purchase_Discount_Controller
				->amount($amountPurchaseDiscount)
				->quantity($quantityPurchaseDiscount)
				->weight($this->totalWeight)
				->couponText($this->couponText)
				->siteuserId($this->_oSiteuser ? $this->_oSiteuser->id : 0)
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
			$fAppliedDiscountsAmount > $amountPurchaseDiscount && $fAppliedDiscountsAmount = $amountPurchaseDiscount;
		}

		// Не применять максимальную скидку или сумму по карте больше, чем скидка от суммы заказа
		if (!$bApplyMaxDiscount || !$bApplyShopPurchaseDiscounts)
		{
			if ($fDiscountcard)
			{
				$fAmountForCard = $amountPurchaseDiscount - $fAppliedDiscountsAmount;

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
		$fAppliedDiscountsAmount > $amountPurchaseDiscount && $fAppliedDiscountsAmount = $amountPurchaseDiscount;

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