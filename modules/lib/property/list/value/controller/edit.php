<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Property_List_Value Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lib_Property_List_Value_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		// При добавлении объекта
		if (!$object->id)
		{
			$object->lib_property_id = Core_Array::getGet('property_id');
		}

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Lib_Property_List_Value.form_edit_value')
			: Core::_('Lib_Property_List_Value.form_add_value'));

		return $this;
	}
}
