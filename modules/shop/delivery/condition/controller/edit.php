<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery_Condition Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Delivery_Condition_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Groups tree
	 * @var array
	 */
	protected $_aGroupTree = array();

	/**
	 * Load object's fields when object has been set
	 * После установки объекта загружаются данные о его полях
	 * @param object $object
	 * @return Shop_Delivery_Condition_Controller_Edit
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'shop_delivery_condition':
				if (!$object->id)
				{
					$object->shop_delivery_id = Core_Array::getGet('delivery_id');
					$object->shop_delivery_condition_dir_id = Core_Array::getGet('shop_delivery_condition_dir_id');
				}

				parent::setObject($object);

				// Добавляем вкладку условий доставки
				$this->addTabAfter(
					$oConditionsTab = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Shop_Delivery_Condition.cond_of_delivery_tab'))
						->name('Conditions'),
					$oMainTab = $this->getTab('main')
				);

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oConditionsTab
					->add($oConditionsTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsTabRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsTabRow6 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShop = $object->shop_delivery->Shop;

				$oAdditionalTab = $this->getTab('additional');

				// Переносим поля на другую вкладку
				$oMainTab
					->move($oMinWeightField = $this->getField('min_weight'), $oConditionsTab)
					->moveAfter($oMaxWeightField = $this->getField('max_weight'), $oMinWeightField, $oConditionsTab)
					->moveAfter($oMinPriceField = $this->getField('min_price'), $oMaxWeightField, $oConditionsTab)
					->moveAfter($oMaxPriceField = $this->getField('max_price'), $oMinPriceField, $oConditionsTab)
					->moveAfter($oPriceField = $this->getField('price'), $oMaxPriceField, $oConditionsTab);

				$oMinWeightField->caption(
					Core::_(
						'Shop_Delivery_Condition.min_weight',
						$measure_name = $oShop->Shop_Measure->name == ''
							? Core::_('Shop_Delivery_Condition.shop_measure_not_checked')
							: $oShop->Shop_Measure->name)
				);
				$oMaxWeightField->caption(Core::_('Shop_Delivery_Condition.max_weight', $measure_name));

				// Удаляем налоги
				$oAdditionalTab->delete($this->getField('shop_tax_id'));

				// Создаем поле налогов как выпадающий список
				$oTaxSelect = Admin_Form_Entity::factory('Select');

				// Создаем экземпляр класса контроллера товара/группы
				$Shop_Item_Controller_Edit = new Shop_Item_Controller_Edit($this->_Admin_Form_Action);

				$oTaxSelect
					->caption(Core::_("Shop_Delivery_Condition.shop_tax_id"))
					->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2'))
					->options(
						$Shop_Item_Controller_Edit->fillTaxesList()
					)
					->name('shop_tax_id')
					->value($this->_object->shop_tax_id);

				$oConditionsTabRow6->add($oTaxSelect);

				$oAdditionalTab
					->delete($this->getField('shop_country_id'))
					->delete($this->getField('shop_country_location_id'))
					->delete($this->getField('shop_country_location_city_id'))
					->delete($this->getField('shop_country_location_city_area_id'));

				$oMainTab
					->delete($this->getField('shop_country_id_inverted'))
					->delete($this->getField('shop_country_location_id_inverted'))
					->delete($this->getField('shop_country_location_city_id_inverted'))
					->delete($this->getField('shop_country_location_city_area_id_inverted'));

				$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);
				$objectId = intval($this->_object->id);
				$windowId = $this->_Admin_Form_Controller->getWindowId();

				// Создаем поле стран как выпадающий список
				$CountriesSelectField = Admin_Form_Entity::factory('Select')
					->name('shop_country_id')
					->caption(Core::_('Shop_Delivery_Condition.shop_country_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->options($Shop_Controller_Edit->fillCountries())
					->value($this->_object->shop_country_id)
					->invertor(true)
					->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_id_inverted'))
					->inverted($this->_object->shop_country_id_inverted)
					->onchange("$('#{$windowId} #list4').clearSelect();$('#{$windowId} #list3').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list2', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList2',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
				$oMainRow2->add($CountriesSelectField);

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
					->invertor(true)
					->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_location_id_inverted'))
					->inverted($this->_object->shop_country_location_id_inverted)
					->onchange("$('#{$windowId} #list4').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list3', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList3',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");

				$oMainRow2->add($CountryLocationsSelectField);

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
					->invertor(true)
					->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_location_city_id_inverted'))
					->inverted($this->_object->shop_country_location_city_id_inverted)
					->onchange("$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list4', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList4',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");

				$oMainRow3->add($CountryLocationCitiesSelectField);

				// Создаем поле районов как выпадающий список
				$CountryLocationCityAreasSelectField = Admin_Form_Entity::factory('Select')
					->name('shop_country_location_city_area_id')
					->id('list4')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_area_id'))
					->options(
							$Shop_Controller_Edit->fillCountryLocationCityAreas($this->_object->shop_country_location_city_id)
						)
					->value($this->_object->shop_country_location_city_area_id)
					->invertor(true)
					->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_location_city_area_id_inverted'))
					->inverted($this->_object->shop_country_location_city_area_id_inverted);

				$oMainRow3->add($CountryLocationCityAreasSelectField);

				// Удаляем типы доставок
				$oAdditionalTab->delete
				(
					$this->getField('shop_delivery_id')
				);

				// Создаем экземпляр контроллера типов доставки
				$Shop_Delivery_Controller_Edit = new Shop_Delivery_Controller_Edit($this->_Admin_Form_Action);

				// Создаем поле типов доставок как выпадающий список
				$DeliverySelectField = Admin_Form_Entity::factory('Select')
					->name('shop_delivery_id')
					->caption(Core::_('Shop_Delivery_Condition.shop_delivery_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->options(
							$Shop_Delivery_Controller_Edit->fillDeliveries($this->_object->Shop_Delivery->shop_id)
					)
					->value($this->_object->shop_delivery_id);

				// Добавляем типы доставок
				$oMainRow4->add($DeliverySelectField);

				// Удаляем группу товаров
				$oAdditionalTab->delete($this->getField('shop_delivery_condition_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');
				$oGroupSelect->caption(Core::_('Shop_Delivery_Condition_Dir.parent_id'))
					->options(array(' … ') + $this->fillGroupList($this->_object->shop_delivery_id))
					->name('shop_delivery_condition_dir_id')
					->value($this->_object->shop_delivery_condition_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12'))
					->filter(TRUE);

				// Добавляем группу товаров
				$oMainRow1->add($oGroupSelect);

				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5);
				$oMainTab->move($this->getField('marking')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6);
				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow7);
				$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow8);

				$oConditionsTab->move($oMinWeightField->divAttr(array('class' => 'form-group col-xs-12')), $oConditionsTabRow1);
				$oConditionsTab->move($oMaxWeightField->divAttr(array('class' => 'form-group col-xs-12')), $oConditionsTabRow2);
				$oConditionsTab->move($oMinPriceField->divAttr(array('class' => 'form-group col-xs-12')), $oConditionsTabRow3);
				$oConditionsTab->move($oMaxPriceField->divAttr(array('class' => 'form-group col-xs-12')), $oConditionsTabRow4);
				$oConditionsTab->move($oPriceField->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-8')), $oConditionsTabRow5);

				// Создаем экземпляр контроллера магазина
				$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

				// Удаляем валюты
				$oAdditionalTab->delete($this->getField('shop_currency_id'));

				$oConditionsTabRow5->add(
					Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop_Delivery_Condition.shop_currency_id'))
						->options(
							$Shop_Controller_Edit->fillCurrencies()
						)
						->name('shop_currency_id')
						->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4'))
						->caption("&nbsp;")
						->value($this->_object->shop_currency_id)
				);

				// Заголовок формы
				$title = $this->_object->id
					? Core::_('Shop_Delivery_Condition.cond_of_delivery_edit_form_title')
					: Core::_('Shop_Delivery_Condition.cond_of_delivery_add_form_title');

				$this->title($title);
			break;
			case 'shop_delivery_condition_dir':
				if (!$object->id)
				{
					$object->shop_delivery_id = Core_Array::getGet('delivery_id');
					$object->parent_id = Core_Array::getGet('shop_delivery_condition_dir_id');
				}

				parent::setObject($object);

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				// Удаляем группу товаров
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');
				$oGroupSelect->caption(Core::_('Shop_Delivery_Condition_Dir.parent_id'))
					->options(array(' … ') + $this->fillGroupList($this->_object->shop_delivery_id))
					->name('parent_id')
					->value($this->_object->parent_id)
					->filter(TRUE);

				// Добавляем группу товаров
				$oMainTab->addAfter($oGroupSelect, $this->getField('name'));
			break;
		}

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $shop_delivery_id delivery ID
	 * @param int $parent_id parent directory ID
	 * @param boolean $aExclude exclude group IDs array
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillGroupList($shop_delivery_id, $parent_id = 0, $aExclude = array(), $iLevel = 0)
	{
		$shop_delivery_id = intval($shop_delivery_id);
		$parent_id = intval($parent_id);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_delivery_condition_dirs')
				->where('shop_delivery_id', '=', $shop_delivery_id)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				$this->_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset($this->_aGroupTree[$parent_id]))
		{
			$countExclude = count($aExclude);
			foreach ($this->_aGroupTree[$parent_id] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'];
					$aReturn += $this->fillGroupList($shop_delivery_id, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && $this->_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Generate linked lists for $object add/edit form
	 * @param object $object object
	 * @param Admin_Form_Entity_Tab $tab tab for linked list
	 * @param Admin_Form_Entity $fieldAfter after that field linked list should be inserted
	 * @return Admin_Form_Entity_Select
	 */
	public function generateCountryFields($object, $tab, $fieldAfter)
	{
		$objectId = intval($object->_object->id);
		$windowId = $object->_Admin_Form_Controller->getWindowId();

		$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

		// Создаем поле стран как выпадающий список
		$CountriesSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_id')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_id'))
			->divAttr(array('style' => 'float: left'))
			->options(
					$Shop_Controller_Edit->fillCountries()
				)
			->value($object->_object->shop_country_id)
			->onchange("$('#{$windowId} #list4').clearSelect();$('#{$windowId} #list3').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list2', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList2',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");

		if ($object instanceof Shop_Delivery_Condition_Controller_Edit)
		{
			$CountriesSelectField
				->invertor(true)
				->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_id_inverted'))
				->inverted($object->_object->shop_country_id_inverted);
		}

		// Добавляем страны
		is_null($fieldAfter)
			? $tab->add($CountriesSelectField)
			: $tab->addAfter($CountriesSelectField, $fieldAfter);

		// Создаем поле местоположений как выпадающий список
		$CountryLocationsSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_id')
			->id('list2')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_id'))
			->options(
					$Shop_Controller_Edit->fillCountryLocations($object->_object->shop_country_id)
				)
			->value($object->_object->shop_country_location_id)
			->onchange("$('#{$windowId} #list4').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list3', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList3',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");

		if ($object instanceof Shop_Delivery_Condition_Controller_Edit)
		{
			$CountryLocationsSelectField
				->invertor(true)
				->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_location_id_inverted'))
				->inverted($object->_object->shop_country_location_id_inverted);
		}

		// Добавляем местоположения
		$tab->addAfter($CountryLocationsSelectField, $CountriesSelectField);

		// Создаем поле городов как выпадающий список
		$CountryLocationCitiesSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_city_id')
			->id('list3')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_id'))
			->divAttr(array('style' => 'float: left'))
			->options(
					$Shop_Controller_Edit->fillCountryLocationCities($object->_object->shop_country_location_id)
				)
			->value($object->_object->shop_country_location_city_id)
			->onchange("$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list4', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList4',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");

		if ($object instanceof Shop_Delivery_Condition_Controller_Edit) {
			$CountryLocationCitiesSelectField
				->invertor(true)
				->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_location_city_id_inverted'))
				->inverted($object->_object->shop_country_location_city_id_inverted);
		}

		// Добавляем города
		$tab->addAfter($CountryLocationCitiesSelectField, $CountryLocationsSelectField);

		// Создаем поле районов как выпадающий список
		$CountryLocationCityAreasSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_city_area_id')
			->id('list4')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_area_id'))
			->options(
					$Shop_Controller_Edit->fillCountryLocationCityAreas($object->_object->shop_country_location_city_id)
				)
			->value($object->_object->shop_country_location_city_area_id);
		if ($object instanceof Shop_Delivery_Condition_Controller_Edit) {
			$CountryLocationCityAreasSelectField
				->invertor(true)
				->invertorCaption(Core::_('Shop_Delivery_Condition.shop_country_location_city_area_id_inverted'))
				->inverted($object->_object->shop_country_location_city_area_id_inverted);
		}

		// Добавляем районы
		$tab->addAfter($CountryLocationCityAreasSelectField, $CountryLocationCitiesSelectField);

		return $CountryLocationCityAreasSelectField;
	}
}