<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount Type Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Chartaccount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title(
			$this->_object->id
				? Core::_('Chartaccount.edit_title', $this->_object->name, FALSE)
				: Core::_('Chartaccount.add_title')
		);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('code')->class('form-control input-lg')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('name')->class('form-control input-lg')->format(array('minlen' => array('value' => 1)))->divAttr(array('class' => 'form-group col-xs-12 col-sm-9')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

		$oMainTab->delete($this->getField('type'));

		$oTypesSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount.type'))
			->options(array(
				0 => Core::_('Chartaccount.type0'),
				1 => Core::_('Chartaccount.type1'),
				2 => Core::_('Chartaccount.type2')
			))
			->name('type')
			->value($this->_object->type)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow3->add($oTypesSelect);

		$oMainTab
			->move($this->getField('folder')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')), $oMainRow3)
			->move($this->getField('currency')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4)
			->move($this->getField('quantitative')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow5)
			->move($this->getField('off_balance')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow6);

		$oMainTab
			->delete($this->getField('sc0'))
			->delete($this->getField('sc1'))
			->delete($this->getField('sc2'));

		$aOptions = Chartaccount_Controller::getSubcountList();

		$oSc0Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount.sc0'))
			->options($aOptions)
			->name('sc0')
			->value($this->_object->sc0)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oSc1Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount.sc1'))
			->options($aOptions)
			->name('sc1')
			->value($this->_object->sc1)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oSc2Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount.sc2'))
			->options($aOptions)
			->name('sc2')
			->value($this->_object->sc2)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainRow7
			->add($oSc0Select)
			->add($oSc1Select)
			->add($oSc2Select);

		return $this;
	}
}