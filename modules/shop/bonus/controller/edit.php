<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Bonus Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Bonus_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('description')
			->rows(7)
			->wysiwyg(Core::moduleIsActive('wysiwyg'));

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);
		$oMainTab->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow3);
		$oMainTab->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow3);

		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);

		$oMainTab->delete($this->getField('type'));

		$oTypeSelectField = Admin_Form_Entity::factory('Select');

		$oTypeSelectField
			->name('type')
			->divAttr(array('class' => 'form-group col-lg-2 col-md-4 col-sm-6 col-xs-6'))
			->caption(Core::_('Shop_Bonus.type'))
			->options(array(
				Core::_('Shop_Bonus.form_edit_affiliate_values_type_percent'),
				Core::_('Shop_Bonus.form_edit_affiliate_values_type_summ'))
			)
			->value($this->_object->type);

		$oMainRow1->add($oTypeSelectField);
		$oMainTab->move($this->getField('value')
			->divAttr(array('class' => 'form-group col-lg-2 col-md-4 col-sm-6 col-xs-6')), $oMainRow1);

		$title = $this->_object->id
			? Core::_('Shop_Bonus.edit_title', $this->_object->name)
			: Core::_('Shop_Bonus.add_title');

		$this->title($title);

		return $this;
	}
}