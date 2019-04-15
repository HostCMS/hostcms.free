<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Print extends Printlayout_Controller_Print
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
				$shop_order_id = key($aChecked[0]);

				$oShop_Order = Core_Entity::factory('Shop_Order')->getById($shop_order_id);

				if (!is_null($oShop_Order))
				{
					$oCompany = $oShop_Order->company_id
						? $oShop_Order->Shop_Company
						: $oShop_Order->Shop->Shop_Company;

					$aReplace = array(
						// Core_Meta
						'this' => $oShop_Order,
						'shop_order' => $oShop_Order,
						'company' => $oCompany,
						'shop' => $oShop_Order->Shop,
						'total_count' => 0,
						'Items' => array(),
					);

					$position = 1;

					$total_amount = $total_quantity = $total_tax = 0;	

					$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll();
					foreach ($aShop_Order_Items as $oShop_Order_Item)
					{
						$oShop_Item = $oShop_Order_Item->Shop_Item;

						$amount = Shop_Controller::instance()->round($oShop_Order_Item->quantity * $oShop_Order_Item->price);

						$tax = Shop_Controller::instance()->round($amount * $oShop_Order_Item->rate / 100);

						$aReplace['Items'][] = array(
							'position' => $position++,
							'item' => $oShop_Item,
							'id' => $oShop_Item->id,
							'name' => htmlspecialchars($oShop_Order_Item->name),
							'measure' => htmlspecialchars($oShop_Item->Shop_Measure->name),
							'okei' => htmlspecialchars($oShop_Item->Shop_Measure->okei),
							'price' => $oShop_Order_Item->price,
							'quantity' => $oShop_Order_Item->quantity,
							'rate' => $oShop_Order_Item->rate,
							'rate%' => $oShop_Order_Item->rate ? $oShop_Order_Item->rate . '%' : '',
							'tax' => $tax,
							'amount' => $amount,
							'amount_tax_included' => Shop_Controller::instance()->round($amount + $tax)
						);

						$total_quantity += $oShop_Order_Item->quantity;
						$total_tax += $tax;
						$total_amount += $amount;

						$aReplace['total_count']++;
					}

					$aReplace['quantity'] = $total_quantity;
					$aReplace['tax'] = Shop_Controller::instance()->round($total_tax);
					$aReplace['amount'] = Shop_Controller::instance()->round($total_amount);
					$aReplace['amount_tax_included'] = Shop_Controller::instance()->round($oShop_Order->getAmount());
					$aReplace['amount_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($aReplace['amount_tax_included']));

					$this->_oPrintlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Order);
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