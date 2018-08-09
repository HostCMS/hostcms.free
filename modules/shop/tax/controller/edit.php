<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Tax Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Tax_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$title = $this->_object->id
			? Core::_('Shop_Tax.tax_edit_form_title')
			: Core::_('Shop_Tax.tax_add_form_title');

		$this->getField('rate')->format(
				array('maxlen' => array('value' => 5))
			)
			->divAttr(array('class' => "form-group col-xs-6 col-sm-6 col-md-6 col-lg-6"));

		$this->getField('tax_is_included')
			->divAttr(array('class' => "form-group col-xs-6 col-sm-6 col-md-6 col-lg-6 margin-top-21"));

		$oMainTab->delete($this->getField('tax_is_included'));

		$oMainTab->addAfter(
			$this->getField('tax_is_included'), $this->getField('rate')
		);

		$this->title($title);

		return $this;
	}
}