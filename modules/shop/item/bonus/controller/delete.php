<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Bonus_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));
		$oShopBonus = Core_Entity::factory('Shop_Bonus', $this->_object->id);
		$oShopItemBonus = $oShopItem->Shop_Item_Bonuses->getByBonusId($oShopBonus->id);

		if(!is_null($oShopItemBonus))
		{
			$oShopItemBonus->delete();
		}
	}
}