<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SEO Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(
			$this->_object->id
				? Core::_('Seo.edit_title')
				: Core::_('Seo.add_title')
		);

		$oMainTab = $this->getTab('main');

		// Закладка обратных ссылок
		$oLinksTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_links'))
			->name('links');
		$this->addTabAfter($oLinksTab, $oMainTab);

		// Закладка проиндексированных страниц
		$oIndexedTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_indexed'))
			->name('indexed');
		$this->addTabAfter($oIndexedTab, $oLinksTab);

		// Закладка каталогов
		$oCatalogTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_catalog'))
			->name('catalog');
		$this->addTabAfter($oCatalogTab, $oIndexedTab);

		// Закладка счетчиков
		$oCounterTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Seo.tab_counter'))
			->name('counter');
		$this->addTabAfter($oCounterTab, $oCatalogTab);

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oLinksTab
			->add($oLinksTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oLinksTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oIndexedTab
			->add($oIndexedTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oIndexedTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oCatalogTab
			->add($oCatalogTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCatalogTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCatalogTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCatalogTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oCounterTab
			->add($oCounterTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCounterTabRow5 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainTab->move($this->getField('tcy')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow1);
		$oMainTab->move($this->getField('tcy_topic')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow1);

		// Закладка обратных ссылок
		$oMainTab->move($this->getField('yandex_links')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oLinksTabRow1);
		$oMainTab->move($this->getField('google_links')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oLinksTabRow1);
		$oMainTab->move($this->getField('yahoo_links')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oLinksTabRow2);
		$oMainTab->move($this->getField('bing_links')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oLinksTabRow2);

		// Закладка проиндексированных страниц
		$oMainTab->move($this->getField('yandex_indexed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oIndexedTabRow1);
		$oMainTab->move($this->getField('google_indexed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oIndexedTabRow1);
		$oMainTab->move($this->getField('yahoo_indexed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oIndexedTabRow2);
		$oMainTab->move($this->getField('bing_indexed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oIndexedTabRow2);

		// Закладка каталогов
		$oMainTab->move($this->getField('yandex_catalog')->divAttr(array('class' => 'form-group col-xs-12')), $oCatalogTabRow1);
		$oMainTab->move($this->getField('rambler_catalog')->divAttr(array('class' => 'form-group col-xs-12')), $oCatalogTabRow2);
		$oMainTab->move($this->getField('dmoz_catalog')->divAttr(array('class' => 'form-group col-xs-12')), $oCatalogTabRow3);
		$oMainTab->move($this->getField('mail_catalog')->divAttr(array('class' => 'form-group col-xs-12')), $oCatalogTabRow4);

		// Закладка счетчиков
		$oMainTab->move($this->getField('rambler_counter')->divAttr(array('class' => 'form-group col-xs-12')), $oCounterTabRow1);
		$oMainTab->move($this->getField('spylog_counter')->divAttr(array('class' => 'form-group col-xs-12')), $oCounterTabRow2);
		$oMainTab->move($this->getField('hotlog_counter')->divAttr(array('class' => 'form-group col-xs-12')), $oCounterTabRow3);
		$oMainTab->move($this->getField('liveinternet_counter')->divAttr(array('class' => 'form-group col-xs-12')), $oCounterTabRow4);
		$oMainTab->move($this->getField('mail_counter')->divAttr(array('class' => 'form-group col-xs-12')), $oCounterTabRow5);

		return $this;
	}
}