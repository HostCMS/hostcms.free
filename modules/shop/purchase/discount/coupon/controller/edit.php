<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount_Coupon Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Purchase_Discount_Coupon_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'shop_purchase_discount_coupon':
				if (!$object->id)
				{
					// Generate Unique Random Coupon Code
					$object->generateCode();

					$object->shop_purchase_discount_coupon_dir_id = Core_Array::getGet('shop_purchase_discount_coupon_dir_id', 0);
				}
			break;
			case 'shop_purchase_discount_coupon_dir':
				if (!$object->id)
				{
					$object->shop_id = Core_Array::getGet('shop_id', 0);
					$object->parent_id = Core_Array::getGet('shop_purchase_discount_coupon_dir_id', 0);
				}
			break;
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

		$modelName = $this->_object->getModelName();

		$shop_id = Core_Array::getGet('shop_id', 0, 'int');

		switch ($modelName)
		{
			case 'shop_purchase_discount_coupon':

				$oAdditionalTab->delete($this->getField('shop_purchase_discount_id'));

				$aOptions = $this->_fillShopPurchaseDiscounts($shop_id);

				if (!count($aOptions))
				{
					throw new Core_Exception(Core::_('Shop_Purchase_Discount_Coupon.not_enough_discounts'), array(), 0, FALSE);
				}

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('shop_purchase_discount_coupon_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Purchase_Discount_Coupon.shop_purchase_discount_coupon_dir_id'))
					->options(array(' … ') + self::fillShopPurchaseDiscountCouponDir($shop_id))
					->name('shop_purchase_discount_coupon_dir_id')
					->value($this->_object->shop_purchase_discount_coupon_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-lg-3'));

				$oMainRow1->add($oGroupSelect);

				$oCouponSelect = Admin_Form_Entity::factory('Select')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
					->caption(Core::_('Shop_Purchase_Discount_Coupon.shop_purchase_discount_id'))
					->options(
						count($aOptions) ? $aOptions : array(' … ')
					)
					->name('shop_purchase_discount_id')
					->value($this->_object->shop_purchase_discount_id);

				$oMainTab->move($this->getField('text')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow1);
				$oMainRow1->add($oCouponSelect);
				$oMainTab->move($this->getField('count')->divAttr(array('class' => 'form-group col-xs-12 col-md-2')), $oMainRow1);

				$oMainTab
					->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow2)
					->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow2)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow2)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-md-3 margin-top-21')), $oMainRow2)
					;

				$title = $this->_object->id
					? Core::_('Shop_Purchase_Discount_Coupon.coupon_form_table_title_edit', $this->_object->name, FALSE)
					: Core::_('Shop_Purchase_Discount_Coupon.coupon_form_table_title_add');
			break;
			case 'shop_purchase_discount_coupon_dir':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('description')->rows(9)->wysiwyg(Core::moduleIsActive('wysiwyg'));
				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Purchase_Discount_Coupon_Dir.parent_id'))
					->options(array(' … ') + self::fillShopPurchaseDiscountCouponDir($shop_id, 0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

				$title = $this->_object->id
					? Core::_('Shop_Purchase_Discount_Coupon_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Purchase_Discount_Coupon_Dir.add_title');
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Redirect groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iShopId shop ID
	 * @param int $iDirParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopPurchaseDiscountCouponDir($iShopId, $iDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iDirParentId = intval($iDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_purchase_discount_coupon_dirs')
				->where('shop_id', '=', $iShopId)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aGroupTree[$iDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShopPurchaseDiscountCouponDir($iShopId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Fill discounts list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	protected function _fillShopPurchaseDiscounts($iShopId)
	{
		$oShopPurchaseDiscountCoupon = Core_Entity::factory('Shop_Purchase_Discount');

		$oShopPurchaseDiscountCoupon
			->queryBuilder()
			->where('shop_id', '=', $iShopId)
			->where('active', '=', 1)
			->orderBy('id', 'ASC');

		$aShopPurchaseDiscountCoupons = $oShopPurchaseDiscountCoupon->findAll(FALSE);

		$aReturn = array();
		foreach ($aShopPurchaseDiscountCoupons as $oShopPurchaseDiscountCoupon)
		{
			$aReturn[$oShopPurchaseDiscountCoupon->id] = $oShopPurchaseDiscountCoupon->name;
		}

		return $aReturn;
	}
}