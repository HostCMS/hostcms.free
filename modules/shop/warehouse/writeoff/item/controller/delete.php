<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Writeoff_Item_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_warehouse_writeoff_item_id = intval(Core_Array::getGet('shop_warehouse_writeoff_item_id'));

		if ($shop_warehouse_writeoff_item_id)
		{
			$oShop_Warehouse_Writeoff_Item = Core_Entity::factory('Shop_Warehouse_Writeoff_Item')->getById($shop_warehouse_writeoff_item_id);

			if (!is_null($oShop_Warehouse_Writeoff_Item))
			{	
				$oShop_Warehouse_Writeoff = $oShop_Warehouse_Writeoff_Item->Shop_Warehouse_Writeoff;
				$oShop_Warehouse = $oShop_Warehouse_Writeoff->Shop_Warehouse;

				$shop_item_id = $oShop_Warehouse_Writeoff_Item->shop_item_id;

				$oShop_Warehouse_Writeoff_Item->delete();

				// Удаляем проводки в документе
				$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocumentAndShopItem($oShop_Warehouse_Writeoff->id, $oShop_Warehouse_Writeoff->getEntityType(), $shop_item_id);
				foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
				{
					$oShop_Warehouse_Entry->delete();
				}

				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($shop_item_id, $oShop_Warehouse->id, $oShop_Warehouse_Writeoff->datetime);

				$rest = $oShop_Warehouse->getRest($shop_item_id);

				// Recount
				$oShop_Warehouse->setRest($shop_item_id, is_null($rest) ? 0 : $rest);
			}

			// $this->_Admin_Form_Controller->addMessage(
				// "<script>$('.shop-item-table tr#{$oShop_Warehouse_Writeoff_Item->id}').remove();</script>"
			// );
		}

		return TRUE;
	}
}