<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Property_Controller_Edit extends Property_Controller_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 * @hostcms-event Shop_Item_Property_Controller_Edit.onAfterRedeclaredPrepareForm
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		// Создаем вкладку
		$oShopItemTabExportImport = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Item.tab_export'))
			->name('ExportImport');

		// Добавляем вкладку
		$this
			->addTabAfter($oShopItemTabExportImport, $oMainTab);

		switch ($modelName)
		{
			case 'property':

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopItemTabExportImport
					->add($oShopItemTabExportImportRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				// Создаем экземпляр контроллера магазина
				$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

				//Переносим GUID на "Экспорт/Импорт"
				$oAdditionalTab->move($this->getField('guid'), $oShopItemTabExportImport);

				// Создаем поле единиц измерения как выпадающий список
				$oShopMeasuresSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_("Shop_Item.shop_measure_id"))
					->options(
						$Shop_Controller_Edit->fillMeasures()
					)
					->name('shop_measure_id')
					->value($this->_object->Shop_Item_Property->shop_measure_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow1->add($oShopMeasuresSelect);

				// Префикс
				$oShopPrefixInput = Admin_Form_Entity::factory('Input')
					->caption(Core::_('Shop_Item.property_prefix'))
					->name('prefix')
					->value($this->_object->Shop_Item_Property->prefix)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow1->add($oShopPrefixInput);

				// Способ отображения в фильтре
				$oShopFilterSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.property_filter'))
					->options(
						array(
							0 => Core::_('Shop_Item.properties_show_kind_none'),
							1 => Core::_('Shop_Item.properties_show_kind_text'),
							2 => Core::_('Shop_Item.properties_show_kind_list'),
							3 => Core::_('Shop_Item.properties_show_kind_radio'),
							4 => Core::_('Shop_Item.properties_show_kind_checkbox'),
							7 => Core::_('Shop_Item.properties_show_kind_listbox'),
							5 => Core::_('Shop_Item.properties_show_kind_checkbox_one'),
							6 => Core::_('Shop_Item.properties_show_kind_from_to')
						)
					)
					->name('filter')
					->value($this->_object->Shop_Item_Property->filter)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow1->add($oShopFilterSelect);

				$oShopShowInGroupCheckbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked($this->_object->Shop_Item_Property->show_in_group == 1)
					->caption(Core::_("Shop_Item.show_in_group"))
					->name("show_in_group");

				$oMainRow2->add($oShopShowInGroupCheckbox);

				$oShopShowInItemCheckbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked($this->_object->Shop_Item_Property->show_in_item == 1)
					->caption(Core::_("Shop_Item.show_in_item"))
					->name("show_in_item");

				$oMainRow3->add($oShopShowInItemCheckbox);

				$oAddValueCheckbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked(is_null($object->id))
					->caption(Core::_("Shop_Item.add_value"))
					->name("add_value");

				$oMainRow4->add($oAddValueCheckbox);

				$oShopItemTabExportImport->move($this->getField('guid'), $oShopItemTabExportImportRow1);

			break;
			case 'property_dir':
			default:
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Item_Property_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'property':
				$Shop_Item_Property = $this->_object->Shop_Item_Property;
				$Shop_Item_Property->shop_measure_id = intval(Core_Array::getPost('shop_measure_id'));
				$Shop_Item_Property->prefix = Core_Array::getPost('prefix');
				$Shop_Item_Property->filter = intval(Core_Array::getPost('filter'));
				$Shop_Item_Property->show_in_group = intval(Core_Array::getPost('show_in_group'));
				$Shop_Item_Property->show_in_item = intval(Core_Array::getPost('show_in_item'));
				$Shop_Item_Property->save();

				// Fast filter
				if ($this->linkedObject->filter)
				{
					$oShop_Filter_Controller = new Shop_Filter_Controller($this->linkedObject);

					$filter = intval(Core_Array::get($this->_formValues, 'filter'));

					if ($filter)
					{
						!$oShop_Filter_Controller->checkPropertyExist($this->_object->id)
							&& $oShop_Filter_Controller->addProperty($this->_object);
					}
					else
					{
						$oShop_Filter_Controller->checkPropertyExist($this->_object->id)
							&& $oShop_Filter_Controller->removeProperty($this->_object);
					}
				}

				if (Core_Array::getPost('add_value') && $this->_object->type != 2)
				{
					$tableName = Property_Controller_Value::factory($this->_object->type)->getTableName();

					$defaultValue = $this->_object->default_value;

					switch ($tableName)
					{
						case 'property_value_ints':
							$defaultValue = intval($defaultValue);
						break;
						case 'property_value_floats':
							$defaultValue = floatval($defaultValue);
						break;
					}

					Core_QueryBuilder::insert($tableName)
						->columns('property_id', 'entity_id', 'value')
						->select(
							Core_QueryBuilder::select(intval($this->_object->id), 'shop_items.id', Core_QueryBuilder::raw(Core_DataBase::instance()->quote($defaultValue)))
								->from('shop_items')
								->leftJoin($tableName, $tableName . '.entity_id', '=', 'shop_items.id')
								->where($tableName . '.entity_id', 'IS', NULL)
								->where('shop_items.shop_id', '=', $Shop_Item_Property->shop_id)
								->where('shop_items.deleted', '=', 0)
						)
						->execute();
				}
			break;
			case 'property_dir':
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}