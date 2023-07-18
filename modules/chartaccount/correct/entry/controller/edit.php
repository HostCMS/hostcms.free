<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount Type Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Chartaccount_Correct_Entry_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title(
			$this->_object->id
				? Core::_('Chartaccount_Correct_Entry.edit_title')
				: Core::_('Chartaccount_Correct_Entry.add_title')
		);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab
			->delete($this->getField('debit'))
			->delete($this->getField('credit'));

		$aOptions = Chartaccount_Controller::getOptions();

		$oDebitSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount_Correct_Entry.debit'))
			->class('form-control input-lg')
			->options($aOptions)
			->name('debit')
			->value($this->_object->debit)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

		$oMainRow1->add($oDebitSelect);

		$oCreditSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Chartaccount_Correct_Entry.credit'))
			->class('form-control input-lg')
			->options($aOptions)
			->name('credit')
			->value($this->_object->credit)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

		$oMainRow1->add($oCreditSelect);

		return $this;
	}
}