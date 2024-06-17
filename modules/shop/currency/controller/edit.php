<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Currency Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Currency_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name'), $oMainRow1)
			->move($this->getField('exchange_rate')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('date')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow2)
			->move($this->getField('sign')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow3);

		$oMainTab->delete($this->getField('sign_position'));

		$oMainRow3->add(Admin_Form_Entity::factory('Select')
			->name('sign_position')
			->caption(Core::_('Shop_Currency.sign_position'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'))
			->options(array(
				0 => Core::_('Shop_Currency.after_number'),
				1 => Core::_('Shop_Currency.before_number')
			))
			->value($this->_object->sign_position)
		);

		$oMainTab
			->move($this->getField('decimal_separator')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow3)
			->move($this->getField('thousands_separator')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow3)
			->move($this->getField('hide_zeros')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow3)
			->move($this->getField('code')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow4);

		$this->title($this->_object->id
			? Core::_('Shop_Currency.currency_edit_form_title', $this->_object->name, FALSE)
			: Core::_('Shop_Currency.currency_add_form_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Currency_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		// Reset default for other currencies
		if (Core_Array::get($this->_formValues, 'default'))
		{
			$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();
			foreach ($aShop_Currencies as $oShop_Currency)
			{
				if ($oShop_Currency->default)
				{
					$oShop_Currency->default = 0;
					$oShop_Currency->save();
				}
			}
		}

		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}