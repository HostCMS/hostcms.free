<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Siteuser_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Discount_Siteuser_Group_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
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

		$oAdditionalTab
			->delete($this->getField('shop_discount_id'))
			->delete($this->getField('shop_group_id'))
			->delete($this->getField('siteuser_id'));

		$shop_id = Core_Array::getGet('shop_id', 0, 'int');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow1->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Discount.item_discount_name'))
				->options($this->_fillDiscounts($shop_id))
				->name('shop_discount_id')
				->value($this->_object->id)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
		);

		$oItemsSelect = Admin_Form_Entity::factory('Select')
			->name('shop_groups_id[]')
			->class('shop-groups')
			->style('width: 100%')
			->multiple('multiple')
			->value(3)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 margin-top-21'));

		$this->addField($oItemsSelect);
		$oMainRow1->add($oItemsSelect);

		$html = '<script>
		$(function(){
			$("#' . $windowId . ' .shop-groups").select2({
				dropdownParent: $("#' . $windowId . '"),
				language: "' . Core_I18n::instance()->getLng() . '",
				minimumInputLength: 1,
				placeholder: "' . Core::_('Shop_Discount_Siteuser.select_group') . '",
				tags: true,
				allowClear: true,
				multiple: true,
				ajax: {
					url: "/admin/shop/item/index.php?groups&shop_id=' . $shop_id .'",
					dataType: "json",
					type: "GET",
					processResults: function (data) {
						var aResults = [];
						$.each(data, function (index, item) {
							aResults.push({
								"id": item.id,
								"text": item.text
							});
						});
						return {
							results: aResults
						};
					}
				}
			});
		});</script>';

		$oMainRow1->add(Admin_Form_Entity::factory('Code')->html($html));

		$this->title($this->_object->id
			? Core::_('Shop_Discount_Siteuser.edit_title')
			: Core::_('Shop_Discount_Siteuser.add_title')
		);

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
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Discount_Siteuser_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$siteuser_id = Core_Array::getGet('siteuser_id', 0, 'int');
		$shop_discount_id = intval(Core_Array::get($this->_formValues, 'shop_discount_id'));

		//parent::_applyObjectProperty();

		if ($siteuser_id)
		{
			$aItemIds = Core_Array::getPost('shop_groups_id', array());
			!is_array($aItemIds) && $aItemIds = array();

			foreach ($aItemIds as $shop_group_id)
			{
				$oShop_Group_Discounts = Core_Entity::factory('Shop_Group_Discount');
				$oShop_Group_Discounts->queryBuilder()
					->where('shop_group_discounts.shop_group_id', '=', $shop_group_id)
					->where('shop_group_discounts.shop_discount_id', '=', $shop_discount_id)
					->where('shop_group_discounts.siteuser_id', '=', $siteuser_id)
					->limit(1);

				$oShop_Group_Discount = $oShop_Group_Discounts->getLast(FALSE);

				if (is_null($oShop_Group_Discount))
				{
					$oShop_Group_Discount = Core_Entity::factory('Shop_Group_Discount');
					$oShop_Group_Discount->shop_discount_id = $shop_discount_id;
					$oShop_Group_Discount->siteuser_id = $siteuser_id;
					$oShop_Group_Discount->shop_group_id = $shop_group_id;
					$oShop_Group_Discount->save();
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}