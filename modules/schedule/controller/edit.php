<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
				? Core::_('Schedule.edit_title', $this->_object->getActionName())
				: Core::_('Schedule.add_title')
		);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab->delete($this->getField('completed'));

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('start_datetime')
			->class('form-control input-lg')
			->divAttr(array('class' => 'form-group col-xs-12 col-md-4'));

		$this->getField('datetime')
			->class('form-control input-lg')
			->divAttr(array('class' => 'form-group col-xs-12 col-md-4'));

		$oMainTab
			->move($this->getField('start_datetime'), $oMainRow1)
			->move($this->getField('datetime'), $oMainRow1);

		$oMainTab->delete($this->getField('interval'));

		$aInterval = $this->_object->getInterval();

		$oMainRow1
			->add(Admin_Form_Entity::factory('Input')
				->name('interval')
				->caption(Core::_('Schedule.interval'))
				->class('input-lg form-control')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2'))
				->value($aInterval['value'])
			)
			->add(Admin_Form_Entity::factory('Select')
				->name('type')
				->class('form-control input-lg')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2 margin-top-21'))
				->options(array(
					0 => Core::_('Schedule.seconds'),
					1 => Core::_('Schedule.minutes'),
					2 => Core::_('Schedule.hours'),
					3 => Core::_('Schedule.days')
				))
				->value($aInterval['type'])
			);

		$oAdditionalTab->delete($this->getField('module_id'));

		// Добавляем список модулей
		$oMainRow2->add(
			Admin_Form_Entity::factory('Select')
				->name('module_id')
				->caption(Core::_('Schedule.module_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options(
					array(' … ') + $this->fillModules()
				)
				->value($this->_object->module_id)
				->onchange("$.ajaxRequest({path: '/admin/schedule/index.php', context: 'scheduleAction', callBack: $.loadSelectOptionsCallback, action: 'loadModuleActions', additionalParams: 'module_id=' + this.value,windowId: '{$windowId}'}); return false")
			);

		$oMainTab->delete($this->getField('action'));

		$oSchedule_Controller = new Schedule_Controller();

		$aModuleActions = $oSchedule_Controller->getModuleActions($this->_object->module_id);

		// Добавляем список действий
		$oMainRow2->add(Admin_Form_Entity::factory('Select')
			->id('scheduleAction')
			->name('action')
			->caption(Core::_('Schedule.action'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->options(is_array($aModuleActions) && count($aModuleActions) ? $aModuleActions : array(' … '))
			->value($this->_object->action));

		$oMainTab->move($this->getField('entity_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2);

		$oMainTab->move($this->getField('description'), $oMainRow3);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Printlayout_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['interval'] = intval(Core_Array::getPost('interval'));

		parent::_applyObjectProperty();

		$this->_object->convertInterval(Core_Array::getPost('type', 0));

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill list of modules
	 * @return array
	 */
	public function fillModules()
	{
		$aReturn = array();

		$aObjects = Core_Entity::factory('Module')->getAllByActive(1);
		foreach ($aObjects as $oObject)
		{
			$oCore_Module = Core_Module::factory($oObject->path);
			if ($oCore_Module)
			{
				$aScheduleActions = $oCore_Module->getScheduleActions();
				if (count($aScheduleActions))
				{
					$aReturn[$oObject->id] = $oObject->name;
				}
			}
		}

		return $aReturn;
	}
}