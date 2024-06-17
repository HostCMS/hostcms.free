<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout Driver Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Printlayout_Driver_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('driver')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow2);

		$this->title($this->_object->id
			? Core::_('Printlayout_Driver.edit_title', $this->_object->name, FALSE)
			: Core::_('Printlayout_Driver.add_title')
		);

		return $this;
	}
}