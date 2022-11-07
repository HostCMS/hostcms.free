<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Setting_Controller_Print
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Setting_Controller_Print extends Printlayout_Controller_Print
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
				$shop_price_setting_id = key($aChecked[0]);

				$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting')->getById($shop_price_setting_id);

				if (!is_null($oShop_Price_Setting))
				{
					$shop_price_id = intval(Core_Array::getGet('shop_price_id', 0));

					$oShop_Price = Core_Entity::factory('Shop_Price')->getById($shop_price_id);
					$priceName = !is_null($oShop_Price)
						? htmlspecialchars($oShop_Price->name)
						: '';

					$aReplace = array(
						// Core_Meta
						'this' => $oShop_Price_Setting,
						'shop_price_setting' => $oShop_Price_Setting,
						'company' => $oShop_Price_Setting->Shop->Shop_Company,
						'shop' => $oShop_Price_Setting->Shop,
						'user' => $oShop_Price_Setting->User,
						'price_name' => $priceName,
						'Items' => array(),
					);

					$position = 1;

					$aShop_Price_Setting_Items = $oShop_Price_Setting->Shop_Price_Setting_Items->getAllByShop_price_id($shop_price_id);

					foreach ($aShop_Price_Setting_Items as $oShop_Price_Setting_Item)
					{
						$oShop_Item = $oShop_Price_Setting_Item->Shop_Item;

						$node = new stdClass();

						$node->position = $position++;
						$node->name = htmlspecialchars((string) $oShop_Item->name);
						$node->measure = htmlspecialchars((string) $oShop_Item->Shop_Measure->name);
						$node->price = $oShop_Price_Setting_Item->new_price;

						$aReplace['Items'][] = $node;
					}

					$this->_oPrintlayout_Controller
						->replace($aReplace)
						->driver($oPrintlayout_Driver)
						->entity($oShop_Price_Setting);
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