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
class Shop_Filter_Seo_Property_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$shop_filter_seo_property_id = intval(Core_Array::getGet('shop_filter_seo_property_id'));

		if ($shop_filter_seo_property_id)
		{
			$oShop_Filter_Seo_Property = Core_Entity::factory('Shop_Filter_Seo_Property')->getById($shop_filter_seo_property_id);

			!is_null($oShop_Filter_Seo_Property)
				&& $oShop_Filter_Seo_Property->delete();

			$this->_Admin_Form_Controller->addMessage(
				"<script>$('.filter-conditions div#{$shop_filter_seo_property_id}').parents('.dd').remove(); $.loadNestable();</script>"
			);
		}

		return TRUE;
	}
}