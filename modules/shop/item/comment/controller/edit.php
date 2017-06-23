<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Comment Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Comment_Controller_Edit extends Comment_Controller_Edit
{
	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Item_Comment_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$Comment_Shop_Item = $this->_object->Comment_Shop_Item;

		if (is_null($Comment_Shop_Item->id))
		{
			$Comment_Shop_Item->shop_item_id = intval($this->_object->parent_id
				? Core_Entity::factory('Comment', $this->_object->parent_id)->Comment_Shop_Item->shop_item_id
				: Core_Array::getRequest('shop_item_id'));
			$Comment_Shop_Item->save();
		}

		// Cached tags
		$Comment_Shop_Item->Shop_Item->clearCache();
		
		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}