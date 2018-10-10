<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Template_Section.section_form_title_edit', $this->_object->name)
			: Core::_('Template_Section.section_form_title_add'));

		// Получаем основную вкладку
		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
			->value("
				$('.colorpicker').each(function () {
					$(this).minicolors({
						control: $(this).attr('data-control') || 'hue',
						defaultValue: $(this).attr('data-defaultValue') || '',
						inline: $(this).attr('data-inline') === 'true',
						letterCase: $(this).attr('data-letterCase') || 'lowercase',
						// opacity: $(this).attr('data-opacity') || 'true',
						position: $(this).attr('data-position') || 'bottom right',
						change: function (hex, opacity) {
							if (!hex) return;
							if (opacity) hex += ', ' + opacity;
							try {
							} catch (e) { }
						},
						theme: 'bootstrap'
					});
				});
			");

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('alias')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('color')->class('form-control colorpicker')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2);

		$oMainRow2
			->add($oCore_Html_Entity_Script);

		return $this;
	}
}
