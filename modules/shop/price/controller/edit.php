<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$oImportExportTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Price.import_export_tab'))
			->name('ImportExport');

		$oImportExportTab
			->add($oImportExportTabRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->addTabAfter($oImportExportTab, $oMainTab);

		$oMainTab
			->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')), $oImportExportTabRow1)
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		// Удаляем группу доступа
		$oAdditionalTab->delete($this->getField('siteuser_group_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
			$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups(
				$this->_object->Shop->site_id
			);
		}
		else
		{
			$aSiteuser_Groups = array();
		}

		// Создаем поле групп пользователей сайта как выпадающий список
		$oSiteUserGroupSelect = Admin_Form_Entity::factory('Select');
		$oSiteUserGroupSelect
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->caption(Core::_("Shop_Item.siteuser_group_id"))
			->options(
				array(
					-1 => Core::_('Shop_Item.shop_users_group_parrent')
				) + $aSiteuser_Groups
			)
			->name('siteuser_group_id')
			->value($this->_object->siteuser_group_id);

		// Добавляем группы пользователей сайта
		$oMainRow2->add($oSiteUserGroupSelect);

		$oMainTab
			->move($this->getField('percent')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2);

		$oApplyForAll = Admin_Form_Entity::factory('Checkbox')
			->name('apply_for_all')
			->caption(Core::_("Shop_Item.prices_add_form_apply_for_all"))
			->value($object->id ? 0 : 1);

		$oMainRow3->add($oApplyForAll);

		if (!is_null($object->id))
		{
			$oRecalculatePrice = Admin_Form_Entity::factory('Checkbox')
				->name('recalculate_price')
				->caption(Core::_("Shop_Item.prices_add_form_recalculate"))
				->value(0);

			$oMainRow3->add($oRecalculatePrice);
		}

		$title = $this->_object->id
			? Core::_('Shop_Price.prices_edit_form_title', $this->_object->name)
			: Core::_('Shop_Price.prices_add_form_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Price_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		if (!is_null(Core_Array::getPost('apply_for_all')))
		{
			$offset = 0;
			$limit = 100;

			do {
				$oShop_Items = $this->_object->Shop->Shop_Items;

				$oShop_Items->queryBuilder()
					->offset($offset)
					->limit($limit);

				$aShop_Items = $oShop_Items->findAll(FALSE);

				foreach ($aShop_Items as $oShop_Item)
				{
					$oShop_Item_Price = $oShop_Item->Shop_Item_Prices->getByShop_price_id($this->_object->id, FALSE);

					if (is_null($oShop_Item_Price))
					{
						$oShop_Item_Price = Core_Entity::factory('Shop_Item_Price');
						$oShop_Item_Price->value = $oShop_Item->price / 100 * $this->_object->percent;
						$oShop_Item_Price->shop_price_id = $this->_object->id;
						$oShop_Item->add($oShop_Item_Price);
					}
				}
				$offset += $limit;
			}
			while (count($aShop_Items));
		}

		if (!is_null(Core_Array::getPost('recalculate_price')))
		{
			$offset = 0;
			$limit = 100;

			do {
				$oShop_Item_Prices = $this->_object->Shop_Item_Prices;

				$oShop_Item_Prices->queryBuilder()
					->offset($offset)
					->limit($limit);

				$aShop_Item_Prices = $oShop_Item_Prices->findAll(FALSE);

				foreach ($aShop_Item_Prices as $oShop_Item_Price)
				{
					$oShop_Item_Price->value = $oShop_Item_Price->Shop_Item->price / 100 * $this->_object->percent;
					$oShop_Item_Price->save();
				}
				$offset += $limit;
			}
			while (count($aShop_Item_Prices));
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}