<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Discount_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		switch (get_class($this->_object))
		{
			case 'Shop_Bonus_Model':
				$oEntity = $oShop_Item->Shop_Item_Bonuses->getByShop_bonus_id($this->_object->id);
			break;
			case 'Shop_Discount_Model':
				$oEntity = $oShop_Item->Shop_Item_Discounts->getByShop_discount_id($this->_object->id);
			break;
			default:
				$oEntity = NULL;
		}

		!is_null($oEntity) && $oEntity->delete();
	}
}