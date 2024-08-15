<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sites.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Site_Controller_AccountInfo extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('user_id');

		parent::setObject($object);

		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption('Main')
			->name('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->addTab($oMainTab);

		$this->title(Core::_('Site.accountinfo_title'));

		$oMainRow1->add(Admin_Form_Entity::factory('Input')
			->caption(Core::_("Site.accountinfo_login"))
			->divAttr(array('class'=>'form-group col-xs-12 col-sm-12 col-lg-6'))
			->name("HOSTCMS_USER_LOGIN")
			->value(defined('HOSTCMS_USER_LOGIN')
				? HOSTCMS_USER_LOGIN
				: ''
			)
		);

		$oMainRow2->add(Admin_Form_Entity::factory('Input')
			->caption(Core::_("Site.accountinfo_contract_number"))
			->divAttr(array('class'=>'form-group col-xs-6 col-sm-6 col-lg-3'))
			->name("HOSTCMS_CONTRACT_NUMBER")
			->value(defined('HOSTCMS_CONTRACT_NUMBER')
				? HOSTCMS_CONTRACT_NUMBER
				: ''
			)
		)
		->add(Admin_Form_Entity::factory('Input')
			->caption(Core::_("Site.accountinfo_pin_code"))
			->divAttr(array('class'=>'form-group col-xs-6 col-sm-6 col-lg-3'))
			->name("HOSTCMS_PIN_CODE")
			->value(defined('HOSTCMS_PIN_CODE')
				? HOSTCMS_PIN_CODE
				: ''
			)
			->placeholder('XXXX')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Site_Controller_AccountInfo.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		//parent::_applyObjectProperty();
		
		$secret_csrf = Core_Array::getPost('secret_csrf', '', 'trim');
		$this->_checkCsrf($secret_csrf);

		$oConstantLogin = Core_Entity::factory('Constant')->getByName('HOSTCMS_USER_LOGIN');
		$oConstantNumber = Core_Entity::factory('Constant')->getByName('HOSTCMS_CONTRACT_NUMBER');
		$oConstantPin = Core_Entity::factory('Constant')->getByName('HOSTCMS_PIN_CODE');

		if (is_null($oConstantLogin))
		{
			$oConstantLogin = Core_Entity::factory('Constant');
			$oConstantLogin->name = 'HOSTCMS_USER_LOGIN';
			$oConstantLogin->active = 1;
		}

		if (is_null($oConstantNumber))
		{
			$oConstantNumber = Core_Entity::factory('Constant');
			$oConstantNumber->name = 'HOSTCMS_CONTRACT_NUMBER';
			$oConstantNumber->active = 1;
		}

		if (is_null($oConstantPin))
		{
			$oConstantPin = Core_Entity::factory('Constant');
			$oConstantPin->name = 'HOSTCMS_PIN_CODE';
			$oConstantPin->active = 1;
		}

		$oConstantLogin->value = trim(Core_Array::getPost('HOSTCMS_USER_LOGIN'));
		$oConstantLogin->save();

		$oConstantNumber->value = trim(Core_Array::getPost('HOSTCMS_CONTRACT_NUMBER'));
		$oConstantNumber->save();

		$oConstantPin->value = trim(Core_Array::getPost('HOSTCMS_PIN_CODE'));
		$oConstantPin->save();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}