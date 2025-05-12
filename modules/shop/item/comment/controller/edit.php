<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Comment Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Item_Comment_Controller_Edit extends Comment_Controller_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$oShop = is_null($object->id)
			? Core_Entity::factory('Shop_Item', Core_Array::getRequest('shop_item_id', 0, 'int'))->Shop
			: $object->Comment_Shop_Item->Shop_Item->Shop;

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$template_id = $oShop->Structure->template_id
			? $oShop->Structure->template_id
			: 0;

		$oPropertyTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Admin_Form.tabProperties'))
			->name('Property');

		$this->addTabAfter($oPropertyTab, $oMainTab);

		// Properties
		Core::moduleIsActive('property') && Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->setDatasetId($this->getDatasetId())
			->linkedObject(Core_Entity::factory('Shop_Comment_Property_List', $oShop->id))
			->setTab($oPropertyTab)
			->template_id($template_id)
			->fillTab();

		$oAdditionalTab
			->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$shop_item_id = is_null($object->id)
			? intval($this->_object->parent_id
				? Core_Entity::factory('Comment', $this->_object->parent_id)->Comment_Shop_Item->shop_item_id
				: Core_Array::getRequest('shop_item_id'))
			: $this->_object->Comment_Shop_Item->shop_item_id;

		$oShop_Item = Core_Entity::factory('Shop_Item', $shop_item_id);

		$oAdmin_Form_Entity_Input_Name = Admin_Form_Entity::factory('Input')
			->name('shop_item_id')
			->caption(Core::_('Shop_Item_Comment.shop_item_id'))
			->value($shop_item_id)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->class('form-control col-xs-12')
			->add(
				Admin_Form_Entity::factory('A')
					->id('pathLink')
					->class('input-group-addon blue')
					->value('<i class="fa fa-external-link"></i>')
					->target('_blank')
					->href(Admin_Form_Controller::correctBackendPath("/{admin}/shop/item/index.php?hostcms[action]=edit&hostcms[window]=id_content&shop_id={$oShop->id}&shop_group_id={$oShop_Item->shop_group_id}&hostcms[checked][1][{$shop_item_id}]=1"))
			);

		$oAdditionalRow1->add($oAdmin_Form_Entity_Input_Name);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Item_Comment_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$Comment_Shop_Item = $this->_object->Comment_Shop_Item;
		$Comment_Shop_Item->shop_item_id = Core_Array::getRequest('shop_item_id', 0, 'int');
		$Comment_Shop_Item->save();

		// Cached tags
		$Comment_Shop_Item->Shop_Item->clearCache();

		// Properties
		Core::moduleIsActive('property') && Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->linkedObject(Core_Entity::factory('Shop_Comment_Property_List', $Comment_Shop_Item->Shop_Item->shop_id))
			->applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}