<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Language Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Language_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title(is_null($this->_object->id)
			? Core::_('Admin_Language.form_add_forms_add_language_title')
			: Core::_('Admin_Language.form_add_forms_edit_language_title', $this->_object->name, FALSE)
		);

		return $this;
	}
}