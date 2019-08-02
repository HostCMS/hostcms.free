<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Entry_Controller extends Core_Servant_Properties
{
	/**
	 * Get price
	 * @param $shop_price_id shop price id
	 * @param $shop_item_id shop item id
	 * @param $dateTo date
	 * @return float
	 */
	public function getPrice($shop_price_id, $shop_item_id, $dateTo = NULL)
	{
		$price = NULL;

		$oShop_Price_Entries = Core_Entity::factory('Shop_Price_Entry');
		$oShop_Price_Entries->queryBuilder()
			->where('shop_price_entries.shop_price_id', '=', $shop_price_id)
			->where('shop_price_entries.shop_item_id', '=', $shop_item_id)
			->clearOrderBy()
			->orderBy('shop_price_entries.datetime', 'ASC');

		if (!is_null($dateTo))
		{
			$oShop_Price_Entries->queryBuilder()
				->where('shop_price_entries.datetime', '<', $dateTo);
		}

		$aShop_Price_Entries = $oShop_Price_Entries->findAll(FALSE);

		foreach ($aShop_Price_Entries as $oShop_Price_Entry)
		{
			$type = $oShop_Price_Entry->getDocumentType();

			if (!is_null($type))
			{
				switch ($type)
				{
					// Установка цен
					case 0:
						$price = $oShop_Price_Entry->value;
					break;
				}
			}
		}

		return is_null($price)
			? $price
			: number_format($price, 2, '.', '');
	}

	/**
	 * Set price
	 * @param $shop_price_id shop price id
	 * @param $shop_item_id shop item id
	 * @param $value value
	 * @return self
	 */
	public function setPrice($shop_price_id, $shop_item_id, $value)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', $shop_item_id);

		$oShop_Item = $oShop_Item->shortcut_id
			? $oShop_Item->Shop_Item
			: $oShop_Item;

		if ($shop_price_id)
		{
			// Цена из справочника цен
			$oShop_Item_Price = $oShop_Item->Shop_Item_Prices->getByShop_price_id($shop_price_id);

			if (is_null($oShop_Item_Price))
			{
				$oShop_Item_Price = Core_Entity::factory('Shop_Item_Price');
				$oShop_Item_Price->shop_item_id = $oShop_Item->id;
				$oShop_Item_Price->shop_price_id = $shop_price_id;
			}

			$oShop_Item_Price->value = $value;
			$oShop_Item_Price->save();
		}
		else
		{
			// Розничная цена
			$oShop_Item->price = $value;
			$oShop_Item->save();
		}
	}
}