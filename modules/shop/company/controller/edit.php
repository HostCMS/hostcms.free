<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Company Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Company_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Load object's fields when object has been set
	 * После установки объекта загружаются данные о его полях
	 * @param object $object
	 * @return Shop_Company_Controller_Edit
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		// Основная вкладка
		$oMainTab = $this->getTab('main');

		// Добавляем вкладки
		$this
			->addTabAfter($oTabManagers = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Shop_Company.tabManagers'))
				->name('Managers'),
			$oMainTab)
			->addTabAfter($oTabContacts = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Shop_Company.tabContacts'))
				->name('Contacts'),
			$oTabManagers)
			->addTabAfter($oTabBankingDetails = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Shop_Company.tabBankingDetails'))
				->name('BankingDetails'),
			$oTabContacts)
			->addTabAfter($oTabGUID = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Shop_Company.guid'))
				->name('GUID'),
			$oTabBankingDetails);

		$oTabManagers
			->add($oTabManagersRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oTabContacts
			->add($oTabContactsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabContactsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabContactsRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oTabBankingDetails
			->add($oTabBankingDetailsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$oTabGUID
			->add($oTabGUIDRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			// Managers
			->move($this->getField('legal_name'), $oTabManagers)
			->move($this->getField('accountant_legal_name'), $oTabManagers)
			// Contacts
			->move($this->getField('address'), $oTabContacts)
			->move($this->getField('phone'), $oTabContacts)
			->move($this->getField('fax'), $oTabContacts)
			->move($this->getField('site'), $oTabContacts)
			->move($this->getField('email'), $oTabContacts)
			// BankingDetails
			->move($this->getField('tin'), $oTabBankingDetails)
			->move($this->getField('kpp'), $oTabBankingDetails)
			->move($this->getField('psrn'), $oTabBankingDetails)
			->move($this->getField('okpo'), $oTabBankingDetails)
			->move($this->getField('okved'), $oTabBankingDetails)
			->move($this->getField('bic'), $oTabBankingDetails)
			->move($this->getField('current_account'), $oTabBankingDetails)
			->move($this->getField('correspondent_account'), $oTabBankingDetails)
			->move($this->getField('bank_name'), $oTabBankingDetails)
			->move($this->getField('bank_address'), $oTabBankingDetails)
			// GUID
			->move($this->getField('guid'), $oTabGUID)
		;

		$oTabManagers->move($this->getField('legal_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabManagersRow1);
		$oTabManagers->move($this->getField('accountant_legal_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabManagersRow1);

		$oTabContacts->move($this->getField('address')->divAttr(array('class' => 'form-group col-xs-12')),$oTabContactsRow1);

		$oTabContacts->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabContactsRow2);
		$oTabContacts->move($this->getField('fax')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabContactsRow2);

		$oTabContacts->move($this->getField('site')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabContactsRow3);
		$oTabContacts->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabContactsRow3);

		$oTabBankingDetails->move($this->getField('tin')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow1);
		$oTabBankingDetails->move($this->getField('kpp')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow1);

		$oTabBankingDetails->move($this->getField('psrn')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow2);
		$oTabBankingDetails->move($this->getField('okpo')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow2);

		$oTabBankingDetails->move($this->getField('okved')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow3);
		$oTabBankingDetails->move($this->getField('bic')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow3);

		$oTabBankingDetails->move($this->getField('current_account')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow4);
		$oTabBankingDetails->move($this->getField('correspondent_account')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow4);

		$oTabBankingDetails->move($this->getField('bank_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow5);
		$oTabBankingDetails->move($this->getField('bank_address')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow5);

		$oTabGUID->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')),$oTabGUIDRow1);

		$title = $this->_object->id
			? Core::_('Shop_Company.company_form_edit_title')
			: Core::_('Shop_Company.company_form_add_title');

		$this->title($title);

		return $this;
	}
}