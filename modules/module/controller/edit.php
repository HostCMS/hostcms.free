<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Module
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Module_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Defined constants
	 * @var array
	 */
	protected $_definedConstants = array();

	/**
	 * Config type
	 * @var array
	 */
	protected $_configType = array();

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		// Категоризированный массив констант, используется для определения имени константы по числовому значению
		$this->_definedConstants = get_defined_constants(TRUE);

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

			$oCore_Module = Core_Module_Abstract::factory($this->_object->path);

			if ($oCore_Module)
			{
				$aSettings = $oCore_Module->getSettings();

				if (is_array($aSettings))
				{
					$oSettingsTab->add(
						Admin_Form_Entity::factory('Div')->class('row')
							->add($oTabs = Admin_Form_Entity::factory('Tabs'))
					);

					foreach ($aSettings as $configName => $aSettingsValues)
					{
						$aModule_Options = $oCore_Module->getOptions($configName);

						$oTab = Admin_Form_Entity::factory('Tab')
							->id($configName)
							->caption($configName . '.php')
							->name($configName);

						$oTab->add($oRow = Admin_Form_Entity::factory('Div')->class('row'));

						$aConfig = $oCore_Module->getConfig($configName);
						!is_array($aConfig) && $aConfig = array();

						if (isset($aSettingsValues['data']) && isset($aSettingsValues['type'])
							&& is_array($aSettingsValues['data']) && is_array($aSettingsValues['type'])
						)
						{
							$aDefaultValues = $aSettingsValues['data'];
							// тип констант в конфиге, e.g. array('gd'), array('curl')
							$this->_configType = $aSettingsValues['type'];
						}
						else
						{
							$aDefaultValues = $aSettingsValues;
							$this->_configType = array();
						}

						$this->_getConfigBlock($configName, $oRow, $aConfig, $aDefaultValues, TRUE, $aModule_Options);

						$oTabs->add($oTab);
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
	 * Get config item
	 * @param string $configName
	 * @param bool $isset
	 * @param string $name
	 * @param string $value
	 * @param array $aDefaultValues
	 * @param array $aModule_Options
	 * @return Admin_Form_Entity_Model
	 */
	protected function _getConfigItem($configName, $isset, $name, $value, $aDefaultValues, $aModule_Options = array())
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oAdmin_Form_Entity = NULL;
		$aFormat = array();

		if (isset($aModule_Options[$name]))
		{
			$aOptions = $aModule_Options[$name] + array('default' => NULL);

			$type = $aOptions['type'];

			// value динамически автором модуля формируется или читается из config
			$value = isset($aOptions['value'])
				? $aOptions['value']
				: (!is_null($value) ? $value : $aOptions['default']);
		}
		else
		{
			$type = gettype($value);
		}

		$bCheckbox = FALSE;

		// if new line string => textarea
		if ($type == 'string' && strpos($value, "\n") !== FALSE)
		{
			$type = 'textarea';
		}

		$oPrev_Admin_Form_Entity = $oLast_Admin_Form_Entity = NULL;

		switch ($type)
		{
			case 'string':
			case 'input':
			default:
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
					->format($aFormat)
					->value($value);
			break;
			case 'int':
			case 'integer':
				if (in_array($name, array('fileMode', 'dirMode')))
				{
					$value = 0 . decoct($value);
				}

				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
					->format($aFormat + array('lib' => array(
						'value' => 'integer'
					)))
					->value($value);
			break;
			case 'array':
				// echo "<pre>"; var_dump($value); echo "</pre>";

				if (Core_Array::isList($value))
				{
					$value = implode(', ', $value);

					$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
						->format($aFormat)
						->value($value);
				}
				else
				{
					$oAdmin_Form_Entity = Admin_Form_Entity::factory('Div')/*->style('margin-left: 20px;')*/;

					$this->_getConfigBlock($configName . "[{$name}]", $oAdmin_Form_Entity, $value, $aDefaultValues, $isset);
				}
			break;
			case 'boolean':
			case 'checkbox':
				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');
				$oAdmin_Form_Entity
					->value(1)
					->checked($value);

				$bCheckbox = TRUE;
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
				is_null($value)
					&& $value = '#aebec4';

				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
					->colorpicker(TRUE)
					->set('data-control', 'hue')
					->value($value);
			break;
			case 'xsl':
				$oXsl_Controller_Edit = new Xsl_Controller_Edit($this->_Admin_Form_Action);
				$aXslDirs = $oXsl_Controller_Edit->fillXslDir(0);

				$xsl_id = $xsl_dir_id = 0;

				$oXsl = Core_Entity::factory('Xsl')->getByName($value);
				if ($oXsl)
				{
					$xsl_id = $oXsl->id;
					$xsl_dir_id = $oXsl->xsl_dir_id;
				}

				$id = $configName . '[' . $name . ']';

				$oPrev_Admin_Form_Entity = 	Admin_Form_Entity::factory('Select')
					->name("xsl_dir_id_{$configName}_{$name}")
					->id("xsl_dir_id_{$configName}_{$name}")
					->class('form-control')
					->divAttr(array('class' => 'col-xs-12 col-sm-6'))
					->options(
						array(' … ') + $aXslDirs
					)
					->value($xsl_dir_id)
					->onchange("$.ajaxRequest({path: hostcmsBackend + '/structure/index.php', context: $.escapeSelector('{$id}'), callBack: [$.loadSelectOptionsCallback, function(){var xsl_id = \$('#{$windowId} #' + $.escapeSelector('{$id}') + ' [value=\'{$xsl_id}\']').get(0) ? {$xsl_id} : 0; \$('#{$windowId} #' + \$.escapeSelector('{$id}')).val(xsl_id)}], action: 'loadXslList',additionalParams: 'xsl_dir_id=' + this.value, windowId: '{$windowId}'}); return false");

				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select')
					->value($xsl_id)
					->class('form-control')
					->divAttr(array('class' => 'col-xs-12 col-sm-6'));

				$oLast_Admin_Form_Entity = Admin_Form_Entity::factory('Script')->value("$('#{$windowId} #xsl_dir_id_{$configName}_{$name}').change();");
			break;
		}

		$oAdmin_Form_Entity_Div = Admin_Form_Entity::factory('Div')
			->class('col-xs-12 option-row');

		$oAdmin_Form_Entity_Div
			->add(Admin_Form_Entity::factory('Checkbox')
				// ->name("config_checkbox[{$name}]")
				->name('')
				->class('form-control option-check colored-blue check')
				->divAttr(array('class' => !isset($oAdmin_Form_Entity->caption) ? 'pull-left margin-bottom-5' : 'margin-right-10'))
				->value(1)
				->checked($isset)
				->onclick('$.changeModuleOption(this, "' . $windowId . '", "' . $configName . '[' . $name . ']")')
		);

		if (isset($oAdmin_Form_Entity->caption))
		{
			if (isset($aOptions['caption']))
			{
				$caption = $aOptions['caption'];
			}
			else
			{
				if (Core_I18n::instance()->check($i18 = $this->_object->path . '.option_' . $name))
				{
					$caption = Core::_($i18);
				}
				else
				{
					$caption = $name;

					if (is_numeric($name) && count($this->_configType))
					{
						foreach ($this->_configType as $configType)
						{
							if (isset($this->_definedConstants[$configType]))
							{
								$caption = array_search($name, $this->_definedConstants[$configType]);
								break;
							}
						}
					}
				}
			}

			$oAdmin_Form_Entity
				->id("{$configName}[{$name}]")
				->divAttr($type != 'xsl' ? array('class' => '') : $oAdmin_Form_Entity->divAttr)
				// ->caption($caption)
				->name("{$configName}[{$name}]");

			if ($type != 'xsl')
			{
				$oAdmin_Form_Entity->caption($caption);
			}

			if ($bCheckbox)
			{
				$oAdmin_Form_Entity->id = '';
				$oAdmin_Form_Entity->name = '';
				$oAdmin_Form_Entity->onclick = '$.changeModuleOptionValue(this, "' . $windowId . '", "' . $configName . '[' . $name . ']")';

				$oHiddenInput = Admin_Form_Entity::factory('Input')
					->type('hidden')
					->id("{$configName}[{$name}]")
					->class('')
					->divAttr(array('class' => ''))
					->name("{$configName}[{$name}]")
					->value($value ? 1 : 0);

				!$isset && $oHiddenInput->disabled('disabled');

				$oAdmin_Form_Entity_Div->add($oHiddenInput);
			}

			!$isset && $oAdmin_Form_Entity->disabled('disabled');
		}
		else
		{
			$oAdmin_Form_Entity_Div
				->class('col-xs-12 option-row-parent')
				->add(
					Admin_Form_Entity::factory('Div')
						->class('semi-bold')
						->value($name)
				);
		}

		$parentDiv = $oAdmin_Form_Entity_Div;

		if (!is_null($oPrev_Admin_Form_Entity) || !is_null($oLast_Admin_Form_Entity))
		{
			$parentDiv = Admin_Form_Entity::factory('Div')
				->class('row');

			if ($type == 'xsl')
			{
				$parentDiv->add(
					Admin_Form_Entity::factory('Span')
						->divAttr(array('class' => ''))
						->class('caption col-xs-12')
						->value($caption)
				);
			}

			$oAdmin_Form_Entity_Div->add($parentDiv);
		}

		if (!is_null($oPrev_Admin_Form_Entity))
		{
			$parentDiv->add($oPrev_Admin_Form_Entity);
		}

		$parentDiv->add($oAdmin_Form_Entity);

		if (!is_null($oLast_Admin_Form_Entity))
		{
			$parentDiv->add($oLast_Admin_Form_Entity);
		}

		return $oAdmin_Form_Entity_Div;
	}

	/**
	 * Get config block
	 * @param string $configName
	 * @param object $parentObject
	 * @param array $aConfig
	 * @param array $aDefaultValues
	 * @param boolean $parentIsset
	 * @param array $aModule_Options
	 */
	protected function _getConfigBlock($configName, $parentObject, array $aConfig, array $aDefaultValues, $parentIsset = TRUE, $aModule_Options = array())
	{
		foreach ($aConfig as $name => $value)
		{
			$aAdmin_Form_Entity = array();

			// before unset
			$aTmpDefaultValue = isset($aDefaultValues[$name]) ? $aDefaultValues[$name] : array();

			// Если в образцовом есть еще значения
			if (count($aDefaultValues))
			{
				// Первый ключ в образцовом конфиге
				$nextDefaultKey = key($aDefaultValues);
				// Если первый ключ в образцовом не соответсвует текущему, и в текущем нет такой опции где-то ниже
				if ($name != $nextDefaultKey && !isset($aConfig[$nextDefaultKey]))
				{
					// Показываем элемент из образцового
					$aAdmin_Form_Entity[] = $this->_getConfigItem($configName, FALSE, $nextDefaultKey, $aDefaultValues[$nextDefaultKey], array());
					unset($aDefaultValues[$nextDefaultKey]);
				}
			}

			// В образцовом есть показываемый элемент из конфига, удаляем его, так как показываем из текущего конфига
			if (array_key_exists($name, $aDefaultValues))
			{
				unset($aDefaultValues[$name]);
			}

			// Элемент из текущего конфига
			$aAdmin_Form_Entity[] = $this->_getConfigItem($configName, $parentIsset, $name, $value, $aTmpDefaultValue, $aModule_Options);

			// Добавляем выбранные 1-2 элемента
			foreach ($aAdmin_Form_Entity as $oAdmin_Form_Entity)
			{
				$oAdmin_Form_Entity
					&& $parentObject->add($oAdmin_Form_Entity);
			}
		}

		// Оставшиеся элементы, которые не были затронуты при цикле по $aConfig
		foreach ($aDefaultValues as $name => $value)
		{
			$oAdmin_Form_Entity = $this->_getConfigItem($configName, FALSE, $name, $value, array(), $aModule_Options);

			$oAdmin_Form_Entity
				&& $parentObject->add($oAdmin_Form_Entity);
		}

		//echo "<br>Осталось" . count($aDefaultValues); var_dump($aDefaultValues);
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
     */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$path = Core_Array::getRequest('path', '', 'trim');
			$id = Core_Array::getRequest('id', 0, 'int');

			$oSameModule = Core_Entity::factory('Module')->getByPath($path);

			if (!is_null($oSameModule) && $oSameModule->id != $id)
			{
				$this->addMessage(
					Core_Message::get(Core::_('Module.add_error'), 'error')
				);

				return TRUE;
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Module_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oldActive = $this->_object->id && $this->_object->active;
		$bAdd = !$this->_object->id;

		parent::_applyObjectProperty();

		if ($oldActive != $this->_object->active)
		{
			$this->_object->setupModule();

			$this->addMessage('<script>$.loadNavSidebarMenu({moduleName: \'' . Core_Str::escapeJavascriptVariable($this->_object->path) . '\'})</script>');
		}

		if (!$bAdd)
		{
			$oCore_Module = Core_Module_Abstract::factory($this->_object->path);

			if ($oCore_Module)
			{
				$aSettings = $oCore_Module->getSettings();

				if (is_array($aSettings))
				{
					foreach ($aSettings as $configName => $aConfigValues)
					{
						if (isset($aConfigValues['data']) && isset($aConfigValues['type'])
							&& is_array($aConfigValues['data']) && is_array($aConfigValues['type'])
						)
						{
							// тип констант в конфиге, e.g. array('gd'), array('curl')
							$type = $aConfigValues['type'];
						}
						else
						{
							$type = array();
						}

						// backward compatibility for third party ->setOptions($data) solutions
						/*if ($configName == 'config' && isset($_POST[$configName]) && is_array($_POST[$configName]))
						{
							foreach ($_POST['config'] as $option_name => $value)
							{
								$_POST['config']["option_{$option_name}"] = $value;
							}
						}*/

						$oCore_Module
							->configName($configName)
							->configType($type)
							->setOptions(Core_Array::getPost($configName));
					}
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}