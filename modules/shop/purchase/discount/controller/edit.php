<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Purchase_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab->delete($this->getField('shop_currency_id'));
		$oMainTab
			->delete($this->getField('mode'))
			->delete($this->getField('value'))
			->delete($this->getField('type'))
			->delete($this->getField('position'));

		$oMainRow1->add(Admin_Form_Entity::factory('Div')
			->class('col-xs-12 col-sm-4 col-md-3 col-lg-2 input-group select-group')
			->add(Admin_Form_Entity::factory('Code')
				->html('<div class="caption">' . Core::_('Shop_Purchase_Discount.value') . '</div>')
			)
			->add(Admin_Form_Entity::factory('Input')
				->name('value')
				->value($this->_object->value)
				->divAttr(array('class' => ''))
				->class('form-control semi-bold')
			)
			->add(Admin_Form_Entity::factory('Select')
				->name('type')
				->divAttr(array('class' => ''))
				->options(array(
					'%',
					$this->_object->Shop->Shop_Currency->sign
				))
				->value($this->_object->type)
				->class('form-control input-group-addon')
			)
		);

		$oPositionSelectField = Admin_Form_Entity::factory('Select')
			->id('position')
			->name('position')
			->caption(Core::_('Shop_Purchase_Discount.position'))
			->options(array(0 => Core::_('Shop_Purchase_Discount.total-amount'), 2 => 2, 3 => 3, 4 => 4, 5 => 5))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'))
			->value($this->_object->position);

		$oMainRow1->add($oPositionSelectField);

		$oMainTab
			->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('min_amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow2)
			->move($this->getField('max_amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow2);

		$oMainRow2->add(
			Admin_Form_Entity::factory('Select')
				->name('shop_currency_id')
				->caption(Core::_('Shop_Purchase_Discount.shop_currency_id'))
				->options(
					Shop_Controller::fillCurrencies()
				)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'))
				->value(
					is_null($this->_object->id)
						? $this->_object->Shop->shop_currency_id
						: $this->_object->shop_currency_id
				)
		);

		$oMainRow3->add(
			Admin_Form_Entity::factory('Radiogroup')
				->name('mode')
				->value($this->_object->mode)
				->radio(array(
					Core::_('Shop_Purchase_Discount.order_discount_case_and'),
					Core::_('Shop_Purchase_Discount.order_discount_case_or'),
					Core::_('Shop_Purchase_Discount.order_discount_case_accumulative')
				))
				->ico(
					array(
						'fa-chevron-up',
						'fa-chevron-down',
						'fa-shopping-cart',
					)
				)
				->divAttr(array('class' => 'form-group col-xs-12'))
		);

		$oMainTab->move($this->getField('min_count')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow4);
		$oMainTab->move($this->getField('max_count')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow4);

		$oMainTab
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5)
			->move($this->getField('coupon')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6);

		$this->title($this->_object->id
			? Core::_('Shop_Purchase_Discount.edit_order_discount_form_title', $this->_object->name)
			: Core::_('Shop_Purchase_Discount.add_order_discount_form_title'));

		return $this;
	}
}