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
class Chartaccount_Closure_Period_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
				? Core::_('Chartaccount_Closure_Period.edit_title')
				: Core::_('Chartaccount_Closure_Period.add_title')
		);

		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'))->class('form-control input-lg'), $oMainRow1);

		$oAdditionalTab->delete($this->getField('company_id'));

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aCompanies = $oSite->Companies->findAll();

		$aTmp = [];

		foreach($aCompanies as $oCompany)
		{
			$aTmp[$oCompany->id] = $oCompany->name;
		}

		$oSelect_Companies = Admin_Form_Entity::factory('Select')
			->options($aTmp)
			->id('company_id')
			->name('company_id')
			->value($this->_object->company_id)
			->caption(Core::_('Chartaccount_Closure_Period.company_id'))
			->divAttr(array('class'=>'form-group col-xs-12 col-md-6'))
			;

		$oMainRow2->add($oSelect_Companies);

		$oMainTab
			->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21')), $oMainRow2)
			->move($this->getField('сlosure_cost_accounting')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			->move($this->getField('financial_result')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4)
			->move($this->getField('balance_reformation')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6);
			;

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Chartaccount_Operation_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->addSkipColumn('posted');

		parent::_applyObjectProperty();

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		Core_Array::getPost('posted')
			? $this->_object->post()
			: $this->_object->unpost();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}