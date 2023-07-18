<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Discount_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Producer_Discount_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_producer_id = Core_Array::getGet('shop_producer_id', 0, 'int');

		if ($shop_producer_id && $this->_object->id)
		{
			$oShop_Producer = Core_Entity::factory('Shop_Producer', $shop_producer_id);

			switch (get_class($this->_object))
			{
				case 'Shop_Bonus_Model':
					$oEntity = $oShop_Producer->Shop_Producer_Bonuses->getByShop_bonus_id($this->_object->id);
				break;
				case 'Shop_Discount_Model':
					$oEntity = $oShop_Producer->Shop_Producer_Discounts->getByShop_discount_id($this->_object->id);
				break;
				default:
					$oEntity = NULL;
			}

			if (!is_null($oEntity))
			{
				$oEntity->delete();
				// $oShop_Group->clearCache();
			}
		}
	}
}