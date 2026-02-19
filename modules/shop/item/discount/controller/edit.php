<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Item_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0, 'int'));

			$object->shop_id = $oShopItem->shop_id;
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
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

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

		$oMainRow2->add(Admin_Form_Entity::factory('Radiogroup')
			->radio(array(
				'—',
				Core::_("Shop_Discount.shop_apply_modification_discount"),
				Core::_("Shop_Discount.shop_not_apply_modification_discount")
			))
			->ico(
				array(
					'fa-minus-circle',
					'fa-check',
					'fa-ban'
				)
			)
			->name('apply_for_modifications')
			->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group'))
		);

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
	 * @hostcms-event Shop_Item_Discount_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
				$shop_discount_id = Core_Array::getPost('shop_discount_id', 0, 'int');

				if ($shop_discount_id)
				{
					$oObject = Core_Entity::factory('Shop_Discount', $shop_discount_id);
					if (is_null($oShop_Item->Shop_Item_Discounts->getByDiscountId($oObject->id)))
					{
						$oShop_Item->add($oObject)->clearCache();

						// Fast filter
						if ($oShop_Item->Shop->filter)
						{
							$oShop_Filter_Controller = new Shop_Filter_Controller($oShop_Item->Shop);
							$oShop_Filter_Controller->fill($oShop_Item);
						}
					}
				}
			break;
			case 'shop_bonus':
				$shop_bonus_id = Core_Array::getPost('shop_bonus_id', 0, 'int');

				if ($shop_bonus_id)
				{
					$oObject = Core_Entity::factory('Shop_Bonus', $shop_bonus_id);
					is_null($oShop_Item->Shop_Item_Bonuses->getByBonusId($oObject->id))
						&& $oShop_Item->add($oObject)->clearCache();
				}
			break;
			case 'shop_gift':
				$shop_gift_id = Core_Array::getPost('shop_gift_id', 0, 'int');

				if ($shop_gift_id)
				{
					$oObject = Core_Entity::factory('Shop_Gift', $shop_gift_id);
					is_null($oShop_Item->Shop_Item_Gifts->getByGiftId($oObject->id))
						&& $oShop_Item->add($oObject)->clearCache();
				}
			break;
		}

		// Применять/удалять у модификаций
		if (Core_Array::getPost('apply_for_modifications'))
		{
			$aModifications = $oShop_Item->Modifications->findAll(FALSE);
		}

		switch (Core_Array::getPost('apply_for_modifications'))
		{
			case 1:
				foreach ($aModifications as $oModification)
				{
					switch ($modelName)
					{
						case 'shop_discount':
							if (is_null($oModification->Shop_Item_Discounts->getByDiscountId($oObject->id)))
							{
								$oModification->add($oObject)->clearCache();

								// Fast filter
								if ($oModification->Shop->filter)
								{
									$oShop_Filter_Controller = new Shop_Filter_Controller($oModification->Shop);
									$oShop_Filter_Controller->fill($oModification);
								}
							}
						break;
						case 'shop_bonus':
							if (is_null($oModification->Shop_Item_Bonuses->getByBonusId($oObject->id)))
							{
								$oModification->add($oObject)->clearCache();
							}
						break;
						case 'shop_gift':
							if (is_null($oModification->Shop_Item_Gifts->getByGiftId($oObject->id)))
							{
								$oModification->add($oObject)->clearCache();
							}
						break;
					}
				}
			break;
			case 2:
				foreach ($aModifications as $oModification)
				{
					$oModification->remove($oObject)->clearCache();
				}
			break;
		}

		//parent::_applyObjectProperty();
		parent::_deleteAutosave();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}