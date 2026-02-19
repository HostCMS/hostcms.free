<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Purchase_Discount_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'applyDiscountCards', // применять дисконтные карты
		'applyDiscounts', // применять скидки
		'amount', // сумма заказа
		'quantity', // количество товаров в заказе
		'weight', // масса товаров в заказе
		'couponText', // текст купона, если есть
		'siteuserId', // Идентификатор пользователя сайта, нужен для расчета накопительных скидок
		'prices', // массив цен товаров, используется при расчете скидки на N-й товар
		'dateTime'
	);

	/**
	 * Shop
	 * @var Shop_Model
	 */
	protected $_shop = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct();

		$this->_shop = $oShop;
		$this->prices = array();
		$this->dateTime = Core_Date::timestamp2sql(time());
	}

	/**
	 * Array of discounts
	 * @var array
	 */
	protected $_aReturn = array();

	/**
	 * Get $this->_aReturn
	 * @return array
	 */
	public function getReturn()
	{
		return $this->_aReturn;
	}

	/**
	 * Set $this->_aReturn
	 * @param array $array
	 * @return self
	 */
	public function setReturn(array $array)
	{
		$this->_aReturn = $array;
		return $this;
	}

	//protected $_aShop_Purchase_Discounts_For_Coupons

	/**
	 * Расчет скидки на сумму товара, в соответствии со списком скидок, доступных для указанного магазина
	 * $return array
	 * @hostcms-event Shop_Purchase_Discount_Controller.onBeforeGetDiscounts
	 * @hostcms-event Shop_Purchase_Discount_Controller.onAfterGetDiscounts
	 */
	public function getDiscounts()
	{
		$amount = floatval($this->amount);
		$quantity = floatval($this->quantity);
		$weight = floatval($this->weight);

		$this->_aReturn = array();

		Core_Event::notify(get_class($this) . '.onBeforeGetDiscounts', $this, array($this->_shop));

		if ($amount <= 0 || $quantity <= 0)
		{
			return $this->_aReturn;
		}

		$aPrices = $this->prices;
		rsort($aPrices);

		// Идентификаторы скидок для переданного купона
		$aShop_Purchase_Discount_IDs = array();

		if ($this->couponText != '')
		{
			// Все скидки, связанные с этим купоном
			$aShop_Purchase_Discounts_For_Coupons = $this->getAllByCouponText($this->couponText);
			foreach ($aShop_Purchase_Discounts_For_Coupons as $oShop_Purchase_Discounts_For_Coupon)
			{
				$aShop_Purchase_Discount_IDs[] = $oShop_Purchase_Discounts_For_Coupon->id;
			}
		}

		$oShop_Controller = Shop_Controller::instance();

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

		// Извлекаем все активные скидки, доступные для текущей даты
		$oShop_Purchase_Discounts = $this->_shop->Shop_Purchase_Discounts;
		$oShop_Purchase_Discounts->queryBuilder()
			->select('shop_purchase_discounts.*')
			->join('shop_purchase_discount_siteuser_groups', 'shop_purchase_discount_siteuser_groups.shop_purchase_discount_id', '=', 'shop_purchase_discounts.id')
			->where('shop_purchase_discounts.active', '=', 1)
			//->where('shop_purchase_discounts.coupon', '=', 0)
			->where('shop_purchase_discounts.start_datetime', '<=', $this->dateTime)
			->where('shop_purchase_discounts.end_datetime', '>=', $this->dateTime)
			->where('shop_purchase_discount_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs);

		$aShop_Purchase_Discounts = $oShop_Purchase_Discounts->findAll();

		foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
		{
			if (!$oShop_Purchase_Discount->coupon || in_array($oShop_Purchase_Discount->id, $aShop_Purchase_Discount_IDs))
			{
				// Определяем коэффициент пересчета
				$fCoefficient = $oShop_Purchase_Discount->Shop_Currency->id > 0 && $this->_shop->Shop_Currency->id > 0
					? $oShop_Controller->getCurrencyCoefficientInShopCurrency(
						$oShop_Purchase_Discount->Shop_Currency, $this->_shop->Shop_Currency
					)
					: 0;

				// Нижний предел суммы
				$min_amount = $fCoefficient * $oShop_Purchase_Discount->min_amount;

				// Верхний предел суммы
				$max_amount = $fCoefficient * $oShop_Purchase_Discount->max_amount;

				$bCheckAmount = $amount >= $min_amount
					&& ($amount < $max_amount || $max_amount == 0);

				$bCheckQuantity = $quantity >= $oShop_Purchase_Discount->min_count
					&& ($quantity < $oShop_Purchase_Discount->max_count || $oShop_Purchase_Discount->max_count == 0);

				$bCheckWeight = $weight >= $oShop_Purchase_Discount->min_weight
					&& ($weight < $oShop_Purchase_Discount->max_weight || $oShop_Purchase_Discount->max_weight == 0);

				$bCheckOrdersSum = FALSE;

				if ($oShop_Purchase_Discount->mode == 2 && $this->siteuserId)
				{
					$oSiteuser = Core_Entity::factory('Siteuser')->find($this->siteuserId);
					if (!is_null($oSiteuser->id))
					{
						$fSum = 0.0;

						$oShop_Orders = $oSiteuser->Shop_Orders->getAllBypaid(1);
						foreach ($oShop_Orders as $oShop_Order)
						{
							$fSum += $oShop_Order->getAmount();
						}

						$bCheckOrdersSum = $fSum >= $min_amount
							&& ($fSum < $max_amount || $max_amount == 0)
							&& (!$oShop_Purchase_Discount->coupon || in_array($oShop_Purchase_Discount->id, $aShop_Purchase_Discount_IDs));
					}
				}

				if (
					// И
					$oShop_Purchase_Discount->mode == 0 && $bCheckAmount && $bCheckQuantity && $bCheckWeight
					// ИЛИ
					|| $oShop_Purchase_Discount->mode == 1 && ($bCheckAmount || $bCheckQuantity || $bCheckWeight)
					// Накопленная сумма
					|| $oShop_Purchase_Discount->mode == 2 && $bCheckOrdersSum
				)
				{
					$fTmpAmount = $amount;

					// Скидка на N-й товар
					if ($oShop_Purchase_Discount->position)
					{
						// В заказе товаров достаточно для применения скидки на N-й
						if (count($this->prices) >= $oShop_Purchase_Discount->position && isset($aPrices[$oShop_Purchase_Discount->position - 1]))
						{
							$fTmpAmount = $aPrices[$oShop_Purchase_Discount->position - 1];
						}
						else
						{
							// Товара недостаточно для применения этой скидки
							continue;
						}
					}

					// Учитываем перерасчет суммы скидки в валюту магазина
					/*$discount = $fCoefficient * ($oShop_Purchase_Discount->type == 0
						// Процент
						? $fTmpAmount * $oShop_Purchase_Discount->value / 100
						// Фиксированная скидка
						: ($oShop_Purchase_Discount->value <= $fTmpAmount
							? $oShop_Purchase_Discount->value
							: $fTmpAmount
							)
						);*/

					// Процент
					if ($oShop_Purchase_Discount->type == 0)
					{
						$discount = $fCoefficient * ($fTmpAmount * $oShop_Purchase_Discount->value / 100);

						$oShop_Purchase_Discount->max_discount > 0 && $discount > $oShop_Purchase_Discount->max_discount
							&& $discount = $oShop_Purchase_Discount->max_discount;
					}
					else // Фиксированная скидка
					{
						$discount = $fCoefficient * ($oShop_Purchase_Discount->value <= $fTmpAmount
							? $oShop_Purchase_Discount->value
							: $fTmpAmount);
					}

					$discount = $oShop_Controller->round($discount);

					$this->_aReturn[] = $oShop_Purchase_Discount->discountAmount($discount);
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterGetDiscounts', $this, array($this->_shop, $this->_aReturn));

		$eventResult = Core_Event::getLastReturn();
		if (is_array($eventResult))
		{
			$this->_aReturn = $eventResult;
		}

		return $this->_aReturn;
	}

	/**
	 * Calculate Discounts
	 * @return array
	 * @hostcms-event Shop_Purchase_Discount_Controller.onAfterCalculateDiscounts
	 */
	public function calculateDiscounts()
	{
		$aReturn = array(
			'discounts' => array(),
			'discountcard' => NULL,
			'discountcard_level' => NULL,
			'discountAmount' => 0,
		);

		$bApplyMaxDiscount = $bApplyShopPurchaseDiscounts = FALSE;
		$fDiscountcard = $fAppliedDiscountsAmount = 0;

		$oSiteuser = Core::moduleIsActive('siteuser') && $this->siteuserId
			? Core_Entity::factory('Siteuser', $this->siteuserId)
			: NULL;

		// Дисконтная карта
		if ($this->applyDiscountCards && Core::moduleIsActive('siteuser') && $oSiteuser)
		{
			$oShop_Discountcard = $oSiteuser->Shop_Discountcards->getByShop_id($this->_shop->id);
			if (!is_null($oShop_Discountcard)
				&& $oShop_Discountcard->active
				&& $oShop_Discountcard->shop_discountcard_level_id
			)
			{
				$aReturn['discountcard'] = $oShop_Discountcard;

				$oShop_Discountcard_Level = $oShop_Discountcard->Shop_Discountcard_Level;

				$aReturn['discountcard_level'] = $oShop_Discountcard_Level;

				$bApplyMaxDiscount = $oShop_Discountcard_Level->apply_max_discount == 1;

				// Сумма скидки по дисконтной карте
				$fDiscountcard = $this->amount * ($oShop_Discountcard_Level->discount / 100);
			}
		}

		// Скидки от суммы заказа
		if ($this->applyDiscounts)
		{
			$aReturn['discounts'] = $aShop_Purchase_Discounts = $this->getDiscounts();

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
					/*if (!$bTpl)
					{
						$this->addEntity($oShop_Purchase_Discount->clearEntities());
					}
					else
					{
						$this->append('aShop_Purchase_Discounts', $oShop_Purchase_Discount);
					}*/

					$fAppliedDiscountsAmount += $oShop_Purchase_Discount->getDiscountAmount();
				}
			}

			// Скидка больше суммы заказа
			$fAppliedDiscountsAmount > $this->amount && $fAppliedDiscountsAmount = $this->amount;
		}

		// Не применять максимальную скидку или сумма по карте больше, чем скидка от суммы заказа
		if (!$bApplyMaxDiscount || !$bApplyShopPurchaseDiscounts)
		{
			if ($fDiscountcard)
			{
				$fAmountForCard = $this->amount - $fAppliedDiscountsAmount;

				if ($fAmountForCard > 0)
				{
					$fDiscountcard = $fAmountForCard * ($oShop_Discountcard_Level->discount / 100);

					// Округляем до целых
					$oShop_Discountcard_Level->round
						&& $fDiscountcard = round($fDiscountcard);

					$oShop_Discountcard->discountAmount(
						Shop_Controller::instance()->round($fDiscountcard)
					);

					/*if (!$bTpl)
					{
						$this->addEntity($oShop_Discountcard->clearEntities());
					}
					else
					{
						$this->append('aShop_Discountcards', $oShop_Discountcard);
					}*/

					$fAppliedDiscountsAmount += $oShop_Discountcard->getDiscountAmount();
				}
			}
		}

		$aReturn['discountAmount'] = $fAppliedDiscountsAmount;

		Core_Event::notify(get_class($this) . '.onAfterCalculateDiscounts', $this, array($aReturn));

		$eventResult = Core_Event::getLastReturn();

		return !is_array($eventResult)
			? $aReturn
			: $eventResult;
	}

	/**
	 * Get All Discounts By Coupon Text
	 * @param string $couponText coupon code
	 * @return array
	 */
	public function getAllByCouponText($couponText)
	{
		$sDatetime = Core_Date::timestamp2sql(time());

		$oShop_Purchase_Discounts = Core_Entity::factory('Shop_Purchase_Discount');
		$oShop_Purchase_Discounts->queryBuilder()
			->select('shop_purchase_discounts.*',
				array('shop_purchase_discount_coupons.id', 'shop_purchase_discount_coupon_id')
			)
			->join('shop_purchase_discount_coupons', 'shop_purchase_discounts.id', '=', 'shop_purchase_discount_coupons.shop_purchase_discount_id')
			->where('shop_purchase_discounts.shop_id', '=', $this->_shop->id)
			->where('shop_purchase_discount_coupons.active', '=', 1)
			->where('shop_purchase_discount_coupons.deleted', '=', 0)
			->where('shop_purchase_discount_coupons.text', '=', $couponText)
			->where('shop_purchase_discount_coupons.start_datetime', '<=', $sDatetime)
			->where('shop_purchase_discount_coupons.end_datetime', '>=', $sDatetime)
			->open()
				->where('shop_purchase_discount_coupons.count', '>', 0)
				->setOr()
				->where('shop_purchase_discount_coupons.count', '=', -1)
			->close()
			->open()
				->where('siteuser_id', '=', 0);

		if (Core::moduleIsActive('siteuser'))
		{
			// Персональные купоны
			$oSiteuser = Siteuser_Controller::getCurrent(TRUE);
			$oSiteuser
				&& $oShop_Purchase_Discounts->queryBuilder()
					->setOr()
					->where('siteuser_id', '=', $oSiteuser->id);
		}

		$oShop_Purchase_Discounts->queryBuilder()
			->close();

		// Применять скидку к первому заказу
		if (Core::moduleIsActive('siteuser'))
		{
			if ($oSiteuser)
			{
				// Если нет заказов, то ограничение по first_order не накладываем вовсе, иначе только НЕ для первого
				if ($oSiteuser->Shop_Orders->getCountByshop_id($this->_shop->id) > 0)
				{
					$oShop_Purchase_Discounts->queryBuilder()
						->where('first_order', '=', 0);
				}
			}
		}

		// Чтобы получить новый объект с заполненным shop_purchase_discount_coupon_id используем FALSE
		return $oShop_Purchase_Discounts->findAll(FALSE);
	}
}