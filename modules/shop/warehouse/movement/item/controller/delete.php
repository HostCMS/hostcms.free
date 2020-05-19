<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Movement_Item_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_warehouse_movement_item_id = intval(Core_Array::getGet('shop_warehouse_movement_item_id'));

		$oShop_Warehouse_Movement_Item = Core_Entity::factory('Shop_Warehouse_Movement_Item')->getById($shop_warehouse_movement_item_id);

		if (!is_null($oShop_Warehouse_Movement_Item))
		{
			$oShop_Warehouse_Movement = $oShop_Warehouse_Movement_Item->Shop_Warehouse_Movement;
			$oSource_Shop_Warehouse = $oShop_Warehouse_Movement->Source_Shop_Warehouse;
			$oDestination_Shop_Warehouse = $oShop_Warehouse_Movement->Destination_Shop_Warehouse;

			$shop_item_id = $oShop_Warehouse_Movement_Item->shop_item_id;

			$oShop_Warehouse_Movement_Item->delete();

			// Удаляем проводки в документе
			$aShop_Warehouse_Entries = $oSource_Shop_Warehouse->Shop_Warehouse_Entries->getByDocumentAndShopItem($oShop_Warehouse_Movement->id, $oShop_Warehouse_Movement::TYPE, $shop_item_id);
			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$oShop_Warehouse_Entry->delete();
			}

			$aShop_Warehouse_Entries = $oDestination_Shop_Warehouse->Shop_Warehouse_Entries->getByDocumentAndShopItem($oShop_Warehouse_Movement->id, $oShop_Warehouse_Movement::TYPE, $shop_item_id);
			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$oShop_Warehouse_Entry->delete();
			}

			// Удаляем все накопительные значения с датой больше, чем дата документа
			Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($shop_item_id, $oSource_Shop_Warehouse->id, $oShop_Warehouse_Movement->datetime);
			Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($shop_item_id, $oDestination_Shop_Warehouse->id, $oShop_Warehouse_Movement->datetime);

			$rest = $oSource_Shop_Warehouse->getRest($shop_item_id);
			$oSource_Shop_Warehouse->setRest($shop_item_id, is_null($rest) ? 0 : $rest);

			$rest = $oDestination_Shop_Warehouse->getRest($shop_item_id);
			$oDestination_Shop_Warehouse->setRest($shop_item_id, is_null($rest) ? 0 : $rest);
		}

		// $this->_Admin_Form_Controller->addMessage(
			// "<script>$('.shop-item-table tr#{$oShop_Warehouse_Movement_Item->id}').remove();</script>"
		// );

		return TRUE;
	}
}