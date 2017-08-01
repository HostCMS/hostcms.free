<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Comment Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Comment_Controller_Edit extends Comment_Controller_Edit
{
	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Informationsystem_Item_Comment_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$Comment_Informationsystem_Item = $this->_object->Comment_Informationsystem_Item;

		if (is_null($Comment_Informationsystem_Item->id))
		{
			$Comment_Informationsystem_Item->informationsystem_item_id = intval($this->_object->parent_id
				? Core_Entity::factory('Comment', $this->_object->parent_id)->Comment_Informationsystem_Item->informationsystem_item_id
				: Core_Array::getGet('informationsystem_item_id'));
			$Comment_Informationsystem_Item->save();
		}

		// Cached tags
		$Comment_Informationsystem_Item->Informationsystem_Item->clearCache();
		
		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}