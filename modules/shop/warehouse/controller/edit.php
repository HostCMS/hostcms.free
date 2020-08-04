<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow11 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		// Удаляем типы доставок
		$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

		$oAdditionalTab->delete($this->getField('shop_country_id'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$objectId = intval($this->_object->id);

		$oAdditionalTab->delete($this->getField('shop_company_id'));

		// Добавляем компании
		$oCompaniesField = Admin_Form_Entity::factory('Select')
			->name('shop_company_id')
			->caption(Core::_('Shop.shop_company_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
				array(0 => '…') + Company_Controller::fillCompanies($this->_object->Shop->site_id)
			)
			->value($this->_object->shop_company_id)
			->data('required', 1);

		$oMainRow2->add($oCompaniesField);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow2);

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

		$oMainRow3->add($CountriesSelectField);

		// Удаляем местоположения
		$oAdditionalTab->delete($this->getField('shop_country_location_id'));

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

		$oMainRow3->add($CountryLocationsSelectField);

		// Удаляем города
		$oAdditionalTab->delete($this->getField('shop_country_location_city_id'));

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

		$oMainRow4->add($CountryLocationCitiesSelectField);

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

		$oMainRow4->add($CountryLocationCityAreasSelectField);

		$oAdditionalTab->delete($this->getField('shop_warehouse_type_id'));

		$aWarehouseTypes = array('...');

		$aShop_Warehouse_Types = Core_Entity::factory('Shop_Warehouse_Type')->findAll(FALSE);
		foreach ($aShop_Warehouse_Types as $oShop_Warehouse_Type)
		{
			$aWarehouseTypes[$oShop_Warehouse_Type->id] = $oShop_Warehouse_Type->name;
		}

		$ShopWarehouseTypeSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_warehouse_type_id')
			->caption(Core::_('Shop_Warehouse.shop_warehouse_type'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			->options($aWarehouseTypes)
			->value($this->_object->shop_warehouse_type_id);

		$oMainRow8->add($ShopWarehouseTypeSelectField);

		$oMainTab
			->move($this->getField('address')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow5)
			->move($this->getField('name_other')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow5)
			->move($this->getField('address_info')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow7)
			->move($this->getField('working_time')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow8)
			->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow8)
			->move($this->getField('website')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow8)
			->move($this->getField('latitude')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow9)
			->move($this->getField('longitude')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow9);

		$oMainTab->delete($this->getField('separator'));

		// Добавляем выбор разделителя
		$oSeparatorSelect = Admin_Form_Entity::factory('Select')
			->name('separator')
			->caption(Core::_('Shop_Warehouse.separator'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			->options(array(
				'' => Core::_('Admin_Form.no'),
				' ' => Core::_('Shop_Warehouse.space_separator'),
				'-' => '-',
				'/' => '/',
				'_' => '_'
			))
			->value($this->_object->separator);

		$oMainRow9->add($oSeparatorSelect);

		$oMainTab
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow9)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow10)
			->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow11);

		// Флаг установки количества товара на складе
		$oShopItemCountCheckBox = Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_("Shop_Warehouse.warehouse_default_count"))
			->name("warehouse_default_count")
			->value(1)
			->checked(is_null($object->id));

		$oMainRow11->add($oShopItemCountCheckBox);

		$title = $this->_object->id
			? Core::_('Shop_Warehouse.form_warehouses_edit', $this->_object->name)
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
					->where('shop_items.shortcut_id', '=', 0)
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

	/**
	 * Fill warehouses list
	 * @param Shop_Model $oShop shop object
	 * @return array
	 */
	static public function fillWarehousesList(Shop_Model $oShop)
	{
		// $aReturn = array(' … ');
		$aReturn = array();

		$oShop_Warehouses = $oShop->Shop_Warehouses;
		$oShop_Warehouses->queryBuilder()
			->clearOrderBy()
			->orderBy('shop_warehouses.sorting')
			->orderBy('shop_warehouses.id');

		$aShop_Warehouses = $oShop_Warehouses->findAll(FALSE);
		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$aReturn[$oShop_Warehouse->id] = $oShop_Warehouse->name . ' [' . $oShop_Warehouse->id . ']';
		}

		return $aReturn;
	}
}