<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Siteuser_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discount_Siteuser_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			->delete($this->getField('shop_item_id'))
			->delete($this->getField('shop_discount_id'));

		$shop_id = Core_Array::getGet('shop_id', 0, 'int');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow1->add(
				Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Discount.item_discount_name'))
					->options($this->_fillDiscounts($shop_id))
					->name('shop_discount_id')
					->value($this->_object->id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			);

		$oAdditionalTab->delete($this->getField('siteuser_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = !is_null(Core_Array::getGet('siteuser_id'))
				? Core_Entity::factory('Siteuser')->find(Core_Array::getGet('siteuser_id'))
				: $this->_object->Siteuser;

			$options = !is_null($oSiteuser->id)
				? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
				: array(0);

			$oSiteuserSelect = Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Order.siteuser_id'))
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				->divAttr(array('class' => 'col-xs-12'));

			$oMainRow1
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-6 col-sm-6 no-padding siteuser-select2')
						->add($oSiteuserSelect)
				);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
		}

		$oItemsSelect = Admin_Form_Entity::factory('Select')
			->name('shop_items_id[]')
			->class('shop-items')
			->style('width: 100%')
			->multiple('multiple')
			->value(3)
			->divAttr(array('class' => 'form-group col-xs-12'));

		$this->addField($oItemsSelect);
		$oMainRow2->add($oItemsSelect);

		$html = '<script>
		$(function(){
			$("#' . $windowId . ' .shop-items").select2({
				dropdownParent: $("#' . $windowId . '"),
				language: "' . Core_I18n::instance()->getLng() . '",
				minimumInputLength: 1,
				placeholder: "' . Core::_('Shop_Discount_Siteuser.select_item') . '",
				tags: true,
				allowClear: true,
				multiple: true,
				ajax: {
					url: "/admin/shop/item/index.php?items&shop_id=' . $shop_id .'",
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

		$oMainRow2->add(Admin_Form_Entity::factory('Code')->html($html));

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
		$siteuser_id = intval(Core_Array::get($this->_formValues, 'siteuser_id'));
		$shop_discount_id = intval(Core_Array::get($this->_formValues, 'shop_discount_id'));

		// parent::_applyObjectProperty();

		if ($siteuser_id)
		{
			$aItemIds = Core_Array::getPost('shop_items_id', array());
			!is_array($aItemIds) && $aItemIds = array();

			foreach ($aItemIds as $shop_item_id)
			{
				$oShop_Item_Discounts = Core_Entity::factory('Shop_Item_Discount');
				$oShop_Item_Discounts->queryBuilder()
					->where('shop_item_discounts.shop_item_id', '=', $shop_item_id)
					->where('shop_item_discounts.shop_discount_id', '=', $shop_discount_id)
					->where('shop_item_discounts.siteuser_id', '=', $siteuser_id)
					->limit(1);

				$oShop_Item_Discount = $oShop_Item_Discounts->getLast(FALSE);

				if (is_null($oShop_Item_Discount))
				{
					$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
					$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
					$oShop_Item_Discount->siteuser_id = $siteuser_id;
					$oShop_Item_Discount->shop_item_id = $shop_item_id;
					$oShop_Item_Discount->save();
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}