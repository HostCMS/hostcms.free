<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Group Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Group_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$title = $this->_object->id
			? Core::_('User_Group.ua_edit_user_type_form_title')
			: Core::_('User_Group.ua_add_user_type_form_title');

		$oMainTab = $this->getTab('main');

		// Удаляем стандартный <input>
		$oMainTab->delete($this->getField('site_id'));

		$oUser_Controller_Edit = new User_Controller_Edit($this->_Admin_Form_Action);
		
		// Селектор с сайтами
		$oSelect_Sites = Admin_Form_Entity::factory('Select');
		$oSelect_Sites
			->options($oUser_Controller_Edit->fillSites())
			->name('site_id')
			->value($this->_object->site_id)
			->caption(Core::_('User_Group.site_id'));

		$oMainTab->addAfter($oSelect_Sites, $this->getField('comment'));

		$this->title($title);

		return $this;
	}

}