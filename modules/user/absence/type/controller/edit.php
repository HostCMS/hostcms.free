<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Absence_Type_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Absence_Type_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('abbr')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2);

		$this->title($this->_object->id
			? Core::_('User_Absence_Type.edit_title', $this->_object->name, FALSE)
			: Core::_('User_Absence_Type.add_title')
		);

		return $this;
	}
}