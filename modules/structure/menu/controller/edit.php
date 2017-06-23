<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Structure_Menu Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Menu_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$title = is_null($this->_object->id)
			? Core::_('Structure_Menu.add_title')
			: Core::_('Structure_Menu.edit_title');

		$this->title($title);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name'), $oMainRow1);

		$oMainTab
			->move($this->getField('sorting'), $oMainRow2);

		return $this;
	}
}