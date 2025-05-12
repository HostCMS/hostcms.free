<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount Type Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Chartaccount_Operation_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
				? Core::_('Chartaccount_Operation.edit_title')
				: Core::_('Chartaccount_Operation.add_title')
		);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oEntryBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));
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
			->caption(Core::_('Chartaccount_Operation.company_id'))
			->divAttr(array('class'=>'form-group col-xs-12 col-md-6'))
			;

		$oMainRow2->add($oSelect_Companies);

		$oMainTab
			->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21')), $oMainRow2)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);
			;

		// if ($this->_object->id)
		// {
			$oEntryBlock
				->add(Admin_Form_Entity::factory('Div')
					->class('header bordered-palegreen')
					->value(Core::_('Chartaccount_Operation.entry_header'))
				)
				->add($oEntryRow1 = Admin_Form_Entity::factory('Div')->class('row'));

			$oDivItems = Admin_Form_Entity::factory('Div')
				->id($windowId . '-operation-items')
				->class('col-xs-12 margin-top-10')
				->add(
					$this->_object->id
						? $this->_addItems()
						: Admin_Form_Entity::factory('Code')->html(
							Core_Message::get(Core::_('Chartaccount_Operation.enable_after_save'), 'warning')
						)
				);

			$oEntryRow1->add($oDivItems);
		// }

		return $this;
	}

	/*
	 * Add shop documents
	 * @return Admin_Form_Entity
	 */
	protected function _addItems()
	{
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
		$windowId = $modalWindowId ? $modalWindowId : $this->_Admin_Form_Controller->getWindowId();

		// $document_id = Shop_Controller::getDocumentId($this->_object->id, $this->_object->getEntityType());

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				mainFormLocker.unlock();
				$.adminLoad({ path: hostcmsBackend + '/chartaccount/operation/item/index.php', additionalParams: 'company_id=" . $this->_object->company_id . "&chartaccount_operation_id=" . $this->_object->id . "&parentWindowId=" . $windowId . "&_module=0', windowId: '{$windowId}-operation-items', loadingScreen: false });
			});");
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Chartaccount_Operation_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->addSkipColumn('posted');

		$bAdd = is_null($this->_object->id);

		parent::_applyObjectProperty();

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		if ($bAdd)
		{
			ob_start();
			$this->_addItems()->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		Core_Array::getPost('posted')
			? $this->_object->post()
			: $this->_object->unpost();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}