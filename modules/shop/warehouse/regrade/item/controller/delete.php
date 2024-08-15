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
class Shop_Warehouse_Regrade_Item_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_warehouse_regrade_item_id = intval(Core_Array::getGet('shop_warehouse_regrade_item_id'));

		if ($shop_warehouse_regrade_item_id)
		{
			$oShop_Warehouse_Regrade_Item = Core_Entity::factory('Shop_Warehouse_Regrade_Item')->getById($shop_warehouse_regrade_item_id);

			if (!is_null($oShop_Warehouse_Regrade_Item))
			{
				$oShop_Warehouse_Regrade = $oShop_Warehouse_Regrade_Item->Shop_Warehouse_Regrade;
				$oShop_Warehouse = $oShop_Warehouse_Regrade->Shop_Warehouse;

				$writeoff_shop_item_id = $oShop_Warehouse_Regrade_Item->writeoff_shop_item_id;
				$incoming_shop_item_id = $oShop_Warehouse_Regrade_Item->incoming_shop_item_id;

				$oShop_Warehouse_Regrade_Item->delete();

				// Удаляем проводки в документе
				$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocumentAndShopItem($oShop_Warehouse_Regrade->id, $oShop_Warehouse_Regrade->getEntityType(), array($writeoff_shop_item_id, $incoming_shop_item_id));
				foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
				{
					$oShop_Warehouse_Entry->delete();
				}

				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($writeoff_shop_item_id, $oShop_Warehouse->id, $oShop_Warehouse_Regrade->datetime);
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($incoming_shop_item_id, $oShop_Warehouse->id, $oShop_Warehouse_Regrade->datetime);

				$writeoff_rest = $oShop_Warehouse->getRest($writeoff_shop_item_id);
				$oShop_Warehouse->setRest($writeoff_shop_item_id, is_null($writeoff_rest) ? 0 : $writeoff_rest);

				$incoming_rest = $oShop_Warehouse->getRest($incoming_shop_item_id);
				$oShop_Warehouse->setRest($incoming_shop_item_id, is_null($incoming_rest) ? 0 : $incoming_rest);
			}

			// $this->_Admin_Form_Controller->addMessage(
				// "<script>$('.shop-item-table tr#{$oShop_Warehouse_Regrade_Item->id}').remove();</script>"
			// );
		}

		return TRUE;
	}
}