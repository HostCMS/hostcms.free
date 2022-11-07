<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Bonus_Type Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Bonus_Type_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
		;

		$oMainTab
			->move($this->getField('name')->class('form-control input-lg')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3)
			->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 margin-top-21')), $oMainRow3);

		$title = $this->_object->id
			? Core::_('Shop_Discountcard_Bonus_Type.edit_title', $this->_object->name)
			: Core::_('Shop_Discountcard_Bonus_Type.add_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Discountcard_Bonus_Type_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$this->_object->default
			&& $this->_object->changeDefaultStatus();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Create visual tree of the statuses for dropdownlist
	 * @param int $iParentId parent cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function getDropdownlistOptions()
	{
		$aReturn = array(array('value' => Core::_('Shop_Order.notStatus'), 'color' => '#aebec4'));

		$aShop_Discountcard_Bonus_Types = Core_Entity::factory('Shop_Discountcard_Bonus_Type')->findAll(FALSE);

		foreach ($aShop_Discountcard_Bonus_Types as $oShop_Discountcard_Bonus_Type)
		{
			$aReturn[$oShop_Discountcard_Bonus_Type->id] = array(
				'value' => $oShop_Discountcard_Bonus_Type->name,
				'color' => $oShop_Discountcard_Bonus_Type->color,
				'icon' => 'fa fa-circle'
			);
		}

		return $aReturn;
	}
}