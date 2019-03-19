<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Absence_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Absence_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('employee_id')
			->addSkipColumn('datetime');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('reason')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('user_absence_type_id'));

		$aTypes = array();

		$aUser_Absence_Types = Core_Entity::factory('User_Absence_Type', 0)->findAll();

		foreach ($aUser_Absence_Types as $oUser_Absence_Type)
		{
			$aTypes[$oUser_Absence_Type->id] = array(
				'value' => $oUser_Absence_Type->name,
				'color' => $oUser_Absence_Type->color
			);
		}

		$oDropdownlistTypes = Admin_Form_Entity::factory('Dropdownlist')
			->options($aTypes)
			->name('user_absence_type_id')
			->value($this->_object->user_absence_type_id)
			->caption(Core::_('User_Absence.user_absence_type_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

		$oMainRow2->add($oDropdownlistTypes);

		$oMainTab
			->move($this->getField('start')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('end')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);

		$oMainRow3
			->add(
				Admin_Form_Entity::factory('Code')
					->html('<div class="form-group col-xs-12 col-sm-4">
							<span class="caption">' . Core::_('User_Absence.datetime') . '</span>
							<i class="fa fa-clock-o" style="margin-right: 5px;"></i><span>' . date('d.m.Y H:i', Core_Date::sql2timestamp($this->_object->datetime)) . '</span>
						</div>'
					)
			);

		$oEmployee = $this->_object->Employee;

		$name = strlen($oEmployee->getFullName())
			? $oEmployee->getFullName()
			: $oEmployee->login;

		$title = $this->_object->id
			? Core::_('User_Absence.edit_title', $name)
			: Core::_('User_Absence.add_title');

		$this->title($title);

		return $this;
	}
}