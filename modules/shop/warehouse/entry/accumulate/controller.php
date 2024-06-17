<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Warehouse_Entry_Accumulate_Controller
{
	/**
	 * Delete Shop_Warehouse_Entry_Accumulate
	 * @param int $shop_item_id
	 * @param int $shop_warehouse_id
	 * @param datetime $datetime
	 */
	static public function deleteEntries($shop_item_id, $shop_warehouse_id, $datetime)
	{
		$oShop_Warehouse_Entry_Accumulate = Core_Entity::factory('Shop_Warehouse_Entry_Accumulate');
		$oShop_Warehouse_Entry_Accumulate->queryBuilder()
			->where('shop_item_id', '=', $shop_item_id)
			->where('shop_warehouse_id', '=', $shop_warehouse_id)
			->where('datetime', '>=', $datetime);

		$oShop_Warehouse_Entry_Accumulate->deleteAll(FALSE);
	}
}