<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Favorite Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Favorite_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			case 'shop_favorite':
				if (!$object->id)
				{
					$object->siteuser_id = Core_Array::getGet('siteuser_id', 0, 'int');
					$object->shop_id = Core_Array::getGet('shop_id', 0, 'int');
					$object->shop_favorite_list_id = Core_Array::getGet('shop_favorite_list_id', 0, 'int');
				}

				parent::setObject($object);

				$name = $this->_object->shop_item_id
					?  $this->_object->Shop_Item->name
					: '';

				$title = $this->_object->id
					? Core::_('Shop_Favorite.edit_form_title', $name)
					: Core::_('Shop_Favorite.add_form_title');

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAdditionalTab->delete($this->getField('shop_item_id'));

				$options = $this->_object->id
					? array($this->_object->Shop_Item->id => $this->_object->Shop_Item->name . ' [' . $this->_object->Shop_Item->id . ']')
					: array();

				$oItemsSelect = Admin_Form_Entity::factory('Select')
					->name('shop_item_id')
					->class('shop-items')
					->style('width: 100%')
					// ->multiple('multiple')
					// ->value($this->_object->shop_item_id)
					->options($options)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow1->add($oItemsSelect);

				$html = '<script>
					$(function(){
						$("#' . $windowId . ' .shop-items").select2({
							dropdownParent: $("#' . $windowId . '"),
							language: "' . Core_I18n::instance()->getLng() . '",
							minimumInputLength: 1,
							placeholder: "' . Core::_('Shop_Discount_Siteuser.select_item') . '",
							// tags: true,
							// allowClear: true,
							// multiple: true,
							ajax: {
								url: "/admin/shop/item/index.php?items&shop_id=' . $this->_object->shop_id .'",
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

				$oAdditionalTab->delete($this->getField('shop_id'));

				$oShops = Admin_Form_Entity::factory('Select')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					// ->class('form-control input-lg')
					->caption(Core::_('Shop_Favorite_List.shop_id'))
					->name('shop_id')
					->value($this->_object->shop_id)
					->options(
						/*array('...') + */$this->_fillShops(CURRENT_SITE)
					);

				$oMainRow2->add($oShops);

				$oAdditionalTab->delete($this->getField('shop_favorite_list_id'));

				$oShopFavoriteLists = Admin_Form_Entity::factory('Select')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					// ->class('form-control input-lg')
					->caption(Core::_('Shop_Favorite.shop_favorite_list_id'))
					->name('shop_favorite_list_id')
					->value($this->_object->shop_favorite_list_id)
					->options(
						array('...') + $this->_fillShopFavoriteLists()
					);

				$oMainRow2->add($oShopFavoriteLists);
			break;

			case 'shop_favorite_list':
				if (!$object->id)
				{
					$object->siteuser_id = Core_Array::getGet('siteuser_id', 0, 'int');
					$object->shop_id = Core_Array::getGet('shop_id', 0, 'int');
				}

				parent::setObject($object);

				$title = $this->_object->id
					? Core::_("Shop_Favorite_List.edit_form_title", $this->_object->name)
					: Core::_("Shop_Favorite_List.add_form_title");

				// Получаем стандартные вкладки
				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oMainTab
					->move($this->getField('name')->class('form-control input-lg')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow1);

				$oAdditionalTab->delete($this->getField('shop_id'));

				$oShops = Admin_Form_Entity::factory('Select')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->class('form-control input-lg')
					->caption(Core::_('Shop_Favorite_List.shop_id'))
					->name('shop_id')
					->value($this->_object->shop_id)
					->options(
						array('...') + $this->_fillShops(CURRENT_SITE)
					);

				$oMainRow1->add($oShops);

			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Favorite_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 * @return self
	 */
	protected function _applyObjectProperty()
	{
		// $modelName = $this->_object->getModelName();

		$shop_item_id = intval(Core_Array::get($this->_formValues, 'shop_item_id', 0, 'intval'));

		$oShop_Favorites = Core_Entity::factory('Shop_Favorite');
		$oShop_Favorites->queryBuilder()
			->where('shop_favorites.shop_item_id', '=', $shop_item_id)
			->where('shop_favorites.shop_favorite_list_id', '=', $this->_object->shop_favorite_list_id);

		$count = $oShop_Favorites->getCount();

		if ($count)
		{
			return $this;
		}

		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Build shops array
	 * @param int $iSiteId site ID
	 * @return array
	 */
	protected function _fillShops($iSiteId)
	{
		$iSiteId = intval($iSiteId);

		$oShops = Core_Entity::factory('Shop');

		$oShops->queryBuilder()
			->where('shops.site_id', '=', $iSiteId)
			->orderBy('shops.id', 'ASC');

		$aShops = $oShops->findAll();

		$aReturn = array();
		foreach ($aShops as $oShop)
		{
			$aReturn[$oShop->id] = $oShop->name;
		}

		return $aReturn;
	}

	protected function _fillShopFavoriteLists()
	{
		// $iSiteId = intval($iSiteId);

		$oShop_Favorite_Lists = Core_Entity::factory('Shop_Favorite_List');

		/*$oShops->queryBuilder()
			->where('shops.site_id', '=', $iSiteId)
			->orderBy('shops.id', 'ASC');*/

		$aShop_Favorite_Lists = $oShop_Favorite_Lists->findAll();

		$aReturn = array();
		foreach ($aShop_Favorite_Lists as $oShop_Favorite_List)
		{
			$aReturn[$oShop_Favorite_List->id] = $oShop_Favorite_List->name;
		}

		return $aReturn;
	}
}