<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Module
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Module_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

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

		if ($this->_object->id)
		{
			// Объект вкладки 'Настройки модуля'
			$oSettingsTab = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Module.tab_parameters'))
				->name('parameters');

			// Добавляем вкладку
			$this->addTabAfter($oSettingsTab, $oMainTab);

			$aConfig = Core_Config::instance()->get($this->_object->path . '_config', array());

			$oCore_Module = Core_Module::factory($this->_object->path);

			if ($oCore_Module)
			{
				$aModule_Options = $oCore_Module->getOptions();

				foreach ($aModule_Options as $option_name => $aOptions)
				{
					$oAdmin_Form_Entity = NULL;

					$aFormat = array();

					if (isset($aOptions['type']))
					{
						$aOptions += array('default' => NULL);

						$oSettingsTab->add($oSettingsRow = Admin_Form_Entity::factory('Div')->class('row'));

						$value = isset($aOptions['value'])
							? $aOptions['value']
							: Core_Array::get($aConfig, $option_name, $aOptions['default']);

						switch ($aOptions['type'])
						{
							case 'int':
							case 'integer':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
									->format($aFormat + array('lib' => array(
										'value' => 'integer'
									)))
									->value($value);
							break;
							case 'string':
							case 'input':
							default:
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
									->format($aFormat)
									->value($value);
							break;
							case 'password':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Password')
									->format($aFormat)
									->value($value);
							break;
							case 'textarea':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea')
									->value($value);
							break;
							case 'float':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
									->format($aFormat + array('lib' => array(
										'value' => 'decimal'
									)))
									->value($value);
							break;
							case 'checkbox':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');
								$oAdmin_Form_Entity
									->value(1)
									->checked($value);
							break;
							case 'list':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select')
									->value($value);

								if (isset($aOptions['options']))
								{
									$oAdmin_Form_Entity->options($aOptions['options']);
								}
							break;
							case 'separator':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Separator');
							break;
							case 'code':
								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Code')
									->html($value);
							break;
							case 'color':
								if (is_null($value))
								{
									$value = '#aebec4';
								}

								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
									->colorpicker(TRUE)
									->set('data-control', 'hue')
									->value($value);
							break;
						}

						if ($oAdmin_Form_Entity)
						{
							isset($oAdmin_Form_Entity->caption) && $oAdmin_Form_Entity
								->caption(isset($aOptions['caption'])
									? $aOptions['caption']
									: Core::_($this->_object->path . '.option_' . $option_name)
								)
								->name('option_' . $option_name);

							$oSettingsRow->add($oAdmin_Form_Entity);
						}
					}
				}
			}
		}

		$this->title($this->_object->id
			? Core::_('Module.modules_edit_form_title', $this->_object->name, FALSE)
			: Core::_('Module.modules_add_form_title')
		);

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

		if ($oCore_Module)
		{
			$oCore_Module->setOptions($_POST);
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}