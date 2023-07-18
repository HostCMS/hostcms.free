<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Producer_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$oShop_Producer = Core_Entity::factory('Shop_Producer', Core_Array::getGet('shop_producer_id', 0, 'int'));

			$object->shop_id = $oShop_Producer->shop_id;
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
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Producer_Discount_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oShop_Producer = Core_Entity::factory('Shop_Producer', Core_Array::getGet('shop_producer_id', 0, 'int'));

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
				$shop_discount_id = Core_Array::getPost('shop_discount_id', 0);

				if ($shop_discount_id)
				{
					$oObject = Core_Entity::factory('Shop_Discount', $shop_discount_id);
					if (is_null($oShop_Producer->Shop_Producer_Discounts->getByDiscountId($oObject->id)))
					{
						$oShop_Producer->add($oObject)/*->clearCache()*/;

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
				$shop_bonus_id = Core_Array::getPost('shop_bonus_id', 0);

				if ($shop_bonus_id)
				{
					$oObject = Core_Entity::factory('Shop_Bonus', $shop_bonus_id);
					is_null($oShop_Producer->Shop_Producer_Bonuses->getByBonusId($oObject->id))
						&& $oShop_Producer->add($oObject)/*->clearCache()*/;
				}
			break;
		}

		//parent::_applyObjectProperty();
		parent::_deleteAutosave();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}