<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Country_Location_City_Area Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Country_Location_City_Area_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Load object's fields when object has been set
	 * После установки объекта загружаются данные о его полях
	 * @param object $object
	 * @return Shop_Country_Controller_Edit
	 */
	public function setObject($object)
	{
		if (!$object->id)
		{
			$object->shop_country_location_city_id = Core_Array::getGet('shop_city_id');
		}

		parent::setObject($object);

		$title = $this->_object->id
			? Core::_('Shop_Country_Location_City_Area.edit_city_area_tytle', $this->_object->name)
			: Core::_('Shop_Country_Location_City_Area.add_city_area_tytle');

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainTabRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainTabRow1)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainTabRow2)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainTabRow2);

		$this->addTabAfter($oShopCountryLanguageTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Country_Location_City_Area.language_tab'))
			->name('Language'), $oMainTab);

		$oShopCountryLanguageTab
			->add($oShopCountryLanguageRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow8 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow9 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow10 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow11 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow12 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow13 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopCountryLanguageRow14 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name_en'), $oShopCountryLanguageRow1)
			->move($this->getField('name_ru'), $oShopCountryLanguageRow2)
			->move($this->getField('name_de'), $oShopCountryLanguageRow3)
			->move($this->getField('name_fr'), $oShopCountryLanguageRow4)
			->move($this->getField('name_it'), $oShopCountryLanguageRow5)
			->move($this->getField('name_es'), $oShopCountryLanguageRow6)
			->move($this->getField('name_pt'), $oShopCountryLanguageRow7)
			->move($this->getField('name_ua'), $oShopCountryLanguageRow8)
			->move($this->getField('name_be'), $oShopCountryLanguageRow9)
			->move($this->getField('name_pl'), $oShopCountryLanguageRow10)
			->move($this->getField('name_lt'), $oShopCountryLanguageRow11)
			->move($this->getField('name_lv'), $oShopCountryLanguageRow12)
			->move($this->getField('name_cz'), $oShopCountryLanguageRow13)
			->move($this->getField('name_ja'), $oShopCountryLanguageRow14);

		$this->title($title);

		return $this;
	}
}