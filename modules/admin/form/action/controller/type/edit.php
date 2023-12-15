<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Typical editing controller
 *
 * Типовой контроллер редактирования.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Edit extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title', // Form Title
		'pageTitle', // Form Title
		'skipColumns', // Array of skipped columns
		'tabClass', // Additional class for Admin_Form_Entity_Tab
		'tabsClass', // Additional class for Admin_Form_Entity_Tabs
		'autosave'
	);

	/**
	 * Model's key list
	 * @var array
	 */
	protected $_keys = array();

	/**
	 * Form's ID
	 * @var string
	 */
	protected $_formId = NULL;

	/**
	 * Form
	 * @var Admin_Form_Entity_Form
	 */
	protected $_Admin_Form_Entity_Form = NULL;

	/**
	 * Stores POST, which can change the controller
	 * @var mixed
	 */
	protected $_formValues = NULL;

	/**
	 * Set _formValues
	 * @param array $values
	 * @return self
	 */
	public function setFormValues(array $values)
	{
		$this->_formValues = $values;
		return $this;
	}

	/**
	 * Get _formValues
	 * @return array
	 */
	public function getFormValues()
	{
		return $this->_formValues;
	}

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->_formId = 'formEdit' . rand(0, 99999);

		// Set default title
		$oAdmin_Word = $this->_Admin_Form_Action->Admin_Word->getWordByLanguage(
			Core_Entity::factory('Admin_Language')->getCurrent()->id
		);
		$this->title = is_object($oAdmin_Word) ? $oAdmin_Word->name : 'undefined';

		$this->autosave = TRUE;

		// Пропускаемые свойства модели
		$this->skipColumns = array(
			//'user_id',
			'deleted'
		);

		$this->skipColumns = array_combine($this->skipColumns, $this->skipColumns);

		// Далее может быть изменено
		$this->_formValues = $_POST;

		$this->_Admin_Form_Entity_Form = Admin_Form_Entity::factory('Form');
		$this->_Admin_Form_Entity_Form->id($this->_formId);
	}

	/**
	 * Add skiping column
	 * @param string $column column name
	 * @return self
	 */
	public function addSkipColumn($column)
	{
		$this->skipColumns += array($column => $column);
		return $this;
	}

	/**
	 * Remove skiping column
	 * @param string $column column name
	 * @return self
	 */
	public function removeSkipColumn($column)
	{
		if (isset($this->skipColumns[$column]))
		{
			unset($this->skipColumns[$column]);
		}
		return $this;
	}

	/**
	 * Get model's key list
	 * Получение списка ключей модели (PK и FK)
	 * @return self
	 */
	protected function _loadKeys()
	{
		// Массив ключей, которые будут выводиться на дополнительной вкладке
		$this->_keys = array(
			$this->_object->getPrimaryKeyName()
		);

		if (method_exists($this->_object, 'getRelations'))
		{
			$aRelations = $this->_object->getRelations();
			foreach ($aRelations as $relation)
			{
				$this->_keys[] = $relation['foreign_key'];
			}
		}

		if (!empty($this->_keys))
		{
			$this->_keys = array_combine($this->_keys, $this->_keys);
		}

		return $this;
	}

	/**
	 * Form fields
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * Form tabs
	 * @var array
	 */
	protected $_tabs = array();

	/**
	 * Add new tab into form
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab new tab
	 * @return self
	 */
	public function addTab(Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab)
	{
		$this->_tabs[$oAdmin_Form_Entity_Tab->name] = $oAdmin_Form_Entity_Tab;
		return $this;
	}

	/**
	 * Delete tab
	 * @param string $tabName Name of tab
	 * @return self
	 */
	public function deleteTab($tabName)
	{
		if (isset($this->_tabs[$tabName]))
		{
			unset($this->_tabs[$tabName]);
		}

		return $this;
	}
	
	/**
	 * Get all tabs
	 * @return array
	 */
	public function getTabs()
	{
		return $this->_tabs;
	}

	/**
	 * Get tab
	 * @param string $tabName
	 * @return Admin_Form_Entity_Tab
	 */
	public function getTab($tabName)
	{
		foreach ($this->_tabs as $oTab)
		{
			if ($oTab->name == $tabName)
			{
				return $oTab;
			}
		}

		throw new Core_Exception("Tab %tab does not exist.", array('%tab' => $tabName));
	}
	
	/**
	 * Check is tab isset
	 * @param string $tabName tab name
	 * @return boolean
	 */
	public function issetTab($tabName)
	{
		foreach ($this->_tabs as $oTab)
		{
			if ($oTab->name == $tabName)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Move tab before some another tab
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab tab you want to move
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_Before tab before which you want to place
	 * @return self
	 */
	public function moveTabBefore(Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab, Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_Before)
	{
		$this->deleteTab($oAdmin_Form_Entity_Tab);
		$this->addTabBefore($oAdmin_Form_Entity_Tab, $oAdmin_Form_Entity_Tab_Before);
		return $this;
	}

	/**
	 * Move tab after some another tab
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab tab you want to move
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_After tab after which you want to place
	 * @return self
	 */
	public function moveTabAfter(Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab, Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_After)
	{
		$this->deleteTab($oAdmin_Form_Entity_Tab);
		$this->addTabAfter($oAdmin_Form_Entity_Tab, $oAdmin_Form_Entity_Tab_After);
		return $this;
	}

	/**
	 * Add new tab into form before $oAdmin_Form_Entity_Tab_Before
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab new tab
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_Before old tab
	 * @return self
	 */
	public function addTabBefore(Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab, Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_Before)
	{
		// Find key for before object
		$key = array_search($oAdmin_Form_Entity_Tab_Before, $this->_tabs, $strict = TRUE);

		if ($key !== FALSE)
		{
			$aArrayKeys = array_keys($this->_tabs);
			// Порядковый номер для найденного символьного ключа
			$key = array_search($key, $aArrayKeys, TRUE);

			array_splice($this->_tabs, $key, 0, array(/*$oAdmin_Form_Entity_Tab->name =>*/ $oAdmin_Form_Entity_Tab));

			// Keys in the replacement array of the array_splice are not preserved, change key manually
			$aArrayKeys = array_keys($this->_tabs);
			$aArrayKeys[$key] = $oAdmin_Form_Entity_Tab->name;
			$this->_tabs = array_combine($aArrayKeys, $this->_tabs);

			return $this;
		}

		throw new Core_Exception("Before adding tab does not exist.");
	}

	/**
	 * Add new tab into form after $oAdmin_Form_Entity_Tab_After
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab new tab
	 * @param Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_After old tab
	 * @return self
	 */
	public function addTabAfter(Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab, Skin_Default_Admin_Form_Entity_Tab $oAdmin_Form_Entity_Tab_After)
	{
		// Find key for after object
		$key = array_search($oAdmin_Form_Entity_Tab_After, $this->_tabs, $strict = FALSE);

		if ($key !== FALSE)
		{
			$aArrayKeys = array_keys($this->_tabs);
			// Порядковый номер для найденного символьного ключа
			$key = array_search($key, $aArrayKeys, TRUE);

			array_splice($this->_tabs, $key + 1, 0, array(/*$oAdmin_Form_Entity_Tab->name =>*/ $oAdmin_Form_Entity_Tab));

			// Keys in the replacement array of the array_splice are not preserved, change key manually
			$aArrayKeys = array_keys($this->_tabs);
			$aArrayKeys[$key + 1] = $oAdmin_Form_Entity_Tab->name;
			$this->_tabs = array_combine($aArrayKeys, $this->_tabs);

			return $this;
		}

		throw new Core_Exception("After adding tab does not exist.");
	}

	/**
	 * Get all ordinary fields, created by table's fileds
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * Get form field by name
	 * @param string $fieldName name
	 * @return Admin_Form_Entity
	 */
	public function getField($fieldName)
	{
		if (isset($this->_fields[$fieldName]))
		{
			return $this->_fields[$fieldName];
		}

		throw new Core_Exception("Field `%fieldName` does not exist. Check field name and dataset!", array('%fieldName' => $fieldName));
	}

	/**
	 * Add field
	 * @param Admin_Form_Entity $oAdmin_Form_Entity field
	 * @return self
	 */
	public function addField(Admin_Form_Entity $oAdmin_Form_Entity)
	{
		$this->_fields[$oAdmin_Form_Entity->name] = $oAdmin_Form_Entity;
		return $this;
	}

	/**
	 * Load object's fields when object has been set
	 * После установки объекта загружаются данные о его полях
	 * @param object $object
	 * @return Admin_Form_Action_Controller_Type_Edit
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onBeforeSetObject
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onAfterSetObject
	 */
	public function setObject($object)
	{
		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onBeforeSetObject', $this, array($object, $this->_Admin_Form_Controller));

		parent::setObject($object);

		$className = get_class($this);

		$oReflectionClass = new ReflectionClass($className);

		// Получаем имя класса, в котором объявлен _prepareForm
		$classNameWithPrepareForm = $oReflectionClass
			->getMethod('_prepareForm')
			->getDeclaringClass()
			->name;

		// В конечном объекте класс _prepareForm() не был переопределен
		if ($classNameWithPrepareForm != $className)
		{
			$this->_prepareForm();

			// Событие onAfterRedeclaredPrepareForm вызывается в двух местах
			Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));
		}

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterSetObject', $this, array($object, $this->_Admin_Form_Controller));

		return $this;
	}

	protected $_prepeared = FALSE;

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onBeforePrepareForm
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onAfterPrepareForm
	 */
	protected function _prepareForm()
	{
		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onBeforePrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));

		$this->_prepeared = TRUE;

		$this->_loadKeys();

		$aChecked = $this->_Admin_Form_Controller->getChecked();

		$oAdmin_Form = $this->_Admin_Form_Controller->getAdminForm();

		$this->_Admin_Form_Entity_Form
			->data('adminFormId', $oAdmin_Form ? $oAdmin_Form->id : '')
			->data('datasetId', is_array($aChecked) ? key($aChecked) : '')
			->data('autosave', intval($this->autosave));

		// Получение списка полей объекта
		$aColumns = $this->_object->getTableColumns();

		$modelName = $this->_object->getModelName();
		$primaryKeyName = $this->_object->getPrimaryKeyName();

		// Список закладок
		// Основная закладка
		$oAdmin_Form_Tab_EntityMain = Admin_Form_Entity::factory('Tab')
			//->caption(Core::_('admin_form.form_forms_tab_1'))
			->name('main')
			->class($this->tabClass)
			->icon('fas fa-grip-horizontal')
			->iconTitle(Core::_('admin_form.form_forms_tab_1'));

		$this->addTab($oAdmin_Form_Tab_EntityMain);

		//if (!is_null($this->_object->id))
		//{
			// Дополнительные (ключи)
			$oAdmin_Form_Tab_EntityAdditional = Admin_Form_Entity::factory('Tab')
				//->caption(Core::_('admin_form.form_forms_tab_2'))
				->name('additional')
				->class($this->tabClass)
				->icon('fas fa-gear')
				->iconTitle(Core::_('admin_form.form_forms_tab_2'));

			// $oUser = Core_Auth::getCurrentUser();
			// 6.8.7, вкладка возвращена, т.к. на ней бывают данные о GUID
			//!$oUser->superuser && $oAdmin_Form_Tab_EntityAdditional->active(FALSE);

			$this->addTab($oAdmin_Form_Tab_EntityAdditional);
		//}

		// Fields
		if (Core::moduleIsActive('field'))
		{
			$oAdmin_Form_Tab_EntityFields = Admin_Form_Entity::factory('Tab')
				//->caption(Core::_('admin_form.form_forms_tab_1'))
				->name('user_fields')
				->class($this->tabClass)
				->icon('fas fa-user-cog')
				->iconTitle(Core::_('admin_form.form_forms_tab_3'));

			$this->addTabBefore($oAdmin_Form_Tab_EntityFields, $oAdmin_Form_Tab_EntityAdditional);

			Field_Controller_Tab::factory($this->_Admin_Form_Controller)
				->setObject($this->_object)
				->setDatasetId($this->getDatasetId())
				->setTab($oAdmin_Form_Tab_EntityFields)
				// ->template_id($template_id)
				->fillTab();
		}

		foreach ($aColumns as $columnName => $columnArray)
		{
			if (!isset($this->skipColumns[$columnName]))
			{
				$sTabName = isset($this->_keys[$columnName])
					? 'additional'
					: 'main';

				switch ($columnArray['datatype'])
				{
					case 'datetime':
					case 'timestamp':
						$oAdmin_Form_Entity_For_Column = Admin_Form_Entity::factory('DateTime');

						/*$date = ($this->_object->$columnName == '0000-00-00 00:00:00')
							? $this->_object->$columnName
							: Core_Date::sql2datetime($this->_object->$columnName);*/

						$oAdmin_Form_Entity_For_Column
							->value(
								//Core_Date::sql2datetime($this->_object->$columnName)
								$this->_object->$columnName
							);

					break;
					case 'date':
						$oAdmin_Form_Entity_For_Column = Admin_Form_Entity::factory('Date');

						$oAdmin_Form_Entity_For_Column
							->value(
								$this->_object->$columnName
							);
					break;
					case 'time':
						$oAdmin_Form_Entity_For_Column = Admin_Form_Entity::factory('Time');

						$oAdmin_Form_Entity_For_Column
							->value(
								$this->_object->$columnName
							);
					break;
					case 'tinytext':
					case 'text':
					case 'mediumtext':
					case 'longtext':
					case 'tinyblob':
					case 'blob':
					case 'mediumblob':
					case 'longblob':
						$oAdmin_Form_Entity_For_Column = Admin_Form_Entity::factory('Textarea');

						$oAdmin_Form_Entity_For_Column
							->value(
								$this->_object->$columnName
							);
					break;
					case 'tinyint':
					case 'tinyint unsigned':
						// Только при длине 1 символ или пустом для MySql 8 && unsigned
						if ($columnArray['max_length'] == 1 || $columnArray['max_length'] == '' && $columnArray['unsigned'] == 1)
						{
							$oAdmin_Form_Entity_For_Column = Admin_Form_Entity::factory('Checkbox');
							$oAdmin_Form_Entity_For_Column
								->value(1)
								->checked($this->_object->$columnName != 0);

							break;
						}
					default:
						$oAdmin_Form_Entity_For_Column = Admin_Form_Entity::factory('Input');

						$oAdmin_Form_Entity_For_Column->value($this->_object->$columnName);

						if ($sTabName == 'main'
							&& $this->_tabs[$sTabName]->getCountChildren() == 0)
						{
							$oAdmin_Form_Entity_For_Column->class($oAdmin_Form_Entity_For_Column->class . ' input-lg');
						}

						$columnName == 'id'
							&& $oAdmin_Form_Entity_For_Column->readonly('readonly');

					break;
				}

				$format = array();

				// Найден формат по названию столбца
				if (!is_null($oAdmin_Form_Entity_For_Column->getFormat($columnName)))
				{
					$format += array('lib' => array('value' => $columnName));
				}

				switch ($columnArray['type'])
				{
					case 'string':
						if (!is_null($columnArray['max_length']))
						{
							$format += array('maxlen' =>
								// ограничение длины поля
								array('value' => $columnArray['max_length'])
							);
						}

						// При редактировании уже заданной пустой строки все равно требует значение
						/*if (is_null($columnArray['default']) && !$columnArray['null'])
						{
							$format += array('minlen' =>
								// ограничение длины поля
								array('value' => 1)
							);
						}*/
					break;
					case 'int':
						$format += array('lib' => array(
							'value' => 'integer'
						));
						// В ограничение значений
						// $columnArray['min']
						// $columnArray['max']
					break;
				}

				if (!empty($format))
				{
					$oAdmin_Form_Entity_For_Column->format($format);
				}

				$oAdmin_Form_Entity_For_Column
					->name($columnName)
					->caption(Core::_($modelName . '.' . $columnName));

				// На дополнительную или основную вкладку
				/*$sTabName = isset($this->_keys[$columnName])
					? 'oAdmin_Form_Tab_EntityAdditional'
					: 'oAdmin_Form_Tab_EntityMain';*/

				$oEntity_Row = Admin_Form_Entity::factory('Div')->class('row');

				if (/*!is_null($this->_object->getPrimaryKey())
					|| $sTabName == 'main'*/
					!(is_null($this->_object->getPrimaryKey()) && $columnName == $primaryKeyName)
				)
				{
					$this->_tabs[$sTabName]->add(
						$oEntity_Row->add($oAdmin_Form_Entity_For_Column)
					);

					if ($columnName == 'user_id')
					{
						$oAdmin_Form_Entity_For_Column = $this->_addUserIdField($oAdmin_Form_Entity_For_Column, $sTabName);
					}
				}

				!is_null($oAdmin_Form_Entity_For_Column)
					&& $this->addField($oAdmin_Form_Entity_For_Column);
			}
		}

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Add user_id field
	 * @param Admin_Form_Entity_Model $oAdmin_Form_Entity
	 * @param string $sTabName
	 */
	protected function _addUserIdField($oAdmin_Form_Entity, $sTabName)
	{
		if (Core::moduleIsActive('user'))
		{
			$columnName = 'user_id';
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$oCurrentUser = Core_Auth::getCurrentUser();
			$oSite = Core_Entity::factory('Site', CURRENT_SITE);

			$bShowUserSelect = FALSE;
			if (!$oCurrentUser->only_access_my_own)
			{
				$bShowUserSelect = $this->_object->user_id && $this->_object instanceof Core_Entity
					? $this->_object->User->checkSiteAccess($oSite)
					: TRUE;
			}

			if ($bShowUserSelect)
			{
				$this->_tabs[$sTabName]->delete($oAdmin_Form_Entity);

				$placeholder = Core::_('User.select_user');
				$language = Core_I18n::instance()->getLng();

				$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

				$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select')
					->id('responsible_user')
					->options($aSelectResponsibleUsers)
					->name('user_id')
					->value($this->_object->$columnName);

				$oScript = Admin_Form_Entity::factory('Script')
					->value("$('#{$windowId} #responsible_user').selectUser({
							language: '" . $language . "',
							placeholder: '" . $placeholder . "',
							dropdownParent: $('#" . $windowId . "')
						})
						.val('" . $this->_object->$columnName . "')
						.trigger('change.select2')
						.on('select2:unselect', function (){
							$(this)
								.next('.select2-container')
								.find('.select2-selection--single')
								.removeClass('user-container');
						});"
					);

				$this->_tabs[$sTabName]->add(
					Admin_Form_Entity::factory('Div')->class('row')
						->add($oAdmin_Form_Entity)
						->add($oScript)
				);
			}
			else
			{
				$oAdmin_Form_Entity->type('hidden');

				if ($this->_object->user_id)
				{
					$oUser = $this->_object->User;

					$oAdmin_Form_Entity->add(
						Admin_Form_Entity::factory('Code')
							->html($oUser->getAvatarWithName())
					);
				}
			}
		}

		$oAdmin_Form_Entity
			->caption(Core::_('User.backend-field-caption'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

		return $oAdmin_Form_Entity;
	}

	/**
	 * Return
	 *
	 * @var mixed
	 */
	protected $_return = NULL;

	/**
	 * Set return
	 *
	 * @param mixed $return
	 * @return self
	 */
	public function setReturn($return)
	{
		$this->_return = $return;
		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation for action
	 * @return boolean
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onBeforeExecute
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', $this, array($operation, $this->_Admin_Form_Controller));

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		switch ($operation)
		{
			case NULL: // Показ формы
				if (!$this->_prepeared)
				{
					$this->_prepareForm();

					// Событие onAfterRedeclaredPrepareForm вызывается в двух местах
					Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));
				}

				$this->_Admin_Form_Controller
					->title($this->title)
					->pageTitle(!is_null($this->pageTitle) ? $this->pageTitle : $this->title);

				$this->_return = $this->_showEditForm();
			break;
			case 'modal':
				// $windowId = $this->_Admin_Form_Controller->getWindowId();
				//$newWindowId = 'Modal_' . time();

				ob_start();

				if (!$this->_prepeared)
				{
					$this->_prepareForm();

					// Событие onAfterRedeclaredPrepareForm вызывается в двух местах
					Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));
				}

				$this->_Admin_Form_Controller
					->title($this->title);

				$oAdmin_Form_Action_Controller_Type_Edit_Show = Admin_Form_Action_Controller_Type_Edit_Show::create($this->_Admin_Form_Entity_Form)
					->Admin_Form_Controller($this->_Admin_Form_Controller)
					->tabs($this->_getAdmin_Form_Entity_Tabs())
					->buttons($this->_addButtons());

				echo $oAdmin_Form_Action_Controller_Type_Edit_Show->showEditForm();

				$this->addContent(ob_get_clean());

				$this->_return = TRUE;
			break;
			case 'save':
			case 'saveModal':
				$primaryKeyName = $this->_object->getPrimaryKeyName();

				// Значение первичного ключа до сохранения
				$prevPrimaryKeyValue = $this->_object->$primaryKeyName;

				$this->_applyObjectProperty();

				$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId', '', 'str'));

				ob_start();
				$modelName = $this->_object->getModelName();
				$actionName = $this->_Admin_Form_Controller->getAction();

				// При модальном показе $parentWindowId будет название окна родителя
				$parentWindowId == ''
					&& Core_Message::show(Core::_("{$modelName}.{$actionName}_success"));

				if (is_null($prevPrimaryKeyValue))
				{
					$windowId = $this->_Admin_Form_Controller->getWindowId();
					$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

					?><script><?php
					?>$.appendInput('<?php echo Core_Str::escapeJavascriptVariable($modalWindowId ? $modalWindowId : $windowId)?>', '<?php echo Core_Str::escapeJavascriptVariable($primaryKeyName)?>', '<?php echo Core_Str::escapeJavascriptVariable($this->_object->$primaryKeyName)?>');<?php
					?>$.addHostcmsChecked('<?php echo Core_Str::escapeJavascriptVariable($modalWindowId ? $modalWindowId : $windowId)?>', '<?php echo Core_Str::escapeJavascriptVariable($this->getDatasetId())?>', '<?php echo Core_Str::escapeJavascriptVariable($this->_object->$primaryKeyName)?>');<?php
					?></script><?php
				}
				$this->addMessage(ob_get_clean());

				$this->_return = $parentWindowId == '';
				// $this->_return = TRUE;
			break;
			case 'applyModal':
				$this->_applyObjectProperty();

				//$windowId = $this->_Admin_Form_Controller->getWindowId();
				$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
				$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId', '', 'str'));
				$this->addContent("<script>$('#" . Core_Str::escapeJavascriptVariable($modalWindowId) . "').parents('.bootbox').modal('hide');</script>");

				// При модальном показе $parentWindowId будет название окна родителя
				$this->_return = $parentWindowId == '';
				//$this->_return = TRUE;
			break;
			case 'markDeleted':
				// Закрытие окна происходит на самой кнопке удаления справа от основных кнопок
				//$windowId = $this->_Admin_Form_Controller->getWindowId();
				//$this->addContent("<script>$('#" . Core_Str::escapeJavascriptVariable($windowId) . "').parents('.bootbox').modal('hide');</script>");
				$this->_return = TRUE;
			break;
			default:
				$this->_applyObjectProperty();
				$this->_return = FALSE; // Показываем форму
			break;
		}

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterExecute', $this, array($operation, $this->_Admin_Form_Controller));

		return $this->_return;
	}

	/**
	 * Correct Value by $columnArray
	 * @param mixed $value
	 * @param array $columnArray
	 * @return mixed
	 */
	protected function _correctValue($value, $columnArray)
	{
		switch ($columnArray['datatype'])
		{
			case 'timestamp':
			case 'datetime':
				$value = $value != ''
					? Core_Date::datetime2sql($value)
					: '0000-00-00 00:00:00';
			break;
			case 'date':
				$value = $value != ''
					? Core_Date::date2sql($value)
					: '0000-00-00';
			break;
			case 'tinytext':
			case 'text':
			case 'mediumtext':
			case 'longtext':
			case 'tinyblob':
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				// Nothing to do
			break;
			case 'tinyint':
			case 'tinyint unsigned':
				// Только при длине 1 символ или пустом для MySql 8 && unsigned
				if ($columnArray['max_length'] == 1 || $columnArray['max_length'] == '' && $columnArray['unsigned'] == 1)
				{
					// Checkbox
					$value = is_null($value) ? 0 : $value;
				}
				elseif (!is_null($value) || !$columnArray['null'] && $columnArray['default'] != 'NULL' && strpos($columnArray['extra'], 'auto_increment') === FALSE)
				{
					$value = $columnArray['zerofill']
						? preg_replace('/[^0-9\.\-]/', '', $value)
						: intval($value);
				}
			break;
			case 'smallint':
			case 'smallint unsigned':
			case 'mediumint':
			case 'mediumint unsigned':
			case 'int':
			case 'int unsigned':
			case 'integer unsigned':
				(!is_null($value) || !$columnArray['null'] && $columnArray['default'] != 'NULL' && strpos($columnArray['extra'], 'auto_increment') === FALSE)
					&& $value = $columnArray['zerofill']
						? preg_replace('/[^0-9\.\-]/', '', $value)
						: intval($value);
			break;
			case 'decimal':
				if (!is_null($value))
				{
					$value = str_replace(',', '.', $value);

					// Remove everything except numbers and dot
					$value = preg_replace('/[^0-9\.\-]/', '', $value);

					$value == '' && $value = 0;

					if ($value != 0 && isset($columnArray['max_length']))
					{
						$aMaxLength = explode(',', $columnArray['max_length']);
						if (count($aMaxLength) == 2)
						{
							$maxValue = str_repeat(9, $aMaxLength[0] - $aMaxLength[1]) . '.' . str_repeat(9, $aMaxLength[1]);

							$value > $maxValue && $value = $maxValue;
							$value < -$maxValue && $value = -$maxValue;
						}
					}
				}
				else
				{
					$value = 0;
				}
			break;
			default:
				// Nothing to do
			break;
		}

		return $value;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onBeforeApplyObjectProperty
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onAfterApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		ob_start();

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onBeforeApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		$aColumns = $this->_object->getTableColumns();

		// Show on the additional tab, but not change, while only_access_my_own!
		$oCurrentUser = Core_Auth::getCurrentUser();
		$oCurrentUser->only_access_my_own
			&& $this->skipColumns = $this->skipColumns + array('user_id' => 'user_id');

		// Применение данных к объекту
		foreach ($aColumns as $columnName => $columnArray)
		{
			if (!isset($this->skipColumns[$columnName]))
			{
				$value = Core_Array::get($this->_formValues, $columnName);

				$this->_object->$columnName = $this->_correctValue($value, $columnArray);
			}
		}

		// Autosave
		$this->autosave && $this->_deleteAutosave();

		// Webhooks
		if (Core::moduleIsActive('webhook'))
		{
			$webhookName = 'on' . implode('', array_map('ucfirst', explode('_', $this->_object->getModelName())));

			$this->_object->isEmptyPrimaryKey()
				? Webhook_Controller::notify($webhookName . 'Create', $this->_object)
				: Webhook_Controller::notify($webhookName . 'Update', $this->_object);
		}

		$this->_object->save();

		// Fields
		if (Core::moduleIsActive('field'))
		{
			// Fields
			Field_Controller_Tab::factory($this->_Admin_Form_Controller)
				->setObject($this->_object)
				->applyObjectProperty();
		}

		$message = ob_get_clean();

		!empty($message) && $this->addMessage($message);

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.onAfterApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Delete autosave
	 * @return self
	 */
	protected function _deleteAutosave()
	{
		$aChecked = $this->_Admin_Form_Controller->getChecked();
		$datasetId = is_array($aChecked) ? key($aChecked) : '';

		$oAdmin_Form = $this->_Admin_Form_Controller->getAdminForm();

		if ($datasetId !== '')
		{
			$oAdmin_Form_Autosave = Core_Entity::factory('Admin_Form_Autosave')->getObject($oAdmin_Form->id, $datasetId, intval($this->_object->getPrimaryKey()));

			!is_null($oAdmin_Form_Autosave)
				&& $oAdmin_Form_Autosave->delete();
		}

		// Remove old items
		if (rand(0, 999) == 0)
		{
			Core_QueryBuilder::delete('admin_form_autosaves')
				->where('datetime', '<', Core_Date::timestamp2sql(strtotime('-1 month')))
				->execute();
		}

		return $this;
	}

	/**
	 * Get Admin_Form_Entity_Tabs
	 * @return Admin_Form_Entity_Tabs|NULL
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.getAdmin_Form_Entity_Tabs
	 */
	protected function _getAdmin_Form_Entity_Tabs()
	{
		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit.getAdmin_Form_Entity_Tabs', $this, array($this->_Admin_Form_Controller));

		// Закладки
		if (count($this->_tabs))
		{
			$oAdmin_Form_Entity_Tabs = Admin_Form_Entity::factory('Tabs')
				->formId($this->_Admin_Form_Entity_Form->id)
				->class($this->tabsClass);

			// Add all tabs to $oAdmin_Form_Entity_Tabs
			foreach ($this->_tabs as $oAdmin_Form_Tab_Entity)
			{
				if ($oAdmin_Form_Tab_Entity->deleteEmptyItems()->getCountChildren() > 0)
				{
					$oAdmin_Form_Entity_Tabs->add($oAdmin_Form_Tab_Entity);
				}
			}
		}
		else
		{
			$oAdmin_Form_Entity_Tabs = NULL;
		}

		return $oAdmin_Form_Entity_Tabs;
	}

	/**
	 * Show edit form
	 * @return boolean
	 */
	protected function _showEditForm()
	{
		// Контроллер показа формы редактирования с учетом скина
		$oAdmin_Form_Action_Controller_Type_Edit_Show = Admin_Form_Action_Controller_Type_Edit_Show::create($this->_Admin_Form_Entity_Form)
			->title($this->title)
			->children($this->_children)
			->Admin_Form_Controller($this->_Admin_Form_Controller)
			->tabs($this->_getAdmin_Form_Entity_Tabs())
			->buttons($this->_addButtons());

		ob_start();

		$content = $oAdmin_Form_Action_Controller_Type_Edit_Show->showEditForm();

		$sAdmin_View = NULL;
		if (!is_null($this->_Admin_Form_Controller))
		{
			$sAdmin_View = $this->_Admin_Form_Controller->getWindowId() == 'id_content'
				? $this->_Admin_Form_Controller->Admin_View
				: Admin_View::getClassName('Admin_Internal_View');
		}

		$oAdmin_View = Admin_View::create($sAdmin_View);
		$oAdmin_View
			->children($oAdmin_Form_Action_Controller_Type_Edit_Show->children)
			->pageTitle(!is_null($this->pageTitle) ? $this->pageTitle : $this->title)
			->module($this->_Admin_Form_Controller->getModule())
			->content($content)
			->message($oAdmin_Form_Action_Controller_Type_Edit_Show->message)
			->show();

		$this->addContent(
			//$oAdmin_Form_Action_Controller_Type_Edit_Show->showEditForm()
			ob_get_clean()
		);

		return TRUE;
	}

	/**
	 * Get save button
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _getSaveButton()
	{
		$sOperaion = $this->_Admin_Form_Controller->getOperation();
		$sOperaionSufix = $sOperaion == 'modal' ? 'Modal' : '';

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getRequest('parentWindowId', '', 'str'));

		// remove parentWindowId
		//$additionalParams = preg_replace('/&parentWindowId=[A-Za-z0-9_-]*/', '', $this->_Admin_Form_Controller->additionalParams);

		// Сохранить
		$oAdmin_Form_Entity_Button_Save = Admin_Form_Entity::factory('Button')
			->name('save')
			->id('action-button-save')
			->class('btn btn-blue')
			->value(Core::_('Admin_Form.save'))
			->onclick(
				$this->_Admin_Form_Controller
					->windowId(strlen($parentWindowId) ? $parentWindowId : $windowId)
					//->additionalParams($additionalParams)
					->getAdminSendForm(array('operation' => 'save' . $sOperaionSufix))
			);

		return $oAdmin_Form_Entity_Button_Save;
	}

	/**
	 * Get apply button
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _getApplyButton()
	{
		$sOperaion = $this->_Admin_Form_Controller->getOperation();
		$sOperaionSufix = $sOperaion == 'modal' ? 'Modal' : '';

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getRequest('parentWindowId', '', 'str'));

		// remove parentWindowId
		//$additionalParams = preg_replace('/&parentWindowId=[A-Za-z0-9_-]*/', '', $this->_Admin_Form_Controller->additionalParams);

		$oAdmin_Form_Entity_Button_Apply = Admin_Form_Entity::factory('Button')
			->name('apply')
			->id('action-button-apply')
			->class('btn btn-palegreen')
			->type('submit')
			->value(Core::_('Admin_Form.apply'))
			->onclick(
				// После применения загрузка новой таблицы осуществляется в родительское окно
				$this->_Admin_Form_Controller
					->windowId(strlen($parentWindowId) ? $parentWindowId : $windowId)
					//->additionalParams($additionalParams)
					->getAdminSendForm(array('operation' => 'apply' . $sOperaionSufix))
			);

		return $oAdmin_Form_Entity_Button_Apply;
	}

	/**
	 * Add form buttons
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _addButtons()
	{
		$sOperaion = $this->_Admin_Form_Controller->getOperation();
		//$sOperaionSufix = $sOperaion == 'modal' ? 'Modal' : '';

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getRequest('parentWindowId', '', 'str'));

		// Кнопки
		$oAdmin_Form_Entity_Buttons = Admin_Form_Entity::factory('Buttons');

		// Сохранить
		($oAdmin_Form_Entity_Button_Save = $this->_getSaveButton())
			&& $oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Save);

		// Применить
		($oAdmin_Form_Entity_Button_Apply = $this->_getApplyButton())
			&& $oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Apply);

		$aChecked = $this->_Admin_Form_Controller->getChecked();
		$aFirst = reset($aChecked);

		// Удалить
		if (is_array($aFirst) && key($aFirst))
		{
			$onclick = $this->_Admin_Form_Controller
				->windowId(strlen($parentWindowId) ? $parentWindowId : $windowId)
				->getAdminSendForm($sOperaion == 'modal'
					? array('action' => 'edit', 'operation' => 'markDeleted')
					: array('action' => 'markDeleted')
				);

			if ($sOperaion == 'modal')
			{
				$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
				$modalWindowId != ''
					&& $onclick = "$('#{$modalWindowId}').parents('.bootbox').modal('hide'); " . $onclick;
			}

			$oAdmin_Form_Entity_Button_Delete = Admin_Form_Entity::factory('A')
				->id('action-button-delete')
				->class('btn btn-darkorange pull-right')
				->onclick("res = confirm('" . Core::_('Admin_Form.confirm_dialog', Core::_('Admin_Form.delete')) . "'); if (res) {" . $onclick . " } else { return false }")
				->add(
					Admin_Form_Entity::factory('Code')
						->html('<i class="fa fa-trash no-margin-right"></i>')
				);

			$oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Delete);
		}

		// Возвращаем оригинальный windowId
		$this->_Admin_Form_Controller->windowId($windowId);

		// Back
		if ($sOperaion != 'modal')
		{
			$path = $this->_Admin_Form_Controller->getPath();

			$oAdmin_Form_Entity_Button_Back = Admin_Form_Entity::factory('A')
				->id('action-button-back')
				->class('btn btn-default pull-right margin-right-5')
				->onclick($this->_Admin_Form_Controller->getAdminLoadAjax($path))
				->add(
					Admin_Form_Entity::factory('Code')
						->html('<i class="fa fa-arrow-circle-left no-margin-right darkgray"></i>')
				);

			$oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Back);
		}

		return $oAdmin_Form_Entity_Buttons;
	}
}