<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lib_Property_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->lib_id = Core_Array::getGet('lib_id');
		}

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Lib_Property.lib_property_form_title_edit')
			: Core::_('Lib_Property.lib_property_form_title_add'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oHtmlFormSelect = Admin_Form_Entity::factory('Select')
			->options(array(
				0 => Core::_('Lib_Property.lib_property_type_0'),
				1 => Core::_('Lib_Property.lib_property_type_1'),
				2 => Core::_('Lib_Property.lib_property_type_2'),
				3 => Core::_('Lib_Property.lib_property_type_3'),
				4 => Core::_('Lib_Property.lib_property_type_4'),
				5 => Core::_('Lib_Property.lib_property_type_5'),
				7 => Core::_('Lib_Property.lib_property_type_7')
			))
			->name('type')
			->value($this->_object->type)
			->caption(Core::_('Lib_Property.type'))
			->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1,2,3,4,5,7])");

		// Получаем основную вкладку
		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$this->getField('sql_request')
			->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-5 hidden-7'));
		$this->getField('sql_caption_field')
			->divAttr(array('class' => 'form-group col-md-6 col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-5 hidden-7'));
		$this->getField('sql_value_field')
			->divAttr(array('class' => 'form-group col-md-6 col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-5 hidden-7'));

		$oMainTab
			->move($this->getField('name'), $oMainRow1)
			->move($this->getField('description'), $oMainRow2)
			->move($this->getField('varible_name')->divAttr(array('class' => 'form-group col-md-6 col-xs-12')), $oMainRow3)
			->move($this->getField('default_value')->divAttr(array('class' => 'form-group col-md-6 col-xs-12')), $oMainRow5)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-md-3 col-xs-12')), $oMainRow5)
			->move($this->getField('multivalue')->divAttr(array('class' => 'margin-top-21 form-group col-md-3 col-xs-12')), $oMainRow5)
			->move($this->getField('sql_request'), $oMainRow6)
			->move($this->getField('sql_caption_field'), $oMainRow7)
			->move($this->getField('sql_value_field'), $oMainRow7);

		// Удаляем стандартный <input>
		$oMainTab->delete($this->getField('type'));

		$oMainRow3->add($oHtmlFormSelect->divAttr(array('class' => 'form-group col-md-6 col-xs-12')));

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html(
			"<script>radiogroupOnChange('{$windowId}', {$this->_object->type}, [0,1,2,3,4,5,7])</script>"
		);

		$oMainTab->add($oAdmin_Form_Entity_Code);

		return $this;
	}
}
