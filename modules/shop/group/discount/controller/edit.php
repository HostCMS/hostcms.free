<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Group_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Group_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0, 'int'));

			$object->shop_id = $oShop_Group->shop_id;
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

		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Item.tab_description'))
			->name('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			// ->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$this->addTab($oMainTab);

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
			default:
				$caption = Core::_('Shop_Discount.item_discount_name');
				$options = $this->_fillDiscounts($this->_object->shop_id);
				$name = 'shop_discount_id';

				$title = $this->_object->id
					? Core::_('Shop_Discount.item_discount_edit_form_title', $this->_object->name, FALSE)
					: Core::_('Shop_Discount.item_discount_add_form_title');
			break;
			case 'shop_bonus':
				$caption = Core::_('Shop_Bonus.item_bonus_name');
				$options = $this->_fillBonuses($this->_object->shop_id);
				$name = 'shop_bonus_id';

				$title = $this->_object->id
					? Core::_('Shop_Bonus.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Bonus.add_title');
			break;
			case 'shop_gift':
				$caption = Core::_('Shop_Gift.item_gift_name');
				$options = $this->_fillGifts($this->_object->shop_id);
				$name = 'shop_gift_id';

				$title = $this->_object->id
					? Core::_('Shop_Gift.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Gift.add_title');
			break;
		}

		$oMainRow1->add(Admin_Form_Entity::factory('Select')
			->caption($caption)
			->options($options)
			->name($name)
			->value($this->_object->id));

		$this->title($title);

		return $this;
	}

	/**
	 * Fill discounts list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	protected function _fillDiscounts($iShopId)
	{
		$aReturn = array();

		$aShop_Discounts = Core_Entity::factory('Shop', $iShopId)->Shop_Discounts->findAll(FALSE);
		foreach ($aShop_Discounts as $oShop_Discount)
		{
			$aReturn[$oShop_Discount->id] = $oShop_Discount->getOptions();
		}

		return $aReturn;
	}

	/**
	 * Fill bonuses list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	protected function _fillBonuses($iShopId)
	{
		$aReturn = array(" … ");

		$aShop_Bonuses = Core_Entity::factory('Shop', $iShopId)->Shop_Bonuses->findAll(FALSE);
		foreach ($aShop_Bonuses as $oShop_Bonus)
		{
			$aReturn[$oShop_Bonus->id] = $oShop_Bonus->name;
		}

		return $aReturn;
	}

	/**
	 * Fill bonuses list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	protected function _fillGifts($iShopId)
	{
		$aReturn = array(" … ");

		$aShop_Gifts = Core_Entity::factory('Shop', $iShopId)->Shop_Gifts->findAll(FALSE);
		foreach ($aShop_Gifts as $oShop_Gift)
		{
			$aReturn[$oShop_Gift->id] = $oShop_Gift->name;
		}

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Group_Discount_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
				$shop_discount_id = Core_Array::getPost('shop_discount_id', 0, 'int');

				if ($shop_discount_id)
				{
					$oObject = Core_Entity::factory('Shop_Discount', $shop_discount_id);
					if (is_null($oShop_Group->Shop_Group_Discounts->getByDiscountId($oObject->id)))
					{
						$oShop_Group->add($oObject)->clearCache();

						// Fast filter
						// if ($oShop_Item->Shop->filter)
						// {
						// 	$oShop_Filter_Controller = new Shop_Filter_Controller($oShop_Item->Shop);
						// 	$oShop_Filter_Controller->fill($oShop_Item);
						// }
					}
				}
			break;
			case 'shop_bonus':
				$shop_bonus_id = Core_Array::getPost('shop_bonus_id', 0, 'int');

				if ($shop_bonus_id)
				{
					$oObject = Core_Entity::factory('Shop_Bonus', $shop_bonus_id);
					is_null($oShop_Group->Shop_Group_Bonuses->getByBonusId($oObject->id))
						&& $oShop_Group->add($oObject)->clearCache();
				}
			break;
			case 'shop_gift':
				$shop_gift_id = Core_Array::getPost('shop_gift_id', 0, 'int');

				if ($shop_gift_id)
				{
					$oObject = Core_Entity::factory('Shop_Gift', $shop_gift_id);
					is_null($oShop_Group->Shop_Group_Gifts->getByGiftId($oObject->id))
						&& $oShop_Group->add($oObject)->clearCache();
				}
			break;
		}

		//parent::_applyObjectProperty();
		parent::_deleteAutosave();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}