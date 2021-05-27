<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Skin.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Shop_Item_Property_Controller_Tab extends Skin_Bootstrap_Property_Controller_Tab
{
	/**
	 * Get properties
	 * @return array
	 */
	protected function _getProperties()
	{
		$shop_group_id = $this->_object->modification_id
			? $this->_object->Modification->shop_group_id
			: $this->_object->shop_group_id;

		// Properties
		$oProperties = $this->linkedObject->Properties;
		$oProperties
			->queryBuilder()
			->join('shop_item_property_for_groups', 'shop_item_property_for_groups.shop_item_property_id', '=', 'shop_item_properties.id')
			->where('shop_item_property_for_groups.shop_id', '=', $this->_object->shop_id)
			->where('shop_item_property_for_groups.shop_group_id', '=', $shop_group_id);

		return $oProperties;
	}
}