<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			//->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDaysBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('description')->rows(7)->wysiwyg(Core::moduleIsActive('wysiwyg'));
		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);
		
		$oMainTab->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oMainRow3);
		$oMainTab->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oMainRow3);
		$oMainTab->move($this->getField('start_time')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2')), $oMainRow3);
		$oMainTab->move($this->getField('end_time')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2')), $oMainRow3);
		
		$oDaysBlock
			->add(Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_("Shop_Discount.days"))
			)
			->add($oDaysBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));
		
		$oMainTab->move($this->getField('day1')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
		$oMainTab->move($this->getField('day2')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
		$oMainTab->move($this->getField('day3')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
		$oMainTab->move($this->getField('day4')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
		$oMainTab->move($this->getField('day5')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-3')), $oDaysBlockRow1);
		$oMainTab->move($this->getField('day6')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2'))->class('colored-danger'), $oDaysBlockRow1);
		$oMainTab->move($this->getField('day7')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2'))->class('colored-danger'), $oDaysBlockRow1);
		
		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow5);
		$oMainTab->move($this->getField('public')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow5);

		$oMainTab->move($this->getField('url')->divAttr(array('class' => 'form-group col-xs-12'))->placeholder('https://'), $oMainRow6);

		$oMainTab->delete($this->getField('type'));

		$oTypeSelectField = Admin_Form_Entity::factory('Select');

		$oTypeSelectField
			->name('type')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-2'))
			->caption(Core::_('Shop_Discount.type'))
			->options(array(
				Core::_('Shop_Discount.form_edit_affiliate_values_type_percent'),
				Core::_('Shop_Discount.form_edit_affiliate_values_type_summ'))
			)
			->value($this->_object->type);

		$oMainRow1->add($oTypeSelectField);
		$oMainTab->move($this->getField('value')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-2')), $oMainRow1);

		$oMainTab->move($this->getField('coupon')
			->divAttr(array('class' => 'form-group margin-top-21 col-xs-12 col-sm-6 col-md-3 col-lg-3'))->onclick("$.toggleCoupon(this)"), $oMainRow1);

		$hidden = !$this->_object->coupon
			? ' hidden'
			: '';

		$oMainTab->move($this->getField('coupon_text')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-5' . $hidden)), $oMainRow1);

		$title = $this->_object->id
			? Core::_('Shop_Discount.item_discount_edit_form_title', $this->_object->name)
			: Core::_('Shop_Discount.item_discount_add_form_title');

		$this->title($title);

		return $this;
	}
}