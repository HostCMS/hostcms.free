<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Codetype_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Codetype_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title($this->_object->id
			? Core::_('Shop_Codetype.edit_title', $this->_object->name, FALSE)
			: Core::_('Shop_Codetype.add_title')
		);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
			->move($this->getField('code')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow3);

		return $this;
	}
}