<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Absence_Type_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Absence_Type_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->class('form-control colorpicker minicolors-input')
			->value($sColorValue);

		$oScript = Admin_Form_Entity::factory('Script')
			->value("$('.colorpicker').each(function () {
						$(this).minicolors({
							control: $(this).attr('data-control') || 'hue',
							defaultValue: $(this).attr('data-defaultValue') || '',
							inline: $(this).attr('data-inline') === 'true',
							letterCase: $(this).attr('data-letterCase') || 'lowercase',
							opacity: $(this).attr('data-opacity'),
							position: $(this).attr('data-position') || 'bottom left',
							change: function (hex, opacity) {
								if (!hex) return;
								if (opacity) hex += ', ' + opacity;
								try {
									console.log(hex);
								} catch (e) { }
							},
							theme: 'bootstrap'
						});
					});"
			);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->move($this->getField('abbr')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2)
			->add($oScript);

		$title = $this->_object->id
			? Core::_('User_Absence_Type.edit_title', $this->_object->name)
			: Core::_('User_Absence_Type.add_title');

		$this->title($title);

		return $this;
	}
}