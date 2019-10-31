<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
					->value(
						is_null($object->id) ? 1 : 0
					)
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

				if (Core_Array::getPost('add_value'))
				{
					$offset = 0;
					$limit = 100;

					do {
						$oInformationsystem_Items = $Informationsystem_Item_Property->Informationsystem->Informationsystem_Items;

						$oInformationsystem_Items
							->queryBuilder()
							->clearOrderBy()
							->orderBy('id', 'ASC')
							->offset($offset)->limit($limit);

						$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);

						foreach ($aInformationsystem_Items as $oInformationsystem_Item)
						{
							$aProperty_Values = $this->_object->getValues($oInformationsystem_Item->id, FALSE);

							if (!count($aProperty_Values))
							{
								$oProperty_Value = $this->_object->createNewValue($oInformationsystem_Item->id);

								switch ($this->_object->type)
								{
									case 2: // Файл
									break;
									default:
										$oProperty_Value->value($this->_object->default_value);
								}

								$oProperty_Value->save();
							}
						}

						$offset += $limit;
					}

					while (count($aInformationsystem_Items));
				}
			break;
			case 'property_dir':
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}