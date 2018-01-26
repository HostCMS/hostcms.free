<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->shop_id = Core_Array::getGet('shop_id');

			if ($object->Shop->Shop_Warehouses->getCount() == 0)
			{
				$object->default = 1;
			}
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oAdditionalTab = $this->getTab('additional');

		// Удаляем типы доставок
		$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

		$oAdditionalTab->delete($this->getField('shop_country_id'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$objectId = intval($this->_object->id);

		// Создаем поле стран как выпадающий список
		$CountriesSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_id')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
					$Shop_Controller_Edit->fillCountries()
				)
			->value($this->_object->shop_country_id)
			->onchange("$('#{$windowId} #list4').clearSelect();$('#{$windowId} #list3').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list2', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList2',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
		$oMainRow1->add($CountriesSelectField);

		// Удаляем местоположения
		$oAdditionalTab->delete(
			$this->getField('shop_country_location_id')
		);

		// Создаем поле местоположений как выпадающий список
		$CountryLocationsSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_id')
			->id('list2')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
					$Shop_Controller_Edit->fillCountryLocations($this->_object->shop_country_id)
				)
			->value($this->_object->shop_country_location_id)
			->onchange("$('#{$windowId} #list4').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list3', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList3',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
		$oMainRow1->add($CountryLocationsSelectField);

		// Удаляем города
		$oAdditionalTab->delete(
			$this->getField('shop_country_location_city_id')
		);

		// Создаем поле городов как выпадающий список
		$CountryLocationCitiesSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_city_id')
			->id('list3')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
					$Shop_Controller_Edit->fillCountryLocationCities($this->_object->shop_country_location_id)
				)
			->value($this->_object->shop_country_location_city_id)
			->onchange("$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list4', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList4',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
		$oMainRow2->add($CountryLocationCitiesSelectField);

		$oAdditionalTab->delete($this->getField('shop_country_location_city_area_id'));

		// Создаем поле районов как выпадающий список
		$CountryLocationCityAreasSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_city_area_id')
			->id('list4')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_area_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
					$Shop_Controller_Edit->fillCountryLocationCityAreas($this->_object->shop_country_location_city_id)
				)
			->value($this->_object->shop_country_location_city_area_id);
		$oMainRow2->add($CountryLocationCityAreasSelectField);

		$oMainTab->move($this->getField('address')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);
		$oMainTab->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);

		// флаг установки количества товара на складе
		$oShopItemCountCheckBox = Admin_Form_Entity::factory('Checkbox');
		$oShopItemCountCheckBox
			->value(
				is_null($object->id) ? 1 : 0
			)
			->caption(Core::_("Shop_Warehouse.warehouse_default_count"))
			->name("warehouse_default_count");

		$oMainTab->addAfter($oShopItemCountCheckBox, $this->getField('active'));

		$Shop_Delivery_Condition_Controller_Edit = new Shop_Delivery_Condition_Controller_Edit($this->_Admin_Form_Action);

		$Shop_Delivery_Condition_Controller_Edit->controller($this->_Admin_Form_Controller);

		$title = $this->_object->id
			? Core::_('Shop_Warehouse.form_warehouses_edit')
			: Core::_('Shop_Warehouse.form_warehouses_add');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warehouse_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		if ($this->_object->default)
		{
			$this->_object->active = 1;
			$this->_object->changeDefaultStatus();
		}

		//Установка количества товара на складе
		if (Core_Array::getPost('warehouse_default_count'))
		{
			$offset = 0;
			$limit = 100;

			do {
				$oShop_Items = $this->_object->Shop->Shop_Items;

				$oShop_Items
					->queryBuilder()
					->offset($offset)->limit($limit);

				$aShop_Items = $oShop_Items->findAll(FALSE);

				foreach ($aShop_Items as $oShop_Item)
				{
					if (is_null($oShop_Item->Shop_Warehouse_Items->getByShop_warehouse_id($this->_object->id, FALSE)))
					{
						$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item');
						$oShop_Warehouse_Item->shop_warehouse_id = $this->_object->id;
						$oShop_Warehouse_Item->count = 0;
						$oShop_Item->add($oShop_Warehouse_Item);
					}
				}

				$offset += $limit;
			}
			while (count($aShop_Items));
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}