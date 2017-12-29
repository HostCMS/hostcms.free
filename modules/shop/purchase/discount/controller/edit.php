<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);
		$oMainTab->move($this->getField('coupon')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);
		$oMainTab->move($this->getField('value')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);

		$oAdditionalTab->delete($this->getField('shop_currency_id'));
		$oMainTab->delete($this->getField('mode'));

		$oMainTab
			->delete($this->getField('type'))
			->delete($this->getField('position'));

		$oTypeSelectField = Admin_Form_Entity::factory('Select')
			->name('type')
			->caption(Core::_('Shop_Purchase_Discount.type'))
			->options(array(
				Core::_('Shop_Purchase_Discount.form_edit_affiliate_values_type_percent'),
				Core::_('Shop_Purchase_Discount.form_edit_affiliate_values_type_summ'))
			)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->value($this->_object->type);
			//->onchange('$.changeDiscountPosition(this)');

			/*
				changeDiscountPosition: function(object)
				{
					var jOption = $(object).find('option:selected'),
						id = jOption.val(),
						parentDiv = $("#position").parent();

					id == 1 ? parentDiv.addClass("hidden") : parentDiv.removeClass("hidden");
				},
			*/

		$oPositionSelectField = Admin_Form_Entity::factory('Select')
			->id('position')
			->name('position')
			->caption(Core::_('Shop_Purchase_Discount.position'))
			->options(array(0 => Core::_('Shop_Purchase_Discount.total-amount'), 2 => 2, 3 => 3, 4 => 4, 5 => 5))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->value($this->_object->position);

		$oMainRow3
			->add($oTypeSelectField)
			->add($oPositionSelectField);

		$oMainTab->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow4);
		$oMainTab->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow4);

		$oMainTab->move($this->getField('min_amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow5);
		$oMainTab->move($this->getField('max_amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow5);

		$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

		$oMainRow5->add(
			Admin_Form_Entity::factory('Select')
				->name('shop_currency_id')
				->caption(Core::_('Shop_Purchase_Discount.shop_currency_id'))
				->options($Shop_Controller_Edit->fillCurrencies())
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->value(
					is_null($this->_object->id)
						? $this->_object->Shop->shop_currency_id
						: $this->_object->shop_currency_id
				)
		);

		$oMainRow6->add(
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

		$oMainTab->move($this->getField('min_count')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow7);
		$oMainTab->move($this->getField('max_count')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow7);

		$this->title($this->_object->id
			? Core::_('Shop_Purchase_Discount.edit_order_discount_form_title')
			: Core::_('Shop_Purchase_Discount.add_order_discount_form_title'));

		return $this;
	}
}