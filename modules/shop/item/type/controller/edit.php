<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop Item Type Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Type_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			? Core::_('Shop_Item_Type.edit_title', $this->_object->name, FALSE)
			: Core::_('Shop_Item_Type.add_title')
		);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->class('form-control input-lg')->format(array('minlen' => array('value' => 1)))->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$oMainTab->delete($this->getField('type'));

		$oAdditionalTab
			->delete($this->getField('account'))
			->delete($this->getField('income_account'))
			->delete($this->getField('expenditure_account'));

		$oTypesSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Item_Type.type'))
			->options(array(
				0 => Core::_('Shop_Item_Type.type0'),
				1 => Core::_('Shop_Item_Type.type1'),
			))
			->name('type')
			->value($this->_object->type)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oTypesSelect);

		$aOptions = Chartaccount_Controller::getOptions();

		$oAccountSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Item_Type.account'))
			->options($aOptions)
			->name('account')
			->value($this->_object->account)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oAccountSelect);

		$oIncomeAccountSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Item_Type.income_account'))
			->options($aOptions)
			->name('income_account')
			->value($this->_object->income_account)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oIncomeAccountSelect);

		$oExpenditureAccountSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Item_Type.expenditure_account'))
			->options($aOptions)
			->name('expenditure_account')
			->value($this->_object->expenditure_account)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oExpenditureAccountSelect);

		return $this;
	}
}