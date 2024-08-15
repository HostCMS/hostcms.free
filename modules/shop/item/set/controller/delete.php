<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Set_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$set_item_id = intval(Core_Array::getGet('set_item_id'));

		if ($set_item_id)
		{
			$oShop_Item_Set = Core_Entity::factory('Shop_Item_Set')->getById($set_item_id);

			if (!is_null($oShop_Item_Set))
			{
				$oShop_Item_Set->delete();
			}

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$this->_Admin_Form_Controller->addMessage(
				"<script>$('#{$windowId} .set-item-table tr#{$set_item_id}').remove();</script>"
			);
		}

		return TRUE;
	}
}