<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Group_Discount_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Group_Discount_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_group_id = Core_Array::getGet('shop_group_id', 0, 'int');

		if ($shop_group_id && $this->_object->id)
		{
			$oShop_Group = Core_Entity::factory('Shop_Group', $shop_group_id);

			switch (get_class($this->_object))
			{
				case 'Shop_Bonus_Model':
					$oEntity = $oShop_Group->Shop_Group_Bonuses->getByShop_bonus_id($this->_object->id);
				break;
				case 'Shop_Discount_Model':
					$oEntity = $oShop_Group->Shop_Group_Discounts->getByShop_discount_id($this->_object->id);
				break;
				case 'Shop_Gift_Model':
					$oEntity = $oShop_Group->Shop_Group_Gifts->getByShop_gift_id($this->_object->id);
				break;
				default:
					$oEntity = NULL;
			}

			if (!is_null($oEntity))
			{
				$oEntity->delete();
				$oShop_Group->clearCache();
			}
		}
	}
}