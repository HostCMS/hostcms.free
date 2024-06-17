<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Controller_Code.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Order_Item_Controller_Code extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		// Существующие
		$aShop_Order_Item_Codes = Core_Entity::factory('Shop_Order_Item_Code')->getAllByShop_order_item_id($this->_object->id, FALSE);
		foreach ($aShop_Order_Item_Codes as $oShop_Order_Item_Code)
		{
			$sCode = trim(Core_Array::getPost('shop_order_item_code' . $oShop_Order_Item_Code->id));

			if (isset($_POST['shop_order_item_code' . $oShop_Order_Item_Code->id]) && $sCode != '')
			{
				$oShop_Order_Item_Code->code = $sCode;
				$oShop_Order_Item_Code->shop_codetype_id = intval(Core_Array::getPost('shop_codetype' . $oShop_Order_Item_Code->id));
				$oShop_Order_Item_Code->save();
			}
			else
			{
				$oShop_Order_Item_Code->delete();
			}
		}

		// Новые
		$aNew_Shop_Order_Item_Codes = Core_Array::getPost('shop_order_item_code', array());
		$aNew_Shop_Order_Item_Types = Core_Array::getPost('shop_codetype', array());

		foreach ($aNew_Shop_Order_Item_Codes as $key => $code)
		{
			$sCode = trim($code);

			if ($sCode != '')
			{
				$oShop_Order_Item_Code = Core_Entity::factory('Shop_Order_Item_Code');
				$oShop_Order_Item_Code->shop_order_item_id = $this->_object->id;
				$oShop_Order_Item_Code->code = $sCode;
				$oShop_Order_Item_Code->shop_codetype_id = intval(Core_Array::get($aNew_Shop_Order_Item_Types, $key));
				$oShop_Order_Item_Code->save();
			}
		}

		$count = $this->_object->Shop_Order_Item_Codes->getCount(FALSE);

		ob_start();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		Core_Html_Entity::factory('Script')
			->value('$(function() {
				$("#codes-' . $this->_object->id . '").modal("hide");

				$("#' . $windowId . ' #code-badge' . $this->_object->id . '").text("' . $count . '");
			})')
			->execute();

		Core_Message::show(Core::_('Shop_Order_Item.setCodes_success'));

		$this->addMessage(ob_get_clean());

		return TRUE;
	}
}