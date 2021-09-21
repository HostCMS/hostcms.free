<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Module
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		// Добавляем вкладку выпадающий список
		$this->addTabAfter($oSettingsTab, $oMainTab);

		$aConfig = Core_Config::instance()->get($this->_object->path . '_config', array());

		$oCore_Module = Core_Module::factory($this->_object->path);
		$aModule_Options = $oCore_Module->getOptions();

		foreach ($aModule_Options as $option_name => $aOptions)
		{
			$oAdmin_Form_Entity = NULL;

			$aFormat = array();

			if (isset($aOptions['type']))
			{
				$oSettingsTab->add($oSettingsRow = Admin_Form_Entity::factory('Div')->class('row'));

				switch ($aOptions['type'])
				{
					case 'int':
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
							->format($aFormat + array('lib' => array(
								'value' => 'integer'
							)));
					break;
					case 'string':
					default:
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')->format($aFormat);
					break;
					case 'textarea':
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea');
					break;
					case 'float':
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
							->format($aFormat + array('lib' => array(
								'value' => 'decimal'
							)));
					break;
					case 'checkbox':
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');
					break;
					case 'list':
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select');

						if (isset($aOptions['options']))
						{
							$oAdmin_Form_Entity->options($aOptions['options']);
						}
					break;
				}

				if ($oAdmin_Form_Entity)
				{
					$oAdmin_Form_Entity
						->caption(Core::_($this->_object->path . '.option_' . $option_name))
						->name('option_' . $option_name)
						->value(isset($aConfig[$option_name])
							? $aConfig[$option_name]
							: (isset($aOptions['default'])
								? $aOptions['default']
								: ''
							)
						);

					$aOptions['type'] == 'checkbox'
						&& $oAdmin_Form_Entity
							->value(1)
							->checked($aOptions['default'] == 1);

					$oSettingsRow->add($oAdmin_Form_Entity);
				}
			}
		}

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

			$this->addMessage('<script>$.loadNavSidebarMenu({moduleName: \'' . Core_Str::escapeJavascriptVariable($this->_object->path) . '\'})</script>');
		}

		$oCore_Module = Core_Module::factory($this->_object->path);
		$aModule_Options = $oCore_Module->getOptions();

		if (count($aModule_Options))
		{
			$aConfig = Core_Config::instance()->get($this->_object->path . '_config', array());

			foreach ($aModule_Options as $option_name => $aOptions)
			{
				$value = Core_Array::getPost('option_' . $option_name);

				switch ($aOptions['type'])
				{
					case 'int':
						$value = intval($value);
					break;
					case 'float':
						$value = floatval($value);
					break;
					case 'checkbox':
						$value = $value == 1;
					break;
				}

				$aConfig[$option_name] = $value;
			}

			$aConfig = Core_Config::instance()->set($this->_object->path . '_config', $aConfig);
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}