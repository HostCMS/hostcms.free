<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Language Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Language_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$title = is_null($this->_object->id)
			? Core::_('Admin_Language.form_add_forms_add_language_title')
			: Core::_('Admin_Language.form_add_forms_edit_language_title', $this->_object->name);

		$this->title($title);

		return $this;
	}
}