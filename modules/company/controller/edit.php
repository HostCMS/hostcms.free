<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Load object's fields when object has been set
	 * @param object $object
	 * @return Company_Controller_Edit
	 */
	public function setObject($object)
	{
		/*$this
			->addSkipColumn('~address')
			->addSkipColumn('~phone')
			->addSkipColumn('~fax')
			->addSkipColumn('~site')
			->addSkipColumn('~email');*/

		parent::setObject($object);

		// Основная вкладка
		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		// Добавляем вкладки
		$this
			->addTabAfter($oTabBankingDetails = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Company.tabBankingDetails'))
				->name('BankingDetails'),
			$oMainTab);

		$oMainTab
			->add($oMainTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainTabRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oTabBankingDetails
			->add($oTabBankingDetailsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab
			->add($oAdditionalTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oAdditionalTabRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
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
			->move($this->getField('guid'), $oAdditionalTab);

		$oMainTab->move($this->getField('legal_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')),$oMainTabRow1);
		$oMainTab->move($this->getField('accountant_legal_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')),$oMainTabRow1);
		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')),$oMainTabRow1);

		// Адреса
		$oCompanyAddressesRow = Directory_Controller_Tab::instance('address')
			->title(Core::_('Directory_Address.addresses'))
			->relation($this->_object->Company_Directory_Addresses)
			->execute();

		$oMainTab->add($oCompanyAddressesRow);

		// Телефоны
		$oCompanyPhonesRow = Directory_Controller_Tab::instance('phone')
			->title(Core::_('Directory_Phone.phones'))
			->relation($this->_object->Company_Directory_Phones)
			->execute();

		$oMainTab->add($oCompanyPhonesRow);

		// Email'ы
		$oCompanyEmailsRow = Directory_Controller_Tab::instance('email')
			->title(Core::_('Directory_Email.emails'))
			->relation($this->_object->Company_Directory_Emails)
			->execute();

		$oMainTab->add($oCompanyEmailsRow);

		// Сайты
		$oCompanyWebsitesRow = Directory_Controller_Tab::instance('website')
			->title(Core::_('Directory_Website.sites'))
			->relation($this->_object->Company_Directory_Websites)
			->execute();

		$oMainTab->add($oCompanyWebsitesRow);

		$oAdmin_Form_Entity_Section = Admin_Form_Entity::factory('Section')
			->caption(Core::_('Company.sites'))
			->id('accordion_' . $object->id);

		$oMainTab->add($oAdmin_Form_Entity_Section);

		// Sites
		$aTmp = array();
		$aCompany_Sites = $object->Company_Sites->findAll(FALSE);
		foreach ($aCompany_Sites as $oCompany_Site)
		{
			$aTmp[] = $oCompany_Site->site_id;
		}

		$aSites = Core_Entity::factory('Site')->findAll();
		foreach ($aSites as $oSite)
		{
			$oAdmin_Form_Entity_Section->add($oCheckbox = Admin_Form_Entity::factory('Checkbox')
				->divAttr(array('class' => 'form-group col-xs-12 col-md-6 no-padding-left'))
				->name('site_' . $oSite->id)
				->caption($oSite->name)
			);

			in_array($oSite->id, $aTmp) && $oCheckbox->checked('checked');
		}

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

		$oAdditionalTab->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')),$oAdditionalTabRow1);

		$title = $this->_object->id
			? Core::_('Company.company_form_edit_title', $this->_object->name)
			: Core::_('Company.company_form_add_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Company_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this
			->addSkipColumn('phone')
			->addSkipColumn('fax')
			->addSkipColumn('site')
			->addSkipColumn('email');

		parent::_applyObjectProperty();

		$aTmp = array();

		$aCompany_Sites = $this->_object->Company_Sites->findAll(FALSE);
		foreach ($aCompany_Sites as $oCompany_Site)
		{
			if (Core_Array::getPost('site_' . $oCompany_Site->site_id))
			{
				$aTmp[] = $oCompany_Site->site_id;
			}
			else
			{
				$oCompany_Site->delete();
			}
		}

		$aSites = Core_Entity::factory('Site')->findAll(FALSE);
		foreach ($aSites as $oSite)
		{
			if (Core_Array::getPost('site_' . $oSite->id) && !in_array($oSite->id, $aTmp))
			{
				$oCompany_Site = Core_Entity::factory('Company_Site');
				$oCompany_Site->site_id = $oSite->id;
				$oCompany_Site->company_id = $this->_object->id;
				$oCompany_Site->save();
			}
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		Directory_Controller_Tab::instance('address')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('email')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('phone')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('website')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}