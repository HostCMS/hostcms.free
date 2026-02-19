<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Discount_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Item_Discount_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_item_id = Core_Array::getGet('shop_item_id', 0, 'int');

		if ($shop_item_id && $this->_object->id)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item', $shop_item_id);

			switch (get_class($this->_object))
			{
				case 'Shop_Bonus_Model':
					$oEntity = $oShop_Item->Shop_Item_Bonuses->getByShop_bonus_id($this->_object->id);
				break;
				case 'Shop_Discount_Model':
					$oEntity = $oShop_Item->Shop_Item_Discounts->getByShop_discount_id($this->_object->id);
				break;
				case 'Shop_Gift_Model':
					$oEntity = $oShop_Item->Shop_Item_Gifts->getByShop_gift_id($this->_object->id);
				break;
				default:
					$oEntity = NULL;
			}

			if (!is_null($oEntity))
			{
				$oEntity->delete();
				$oShop_Item->clearCache();
			}
		}
	}
}