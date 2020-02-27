<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Bonus Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oMainTab
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow1)
			->move($this->getField('expired')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow1)
			->move($this->getField('amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow1)
			->move($this->getField('written_off')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow1)
		;

		$title = $this->_object->id
			? Core::_('Shop_Discountcard_Bonus.edit_title')
			: Core::_('Shop_Discountcard_Bonus.add_title');

		$this->title($title);

		return $this;
	}
}