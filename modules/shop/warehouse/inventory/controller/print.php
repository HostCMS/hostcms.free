<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Inventory_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Inventory_Controller_Print extends Printlayout_Controller_Print
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
				$shop_warehouse_inventory_id = key($aChecked[0]);

				$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory')->getById($shop_warehouse_inventory_id);

				if (!is_null($oShop_Warehouse_Inventory))
				{
					$aReplace = array(
						// Core_Meta
						'this' => $oShop_Warehouse_Inventory,
						'company' => $oShop_Warehouse_Inventory->Shop_Warehouse->Shop->Shop_Company,
						'shop_warehouse' => $oShop_Warehouse_Inventory->Shop_Warehouse,
						'shop' => $oShop_Warehouse_Inventory->Shop_Warehouse->Shop,
						'user' => $oShop_Warehouse_Inventory->User,
						'total_count' => 0,
						'Items' => array(),
					);

					$position = 1;
					$inv_amount_total = $amount_total = 0;

					$aShop_Warehouse_Inventory_Items = $oShop_Warehouse_Inventory->Shop_Warehouse_Inventory_Items->findAll();

					$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

					foreach ($aShop_Warehouse_Inventory_Items as $oShop_Warehouse_Inventory_Item)
					{
						$oShop_Item = $oShop_Warehouse_Inventory_Item->Shop_Item;

						$rest = $oShop_Warehouse_Inventory->Shop_Warehouse->getRest($oShop_Item->id, $oShop_Warehouse_Inventory->datetime);
						is_null($rest) && $rest = 0;

						$old_price = $Shop_Price_Entry_Controller->getPrice(0, $oShop_Item->id, $oShop_Warehouse_Inventory->datetime);

						is_null($old_price)
							&& $old_price = $oShop_Item->price;

						$fact_amount = Shop_Controller::instance()->round($rest * $old_price);

						$inv_amount = Shop_Controller::instance()->round($oShop_Warehouse_Inventory_Item->count * $old_price);

						$aReplace['Items'][] = array(
							'position' => $position++,
							'name' => htmlspecialchars($oShop_Item->name),
							'measure' => htmlspecialchars($oShop_Item->Shop_Measure->name),
							'price' => $old_price,
							'quantity' => $rest,
							'amount' => $fact_amount,
							'inv_quantity' => $oShop_Warehouse_Inventory_Item->count,
							'inv_amount' => $inv_amount
						);

						$inv_amount_total += $inv_amount;
						$amount_total += $rest;

						$aReplace['total_count']++;
					}

					$aReplace['amount'] = Shop_Controller::instance()->round($amount_total);
					$aReplace['inv_amount'] = Shop_Controller::instance()->round($inv_amount_total);

					$aReplace['amount_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($aReplace['amount']));
					$aReplace['inv_amount_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($aReplace['inv_amount']));

					$this->_oPrintlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warehouse_Inventory);
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