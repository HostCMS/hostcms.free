<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Bonus Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Bonus_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShop = $oShopItem->Shop;

		if (!$object->id)
		{
			$object->shop_id = $oShop->id;
		}

		parent::setObject($object);

		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Item.tab_description'))
			->name('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$this
			->addTab($oMainTab);

		$oMainRow1->add(Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Bonus.item_bonus_name'))
			->options($this->_fillBonuses($oShop->id))
			->name('shop_bonus_id')
			->value($this->_object->id));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainRow2->add(Admin_Form_Entity::factory('Radiogroup')
			->radio(array(
				'—',
				Core::_("Shop_Bonus.shop_apply_modification_bonus"),
				Core::_("Shop_Bonus.shop_not_apply_modification_bonus")
			))
			->ico(
				array(
					'fa-minus-circle',
					'fa-check',
					'fa-ban'
				)
			)
			->name('apply_for_modifications')
			->divAttr(array('id' => 'import_types', 'class' => 'form-group col-xs-12'))
		)
		->add(Admin_Form_Entity::factory('Code')
			->html("<script>$(function() {
				$('#{$windowId} #import_types').buttonset();
			});</script>")
		);

		$title = $this->_object->id
			? Core::_('Shop_Bonus.item_bonus_edit_form_title')
			: Core::_('Shop_Bonus.item_bonus_add_form_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Fill bonuss list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	protected function _fillBonuses($iShopId)
	{
		$aShopBonuses = Core_Entity::factory('Shop', $iShopId)->Shop_Bonuses->findAll();

		$aReturn = array(" … ");

		foreach($aShopBonuses as $oShopBonus)
		{
			$aReturn[$oShopBonus->id] = $oShopBonus->name;
		}

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Item_Bonus_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));
		$oShopBonus = Core_Entity::factory('Shop_Bonus', Core_Array::getPost('shop_bonus_id', 0));
		$oShopItemBonus = $oShopItem->Shop_Item_Bonuses->getByBonusId($oShopBonus->id);

		if(is_null($oShopItemBonus))
		{
			$oShopItem->add($oShopBonus);
		}

		switch (Core_Array::getPost('apply_for_modifications'))
		{
			case 1:
				$aModifications = $oShopItem->Modifications->findAll();
				foreach ($aModifications as $oModification)
				{
					$oModification->add($oShopBonus);
				}
			break;
			case 2:
				$aModifications = $oShopItem->Modifications->findAll();
				foreach ($aModifications as $oModification)
				{
					$oModification->remove($oShopBonus);
				}
			break;
		}

		//parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}