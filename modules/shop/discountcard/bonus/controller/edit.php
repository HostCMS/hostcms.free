<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Bonus Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Bonus_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->shop_discountcard_id = Core_Array::getGet('shop_discountcard_id');
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
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oMainTab
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'))->format(array('minlen' => array('value' => 1))), $oMainRow1)
			->move($this->getField('expired')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'))->format(array('minlen' => array('value' => 1))), $oMainRow1)
			->move($this->getField('amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow2)
			->move($this->getField('written_off')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow2)
		;

		$oAdditionalTab->delete($this->getField('shop_discountcard_bonus_type_id'));

		// Тип зачисления по умолчанию
		if (!$this->_object->id)
		{
			$oShop_Discountcard_Bonus_Type = $this->_object->Shop_Discountcard->Shop->Shop_Discountcard_Bonus_Types->getDefault();
			!is_null($oShop_Discountcard_Bonus_Type)
				&& $this->_object->shop_discountcard_bonus_type_id = $oShop_Discountcard_Bonus_Type->id;
		}

		$oDropdownlistStatuses = Admin_Form_Entity::factory('Dropdownlist')
			->options(Shop_Discountcard_Bonus_Type_Controller_Edit::getDropdownlistOptions())
			->name('shop_discountcard_bonus_type_id')
			->value($this->_object->shop_discountcard_bonus_type_id)
			->caption(Core::_('Shop_Discountcard_Bonus.shop_discountcard_bonus_type_id'))
			->divAttr(array('class' => 'form-group col-md-3 col-sm-4 col-xs-6'));

		$oMainRow3->add($oDropdownlistStatuses);

		$oMainTab
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow4)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')), $oMainRow3);

		$title = $this->_object->id
			? Core::_('Shop_Discountcard_Bonus.edit_title')
			: Core::_('Shop_Discountcard_Bonus.add_title');

		$this->title($title);

		return $this;
	}
}