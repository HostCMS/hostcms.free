<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Address_Type_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Directory_Address_Type_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);
		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

		$this->title($this->_object->id
			? Core::_('Directory_Address_Type.edit_title')
			: Core::_('Directory_Address_Type.add_title')
		);

		return $this;
	}
}