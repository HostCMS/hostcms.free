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
class Shop_Item_Associated_Controller_Unset extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', intval(Core_Array::getGet('shop_item_id', 0)));

		$oShop_Item->Shop_Item_Associateds->deleteAll(FALSE);

		$this->_Admin_Form_Controller->addMessage(
			Core_Message::get(Core::_('Shop_Item.shop_item_associated_unset'))
		);

		return TRUE;
	}
}