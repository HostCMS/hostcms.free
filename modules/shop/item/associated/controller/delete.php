<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Associated_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$associated_item_id = intval(Core_Array::getGet('associated_item_id'));

		if ($associated_item_id)
		{
			$oShop_Item_Associated = Core_Entity::factory('Shop_Item_Associated')->getById($associated_item_id);

			!is_null($oShop_Item_Associated)
				&& $oShop_Item_Associated->delete();

			$this->_Admin_Form_Controller->addMessage(
				"<script>$('.associated-item-table tr#{$associated_item_id}').remove();</script>"
			);
		}

		return TRUE;
	}
}