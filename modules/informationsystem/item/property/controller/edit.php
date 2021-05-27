<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Property_Controller_Edit extends Property_Controller_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 * @hostcms-event Informationsystem_Item_Property_Controller_Edit.onAfterRedeclaredPrepareForm
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');

		switch ($modelName)
		{
			case 'property':

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAddValueCheckbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked(is_null($object->id))
					->caption(Core::_("Informationsystem_Item.add_value"))
					->name("add_value");

				$oMainRow1->add($oAddValueCheckbox);
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
	 * @hostcms-event Informationsystem_Item_Property_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'property':
				$Informationsystem_Item_Property = $this->_object->Informationsystem_Item_Property;

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
							Core_QueryBuilder::select(intval($this->_object->id), 'informationsystem_items.id', Core_QueryBuilder::raw(Core_DataBase::instance()->quote($defaultValue)))
								->from('informationsystem_items')
								->leftJoin($tableName, $tableName . '.entity_id', '=', 'informationsystem_items.id')
								->where($tableName . '.entity_id', 'IS', NULL)
								->where('informationsystem_items.informationsystem_id', '=', $Informationsystem_Item_Property->informationsystem_id)
								->where('informationsystem_items.deleted', '=', 0)
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