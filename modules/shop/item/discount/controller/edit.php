<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

			$object->shop_id = $oShopItem->Shop->id;
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

		$oShop = $this->_object->Shop;

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
			default:
				$caption = Core::_('Shop_Discount.item_discount_name');
				$options = $this->_fillDiscounts($oShop->id);
				$name = 'shop_discount_id';
			break;
			case 'shop_bonus':
				$caption = Core::_('Shop_Bonus.item_bonus_name');
				$options = $this->_fillBonuses($oShop->id);
				$name = 'shop_bonus_id';
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
			->divAttr(array('class' => 'form-group col-xs-12'))
		);

		$title = $this->_object->id
			? Core::_('Shop_Discount.item_discount_edit_form_title', $this->_object->name)
			: Core::_('Shop_Discount.item_discount_add_form_title');

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
				$shop_discount_id = Core_Array::getPost('shop_discount_id', 0);

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
				$shop_bonus_id = Core_Array::getPost('shop_bonus_id', 0);

				if ($shop_bonus_id)
				{
					$oObject = Core_Entity::factory('Shop_Bonus', $shop_bonus_id);
					is_null($oShop_Item->Shop_Item_Bonuses->getByBonusId($oObject->id))
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