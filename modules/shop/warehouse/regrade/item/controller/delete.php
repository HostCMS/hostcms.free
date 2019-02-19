<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Regrade_Item_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_warehouse_regrade_item_id = intval(Core_Array::getGet('shop_warehouse_regrade_item_id'));

		$oShop_Warehouse_Regrade_Item = Core_Entity::factory('Shop_Warehouse_Regrade_Item')->getById($shop_warehouse_regrade_item_id);

		if (!is_null($oShop_Warehouse_Regrade_Item))
		{
			$oShop_Warehouse_Regrade_Item->delete();
		}

		$this->_Admin_Form_Controller->addMessage(
			"<script>$('.shop-item-table tr#{$oShop_Warehouse_Regrade_Item->id}').remove();</script>"
		);

		return TRUE;
	}
}