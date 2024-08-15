<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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

				$oAdditionalTab->move($this->getField('guid'), $oShopItemTabExportImport);

				$oShopMeasuresSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_("Shop_Item.shop_measure_id"))
					->options(
						Shop_Controller::fillMeasures()
					)
					->name('shop_measure_id')
					->value($this->_object->Shop_Item_Property->shop_measure_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow1->add($oShopMeasuresSelect);

				$oShopPrefixInput = Admin_Form_Entity::factory('Input')
					->caption(Core::_('Shop_Item.property_prefix'))
					->name('prefix')
					->value($this->_object->Shop_Item_Property->prefix)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow1->add($oShopPrefixInput);

				$oShopFilterSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.property_filter'))
					->options(
						array(
							0 => Core::_('Shop_Item.properties_show_kind_none'),
							1 => array('value' => Core::_('Shop_Item.properties_show_kind_text'), 'attr' => array('class' => 'shown-0 shown-11 shown-1 shown-4 shown-6 shown-8 shown-9')),
							2 => array('value' => Core::_('Shop_Item.properties_show_kind_list'), 'attr' => array('class' => 'shown-3')),
							3 => array('value' => Core::_('Shop_Item.properties_show_kind_radio'), 'attr' => array('class' => 'shown-3')),
							4 => array('value' => Core::_('Shop_Item.properties_show_kind_checkbox'), 'attr' => array('class' => 'shown-3')),
							7 => array('value' => Core::_('Shop_Item.properties_show_kind_listbox'), 'attr' => array('class' => 'shown-3')),
							5 => array('value' => Core::_('Shop_Item.properties_show_kind_checkbox_one'), 'attr' => array('class' => 'shown-7')),
							6 => array('value' => Core::_('Shop_Item.properties_show_kind_from_to'), 'attr' => array('class' => 'shown-0 shown-11 shown-8 shown-9')),
							99 => array('value' => Core::_('Shop_Item.properties_show_seo_filter'), 'attr' => array('class' => 'shown-0 shown-11 shown-1 shown-3 shown-4 shown-6 shown-8 shown-9')),
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

				// Для установки значений свойство должно быть разрешено для групп
				if (!is_null($object->id))
				{
					$oAddValueCheckbox = Admin_Form_Entity::factory('Checkbox')
						->value(1)
						//->checked(is_null($object->id))
						->checked(FALSE)
						->caption(Core::_("Shop_Item.add_value"))
						->class('colored-danger')
						->name("add_value");

					$oMainRow4->add($oAddValueCheckbox);
				}

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
		$bNewObject = is_null($this->_object->id);

		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'property':
				$Shop_Item_Property = $this->_object->Shop_Item_Property;
				if ($Shop_Item_Property->id)
				{
					$Shop_Item_Property->shop_measure_id = Core_Array::getPost('shop_measure_id', 0, 'int');
					$Shop_Item_Property->prefix = Core_Array::getPost('prefix');
					$Shop_Item_Property->filter = Core_Array::getPost('filter', 0, 'int');
					$Shop_Item_Property->show_in_group = Core_Array::getPost('show_in_group', 0, 'int');
					$Shop_Item_Property->show_in_item = Core_Array::getPost('show_in_item', 0, 'int');
					$Shop_Item_Property->save();
				}

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

					$oQB = Core_QueryBuilder::select(intval($this->_object->id), 'shop_items.id', Core_QueryBuilder::raw(Core_DataBase::instance()->quote($defaultValue)))
						->from('shop_items')
						->leftJoin($tableName, $tableName . '.entity_id', '=', 'shop_items.id',
							array(
								array('AND' => array($tableName . '.property_id', '=', intval($this->_object->id)))
							)
						)
						->where($tableName . '.entity_id', 'IS', NULL)
						->where('shop_items.shop_id', '=', $Shop_Item_Property->shop_id)
						->where('shop_items.deleted', '=', 0);

					// Для существующих свойств значения создаются только с учетом разрешенных для групп свойств
					if (!$bNewObject)
					{
						$oQB->join('shop_item_property_for_groups', 'shop_item_property_for_groups.shop_group_id', '=', 'shop_items.shop_group_id',
							array(
								array('AND' => array('shop_item_property_for_groups.shop_item_property_id', '=', $Shop_Item_Property->id))
							)
						);
					}

					Core_QueryBuilder::insert($tableName)
						->columns('property_id', 'entity_id', 'value')
						->select($oQB)
						->execute();
				}
			break;
			case 'property_dir':
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}