<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Print_Form Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Print_Form_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$this->addMessage(
			Core_Message::get(Core::_('Shop_Print_Form.attention'), 'error')
		);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oAdditionalTab->delete($this->getField('shop_id'));

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oShopField = Admin_Form_Entity::factory('Select')
			->name('shop_id')
			->caption(Core::_('Shop_Print_Form.shop_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->options(
				$this->_fillShops()
			)
			->value($this->_object->shop_id);

		$oMainRow1->add($oShopField);

		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow1);
		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);
		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);

		$Admin_Form_Entity_Textarea = Admin_Form_Entity::factory('Textarea');

		$oTmpOptions = $Admin_Form_Entity_Textarea->syntaxHighlighterOptions;
		// $oTmpOptions['mode'] = 'application/x-httpd-php';
		$oTmpOptions['mode'] = 'ace/mode/php';

		$Admin_Form_Entity_Textarea
			->value(
				$this->_object->loadPrintFormFile()
			)
			->rows(30)
			->divAttr(array('class' => 'form-group col-xs-12'))
			->caption(Core::_('Shop_Print_Form.print_form_handler'))
			->name('print_form_handler')
			->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
			->syntaxHighlighterOptions($oTmpOptions);

		$oMainRow5->add($Admin_Form_Entity_Textarea);

		$title = $this->_object->id
			? Core::_('Shop_Print_Form.edit_form_title', $this->_object->name)
			: Core::_('Shop_Print_Form.add_form_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Fill shop list
	 * @return array
	 */
	protected function _fillShops()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aShops = $oSite->Shops->findAll();

		$aResult = array(' … ');

		foreach ($aShops as $oShop)
		{
			$aResult[$oShop->id] = $oShop->name;
		}

		return $aResult;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Print_Form_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$this->_object->savePrintFormFile(Core_Array::getRequest('print_form_handler'));

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}