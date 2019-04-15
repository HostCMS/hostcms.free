<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Movement_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Movement_Controller_Print extends Printlayout_Controller_Print
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
				$shop_warehouse_movement_id = key($aChecked[0]);

				$oShop_Warehouse_Movement = Core_Entity::factory('Shop_Warehouse_Movement')->getById($shop_warehouse_movement_id);

				if (!is_null($oShop_Warehouse_Movement))
				{
					$aReplace = array(
						// Core_Meta
						'this' => $oShop_Warehouse_Movement,
						'company' => $oShop_Warehouse_Movement->Source_Shop_Warehouse->Shop->Shop_Company,
						'shop_warehouse' => $oShop_Warehouse_Movement->Source_Shop_Warehouse,
						'destination_shop_warehouse' => $oShop_Warehouse_Movement->Destination_Shop_Warehouse,
						'shop' => $oShop_Warehouse_Movement->Source_Shop_Warehouse->Shop,
						'user' => $oShop_Warehouse_Movement->User,
						'type' => Core::_('Shop_Warehouse_Movement.title'),
						'total_count' => 0,
						'Items' => array(),
					);

					$position = 1;
					$total_amount = 0;

					$aShop_Warehouse_Movement_Items = $oShop_Warehouse_Movement->Shop_Warehouse_Movement_Items->findAll();

					$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

					foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
					{
						$oShop_Item = $oShop_Warehouse_Movement_Item->Shop_Item;

						$old_price = $Shop_Price_Entry_Controller->getPrice(0, $oShop_Item->id, $oShop_Warehouse_Movement->datetime);

						is_null($old_price)
							&& $old_price = $oShop_Item->price;

						$amount = Shop_Controller::instance()->round($oShop_Warehouse_Movement_Item->count * $old_price);

						$aReplace['Items'][] = array(
							'position' => $position++,
							'name' => htmlspecialchars($oShop_Item->name),
							'measure' => htmlspecialchars($oShop_Item->Shop_Measure->name),
							'currency' => htmlspecialchars($oShop_Item->Shop_Currency->name),
							'price' => $old_price,
							'quantity' => $oShop_Warehouse_Movement_Item->count,
							'amount' => $amount
						);

						$aReplace['total_count']++;

						$total_amount += $amount;
					}

					$aReplace['amount'] = Shop_Controller::instance()->round($total_amount);
					$aReplace['amount_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($aReplace['amount']));

					$this->_oPrintlayout_Controller = new Printlayout_Controller($oPrintlayout);
					$this->_oPrintlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warehouse_Movement);
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