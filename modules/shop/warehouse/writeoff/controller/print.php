<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Writeoff_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Writeoff_Controller_Print extends Printlayout_Controller_Print
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
				$shop_warehouse_writeoff_id = key($aChecked[0]);

				$oShop_Warehouse_Writeoff = Core_Entity::factory('Shop_Warehouse_Writeoff')->getById($shop_warehouse_writeoff_id);

				if (!is_null($oShop_Warehouse_Writeoff))
				{
					$aReplace = array(
						// Core_Meta
						'this' => $oShop_Warehouse_Writeoff,
						'company' => $oShop_Warehouse_Writeoff->Shop_Warehouse->Shop->Shop_Company,
						'shop_warehouse' => $oShop_Warehouse_Writeoff->Shop_Warehouse,
						'shop' => $oShop_Warehouse_Writeoff->Shop_Warehouse->Shop,

						'type' => Core::_('Shop_Warehouse_Writeoff.title'),
						'reason' => $oShop_Warehouse_Writeoff->reason,
						'total_count' => 0,
						'Items' => array(),
					);

					$position = 1;
					$total_sum = 0;

					$aShop_Warehouse_Writeoff_Items = $oShop_Warehouse_Writeoff->Shop_Warehouse_Writeoff_Items->findAll();

					foreach ($aShop_Warehouse_Writeoff_Items as $oShop_Warehouse_Writeoff_Item)
					{
						$oShop_Item = $oShop_Warehouse_Writeoff_Item->Shop_Item;

						$amount = $oShop_Warehouse_Writeoff_Item->count * $oShop_Warehouse_Writeoff_Item->price;

						$aReplace['Items'][] = array(
							'position' => $position++,
							'name' => htmlspecialchars($oShop_Item->name),
							'measure' => htmlspecialchars($oShop_Item->Shop_Measure->name),
							'price' => $oShop_Warehouse_Writeoff_Item->price,
							'quantity' => $oShop_Warehouse_Writeoff_Item->count,
							'amount' => $amount
						);

						$aReplace['total_count']++;

						$total_sum += $amount;
					}

					$total_sum = Shop_Controller::instance()->round($total_sum);

					$aReplace['total_sum'] = $total_sum;
					$aReplace['total_sum_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($total_sum));

					$Printlayout_Controller = new Printlayout_Controller($oPrintlayout);
					$Printlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warehouse_Writeoff)
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