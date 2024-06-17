<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Template_Section_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdminFormAction action
	 */
	public function __construct(Admin_Form_Action_Model $oAdminFormAction)
	{
		parent::__construct($oAdminFormAction);
	}

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		// При добавлении объекта
		if (!$object->id)
		{
			$object->template_id = Core_Array::getGet('template_id');
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
			? Core::_('Template_Section.section_form_title_edit', $this->_object->name, FALSE)
			: Core::_('Template_Section.section_form_title_add')
		);

		// Получаем основную вкладку
		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('alias')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('color')->colorpicker(TRUE)->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('prefix')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			->move($this->getField('suffix')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);

		return $this;
	}
}
