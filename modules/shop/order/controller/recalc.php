<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Recalc extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		// Replace shop_delivery_id
		$this->_object->shop_delivery_id = Core_Array::getPost('shop_delivery_id');
		$this->_object->shop_country_id = Core_Array::getPost('shop_country_id');
		$this->_object->shop_country_location_id = Core_Array::getPost('shop_country_location_id');
		$this->_object->shop_country_location_city_id = Core_Array::getPost('shop_country_location_city_id');
		$this->_object->shop_country_location_city_area_id = Core_Array::getPost('shop_country_location_city_area_id');
		$this->_object->save();

		$this->_object->recalcDelivery();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		Core::factory('Admin_Form_Entity_Code')
			->html('
				<script>
					$("#' . $windowId . ' #sum").val(' . $this->_object->getAmount() . ');
					$("#' . $windowId . ' #shop_delivery_condition_id").val(' . $this->_object->shop_delivery_condition_id . ');
				</script>
			')
			->execute();

		Core_Message::show(Core::_('Shop_Order.recalc_delivery_success'));

		return TRUE;
	}
}