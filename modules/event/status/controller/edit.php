<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Status_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Status_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/* Type of bot module */
	const TYPE = 0;

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title($this->_object->id
			? Core::_('Event_Status.edit_title', $this->_object->name, FALSE)
			: Core::_('Event_Status.add_title')
		);

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
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('final')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow2)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);

		if ($this->_object->id && Core::moduleIsActive('bot'))
		{
			$oModule = Core_Entity::factory('Module')->getByPath('event');

			$this->addTabAfter(
				Bot_Controller::getBotTab($oModule, $this->_object->id, self::TYPE), $oMainTab
			);
		}

		return $this;
	}
}