<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'count',
		'siteuser'
	);

	/**
	 * Price array
	 * @var array
	 */
	protected $_aPrice = array();

	/**
	 * Get $this->_aPrice
	 * @return array
	 */
	public function getAPrice()
	{
		return $this->_aPrice;
	}

	/**
	 * Set $this->_aPrice
	 * @param array $aPrice
	 * @return array
	 */
	public function setAPrice(array $aPrice)
	{
		$this->_aPrice = $aPrice;
		return $this;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Количество товара по умолчанию равно 1
		$this->count = 1;
	}

	/**
	 * Calculate the cost with tax and discounts
	 * @param float $price price
	 * @param Shop_Item_Model $oShop_Item item
	 * @param boolean $bRound round prices
	 * @return array
	 * @hostcms-event Shop_Item_Controller.onBeforeCalculatePrice
	 * @hostcms-event Shop_Item_Controller.onAfterCalculatePrice
	 */
	public function calculatePrice($price, Shop_Item_Model $oShop_Item, $bRound = TRUE)
	{
		$oShop = $oShop_Item->Shop;

		$this->_aPrice = array(
			'tax' => 0,
			'rate' => 0,
			'price' => $price,
			'price_discount' => $price,
			'price_tax' => $price,
			'coupon' => NULL,
			'discount' => 0,
			'discounts' => array()
		);

		Core_Event::notify(get_class($this) . '.onBeforeCalculatePrice', $this, array($oShop_Item));

		// Определяем коэффициент пересчета
		$fCurrencyCoefficient = $oShop_Item->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$oShop_Item->Shop_Currency, $oShop->Shop_Currency
			)
			: 0;

		// Умножаем цену товара на курс валюты в базовой валюте
		$this->_aPrice['price'] *= $fCurrencyCoefficient;

		// Расчитываем цену по установленному $this->_aPrice['price']
		$this->_calculatePrice($oShop_Item);

		Core_Event::notify(get_class($this) . '.onAfterCalculatePrice', $this, array($oShop_Item));

		// Округляем значения
		if ($bRound)
		{
			$oShop_Controller = Shop_Controller::instance();
			$this->_aPrice['tax'] = $oShop_Controller->round($this->_aPrice['tax']);
			$this->_aPrice['price'] = $oShop_Controller->round($this->_aPrice['price']);
			$this->_aPrice['discount'] = $oShop_Controller->round($this->_aPrice['discount']);
			$this->_aPrice['price_discount'] = $oShop_Controller->round($this->_aPrice['price_discount']);
			$this->_aPrice['price_tax'] = $oShop_Controller->round($this->_aPrice['price_tax']);
		}

		return $this->_aPrice;
	}

	/**
	 * Calculate the cost with tax and discounts in Shop_Item currency
	 * @param float $price price
	 * @param Shop_Item_Model $oShop_Item item
	 * @param boolean $bRound round prices
	 * @return array
	 * @hostcms-event Shop_Item_Controller.onBeforeCalculatePrice
	 * @hostcms-event Shop_Item_Controller.onAfterCalculatePrice
	 */
	public function calculatePriceInItemCurrency($price, Shop_Item_Model $oShop_Item, $bRound = TRUE)
	{
		$this->_aPrice = array(
			'tax' => 0,
			'rate' => 0,
			'price' => $price,
			'price_discount' => $price,
			'price_tax' => $price,
			'coupon' => NULL,
			'discount' => 0,
			'discounts' => array()
		);

		Core_Event::notify(get_class($this) . '.onBeforeCalculatePriceInItemCurrency', $this, array($oShop_Item));

		// Расчитываем цену по установленному $this->_aPrice['price']
		$this->_calculatePrice($oShop_Item);

		Core_Event::notify(get_class($this) . '.onAfterCalculatePriceInItemCurrency', $this, array($oShop_Item));

		// Округляем значения, переводим с научной нотации 1Е+10 в десятичную
		if ($bRound)
		{
			$oShop_Controller = Shop_Controller::instance();
			$this->_aPrice['tax'] = $oShop_Controller->round($this->_aPrice['tax']);
			$this->_aPrice['price'] = $oShop_Controller->round($this->_aPrice['price']);
			$this->_aPrice['discount'] = $oShop_Controller->round($this->_aPrice['discount']);
			$this->_aPrice['price_discount'] = $oShop_Controller->round($this->_aPrice['price_discount']);
			$this->_aPrice['price_tax'] = $oShop_Controller->round($this->_aPrice['price_tax']);
		}

		return $this->_aPrice;
	}

	static protected $_coupon = NULL;

	static public function coupon($coupon_text)
	{
		self::$_coupon = $coupon_text;
	}

	/**
	 * Calculate the cost with tax and discounts without currencies
	 * @param Shop_Item_Model $oShop_Item item
	 * @return array
	 * @hostcms-event Shop_Item_Controller.onGetShopItemDiscounts
	 */
	protected function _calculatePrice(Shop_Item_Model $oShop_Item)
	{
		if ($this->_aPrice['price'])
		{
			// Определены ли скидки на товар
			$aShop_Item_Discounts = $oShop_Item->Shop_Item_Discounts->findAll();
			
			Core_Event::notify(get_class($this) . '.onGetShopItemDiscounts', $this, array($oShop_Item, $aShop_Item_Discounts));

			$eventResult = Core_Event::getLastReturn();

			if (is_array($eventResult))
			{
				$aShop_Item_Discounts = $eventResult;
			}
			
			if (count($aShop_Item_Discounts))
			{
				// Определяем количество скидок на товар
				$discountPercent = $discountAmount = 0;

				// Цикл по идентификаторам скидок для товара
				foreach ($aShop_Item_Discounts as $oShop_Item_Discount)
				{
					$oShop_Discount = $oShop_Item_Discount->Shop_Discount;
					if ($oShop_Discount->isActive()
						&& ($oShop_Discount->coupon == 0
							|| $bCoupon = strlen(self::$_coupon) && $oShop_Discount->coupon_text == self::$_coupon
						)
					)
					{
						$this->_aPrice['discounts'][] = $oShop_Discount;

						$oShop_Discount->type == 0
							? $discountPercent += $oShop_Discount->value
							: $discountAmount += $oShop_Discount->value;

						if ($oShop_Discount->coupon == 1 && $bCoupon)
						{
							$this->_aPrice['coupon'] = $oShop_Discount->coupon_text;
						}
					}
				}

				// Определяем суммарную величину скидки в %
				$this->_aPrice['discount'] = $this->_aPrice['price'] * $discountPercent / 100;

				// Если оставшаяся цена > скидки в фиксированном размере, то применяем скидку в фиксированном размере
				($this->_aPrice['price'] - $this->_aPrice['discount']) > $discountAmount
					&& $this->_aPrice['discount'] += $discountAmount;

				// Вычисляем цену со скидкой как ее разность с величиной скидки в %
				$this->_aPrice['price_discount'] = $this->_aPrice['price'] - $this->_aPrice['discount'];
			}
			else
			{
				// если скидок нет
				$this->_aPrice['price_discount'] = $this->_aPrice['price'];
			}

			// Выбираем информацию о налогах
			if ($oShop_Item->shop_tax_id)
			{
				// Извлекаем информацию о налоге
				$oShop_Tax = $oShop_Item->Shop_Tax;

				if ($oShop_Tax->id)
				{
					$this->_aPrice['rate'] = $oShop_Tax->rate;

					// Если он не входит в цену
					if ($oShop_Tax->tax_is_included == 0)
					{
						// То считаем цену с налогом
						$this->_aPrice['tax'] = $oShop_Tax->rate / 100 * $this->_aPrice['price_discount'];
						$this->_aPrice['price_tax']
							= $this->_aPrice['price_discount']
							= $this->_aPrice['price_discount'] + $this->_aPrice['tax'];
					}
					else
					{
						$this->_aPrice['tax'] = $this->_aPrice['price_discount'] / (100 + $oShop_Tax->rate) * $oShop_Tax->rate;
						$this->_aPrice['price_tax'] = $this->_aPrice['price_discount'];
						$this->_aPrice['price'] -= $this->_aPrice['tax'];
					}
				}
				else
				{
					$this->_aPrice['price_tax'] = $this->_aPrice['price_discount'];
				}
			}
			else
			{
				$this->_aPrice['price_tax'] = $this->_aPrice['price_discount'];
			}
		}

		return $this->_aPrice;
	}

	/**
	 * Calculate total bonuses for oShop_Item
	 * @param Shop_Item_Model $oShop_Item item
	 * @return array array('total' => Total bonuses, 'bonuses' => array of bonuses)
	 * @hostcms-event Shop_Item_Controller.onBeforeGetBonuses
	 */
	public function getBonuses(Shop_Item_Model $oShop_Item, $price)
	{
		$aBonuses = array(
			'total' => 0,
			'bonuses' => array()
		);

		Core_Event::notify(get_class($this) . '.onBeforeGetBonuses', $this, array($oShop_Item, $price));

		$eventResult = Core_Event::getLastReturn();

		if (is_array($eventResult))
		{
			return $eventResult;
		}

		// Определены ли скидки на товар
		$aShop_Item_Bonuses = $oShop_Item->Shop_Item_Bonuses->findAll();

		if (count($aShop_Item_Bonuses))
		{
			// Определяем количество скидок на товар
			$bonusPercent = $bonusAmount = 0;

			// Цикл по идентификаторам скидок для товара
			foreach ($aShop_Item_Bonuses as $oShop_Item_Bonus)
			{
				$oShop_Bonus = $oShop_Item_Bonus->Shop_Bonus;
				if ($oShop_Bonus->isActive() && $oShop_Bonus->min_amount <= $price)
				{
					$aBonuses['bonuses'][] = $oShop_Bonus;

					$oShop_Bonus->type == 0
						? $bonusPercent += $oShop_Bonus->value
						: $bonusAmount += $oShop_Bonus->value;
				}
			}

			// Определяем суммарную величину бонусов в %
			$aBonuses['total'] += Shop_Controller::instance()->round(
				$price * $bonusPercent / 100 + $bonusAmount
			);
		}

		return $aBonuses;
	}

	/**
	 * Get price for current user
	 * @param Shop_Item_Model $oShop_Item item
	 * @return float
	 */
	public function getPrice(Shop_Item_Model $oShop_Item)
	{
		$oShop = $oShop_Item->Shop;

		$price = $oShop_Item->price;

		// Пользователь задан - цена определяется из таблицы товаров
		if ($this->siteuser && Core::moduleIsActive('siteuser'))
		{
			$aPrices = array();

			$aSiteuser_Groups = $this->siteuser->Siteuser_Groups->findAll();
			foreach ($aSiteuser_Groups as $oSiteuser_Group)
			{
				// Может быть создано несколько цен для одной группы пользователей
				$aShop_Prices = Core_Entity::factory('Shop_Price')->getAllBySiteuserGroupAndShop(
					$oSiteuser_Group->id, $oShop->id
				);

				foreach ($aShop_Prices as $oShop_Price)
				{
					// Если есть цена для группы
					if ($oShop_Price)
					{
						// Смотрим, определена ли такая цена для данного товара
						$oShop_Item_Price = $oShop_Item->Shop_Item_Prices->getByPriceId($oShop_Price->id);

						if ($oShop_Item_Price)
						{
							$aPrices[] = $oShop_Item_Price->value;
						}
					}
				}
			}

			count($aPrices) > 0 && $price = min($aPrices);
		}

		return $price;
	}

	/**
	 * Определение цены товара в соответствии с $this->count и специальными ценами
	 *
	 * @param float $price price
	 * @param boolean $bCache cache mode status
	 * @return float
	 */
	public function getSpecialprice($price, Shop_Item_Model $oShop_Item, $bCache = TRUE)
	{
		// Цены в зависимости от количества самого товара в корзине (а не всей корзины)
		$aShop_Specialprices = $oShop_Item->Shop_Specialprices->findAll($bCache);
		foreach ($aShop_Specialprices as $oShop_Specialprice)
		{
			if ($this->count >= $oShop_Specialprice->min_quantity && ($this->count <= $oShop_Specialprice->max_quantity || $oShop_Specialprice->max_quantity == 0))
			{
				$price = $oShop_Specialprice->percent != 0
					? $price * $oShop_Specialprice->percent / 100
					: $oShop_Specialprice->price;
				break;
			}
		}
		
		return $price;
	}

	/**
	 * Определение цены товара для заданного пользователя $this->siteuser
	 *
	 * @param Shop_Item_Model $oShop_Item товар
	 * @param boolean $bRound round prices
	 * @param boolean $bCache cache mode status
	 * @return array возвращает массив значений цен для данного пользователя
	 * - $price['tax'] сумма налога
	 * - $price['rate'] размер налога
	 * - $price['price'] цена с учетом валюты без налога
	 * - $price['price_tax'] цена с учетом налога
	 * - $price['price_discount'] цена с учетом налога и со скидкой
	 */
	public function getPrices(Shop_Item_Model $oShop_Item, $bRound = TRUE, $bCache = TRUE)
	{
		if (is_null($oShop_Item->id))
		{
			throw new Core_Exception('Shop_Item_Controller::getPrices Shop_Item id is NULL.');
		}

		// Базовая цена товара
		$price = $this->getPrice($oShop_Item);

		// Цены в зависимости от количества самого товара в корзине
		$price = $this->getSpecialprice($price, $oShop_Item, $bCache);

		return $this->calculatePrice($price, $oShop_Item, $bRound);
	}
}