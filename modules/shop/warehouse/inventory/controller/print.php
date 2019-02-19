<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Inventory_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Inventory_Controller_Print extends Printlayout_Controller_Print
{
	protected function _print()
	{
		$oPrintlayout = Core_Entity::factory('Printlayout')->getById($this->printlayout);

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

						'total_count' => 0,
						'Items' => array(),

						/*'{number}' => $oShop_Warehouse_Inventory->number,
						'{date}' => Core_Date::sql2date($oShop_Warehouse_Inventory->datetime),
						'{datetime}' => Core_Date::sql2datetime($oShop_Warehouse_Inventory->datetime),
						'{company_name}' => $oShop_Warehouse_Inventory->Shop_Warehouse->Shop->Shop_Company->name,
						'{warehouse_name}' => $oShop_Warehouse_Inventory->Shop_Warehouse->name,
						'{shop_name}' => $oShop_Warehouse_Inventory->Shop_Warehouse->Shop->name,
						'{total_count}' => 0,
						'{total_sum}' => 0,
						'{inv_total_sum}' => 0,
						'{total_sum_in_words}' => '',
						'{Items}' => array(),*/
					);

					$position = 1;
					$inv_total_sum = $total_sum = 0;

					$aShop_Warehouse_Inventory_Items = $oShop_Warehouse_Inventory->Shop_Warehouse_Inventory_Items->findAll();

					foreach ($aShop_Warehouse_Inventory_Items as $oShop_Warehouse_Inventory_Item)
					{
						$oShop_Item = $oShop_Warehouse_Inventory_Item->Shop_Item;

						$inv_amount = $oShop_Warehouse_Inventory_Item->count * $oShop_Item->price;

						$factWarehouseCount = $factWarehouseSum = '0.00';

						$oShop_Warehouse_Items = Core_Entity::factory('Shop_Warehouse_Item');
						$oShop_Warehouse_Items->queryBuilder()
							->where('shop_warehouse_items.shop_warehouse_id', '=', $oShop_Warehouse_Inventory->Shop_Warehouse->id)
							->where('shop_warehouse_items.shop_item_id', '=', $oShop_Item->id)
							->limit(1);

						$aShop_Warehouse_Items = $oShop_Warehouse_Items->findAll();

						if (isset($aShop_Warehouse_Items[0]))
						{
							$factWarehouseCount = $aShop_Warehouse_Items[0]->count;
						}

						$factWarehouseSum = $factWarehouseCount * $oShop_Item->price;

						$aReplace['Items'][] = array(
							'position' => $position++,
							'name' => htmlspecialchars($oShop_Item->name),
							'measure' => htmlspecialchars($oShop_Item->Shop_Measure->name),
							'price' => $oShop_Item->price,
							'inv_quantity' => $oShop_Warehouse_Inventory_Item->count,
							'inv_amount' => $inv_amount,
							'quantity' => $factWarehouseCount,
							'amount' => $factWarehouseSum
						);

						$inv_total_sum += $inv_amount;
						$total_sum += $factWarehouseSum;

						$aReplace['total_count']++;
					}


					$inv_total_sum = Shop_Controller::instance()->round($inv_total_sum);
					$total_sum = Shop_Controller::instance()->round($total_sum);

					$aReplace['inv_total_sum'] = $inv_total_sum;
					$aReplace['total_sum'] = $total_sum;
					$aReplace['total_sum_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($inv_total_sum));

					$Printlayout_Controller = new Printlayout_Controller($oPrintlayout);
					$Printlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warehouse_Inventory)
						->execute()
						->download()
						//->print()
						;

					exit();
				}
			}
		}

		return $this;
	}
}