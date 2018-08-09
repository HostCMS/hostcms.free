<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Module
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Module_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$title = $this->_object->id
			? Core::_('Module.modules_edit_form_title', $this->_object->name)
			: Core::_('Module.modules_add_form_title');

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('description')->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab->move($this->getField('active'), $oMainRow1);

		$this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));
		$oMainTab->move($this->getField('active'), $oMainRow1);

		$this->getField('indexing')->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));
		$oMainTab->move($this->getField('indexing'), $oMainRow1);

		$this->getField('path')->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));
		$oMainTab->move($this->getField('path'), $oMainRow1);

		$this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));
		$oMainTab->move($this->getField('sorting'), $oMainRow1);

		// Объект вкладки 'Настройки модуля'
		$oSettingsTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Module.tab_parameters'))
			->name('parameters');

		$oSettingsTab
			->add($oSettingsRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		// Добавляем вкладку выпадающий список
		$this->addTabAfter($oSettingsTab, $oMainTab);

		// Создаем текстовое поле "PHP-код с параметрами модуля"
		$oParameters = Admin_Form_Entity::factory('Textarea');

		$oParameters
			->value($this->_object->loadConfigFile())
			->rows(30)
			->caption(Core::_('Module.modules_add_form_params'))
			->name('parameters');

		// Добавляем на вкладку 'Настройки модуля' большое текстовое поле "PHP-код с параметрами модуля"
		$oSettingsRow1->add($oParameters);

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Module_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oldActive = $this->_object->id && $this->_object->active;
		parent::_applyObjectProperty();

		if ($oldActive != $this->_object->active)
		{
			$this->_object->setupModule();
			
			$this->addMessage('<script type="text/javascript">$.loadNavSidebarMenu({moduleName: \'' . Core_Str::escapeJavascriptVariable($this->_object->path) . '\'})</script>');
		}

		$this->_object->saveConfigFile(Core_Array::getPost('parameters'));

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}