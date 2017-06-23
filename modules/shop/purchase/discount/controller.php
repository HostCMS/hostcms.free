<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Purchase_Discount_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'amount', // сумма заказа
		'quantity', // количество товаров в заказе
		'couponText', // текст купона, если есть
		'siteuserId', // Идентификатор пользователя сайта, нужен для расчета накопительных скидок
		'prices' // массив цен товаров, используется при расчете скидки на N-й товар
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
		$this->_shop = $oShop;

		parent::__construct();

		$this->prices = array();
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

	/**
	 * Расчет скидки на сумму товара, в соответствии со списком скидок, доступных для указанного магазина
	 * $return array
	 * @hostcms-event Shop_Purchase_Discount_Controller.onBeforeGetDiscounts
	 * @hostcms-event Shop_Purchase_Discount_Controller.onAfterGetDiscounts
	 */
	function getDiscounts()
	{
		$amount = floatval($this->amount);
		$quantity = floatval($this->quantity);

		$this->_aReturn = array();

		Core_Event::notify(get_class($this) . '.onBeforeGetDiscounts', $this);

		if ($amount <= 0 || $quantity <= 0)
		{
			return $this->_aReturn;
		}

		// Идентификатор скидки по купону
		$shop_purchase_discount_id = 0;

		// Получаем данные о купоне
		if (strlen($this->couponText))
		{
			$oShop_Purchase_Discounts_For_Coupon = $this->_shop->Shop_Purchase_Discounts->getByCouponText($this->couponText);
			!is_null($oShop_Purchase_Discounts_For_Coupon) && $shop_purchase_discount_id = $oShop_Purchase_Discounts_For_Coupon->id;
		}

		// Извлекаем все активные скидки, доступные для текущей даты
		$oShop_Purchase_Discounts = $this->_shop->Shop_Purchase_Discounts;
		$oShop_Purchase_Discounts->queryBuilder()
			->where('active', '=', 1)
			//->where('coupon', '=', 0)
			->where('start_datetime', '<=', Core_Date::timestamp2sql(time()))
			->where('end_datetime', '>=', Core_Date::timestamp2sql(time()));

		$aShop_Purchase_Discounts = $oShop_Purchase_Discounts->findAll();

		$oShop_Controller = Shop_Controller::instance();

		foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
		{
			// Определяем коэффициент пересчета
			$fCoefficient = $oShop_Purchase_Discount->Shop_Currency->id > 0 && $this->_shop->Shop_Currency->id > 0
				? $oShop_Controller->getCurrencyCoefficientInShopCurrency(
					$oShop_Purchase_Discount->Shop_Currency, $this->_shop->Shop_Currency
				)
				: 0;

			// Нижний предел скидки
			$min_amount = $fCoefficient * $oShop_Purchase_Discount->min_amount;

			// Верхний предел скидки
			$max_amount = $fCoefficient * $oShop_Purchase_Discount->max_amount;

			$bCheckAmount = $amount >= $min_amount
				&& ($amount < $max_amount || $max_amount == 0)
				&& (!$oShop_Purchase_Discount->coupon || $oShop_Purchase_Discount->id == $shop_purchase_discount_id);

			$bCheckQuantity = $quantity >= $oShop_Purchase_Discount->min_count
				&& ($quantity < $oShop_Purchase_Discount->max_count || $oShop_Purchase_Discount->max_count == 0)
				&& (!$oShop_Purchase_Discount->coupon || $oShop_Purchase_Discount->id == $shop_purchase_discount_id);

			$bCheckOrdersSum = FALSE;

			if ($oShop_Purchase_Discount->mode == 2 && $this->siteuserId)
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->find($this->siteuserId);
				if(!is_null($oSiteuser))
				{
					$fSum = 0.0;

					$oShop_Orders = $oSiteuser->Shop_Orders->getAllBypaid(1);
					foreach ($oShop_Orders as $oShop_Order)
					{
						$fSum += $oShop_Order->getAmount();
					}

					$bCheckOrdersSum = $fSum >= $min_amount
					&& ($fSum < $max_amount || $max_amount == 0)
					&& (!$oShop_Purchase_Discount->coupon || $oShop_Purchase_Discount->id == $shop_purchase_discount_id);
				}
			}

			// И
			if ($oShop_Purchase_Discount->mode == 0 && $bCheckAmount && $bCheckQuantity
			// ИЛИ
			|| $oShop_Purchase_Discount->mode == 1 && ($bCheckAmount || $bCheckQuantity)
			|| $oShop_Purchase_Discount->mode == 2 && $bCheckOrdersSum)
			{
				$fTmpAmount = $amount;

				// Скидка на N-й товар
				if ($oShop_Purchase_Discount->position)
				{
					// В заказе товаров достаточно для применения скидки на N-й
					if (count($this->prices) >= $oShop_Purchase_Discount->position)
					{
						$fTmpAmount = min($this->prices);
					}
					else
					{
						// Товара недостаточно для применения этой скидки
						continue;
					}
				}

				// Учитываем перерасчет суммы скидки в валюту магазина
				$discount = $fCoefficient * ($oShop_Purchase_Discount->type == 0
					// Процент
					? $fTmpAmount * $oShop_Purchase_Discount->value / 100
					// Фиксированная скидка
					: ($oShop_Purchase_Discount->value <= $fTmpAmount
						? $oShop_Purchase_Discount->value
						: $fTmpAmount
						)
					);

				$discount = $oShop_Controller->round($discount);

				$this->_aReturn[] = $oShop_Purchase_Discount->discountAmount($discount);
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterGetDiscounts', $this);

		return $this->_aReturn;
	}
}