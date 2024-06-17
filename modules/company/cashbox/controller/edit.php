<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Cashbox_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Company_Cashbox_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->company_id = Core_Array::getGet('company_id', 0, 'int');
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title($this->_object->id
			? Core::_('Company_Cashbox.edit_title', $this->_object->name, FALSE)
			: Core::_('Company_Cashbox.add_title')
		);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->format(array('minlen' => array('value' => 1)))->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
			->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12 col-sm-9 margin-top-21')), $oMainRow2)
			;

		return $this;
	}
}