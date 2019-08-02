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
class Shop_Price_Setting_Item_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_price_setting_id = intval(Core_Array::getGet('shop_price_setting_id'));
		$shop_item_id = intval(Core_Array::getGet('shop_item_id'));

		$oShop_Price_Setting_Items = Core_Entity::factory('Shop_Price_Setting_Item');
		$oShop_Price_Setting_Items->queryBuilder()
			->where('shop_price_setting_items.shop_price_setting_id', '=', $shop_price_setting_id)
			->where('shop_price_setting_items.shop_item_id', '=', $shop_item_id)
			->limit(1);

		$aShop_Price_Setting_Items = $oShop_Price_Setting_Items->findAll(FALSE);

		if (isset($aShop_Price_Setting_Items[0]))
		{
			$aShop_Price_Setting_Items[0]->delete();
		}

		$this->_Admin_Form_Controller->addMessage(
			"<script>$('.shop-item-table tr#shop-item-{$shop_item_id}').remove();</script>"
		);

		return TRUE;
	}
}