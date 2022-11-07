<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warrant Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warrant_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('type');

		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		if ($object->type == 0)
		{
			$this
				->addSkipColumn('tax');
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		// $oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		// $oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		// $oAdmin_Form_Controller = $this->_Admin_Form_Controller;

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteuserRow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			// ->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'))->class('form-control input-lg'), $oMainRow1);

		// Дата документа меняется только если документа не проведен.
		// $this->_object->id && $this->_object->posted
		// 	&& $this->getField('datetime')->readonly('readonly');

		if (Core::moduleIsActive('siteuser'))
		{
			$oAdditionalTab->delete($this->getField('siteuser_id'));

			$oSiteuser = !is_null(Core_Array::getGet('siteuser_id'))
				? Core_Entity::factory('Siteuser')->find(Core_Array::getGet('siteuser_id'))
				: $this->_object->Siteuser;

			$options = !is_null($oSiteuser->id)
				? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
				: array(0);

			$oSiteuserSelect = Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Warrant.siteuser_id'))
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				// ->divAttr(array('class' => 'form-group col-xs-12'));
				->divAttr(array('class' => 'col-xs-12'));

			$oSiteuserRow
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-6 col-sm-6 col-md-3 no-padding siteuser-select2')
						->add($oSiteuserSelect)
				);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);

			$icons = Siteuser_Controller_Edit::addSiteuserRepresentativeAvatars($oSiteuser);

			$oSiteuserRow
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-6 col-sm-6 col-md-3 margin-top-21 siteuser-representative-list')
						->add(Admin_Form_Entity::factory('Code')->html($icons))
				);
		}

		$oAdditionalTab->delete($this->getField('shop_cashflow_id'));

		$oSelectCashflows = Admin_Form_Entity::factory('Select')
			->id('shop_cashflow_id')
			->options(self::fillCashflowList())
			->name('shop_cashflow_id')
			->value($this->_object->shop_cashflow_id)
			->caption(Core::_('Shop_Warrant.shop_cashflow_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainRow2->add($oSelectCashflows);

		// Удаляем поле с идентификатором ответственного сотрудника
		$oAdditionalTab->delete($this->getField('user_id'));

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('user_id')
			->options($aSelectResponsibleUsers)
			->name('user_id')
			->value($this->_object->user_id)
			->caption(Core::_('Shop_Warrant.user_id'))
			->divAttr(array('class' => ''));

		$oScriptResponsibleUsers = Admin_Form_Entity::factory('Script')
			->value('$("#' . $windowId . ' #user_id").selectUser({
					placeholder: "",
					language: "' . Core_I18n::instance()->getLng() . '",
					dropdownParent: $("#' . $windowId . '")
				});'
			);

		$oMainRow2
			->add(
				Admin_Form_Entity::factory('Div')
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-5 col-lg-4')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21')), $oMainRow2)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('reason')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);

		$this->_object->type && $oMainTab->move($this->getField('tax')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);

		$title = $this->_object->id
			? Core::_('Shop_Warrant.form_edit', $this->_object->number)
			: Core::_('Shop_Warrant.form_add');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warrant_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));
		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		parent::_applyObjectProperty();

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill cashflow list
	 * @return array
	 */
	static public function fillCashflowList()
	{
		$aReturn = array(' … ');
		// $aReturn = array();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$oShop_Cashflows = $oSite->Shop_Cashflows;
		$oShop_Cashflows->queryBuilder()
			->clearOrderBy()
			->orderBy('shop_cashflows.id');

		$aShop_Cashflows = $oShop_Cashflows->findAll(FALSE);
		foreach ($aShop_Cashflows as $oShop_Cashflow)
		{
			$aReturn[$oShop_Cashflow->id] = $oShop_Cashflow->name . ' [' . $oShop_Cashflow->id . ']';
		}

		return $aReturn;
	}
}