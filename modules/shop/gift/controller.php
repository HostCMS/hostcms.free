<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Gift_Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Gift_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'amount', // сумма заказа
		'quantity', // количество товаров в заказе
		'weight', // масса товаров в заказе
		'couponText', // текст купона, если есть
		'dateTime',
		'limit'
	);

	/**
	 * Shop
	 * @var Shop_Model
	 */
	protected $_oShop = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct();

		$this->_oShop = $oShop;
		$this->dateTime = Core_Date::timestamp2sql(time());
		$this->limit = 3;
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
	 * Get gifts
	 * @param Shop_Item_Model $oShop_Item
	 * @param array $aShop_Cart
	 * @return array
	 * @hostcms-event Shop_Gift_Controller.onBeforeGetGifts
	 * @hostcms-event Shop_Gift_Controller.onAfterGetGifts
	 */
	public function getGifts(Shop_Item_Model $oShop_Item, $aShop_Cart = array())
	{
		$amount = floatval($this->amount);
		$quantity = floatval($this->quantity);
		$weight = floatval($this->weight);

		$this->_aReturn = array();

		Core_Event::notify(get_class($this) . '.onBeforeGetGifts', $this, array($this->_oShop));

		if ($amount <= 0 || $quantity <= 0)
		{
			return $this->_aReturn;
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

		$aTmp = array();

		$subQuery = Core_QueryBuilder::select('shop_group_gifts.shop_gift_id')
			->from('shop_group_gifts')
			->where('shop_group_gifts.shop_group_id', '=', $oShop_Item->shop_group_id)
			->union(
				Core_QueryBuilder::select('shop_item_gifts.shop_gift_id')
					->from('shop_item_gifts')
					->where('shop_item_gifts.shop_item_id', '=', $oShop_Item->id)
			)
		;

		$oShop_Gifts = $this->_oShop->Shop_Gifts;
		$oShop_Gifts->queryBuilder()
			->select('shop_gifts.*')
			->join(array($subQuery, 'tmp'), 'shop_gifts.id', '=', 'tmp.shop_gift_id')
			->join('shop_gift_siteuser_groups', 'shop_gift_siteuser_groups.shop_gift_id', '=', 'shop_gifts.id')
			->where('shop_gifts.active', '=', 1)
			->where('shop_gifts.start_datetime', '<=', $this->dateTime)
			->where('shop_gifts.end_datetime', '>=', $this->dateTime)
			->where('shop_gift_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs)
			->groupBy('shop_gifts.id');

		$aShop_Gifts = $oShop_Gifts->findAll();

		if (count($aShop_Gifts))
		{
			$aItemsInCartIds = $aItemsInCartQuantity = array();
			foreach ($aShop_Cart as $oShop_Cart)
			{
				if ($oShop_Cart->postpone == 0 && $oShop_Cart->shop_item_id)
				{
					!in_array($oShop_Cart->shop_item_id, $aItemsInCartIds)
						&& $aItemsInCartIds[] = $oShop_Cart->shop_item_id;

					!isset($aItemsInCartQuantity[$oShop_Cart->shop_item_id])
						&& $aItemsInCartQuantity[$oShop_Cart->shop_item_id] = 0;

					$aItemsInCartQuantity[$oShop_Cart->shop_item_id] += $oShop_Cart->quantity;
				}
			}

			$coupon = Shop_Item_Controller::getCoupon();

			foreach ($aShop_Gifts as $oShop_Gift)
			{
				if (!$oShop_Gift->coupon
					|| !is_null($coupon) && strlen($coupon) && mb_strtolower($oShop_Gift->coupon_text) == $coupon
				)
				{
					// Определяем коэффициент пересчета
					$fCoefficient = $oShop_Gift->Shop_Currency->id > 0 && $this->_oShop->Shop_Currency->id > 0
						? $oShop_Controller->getCurrencyCoefficientInShopCurrency(
							$oShop_Gift->Shop_Currency, $this->_oShop->Shop_Currency
						)
						: 0;

					// Нижний предел суммы
					$min_amount = $fCoefficient * $oShop_Gift->min_amount;

					// Верхний предел суммы
					$max_amount = $fCoefficient * $oShop_Gift->max_amount;

					$bCheckAmount = $amount >= $min_amount
						&& ($amount < $max_amount || $max_amount == 0);

					$bCheckQuantity = $quantity >= $oShop_Gift->min_count
						&& ($quantity < $oShop_Gift->max_count || $oShop_Gift->max_count == 0);

					$bCheckWeight = $weight >= $oShop_Gift->min_weight
						&& ($weight < $oShop_Gift->max_weight || $oShop_Gift->max_weight == 0);

					if (
						// И
						$oShop_Gift->mode == 0 && $bCheckAmount && $bCheckQuantity && $bCheckWeight
						// ИЛИ
						|| $oShop_Gift->mode == 1 && ($bCheckAmount || $bCheckQuantity || $bCheckWeight)
					)
					{
						//------------------------------------------
						$aShop_Gift_Entities = $oShop_Gift->Shop_Gift_Entities->findAll(FALSE);
						foreach ($aShop_Gift_Entities as $oShop_Gift_Entity)
						{
							switch ($oShop_Gift_Entity->type)
							{
								case 0: // Товар
									if (!count($aItemsInCartIds) || in_array($oShop_Gift_Entity->entity_id, $aItemsInCartIds))
									{
										$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Gift_Entity->entity_id, FALSE);
										if (!is_null($oShop_Item))
										{
											isset($aItemsInCartQuantity[$oShop_Item->id]) && $oShop_Item->cartQuantity($aItemsInCartQuantity[$oShop_Item->id]);

											$aPrices = $this->_getPrice($oShop_Gift, $oShop_Item);
											$price = $aPrices['price'];
											$discount = $aPrices['discount'];

											if (!isset($aTmp[$oShop_Item->id]) || $price < $aTmp[$oShop_Item->id]['price'])
											{
												$aTmp[$oShop_Item->id] = array(
													'shop_gift' => $oShop_Gift,
													'shop_item' => $oShop_Item,
													'price' => $price,
													'discount' => $discount
												);
											}
										}
									}
								break;
								case 1: // Группа
									$oShop_Group = Core_Entity::factory('Shop_Group')->getById($oShop_Gift_Entity->entity_id, FALSE);
									if (!is_null($oShop_Group))
									{
										$oShop_Items = $oShop_Group->Shop_Items;

										if (count($aItemsInCartIds))
										{
											$oShop_Items->queryBuilder()
												->where('shop_items.id', 'IN', $aItemsInCartIds);
										}
										else
										{
											$this->limit && $oShop_Items->queryBuilder()
												->limit($this->limit);

											$oShop_Items->queryBuilder()
												->clearOrderBy()
												->orderBy('RAND()');
										}

										$aShop_Items = $oShop_Items->findAll(FALSE);
										foreach ($aShop_Items as $oShop_Item)
										{
											isset($aItemsInCartQuantity[$oShop_Item->id]) && $oShop_Item->cartQuantity($aItemsInCartQuantity[$oShop_Item->id]);

											$aPrices = $this->_getPrice($oShop_Gift, $oShop_Item);
											$price = $aPrices['price'];
											$discount = $aPrices['discount'];

											if (!isset($aTmp[$oShop_Item->id]) || $price < $aTmp[$oShop_Item->id]['price'])
											{
												$aTmp[$oShop_Item->id] = array(
													'shop_gift' => $oShop_Gift,
													'shop_item' => $oShop_Item,
													'price' => $price,
													'discount' => $discount
												);
											}
										}
									}
								break;
							}
						}

						$this->limit && $aTmp = array_slice($aTmp, 0, $this->limit, TRUE);

						$this->_aReturn = $aTmp;
					}
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterGetGifts', $this, array($this->_oShop, $this->_aReturn));

		$eventResult = Core_Event::getLastReturn();
		if (is_array($eventResult))
		{
			$this->_aReturn = $eventResult;
		}

		return $this->_aReturn;
	}

	/**
	 * Get price for gift
	 * @param Shop_Gift_Model $oShop_Gift
	 * @param Shop_Item_Model $oShop_Item
	 * @return array
	 * @hostcms-event Shop_Gift_Controller.onAfterGetPrice
	 */
	protected function _getPrice(Shop_Gift_Model $oShop_Gift, Shop_Item_Model $oShop_Item)
	{
		$oShop_Controller = Shop_Controller::instance();

		$aPrices = $oShop_Item->getPrices();

		// Процент
		if ($oShop_Gift->type == 0)
		{
			$discount = $aPrices['price_discount'] * $oShop_Gift->value / 100;

			$oShop_Gift->max_discount > 0 && $discount > $oShop_Gift->max_discount
				&& $discount = $oShop_Gift->max_discount;
		}
		else // Фиксированная скидка
		{
			$discount = $oShop_Gift->value <= $aPrices['price_discount']
				? $oShop_Gift->value
				: $aPrices['price_discount'];
		}

		$price = $aPrices['price_discount'] - $discount;

		$aTmp = array(
			'price' => $oShop_Controller->round($price),
			'discount' => $oShop_Controller->round($discount)
		);

		Core_Event::notify('Shop_Gift_Controller.onAfterGetPrice', $this, array($oShop_Gift, $oShop_Item, $aTmp));

		$eventResult = Core_Event::getLastReturn();
		is_array($eventResult) && $aTmp = $eventResult;

		return $aTmp;
	}

	/**
	 * Cache for _cacheGetShopGroupGifts
	 * @var array
	 */
	static protected $_cacheGetShopGroupGifts = array();

	/**
	 * Get array of Shop_Group_Gifts for group $shop_group_id
	 * @param int $shop_group_id Shop_Group id
	 * @return array
	 * @hostcms-event Shop_Gifts_Controller.onGetShopGroupGifts
	 */
	protected function _getShopGroupGifts($shop_group_id)
	{
		if (!isset(self::$_cacheGetShopGroupGifts[$shop_group_id]))
		{
			self::$_cacheGetShopGroupGifts[$shop_group_id] = array();

			if ($shop_group_id)
			{
				$oShop_Group = Core_Entity::factory('Shop_Group', $shop_group_id);
				$oShop_Group_Gifts = $oShop_Group->Shop_Group_Gifts;

				$aShop_Group_Gifts = $oShop_Group_Gifts->findAll();

				Core_Event::notify(get_class($this) . '.onGetShopGroupGifts', $this, array($oShop_Group, $aShop_Group_Gifts));

				$eventResult = Core_Event::getLastReturn();

				if (is_array($eventResult))
				{
					$aShop_Group_Gifts = $eventResult;
				}

				self::$_cacheGetShopGroupGifts[$shop_group_id] = $aShop_Group_Gifts;
			}
		}

		return self::$_cacheGetShopGroupGifts[$shop_group_id];
	}

	/**
	 * Get entity shop_items
	 * @param Shop_Gift_Model $oShop_Gift
	 * @param int $type
	 * @return array
	 */
	static public function getEntitiesByType(Shop_Gift_Model $oShop_Gift, $type)
	{
		$oShop_Gift_Entities = $oShop_Gift->Shop_Gift_Entities;
		$oShop_Gift_Entities->queryBuilder()
			->where('shop_gift_entities.type', '=', $type);

		return $oShop_Gift_Entities->findAll(FALSE);
	}
}