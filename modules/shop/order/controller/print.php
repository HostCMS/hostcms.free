<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Controller_Print
 *
 * @package HostCMS 6
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Print extends Printlayout_Controller_Print
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
						'tax' => '',
						'Items' => array(),
					);

					$position = 1;

					$total_sum = Shop_Controller::instance()->round($oShop_Order->getAmount());

					$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll();

					foreach ($aShop_Order_Items as $oShop_Order_Item)
					{
						$oShop_Item = $oShop_Order_Item->Shop_Item;

						$amount = $oShop_Order_Item->quantity * $oShop_Order_Item->price;

						$aReplace['Items'][] = array(
							'position' => $position++,
							'name' => htmlspecialchars($oShop_Order_Item->name),
							'measure' => htmlspecialchars($oShop_Item->Shop_Measure->name),
							'price' => $oShop_Order_Item->price,
							'quantity' => $oShop_Order_Item->quantity,
							'amount' => $amount
						);

						$aReplace['total_count']++;
					}

					$aReplace['total_sum'] = $total_sum;
					$aReplace['total_sum_in_words'] = Core_Str::ucfirst(Core_Inflection::instance('ru')->numberInWords($total_sum));

					$Printlayout_Controller = new Printlayout_Controller($oPrintlayout);
					$Printlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Order)
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