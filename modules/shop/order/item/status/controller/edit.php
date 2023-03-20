<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Status Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Item_Status_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->shop_id = Core_Array::getGet('shop_id', 0);
			$object->parent_id = Core_Array::getGet('parent_id', 0);
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
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab->delete($this->getField('parent_id'));

		$oSelect_Statuses = Admin_Form_Entity::factory('Select');
		$oSelect_Statuses
			->options(
				array(' … ') + self::getSelectOptions($this->_object->shop_id, 0, $this->_object->id)
			)
			->name('parent_id')
			->value($this->_object->parent_id)
			->caption(Core::_('Shop_Order_Item_Status.parent_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-6'));

		$oMainRow2->add($oSelect_Statuses);

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('canceled')->class('form-control colored-danger times'), $oMainRow3);

		// $oMainTab->move($this->getField('shop_order_status_id'), $oMainRow4);

		$oMainTab->delete($this->getField('shop_order_status_id'));

		$oDropdownlistStatuses = Admin_Form_Entity::factory('Dropdownlist')
			->options(Shop_Order_Status_Controller_Edit::getDropdownlistOptions($this->_object->shop_id))
			->name('shop_order_status_id')
			->value($this->_object->shop_order_status_id)
			->caption(Core::_('Shop_Order_Item_Status.shop_order_status_id'))
			->divAttr(array('class' => 'form-group col-sm-4 col-xs-6'));

		$oMainRow4->add($oDropdownlistStatuses);

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5);

		$this->title($this->_object->id
			? Core::_('Shop_Order_Item_Status.edit_title', $this->_object->name, FALSE)
			: Core::_('Shop_Order_Item_Status.add_title')
		);

		return $this;
	}

	/**
	 * Array of Shop_Order_Item_Statuses tree
	 * @var NULL|array
	 */
	static protected $_statusesTree = NULL;

	static protected function _getStatusesTree($iShopId, $parent_id)
	{
		$oShop = Core_Entity::factory('Shop', $iShopId);

		if (is_null(self::$_statusesTree))
		{
			self::$_statusesTree = array();

			$oShop_Order_Item_Statuses = $oShop->Shop_Order_Item_Statuses->findAll(FALSE);
			foreach ($oShop_Order_Item_Statuses as $oShop_Order_Item_Status)
			{
				self::$_statusesTree[$oShop_Order_Item_Status->parent_id][] = $oShop_Order_Item_Status;
			}
		}

		return isset(self::$_statusesTree[$parent_id])
			? self::$_statusesTree[$parent_id]
			: array();
	}

	/**
	 * Create visual tree of the statuses
	 * @param int $iParentId parent cell ID
	 * @param boolean $bExclude exclude cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function getSelectOptions($iShopId, $iParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iLevel = intval($iLevel);

		$aReturn = array();

		// Дочерние элементы
		$aShop_Order_Item_Statuses = self::_getStatusesTree($iShopId, $iParentId);
		foreach ($aShop_Order_Item_Statuses as $childrenType)
		{
			if ($bExclude != $childrenType->id)
			{
				$aReturn[$childrenType->id] = str_repeat('  ', $iLevel) . $childrenType->name;
				$aReturn += self::getSelectOptions($iShopId, $childrenType->id, $bExclude, $iLevel + 1);
			}
		}

		return $aReturn;
	}

	/**
	 * Create visual tree of the statuses for dropdownlist
	 * @param int $iParentId parent cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function getDropdownlistOptions($iShopId, $iParentId = 0, $iLevel = 0)
	{
		$iLevel = intval($iLevel);

		$aReturn = array(array('value' => Core::_('Shop_Order.notStatus'), 'color' => '#aebec4'));

		// $oShop_Order_Item_Status_Parent = Core_Entity::factory('Shop_Order_Item_Status', $iParentId);

		// Дочерние элементы
		$aShop_Order_Item_Statuses = self::_getStatusesTree($iShopId, $iParentId);

		foreach ($aShop_Order_Item_Statuses as $childrenStatus)
		{
			$aReturn[$childrenStatus->id] = array(
				'value' => $childrenStatus->name,
				'color' => $childrenStatus->color,
				'icon' => 'fa ' . ($childrenStatus->canceled ? 'fa-times-circle' : 'fa-circle') . ' fa-dropdownlist',
				'level' => $iLevel
			);

			$aReturn += self::getDropdownlistOptions($iShopId, $childrenStatus->id, $iLevel + 1);
		}

		return $aReturn;
	}
}