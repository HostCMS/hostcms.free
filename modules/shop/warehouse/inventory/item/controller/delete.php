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
class Shop_Warehouse_Inventory_Item_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_warehouse_inventory_item_id = intval(Core_Array::getGet('shop_warehouse_inventory_item_id'));

		if ($shop_warehouse_inventory_item_id)
		{
			$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item')->getById($shop_warehouse_inventory_item_id);

			if (!is_null($oShop_Warehouse_Inventory_Item))
			{
				$oShop_Warehouse_Inventory = $oShop_Warehouse_Inventory_Item->Shop_Warehouse_Inventory;
				$oShop_Warehouse = $oShop_Warehouse_Inventory->Shop_Warehouse;

				$shop_item_id = $oShop_Warehouse_Inventory_Item->shop_item_id;

				$oShop_Warehouse_Inventory_Item->delete();

				// Удаляем проводки в документе
				$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocumentAndShopItem($oShop_Warehouse_Inventory->id, $oShop_Warehouse_Inventory->getEntityType(), $shop_item_id);
				foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
				{
					$oShop_Warehouse_Entry->delete();
				}

				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($shop_item_id, $oShop_Warehouse->id, $oShop_Warehouse_Inventory->datetime);

				$rest = $oShop_Warehouse->getRest($shop_item_id);

				// Recount
				$oShop_Warehouse->setRest($shop_item_id, is_null($rest) ? 0 : $rest);
			}

			// $this->_Admin_Form_Controller->addMessage(
				// "<script>$('.shop-item-table tr#{$oShop_Warehouse_Inventory_Item->id}').remove();</script>"
			// );
		}

		return TRUE;
	}
}