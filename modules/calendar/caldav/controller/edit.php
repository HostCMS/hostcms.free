<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar CALDAV Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Calendar_Caldav_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('icon')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('driver')->divAttr(array('class' => 'form-group col-xs-6 col-sm-3')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-6 col-sm-3')), $oMainRow2)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);

		$this->title(
			$this->_object->id
				? Core::_('Calendar_Caldav.edit_title')
				: Core::_('Calendar_Caldav.add_title')
		);

		return $this;
	}
}