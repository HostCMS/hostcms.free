<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Regrade_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Regrade_Controller_Print extends Printlayout_Controller_Print
{
	protected function _prepare()
	{
		$oPrintlayout = Core_Entity::factory('Printlayout')->getById($this->printlayout);

		$this->_oPrintlayout_Controller = new Printlayout_Controller($oPrintlayout);

		if (!is_null($oPrintlayout))
		{
			$driver_id = Core_Array::getPost('driver_id');

			$oPrintlayout_Driver = Core_Entity::factory('Printlayout_Driver', $driver_id);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			if (isset($aChecked[0]))
			{
				$shop_warehouse_regrade_id = key($aChecked[0]);

				$oShop_Warehouse_Regrade = Core_Entity::factory('Shop_Warehouse_Regrade')->getById($shop_warehouse_regrade_id);

				if (!is_null($oShop_Warehouse_Regrade))
				{
					$aReplace = array(
						// Core_Meta
						'this' => $oShop_Warehouse_Regrade,
						'company' => $oShop_Warehouse_Regrade->Shop_Warehouse->Shop->Shop_Company,
						'shop_warehouse' => $oShop_Warehouse_Regrade->Shop_Warehouse,
						'shop' => $oShop_Warehouse_Regrade->Shop_Warehouse->Shop,
						'user' => $oShop_Warehouse_Regrade->User,
						'total_count' => 0,
						'Items' => array(),
					);

					$position = 1;

					$aShop_Warehouse_Regrade_Items = $oShop_Warehouse_Regrade->Shop_Warehouse_Regrade_Items->findAll();

					foreach ($aShop_Warehouse_Regrade_Items as $oShop_Warehouse_Regrade_Item)
					{
						$oShop_Item_Writeoff = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id);
						$oShop_Item_Incoming = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->incoming_shop_item_id);

						if (!is_null($oShop_Item_Writeoff) && !is_null($oShop_Item_Incoming))
						{
							$oShop_Item_Writeoff = $oShop_Item_Writeoff->shortcut_id
								? $oShop_Item_Writeoff->Shop_Item
								: $oShop_Item_Writeoff;

							$oShop_Item_Incoming = $oShop_Item_Incoming->shortcut_id
								? $oShop_Item_Incoming->Shop_Item
								: $oShop_Item_Incoming;

							$aReplace['Items'][] = array(
								'position' => $position++,
								'writeoff_name' => htmlspecialchars($oShop_Item_Writeoff->name),
								'writeoff_measure' => htmlspecialchars($oShop_Item_Writeoff->Shop_Measure->name),
								'writeoff_currency' => htmlspecialchars($oShop_Item_Writeoff->Shop_Currency->name),
								'writeoff_price' => $oShop_Warehouse_Regrade_Item->writeoff_price,
								'incoming_name' => htmlspecialchars($oShop_Item_Incoming->name),
								'incoming_measure' => htmlspecialchars($oShop_Item_Incoming->Shop_Measure->name),
								'incoming_currency' => htmlspecialchars($oShop_Item_Incoming->Shop_Currency->name),
								'incoming_price' => $oShop_Warehouse_Regrade_Item->incoming_price,
								'count' => $oShop_Warehouse_Regrade_Item->count
							);

							$aReplace['total_count']++;
						}
					}

					$this->_oPrintlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warehouse_Regrade);
				}
			}
		}

		return $this;
	}

	protected function _print()
	{
		$this->_oPrintlayout_Controller->execute()->downloadFile();

		exit();
	}
}