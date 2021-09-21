<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Comment Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Comment_Controller_Edit extends Comment_Controller_Edit
{
	/**
	 * Prepare backend item's edit form
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$oInformationsystem = is_null($object->id)
			? Core_Entity::factory('Informationsystem', $object->Comment_Informationsystem_Item->Informationsystem_Item->informationsystem_id)
			: $object->Comment_Informationsystem_Item->Informationsystem_Item->Informationsystem;

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$template_id = $oInformationsystem->Structure->template_id
			? $oInformationsystem->Structure->template_id
			: 0;

		$oPropertyTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Admin_Form.tabProperties'))
			->name('Property');

		$this->addTabAfter($oPropertyTab, $oMainTab);

		// Properties
		Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->setDatasetId($this->getDatasetId())
			->linkedObject(Core_Entity::factory('Informationsystem_Comment_Property_List', $oInformationsystem->id))
			->setTab($oPropertyTab)
			->template_id($template_id)
			->fillTab();

		$oAdditionalTab
			->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$informationsystem_item_id = is_null($object->id)
			? intval($this->_object->parent_id
				? Core_Entity::factory('Comment', $this->_object->parent_id)->Comment_Informationsystem_Item->informationsystem_item_id
				: Core_Array::getRequest('informationsystem_item_id'))
			: $this->_object->Comment_Informationsystem_Item->informationsystem_item_id;

		$oAdmin_Form_Entity_Input_Name = Admin_Form_Entity::factory('Input')
			->name('informationsystem_item_id')
			->caption(Core::_('Informationsystem_Item_Comment.informationsystem_item_id'))
			->value($informationsystem_item_id)
			->class('form-control col-xs-12');

		$oAdditionalRow1->add($oAdmin_Form_Entity_Input_Name);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Informationsystem_Item_Comment_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$Comment_Informationsystem_Item = $this->_object->Comment_Informationsystem_Item;

		/*if (is_null($Comment_Informationsystem_Item->id))
		{
			$Comment_Informationsystem_Item->informationsystem_item_id = intval($this->_object->parent_id
				? Core_Entity::factory('Comment', $this->_object->parent_id)->Comment_Informationsystem_Item->informationsystem_item_id
				: Core_Array::getGet('informationsystem_item_id'));
			$Comment_Informationsystem_Item->save();
		}*/
		$Comment_Informationsystem_Item->informationsystem_item_id = Core_Array::getRequest('informationsystem_item_id');
		$Comment_Informationsystem_Item->save();

		// Cached tags
		$Comment_Informationsystem_Item->Informationsystem_Item->clearCache();

		// Properties
		Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->linkedObject(Core_Entity::factory('Informationsystem_Comment_Property_List', $Comment_Informationsystem_Item->Informationsystem_Item->informationsystem_id))
			->applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}