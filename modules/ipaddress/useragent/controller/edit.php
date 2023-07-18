<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Useragent_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Useragent_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			? Core::_('Ipaddress_Useragent.edit_title')
			: Core::_('Ipaddress_Useragent.add_title')
		);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('useragent')->divAttr(array('class' => 'form-group col-xs-12 col-sm-9')), $oMainRow1);

		$oMainTab->delete($this->getField('condition'));

		$oMainRow1->add(
			Admin_Form_Entity::factory('Select')
				->name('condition')
				->caption(Core::_('Ipaddress_Useragent.condition'))
				->options(Ipaddress_Useragent_Controller::getConditions())
				->value($this->_object->condition)
				->class('form-control input-lg')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
		);

		$oMainTab
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

		return $this;
	}
}