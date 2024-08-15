<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
abstract class Admin_Form_Controller extends Core_Servant_Properties
{
	/**
	 * Use skin
	 * @var boolean
	 */
	protected $_skin = TRUE;

	/**
	 * Dataset array
	 * @var array
	 */
	protected $_datasets = array();

	/**
	 * Admin form
	 * @var Admin_Form
	 */
	protected $_Admin_Form = NULL;

	/**
	 * Current language in administrator's center
	 * @var object
	 */
	protected $_Admin_Language = NULL;

	/**
	 * Create new form controller
	 * @param Admin_Form_Model $oAdmin_Form
	 * @return object
	 */
	static public function create(Admin_Form_Model $oAdmin_Form = NULL)
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__;

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $className));
		}

		return new $className($oAdmin_Form);
	}

	/**
	 * 48 icons
	 * @var array
	 */
	static protected $_icon = array(
		'fa fa-address-book', 'fa fa-address-card', 'fa fa-barcode', 'fa fa-bars', 'fa fa-beer', 'fa fa-bell', 'fa fa-bicycle', 'fa fa-binoculars',
		'fa fa-birthday-cake', 'fa fa-bolt', 'fa fa-book', 'fa fa-bookmark', 'fa fa-briefcase', 'fa fa-bullseye', 'fa fa-camera', 'fa fa-car',
		'fa fa-certificate', 'fa fa-cloud', 'fa fa-code', 'fa fa-coffee', 'fa fa-cube', 'fa fa-dashboard', 'fa fa-database', 'fa fa-dot-circle-o',
		'fa fa-flask', 'fa fa-futbol-o', 'fa fa-gift', 'fa fa-glass', 'fa fa-heart', 'fa fa-hourglass', 'fa fa-leaf', 'fa fa-location-arrow',
		'fa fa-magic', 'fa fa-magnet', 'fa fa-paper-plane', 'fa fa-paw', 'fa fa-plane', 'fa fa-plug', 'fa fa-road', 'fa fa-rocket',
		'fa fa-smile-o', 'fa fa-snowflake-o', 'fa fa-space-shuttle', 'fa fa-star', 'fa fa-thumbs-up', 'fa fa-tree', 'fa fa-trophy', 'fa fa-wrench'
	);

	/**
	 * Get Icon for object ID
	 * @param int $id object ID
	 * @return string
	 */
	static public function getIcon($id)
	{
		return self::$_icon[$id % 48];
	}

	/**
	 * Get background color class for object ID
	 * @param int $id object ID
	 * @return string
	 */
	static public function getBackgroundColorClass($id)
	{
		return 'bg-' . ($id % 20);
	}

	/**
	 * Add additional param
	 * @param string $key param name
	 * @param string $value param value
	 * @return self
	 */
	public function addAdditionalParam($key, $value)
	{
		return $this->setAdditionalParam(
			$this->additionalParams . '&' . htmlspecialchars($key) . '=' . rawurlencode($value)
		);
	}

	/**
	 * Set additional param
	 * @param string $key param name
	 * @return self
	 */
	public function setAdditionalParam($value)
	{
		$this->additionalParams = $value;

		// Добавляем замену для additionalParams
		$this->_externalReplace['{additionalParams}'] = $value;

		return $this;
	}

	/**
	 * Form setup
	 * @return self
	 */
	public function setUp()
	{
		if (!defined('DISABLE_COMPRESSION') || !DISABLE_COMPRESSION)
		{
			// Если сжатие уже не включено на сервере директивой zlib.output_compression = On
			// http://php.net/manual/en/function.ini-get.php
			// A boolean ini value of off will be returned as an empty string or "0"
			// while a boolean ini value of on will be returned as "1".
			// The function can also return the literal string of INI value.

			// MSIE 8.0 has problem with the fastpage-enabled content was not being un-gzipped
			if (/*strpos(Core_Array::get($_SERVER, 'HTTP_USER_AGENT'), 'MSIE 8.0') === FALSE
				&& */ini_get('zlib.output_compression') == 0)
			{
				ob_start("ob_gzhandler");
			}
		}

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);
		Core::initConstants($oSite);

		$aTmp = array();
		foreach ($_GET as $key => $value)
		{
			// XSS protect
			if ($oSite->protect && Core_Security::checkXSS($value))
			{
				unset($_GET[$key]);
				unset($_REQUEST[$key]);
			}
			elseif (!is_array($value) && $key != '_' && $key != 'secret_csrf' && strpos($key, 'admin_form_filter_') === FALSE && strpos($key, 'topFilter_') === FALSE)
			{
				//$aTmp[] = htmlspecialchars($key, ENT_QUOTES) . '=' . htmlspecialchars($value, ENT_QUOTES);
				$aTmp[] = htmlspecialchars($key) . '=' . rawurlencode($value);
			}
		}

		$this->setAdditionalParam(implode('&', $aTmp));

		$this->formSettings();

		return $this;
	}

	/**
	 * Escape jQuery selectors
	 * @param string $str string
	 * @return string
	 */
	public function jQueryEscape($str)
	{
		return str_replace(array('%', "'"), array('\\%', "\\'"), strval($str));
	}

	/**
	 * Add view
	 * @param string $name
	 * @param string $className
	 * @return self
	 */
	public function addView($name, $className = NULL)
	{
		$viewList = $this->viewList;
		//$this->viewList = array();

		$viewList[$name] = is_null($className)
			? 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__ . '_' . ucfirst($name)
			: $className;

		$this->viewList = $viewList;

		return $this;
	}

	/**
	 * Data set from _REQUEST
	 * @var array
	 */
	public $request = array();

	/**
	 * Admin_Form_Setting
	 * @var Admin_Form_Setting_Model|NULL
	 */
	protected $_oAdmin_Form_Setting = NULL;

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		//'request', // Нельзя, т.к. к request используется прямой доступ в различных index.php
		// Page title <h1>
		'title',
		// Page title <title>
		'pageTitle',
		// Limits elements on page
		'limit',
		// Current page
		'current',
		// ID of sorting field
		'sortingFieldId',
		// Sorting direction
		'sortingDirection',
		// Current Filter Id
		'filterId',
		// Controller view
		'view',
		'viewList',
		// Action name
		'action',
		// Action's operation e.g. "save" or "apply"
		'operation',
		// Array of checked items
		'checked',
		// Window ID
		'windowId',
		// Use AJAX
		'ajax',
		'module',
		// Is showing operations necessary
		'showOperations',
		// String of additional parameters
		'additionalParams',
		// Set filter settings
		//'filterSettings',
		// Admin_View
		'Admin_View',
		'showTopFilterTags',
	);

	/**
	 * Apply form settings
	 * @return self
	 */
	public function formSettings()
	{
		$oUserCurrent = Core_Auth::getCurrentUser();

		if ($this->_Admin_Form)
		{
			if (is_null($this->_Admin_Form->key_field))
			{
				throw new Core_Exception('Admin form does not exist.');
			}

			$this->_Admin_Language = Core_Entity::factory('Admin_Language')->getCurrent();

			$oAdmin_Word = $this->_Admin_Form->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

			if ($oAdmin_Word && strlen($oAdmin_Word->name))
			{
				$this->title = $this->pageTitle = $oAdmin_Word->name;
			}

			// Load Admin_Form_Setting
			if (!is_null($oUserCurrent))
			{
				$this->_oAdmin_Form_Setting = $this->_Admin_Form->getSettingForUser($oUserCurrent->id);

				// Данные поля сортировки и направления из настроек пользователя
				if ($this->_oAdmin_Form_Setting)
				{
					$aFilter = $this->getFilterJson();

					$this
						->filterSettings(is_array($aFilter) ? $aFilter : array())
						->limit($this->_oAdmin_Form_Setting->on_page)
						->current($this->_oAdmin_Form_Setting->page_number)
						->view($this->_oAdmin_Form_Setting->view);

					$this->sortingFieldId($this->_oAdmin_Form_Setting->order_field_id)
						&& $this->sortingDirection($this->_oAdmin_Form_Setting->order_direction);
				}
				else
				{
					$oAdmin_Form_Field = $this->getAdminFormFieldByName($this->_Admin_Form->default_order_field);

					// Данные по умолчанию из настроек формы
					$oAdmin_Form_Field && $this->sortingFieldId($oAdmin_Form_Field->id)
						&& $this->sortingDirection($this->_Admin_Form->default_order_direction);
				}
			}
		}

		$this->request = $_REQUEST;

		$formSettings = Core_Array::get($this->request, 'hostcms', array(), 'array') + array(
			'limit' => NULL,
			'current' => NULL,
			'sortingfield' => NULL,
			'sortingdirection' => NULL,
			'filterId' => 'main',
			'action' => NULL,
			'operation' => NULL,
			'window' => 'id_content',
			'view' => NULL, //'list',
			'checked' => array()
		);

		// При передаче нескольких выбранных значений нулевого элемента в таком датасете быть не может
		if (is_array($formSettings['checked']))
		{
			foreach ($formSettings['checked'] as $dataset => $checked)
			{
				if (count($checked) > 1 && isset($checked[0]))
				{
					unset($formSettings['checked'][$dataset][0]);
				}
			}
		}

		$formSettings['limit'] > 0 && $this->limit($formSettings['limit']);
		$formSettings['current'] > 0 && $this->current($formSettings['current']);

		// Может быть строковым
		if ($formSettings['sortingfield'] != '')
		{
			// sortingFieldId() возвращает результат замены поля сортировки
			// Установка sortingdirection возможна только в случае, если пришедшее поле сортировки принадлежит отображаемой форме
			//$oAdmin_Form_Field_Sorting = $this->getAdminFormFieldById($formSettings['sortingfield']);
			//if ($oAdmin_Form_Field_Sorting && $oAdmin_Form_Field_Sorting->allow_sorting || strpos($formSettings['sortingfield'], 'uf_') === 0)
			//{
				$this->sortingFieldId($formSettings['sortingfield']) && is_numeric($formSettings['sortingdirection'])
					&& $this->sortingDirection($formSettings['sortingdirection']);
			//}
		}

		$formSettings['view'] != '' && $this->view($formSettings['view']);

		$this->view == '' && $this->view = 'list';

		$this
			->filterId($formSettings['filterId'])
			->action($formSettings['action'] !== '' ? $formSettings['action'] : NULL)
			->operation($formSettings['operation'] !== '' ? $formSettings['operation'] : NULL)
			->checked($formSettings['checked'])
			->window($formSettings['window'])
			->ajax(Core_Array::get($this->request, '_', FALSE));

		// Save Admin_Form_Setting
		if ($this->_Admin_Form && $oUserCurrent)
		{
			if (!$this->_oAdmin_Form_Setting)
			{
				$this->_oAdmin_Form_Setting = Core_Entity::factory('Admin_Form_Setting');
				$this->_oAdmin_Form_Setting->user_id = $oUserCurrent->id;
				$this->_oAdmin_Form_Setting->admin_form_id = $this->_Admin_Form->id;
			}

			!is_null($this->limit)
				&& $this->_oAdmin_Form_Setting->on_page = intval($this->limit);

			!is_null($this->current)
				&& $this->_oAdmin_Form_Setting->page_number = intval($this->current);

			if (!is_null($this->sortingFieldId))
			{
				$this->_oAdmin_Form_Setting->order_field_id = $this->sortingFieldId;

				!is_null($this->sortingDirection)
					&& $this->_oAdmin_Form_Setting->order_direction = $this->sortingDirection;
			}

			!is_null($this->view)
				&& $this->_oAdmin_Form_Setting->view = strval($this->view);

			$this->_oAdmin_Form_Setting->save();
		}

		// Добавляем замену для windowId
		$this->_externalReplace['{windowId}'] = $this->getWindowId();
		$this->_externalReplace['{secret_csrf}'] =  Core_Security::getCsrfToken();

		return $this;
	}

	/**
	 * Admin_Form_Fields of Admin_Form
	 * @var array|NULL
	 */
	protected $_Admin_Form_Fields = NULL;

	/**
	 * Load Admin_Form_Fields
	 * @return self
	 */
	public function loadAdminFormFields()
	{
		if (!is_null($this->_Admin_Form) && is_null($this->_Admin_Form_Fields))
		{
			$this->_Admin_Form_Fields = array();

			$aAdmin_Form_Fields = $this->_Admin_Form->Admin_Form_Fields->findAll(FALSE);
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				$this->_Admin_Form_Fields[$oAdmin_Form_Field->id] = $oAdmin_Form_Field;
			}
		}

		return $this;
	}

	/**
	 * Set Admin_Form_Fields
	 * @param array $aAdmin_Form_Fields
	 * @return self
	 */
	public function setAdminFormFields(array $aAdmin_Form_Fields)
	{
		$this->_Admin_Form_Fields = $aAdmin_Form_Fields;

		return $this;
	}

	/**
	 * Get Admin_Form_Fields
	 * @return array
	 */
	public function getAdminFormFields()
	{
		$this->loadAdminFormFields();

		return $this->_Admin_Form_Fields;
	}

	/**
	 * Get Admin_Form_Field by ID
	 * @return object|NULL
	 */
	public function getAdminFormFieldById($id)
	{
		$this->loadAdminFormFields();

		return isset($this->_Admin_Form_Fields[$id])
			? $this->_Admin_Form_Fields[$id]
			: NULL;
	}

	/**
	 * Get Admin_Form_Field by Name
	 * @return object|NULL
	 */
	public function getAdminFormFieldByName($name)
	{
		$this->loadAdminFormFields();

		foreach ($this->_Admin_Form_Fields as $id => $oAdmin_Form_Field)
		{
			if ($oAdmin_Form_Field->name == $name)
			{
				return $oAdmin_Form_Field;
			}
		}

		return NULL;
	}

	/**
	 * Delete Admin_Form_Field by ID
	 * @return self
	 */
	public function deleteAdminFormFieldById($id)
	{
		$this->loadAdminFormFields();

		if (isset($this->_Admin_Form_Fields[$id]))
		{
			unset($this->_Admin_Form_Fields[$id]);
		}

		return $this;
	}

	/**
	 * Admin_Form_Actions of Admin_Form
	 * @var array|NULL
	 */
	protected $_Admin_Form_Actions = NULL;

	/**
	 * Load Admin_Form_Actions
	 * @return self
	 */
	public function loadAdminFormActions()
	{
		if (!is_null($this->_Admin_Form) && is_null($this->_Admin_Form_Actions))
		{
			// Текущий пользователь
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}

			$this->_Admin_Form_Actions = array();

			// Доступные действия для пользователя
			$aAdmin_Form_Actions = $this->_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);
			foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
			{
				$this->_Admin_Form_Actions[$oAdmin_Form_Action->id] = $oAdmin_Form_Action;
			}
		}

		return $this;
	}

	/**
	 * Set Admin_Form_Actions
	 * @param array $aAdmin_Form_Actions
	 * @return self
	 */
	public function setAdminFormActions(array $aAdmin_Form_Actions)
	{
		$this->_Admin_Form_Actions = $aAdmin_Form_Actions;

		return $this;
	}

	/**
	 * Get Admin_Form_Actions
	 * @return array
	 */
	public function getAdminFormActions()
	{
		$this->loadAdminFormActions();

		return $this->_Admin_Form_Actions;
	}

	/**
	 * Get Admin_Form_Action by ID
	 * @return object|NULL
	 */
	public function getAdminFormActionById($id)
	{
		$this->loadAdminFormActions();

		return isset($this->_Admin_Form_Actions[$id])
			? $this->_Admin_Form_Actions[$id]
			: NULL;
	}

	/**
	 * Get Admin_Form_Action by Name
	 * @return object|NULL
	 */
	public function getAdminFormActionByName($name)
	{
		$this->loadAdminFormActions();

		foreach ($this->_Admin_Form_Actions as $id => $oAdmin_Form_Action)
		{
			if ($oAdmin_Form_Action->name == $name)
			{
				return $oAdmin_Form_Action;
			}
		}

		return NULL;
	}

	/**
	 * Delete Admin_Form_Action by ID
	 * @return self
	 */
	public function deleteAdminFormActionById($id)
	{
		$this->loadAdminFormActions();

		if (isset($this->_Admin_Form_Actions[$id]))
		{
			unset($this->_Admin_Form_Actions[$id]);
		}

		return $this;
	}

	/**
	 * Get current Admin_Language
	 * @return Admin_Language_Model|NULL
	 */
	public function getAdminLanguage()
	{
		return $this->_Admin_Language;
	}

	/**
	 * Path
	 * @var string
	 */
	protected $_path = NULL;

	/**
	 * Set path
	 * @param string $path path
	 * @return self
	 */
	public function path($path)
	{
		$this->_path = $path;

		// Добавляем замену для path
		$this->_externalReplace['{path}'] = $this->_path;

		return $this;
	}

	/**
	 * Get path
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Get checked items on the form
	 * @return array
	 */
	public function getChecked()
	{
		return $this->checked;
	}

	/**
	 * Clear checked items on the form
	 * @return self
	 */
	public function clearChecked()
	{
		$this->checked = array();
		return $this;
	}

	/**
	 * List of handlers' actions
	 * @var array
	 */
	protected $_actionHandlers = array();

	/**
	 * Добавление обработчика действия
	 * @param Admin_Form_Action_Controller $oAdmin_Form_Action_Controller action controller
	 */
	public function addAction(Admin_Form_Action_Controller $oAdmin_Form_Action_Controller)
	{
		// Set link to controller
		$oAdmin_Form_Action_Controller->controller($this);

		$this->_actionHandlers[$oAdmin_Form_Action_Controller->getName()] = $oAdmin_Form_Action_Controller;
	}

	/**
	 * List of children entities
	 * @var array
	 */
	protected $_children = array();

	/**
	 * Add entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity
	 * @return self
	 * @hostcms-event Admin_Form_Controller.onBeforeAddEntity
	 */
	public function addEntity(Admin_Form_Entity $oAdmin_Form_Entity)
	{
		// Set link to controller
		$oAdmin_Form_Entity->controller($this);

		Core_Event::notify('Admin_Form_Controller.onBeforeAddEntity', $this, array($oAdmin_Form_Entity));

		$this->_children[] = $oAdmin_Form_Entity;
		return $this;
	}

	/**
	 * Get Children
	 * @return array
	 */
	public function getChildren()
	{
		return $this->_children;
	}

	/**
	 * List of external replaces
	 * @var array
	 */
	protected $_externalReplace = array();

	/**
	* Add external replacement
	* Добавление внешней подстановки
	* @param string $key name of replacement
	* @param string $value value of replacement
	* @return self
	*/
	public function addExternalReplace($key, $value)
	{
		$this->_externalReplace[$key] = $value;
		return $this;
	}

	/**
	* Get external replacement
	* @return array
	*/
	public function getExternalReplace()
	{
		return $this->_externalReplace;
	}

	/**
	 * Get Admin_Form
	 * @return Admin_Form_Model
	 */
	public function getAdminForm()
	{
		return $this->_Admin_Form;
	}

	//protected $_module = NULL;

	/**
	 * Set module
	 * @param Core_Module $oModule
	 * @return self
	 */
	public function module(Core_Module_Abstract $oModule)
	{
		$this->module = $oModule;
		return $this;
	}

	/**
	 * Get module
	 * @return object
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Get sorting field
	 * @return Admin_Form_Field_Model
	 */
	public function getSortingField()
	{
		// Set sorting field
		if ($this->sortingFieldId)
		{
			$sortingAdmin_Form_Field = $this->getAdminFormFieldById($this->sortingFieldId);
		}
		else
		{
			// Default sorting field
			$sortingFieldName = $this->_Admin_Form->default_order_field;
			$sortingAdmin_Form_Field = $this->getAdminFormFieldByName($sortingFieldName);

			/*if (is_null($sortingAdmin_Form_Field))
			{
				throw new Core_Exception("Default form sorting field '%sortingFieldName' does not exist.",
					array ('%sortingFieldName' => $sortingFieldName)
				);
			}*/
		}

		return $sortingAdmin_Form_Field;
	}

	/**
	 * Constructor.
	 * @param Admin_Form_Model $oAdmin_Form admin form
	 */
	public function __construct(Admin_Form_Model $oAdmin_Form = NULL)
	{
		parent::__construct();

		$this->limit(ON_PAGE);
		$this->current = 1; // счет с 1

		$this->showOperations = TRUE;

		// Default View Is 'list' Mode
		$this->addView('list');
		$this->view = 'list';

		$this->_Admin_Form = $oAdmin_Form;

		// Current path
		$this->path($_SERVER['PHP_SELF']);
	}

	/**
	 * Get filter json
	 * @return array
	 */
	public function getFilterJson()
	{
		return $this->_oAdmin_Form_Setting->filter != ''
			? json_decode($this->_oAdmin_Form_Setting->filter, TRUE)
			: array();
	}

	/**
	 * List of filter handlers
	 * @var array
	 */
	protected $_filterCallbacks = array();

	/**
	 * Добавление функции обратного вызова, используемой для корректировки переданного значения
	 *
	 * @param string $fieldName field name
	 * @param string $function function name
	 * @return self
	 */
	public function addFilterCallback($fieldName, $function)
	{
		$this->_filterCallbacks[$fieldName] = $function;
		return $this;
	}

	/**
	 * List of filter handlers
	 * @var array
	 */
	protected $_filters = array();

	/**
	 * Add handler of the filter
	 * Добавление обработчика фильтра
	 * @param string $fieldName field name
	 * @param string $function function name
	 * @return self
	 */
	public function addFilter($fieldName, $function)
	{
		$this->_filters[$fieldName] = $function;
		return $this;
	}

	/**
	 * Set <h1> for form
	 * @param string $title content
	 * @return self
	 */
	public function title($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Set page <title>
	 * @param $pageTitle title
	 * @return self
	 */
	public function pageTitle($pageTitle)
	{
		$this->pageTitle = html_entity_decode((string) $pageTitle);
		return $this;
	}

	/**
	 * Get page <title>
	 * @return string
	 */
	public function getPageTitle()
	{
		return $this->pageTitle;
	}

	/**
	 * Set limit of elements on page
	 * @param int $limit count
	 * @return self
	 */
	public function limit($limit)
	{
		$limit = intval($limit);
		$limit > 0 && $limit <= 1000
			&& $this->limit = $limit;

		return $this;
	}

	/**
	 * Set current page
	 * @param int $current page
	 * @return self
	 */
	public function current($current)
	{
		$current = intval($current);
		$current > 0 && $this->current = intval($current);

		return $this;
	}

	/**
	 * Get current page
	 * @return int
	 */
	public function getCurrent()
	{
		return $this->current;
	}

	/**
	 * Set action
	 * @param string $action
	 * @return self
	 */
	public function action($action)
	{
		$this->action = !is_null($action) ? preg_replace('/[^A-Za-z0-9_-]/', '', $action) : NULL;
		return $this;
	}

	/**
	 * Get action
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Set operation
	 * @param string $operation
	 * @return self
	 */
	public function operation($operation)
	{
		$this->operation = !is_null($operation) ? preg_replace('/[^A-Za-z0-9_-]/', '', $operation) : NULL;
		return $this;
	}

	/**
	 * Get operation
	 * @return string
	 */
	public function getOperation()
	{
		return $this->operation;
	}

	/**
	 * Set filterId
	 * @param string $filterId
	 * @return self
	 */
	public function filterId($filterId)
	{
		$this->filterId = preg_replace('/[^A-Za-z0-9_-]/', '', $filterId);
		return $this;
	}

	/**
	 * Set AJAX
	 * @param boolean $ajax ajax
	 * @return self
	 */
	public function ajax($ajax)
	{
		$this->ajax = ($ajax != FALSE);
		return $this;
	}

	/**
	 * Get AJAX
	 * @return boolean
	 */
	public function getAjax()
	{
		return $this->ajax;
	}

	/**
	 * Show skin
	 * @param boolean $skin use skin mode
	 * @return self
	 */
	public function skin($skin)
	{
		$this->_skin = ($skin != FALSE);
		return $this;
	}

	/**
	 * filter settings
	 */
	public $filterSettings = array();

	/**
	 * Set filter settings
	 * @param array $filterSettings
	 * @return self
	 */
	public function filterSettings(array $filterSettings)
	{
		$this->filterSettings = $filterSettings;
		return $this;
	}

	public function getFilterSettings()
	{
		return $this->filterSettings;
	}

	/**
	 * Add dataset
	 * @param Admin_Form_Dataset $oAdmin_Form_Dataset dataset
	 * @return self
	 * @hostcms-event Admin_Form_Controller.onBeforeAddDataset
	 */
	public function addDataset(Admin_Form_Dataset $oAdmin_Form_Dataset)
	{
		$oAdmin_Form_Dataset->controller($this);

		Core_Event::notify('Admin_Form_Controller.onBeforeAddDataset', $this, array($oAdmin_Form_Dataset));

		$this->_datasets[] = $oAdmin_Form_Dataset;
		return $this;
	}

	/**
	 * Get dataset
	 * @param int $key index
	 * @return Admin_Form_Dataset|NULL
	 */
	public function getDataset($key)
	{
		return isset($this->_datasets[$key])
			? $this->_datasets[$key]
			: NULL;
	}

	/**
	 * Get datasets
	 * @return array
	 */
	public function getDatasets()
	{
		return $this->_datasets;
	}

	/**
	 * Set window ID
	 * @param string $windowId
	 * @return self
	 */
	public function window($windowId)
	{
		$this->windowId = preg_replace('/[^A-Za-z0-9_-]/', '', $windowId);
		return $this;
	}

	/**
	 * Set view
	 * @param string $view
	 * @return self
	 */
	public function view($view)
	{
		$this->view = !is_null($view) ? preg_replace('/[^A-Za-z0-9_-]/', '', $view) : NULL;
		return $this;
	}

	/**
	 * Get window ID
	 * @return int
	 */
	public function getWindowId()
	{
		return $this->windowId;
	}

	/**
	 * Total founded items
	 * @var int
	 */
	protected $_totalCount = NULL;

	/**
	 * Get count of total founded items
	 * @return int
	 */
	public function getTotalCount()
	{
		if (is_null($this->_totalCount))
		{
			try
			{
				foreach ($this->_datasets as $oAdmin_Form_Dataset)
				{
					$this->_totalCount += $oAdmin_Form_Dataset->getCount();
				}
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		return $this->_totalCount;
	}

	/**
	 * Set count of total founded items
	 * @param int $count Total countt
	 * @return self
	 */
	public function setTotalCount($count)
	{
		$this->_totalCount = $count;
		return $this;
	}

	/**
	 * Content
	 * @var string
	 */
	protected $_content = NULL;

	/**
	 * Clear content for Back-end form
	 * @return self
	 */
	public function clearContent()
	{
		$this->_content = NULL;
		return $this;
	}

	/**
	 * Get content message
	 * @return string
	 */
	public function getContent()
	{
		return $this->_content;
	}

	/**
	 * Add content for Back-end form
	 * @param string $content content
	 * @return self
	 */
	public function addContent($content)
	{
		$this->_content .= $content;
		return $this;
	}

	/**
	 * Message text
	 * @var string
	 */
	protected $_message = NULL;

	/**
	 * Get form message
	 * @return string
	 */
	public function getMessage()
	{
		return $this->_message;
	}

	/**
	 * Add message for Back-end form
	 * @param string $message message
	 * @return self
	 */
	public function addMessage($message)
	{
		$this->_message .= $message;
		return $this;
	}

	/**
	 * Clear messages for Back-end form
	 * @return self
	 */
	public function clearMessages()
	{
		$this->_message = NULL;
		return $this;
	}

	/**
	 * Show built data
	 */
	public function show()
	{
		$oAdmin_Answer = Core_Skin::instance()->answer();

		!is_null($this->module)
			&& Core_Array::get($this->request, '_module', TRUE)
			&& $this->windowId == 'id_content'
			&& $oAdmin_Answer->module($this->module->getModuleName());

		$oAdmin_Answer
			->ajax($this->ajax)
			->skin($this->_skin)
			->content($this->getContent())
			->message($this->getMessage())
			->title($this->title)
			->execute();
	}

	/**
	 * @hostcms-event Admin_Form_Controller.onBeforeShowContent
	 * @hostcms-event Admin_Form_Controller.onAfterShowContent
	 */
	public function perform()
	{
		// ---------------------------
		//$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__ . '_' . 'List';

		$viewList = $this->viewList;

		/*if (!isset($viewList[$this->view]))
		{
			throw new Core_Exception("Wrong view mode '%view'",
				array('%view' => $this->view));
		}*/

		$className = isset($viewList[$this->view])
			? $viewList[$this->view]
			: reset($viewList);

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $className));
		}

		ob_start();
		Core_Event::notify('Admin_Form_Controller.onBeforeShowContent', $this);

		// Пользовательские поля для каждого dataset
		if (Core::moduleIsActive('field'))
		{
			foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				$oEntity = $oAdmin_Form_Dataset->getEntity();
				if ($oEntity instanceof Core_Entity)
				{
					$aFields = self::getFields(CURRENT_SITE, $oEntity->getModelName());
					foreach ($aFields as $oField)
					{
						$this->_Admin_Form_Fields[$oField->id] = $oField;
					}
				}
			}
		}

		$viewAdmin_Form_Controller = new $className($this);
		$viewAdmin_Form_Controller->execute();

		Core_Event::notify('Admin_Form_Controller.onAfterShowContent', $this);

		$this
			->addContent(ob_get_clean());

		return $this;
	}

	/**
	 * Get fields
	 * @param string|array $modelNames
	 * @return array
	 */
	static public function getFields($site_id, $modelNames)
	{
		$aReturn = array();
		if (is_scalar($modelNames) || is_array($modelNames) && count($modelNames))
		{
			$oFields = Core_Entity::factory('Field');
			$oFields->queryBuilder()
				->where('fields.model', is_array($modelNames) ? 'IN' : '=', $modelNames)
				->open()
					->where('fields.site_id', '=', $site_id)
					->setOr()
					->where('fields.site_id', '=', 0)
				->close()
				->clearOrderBy()
				->orderBy('sorting', 'ASC');

			$aFields = $oFields->findAll();
			foreach ($aFields as $oField)
			{
				$oNew = new stdClass();
				$oNew->id = 'uf_' . $oField->id;
				$oNew->name = 'datauf_' . $oField->id;
				$oNew->caption = $oField->name;
				$oNew->allow_filter = TRUE;
				$oNew->view = 0;
				$oNew->type = 1;
				$oNew->editable = 0;
				$oNew->filter_type = 1; // HAVING
				$oNew->filter_condition = 0;
				$oNew->show_by_default = 0;
				$oNew->width = '';
				$oNew->ico = '';
				$oNew->format = '';
				$oNew->class = '';
				$oNew->allow_sorting = 1;
				$oNew->_model_name = $oField->model;

				$aReturn[$oNew->id] = $oNew;
			}
		}

		return $aReturn;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Admin_Form_Controller.onBeforeExecute
	 * @hostcms-event Admin_Form_Controller.onAfterExecute
	 */
	public function execute()
	{
		ob_start();

		Core_Event::notify('Admin_Form_Controller.onBeforeExecute', $this);

		if ($this->action != '')
		{
			$actionName = $this->action;

			$aReadyAction = array();

			try
			{
				// Текущий пользователь
				$oUser = Core_Auth::getCurrentUser();

				if (is_null($oUser))
				{
					return FALSE;
				}

				// Read Only режим
				if (defined('READ_ONLY') && READ_ONLY || $oUser->read_only && !$oUser->superuser)
				{
					throw new Core_Exception(
						Core::_('User.demo_mode') , array(), 0, FALSE
					);
				}

				if (count($this->checked))
				{
					// Доступные действия для пользователя
					$aAdmin_Form_Actions = $this->getAdminFormActions();

					$bActionAllowed = FALSE;

					// Проверка на право доступа к действию
					foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
					{
						if ($oAdmin_Form_Action->name == $actionName)
						{
							$bActionAllowed = TRUE;
							break;
						}
					}

					if ($bActionAllowed)
					{
						foreach ($this->checked as $datasetKey => $checkedItems)
						{
							foreach ($checkedItems as $checkedItemId => $v1)
							{
								if (isset($this->_datasets[$datasetKey]))
								{
									// Проверка CSRF
									if ($oAdmin_Form_Action->confirm)
									{
										$secret_csrf = Core_Array::getGet('secret_csrf', '', 'trim');

										if (!Core_Security::checkCsrf($secret_csrf, Core::$mainConfig['csrf_lifetime']))
										{
											Core_Security::throwCsrfError();
										}
									}

									$oObject = $this->_datasets[$datasetKey]->getObject($checkedItemId);

									// Проверка на наличие объекта и доступность действия к dataset
									if (!is_object($oObject) || $oAdmin_Form_Action->dataset != -1
										&& $oAdmin_Form_Action->dataset != $datasetKey)
									{
										break;
									}

									// Проверка через user_id на право выполнения действия над объектом
									$bAccessToObject = $oObject instanceof Core_Entity
										? $oUser->checkObjectAccess($oObject)
										: TRUE;

									if (!$bAccessToObject)
									{
										throw new Core_Exception(
											Core::_('User.error_object_owned_another_user'), array(), 0, FALSE
										);
									}

									// Если у модели есть метод checkBackendAccess(), то проверяем права на это действие, совершаемое текущим пользователем
									if (method_exists($oObject, 'checkBackendAccess') && !$oObject->checkBackendAccess($actionName, $oUser))
									{
										continue;
									}

									if (isset($this->_actionHandlers[$actionName]))
									{
										$this->_actionHandlers[$actionName]
											->setDatasetId($datasetKey)
											->setObject($oObject);

										$actionResult = $this->_actionHandlers[$actionName]->execute($this->operation);

										$this->addMessage(
											$this->_actionHandlers[$actionName]->getMessage()
										);

										$this->addContent(
											$this->_actionHandlers[$actionName]->getContent()
										);
									}
									else
									{
										Core_Event::notify('Admin_Form_Controller.onCall' . $actionName, $this, array($datasetKey, $oObject, $this->operation));

										$eventResult = Core_Event::getLastReturn();
										if (is_array($eventResult))
										{
											list($actionResult, $message, $content) = $eventResult;

											$this
												->addMessage($message)
												->addContent($content);
										}
										else
										{
											// Уже есть выше при проверке права доступа к действию, если действие было, то здесь также есть доступ
											// Проверяем наличие действия с такими именем у формы
											/*$oAdmin_Form_Action = $this->_Admin_Form->Admin_Form_Actions->getByName($actionName);
											if (!is_null($oAdmin_Form_Action))
											{*/
												$actionResult = $oObject->$actionName();
											/*}
											else
											{
												throw new Core_Exception('Action "%actionName" does not exist.',
													array('%actionName' => $actionName)
												);
											}*/
										}
									}

									// Действие вернуло TRUE, прерываем выполнение
									if ($actionResult === TRUE)
									{
										$this->addMessage(ob_get_clean());

										return $this->show();
									}
									elseif ($actionResult !== NULL)
									{
										$aReadyAction[$oObject->getModelName()] = isset($aReadyAction[$datasetKey])
											? $aReadyAction[$datasetKey] + 1
											: 1;
									}
								}
								else
								{
									throw new Core_Exception('Dataset %datasetKey does not exist.',
										array('%datasetKey' => $datasetKey)
									);
								}
							}
						}

						// Log
						$oAdmin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage();
						$sEventName = $oAdmin_Word_Value
							? $oAdmin_Word_Value->name
							: Core::_('Core.default_event_name');

						// Название формы для действия
						$oAdmin_Word_Value = $oAdmin_Form_Action->Admin_Form->Admin_Word->getWordByLanguage();
						$sFormName = $oAdmin_Word_Value
							? $oAdmin_Word_Value->name
							: Core::_('Core.default_form_name');

						Core_Log::instance()->clear()
							->status(Core_Log::$SUCCESS)
							->write(Core::_('Core.error_log_action_access_allowed', $sEventName, $sFormName));
					}
					else
					{
						throw new Core_Exception(
							Core::_('Admin_Form.msg_error_access', $actionName), array()/*, 0, FALSE*/
						);
					}
				}
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}

			// были успешные операции
			foreach ($aReadyAction as $modelName => $actionChangedCount)
			{
				Core_Message::show(Core::_("{$modelName}.{$actionName}_success", NULL, $actionChangedCount));
			}
		}

		$this->addMessage(ob_get_clean());

		$formSettings = Core_Array::get($this->request, 'hostcms');
		if (is_array($formSettings) && Core_Array::get($formSettings, 'export') == 'csv')
		{
			header('Pragma: public');
			header('Content-Description: File Transfer');
			header('Content-Type: application/force-download');
			header('Content-Disposition: attachment; filename="' . addslashes($this->title) . '_' .date('Y_m_d_H_i_s').'.csv";');
			header('Content-Transfer-Encoding: binary');

			$oAdmin_Language = $this->getAdminLanguage();
			$this->limit = NULL;

			$aData = $aFields = array();

			// Поля формы
			$aAdmin_Form_Fields = $this->getAdminFormFields();
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				// 0 - Столбец и фильтр, 2 - Столбец
				if ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2)
				{
					$fieldName = $oAdmin_Form_Field instanceof Admin_Form_Field_Model
						? $oAdmin_Form_Field->getCaption($oAdmin_Language->id)
						: $oAdmin_Form_Field->name;

					$aData[] = $this->prepareCell($fieldName);

					$aFields[$oAdmin_Form_Field->name] = $oAdmin_Form_Field;
				}
			}

			// Оставшиеся поля моделей
			foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				$oEntity = $oAdmin_Form_Dataset->getEntity();

				if ($oEntity instanceof Core_Entity)
				{
					$modelName = $oEntity->getModelName();

					$aColumns = $oEntity->getTableColumns();

					foreach ($aColumns as $columnName => $aColumn)
					{
						if (!isset($aFields[$columnName]) && Core_I18n::instance()->check($modelName . '.' . $columnName))
						{
							$aData[] = $this->prepareCell(strip_tags(Core::_($modelName . '.' . $columnName)));

							$aFields[$columnName] = NULL;
						}
					}
				}
			}

			// BOM
			echo "\xEF\xBB\xBF";

			$this->_printRow($aData);

			$this->setDatasetConditions();

			foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				$offset = 0;
				$limit = 500;

				do {
					try {
						$aEntities = $oAdmin_Form_Dataset
							->setCount(0)
							->limit($limit)
							->offset($offset)
							->loaded(FALSE)
							->load();

						foreach ($aEntities as $oEntity)
						{
							$aData = array();
							//foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
							foreach ($aFields as $fieldName => $mixed)
							{
								// 0 - Столбец и фильтр, 2 - Столбец
								//if ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2)
								//{
									// Перекрытие параметров для данного поля. Может быть $oAdmin_Form_Field или NULL
									if ($mixed)
									{
										$oAdmin_Form_Field_Changed = $this->changeField($oAdmin_Form_Dataset, $mixed);
										$fieldName = $oAdmin_Form_Field_Changed->name;

										if (isset($oEntity->$fieldName))
										{
											// значение свойства
											$value = $oEntity->$fieldName;
										}
										elseif ($this->isCallable($oEntity, $fieldName))
										{
											// Выполним функцию обратного вызова
											$value = $oEntity->$fieldName($oAdmin_Form_Field_Changed, $this);
										}
										else
										{
											$value = NULL;
										}
									}
									else
									{
										$oAdmin_Form_Field_Changed = NULL;

										$value = isset($oEntity->$fieldName) ? $oEntity->$fieldName : NULL;
									}

									$iType = $oAdmin_Form_Field_Changed ? $oAdmin_Form_Field_Changed->type : 1;
									$sFormat = $oAdmin_Form_Field_Changed ? $oAdmin_Form_Field_Changed->format : '';

									switch ($iType)
									{
										case 1: // Текст.
										case 2: // Поле ввода.
										case 4: // Ссылка.
										case 7: // Картинка-ссылка.
											!is_null($value)
												&& $value = $this->applyFormat($value, $sFormat);
										break;
										case 3: // Checkbox.
											$value = $value == 1 ? Core::_('Admin_Form.yes') : Core::_('Admin_Form.no');
										break;
										case 5: // Дата-время.
											if (!is_null($value))
											{
												$value = $value == '0000-00-00 00:00:00' || $value == ''
													? ''
													: Core_Date::sql2datetime($value);
											}
										break;
										case 6: // Дата.
											if (!is_null($value))
											{
												$value = $value == '0000-00-00 00:00:00' || $value == ''
													? ''
													: Core_Date::sql2date($value);
											}
										break;
										case 8: // Выпадающий список
											if (is_array($oAdmin_Form_Field_Changed->list))
											{
												$aValue = $oAdmin_Form_Field_Changed->list;
											}
											else
											{
												$aValue = array();

												$aListExplode = explode("\n", $oAdmin_Form_Field_Changed->list);
												foreach ($aListExplode as $str_value)
												{
													// Каждую строку разделяем по равно
													$str_explode = explode('=', $str_value);

													if (count($str_explode) > 1 && $str_explode[1] != '…')
													{
														// сохраняем в массив варинаты значений и ссылки для них
														$aValue[trim($str_explode[0])] = trim($str_explode[1]);
													}
												}
											}

											if (isset($aValue[$value]))
											{
												$value = $aValue[$value];
											}
										break;
									}

									$aData[] = $this->prepareCell($value);
								//}
							}

							$this->_printRow($aData);
						}
					}
					catch (Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
					}

					$offset += $limit;

					Core_File::flush();

				} while(count($aEntities) == $limit);
			}
			die();
		}

		$this
			->perform()
			->show();

		Core_Event::notify('Admin_Form_Controller.onAfterExecute', $this);
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	public function prepareString($string)
	{
		return str_replace('"', '""', trim((string) $string));
	}

	/**
	 * Prepare cell
	 * @param string $string
	 * @return string
	 */
	public function prepareCell($string)
	{
		return sprintf('"%s"', $this->prepareString($string));
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo implode(';', $aData) . "\n";
		return $this;
	}

	/**
	 * Edit-in-Place in Back-end
	 * @return self
	 */
	public function applyEditable()
	{
		$windowId = Core_Str::escapeJavascriptVariable($this->getWindowId());
		$path = Core_Str::escapeJavascriptVariable($this->getPath());

		strlen($this->additionalParams)
			&& $path .= '?' . Core_Str::escapeJavascriptVariable($this->additionalParams);

		// Текущий пользователь
		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		// Доступные действия для пользователя
		$aAllowed_Admin_Form_Actions = $this->getAdminFormActions();

		// Editable
		$bEditable = FALSE;
		foreach ($aAllowed_Admin_Form_Actions as $o_Admin_Form_Action)
		{
			if ($o_Admin_Form_Action->name == 'apply')
			{
				$bEditable = TRUE;
				break;
			}
		}

		if ($bEditable)
		{
			Core_Html_Entity::factory('Script')
				->value("(function($){
					$('#{$windowId} .editable').hostcmsEditable({windowId: '{$windowId}', path: '{$path}'});
				})(jQuery);")
				->execute();
		}

		Core_Html_Entity::factory('Script')
			->value("(function($){
				$('#{$windowId} .admin_table_filter :input').on('keydown', $.filterKeyDown);
			})(jQuery);")
			->execute();

		return $this;
	}

	/**
	 * Apply external replaces in $subject
	 * @param array $aAdmin_Form_Fields Admin_Form_Fields
	 * @param Core_Entity $oEntity entity
	 * @param string $subject
	 * @param string $mode link|onclick
	 * @return string
	 */
	public function doReplaces($aAdmin_Form_Fields, $oEntity, $subject, $mode = 'link')
	{
		foreach ($this->_externalReplace as $replace_key => $replace_value)
		{
			$subject = str_replace($replace_key, strval($replace_value), $subject);
		}

		// Columns + Data-attributes
		$aColumns = array_merge(array_keys($oEntity->getTableColumns()), array_keys($oEntity->getDataValues()));
		foreach ($aColumns as $columnName)
		{
			if (isset($oEntity->$columnName))
			{
				$subject = str_replace(
					'{' . $columnName . '}',
					$mode == 'link'
						? htmlspecialchars((string) $oEntity->$columnName)
						: Core_Str::escapeJavascriptVariable(/*$this->jQueryEscape(*/$oEntity->$columnName/*)*/), // jQueryEscape() wrong with filemanager and dir with '+' char
					$subject
				);
			}
		}

		return $subject;
	}

	/**
	* Применяет формат отображения $format к строке $str.
	* Если формат является пустой строкой - $str возвращается в исходном виде.
	*
	* @param string $str исходная строка
	* @param string $format форма отображения. Строка формата состоит из директив: обычных символов (за исключением %),
	* которые копируются в результирующую строку, и описатели преобразований,
	* каждый из которых заменяется на один из параметров.
	*/
	public function applyFormat($str, $format)
	{
		return !empty($format) ? sprintf($format, $str) : $str;
	}

	/**
	 * Set sorting field by ID
	 * @param int $sortingFieldId field ID
	 * @return bool
	 */
	public function sortingFieldId($sortingFieldId)
	{
		$bCorrect = FALSE;

		if (!is_null($sortingFieldId) && $this->_Admin_Form)
		{
			// User field
			if (strpos($sortingFieldId, 'uf_') === 0)
			{
				$bCorrect = TRUE;
				$sortingFieldId = 'uf_' . intval(filter_var($sortingFieldId, FILTER_SANITIZE_NUMBER_INT));
			}
			else
			{
				$sortingFieldId = preg_replace('/[^A-Za-z0-9_-]/', '', $sortingFieldId);

				// Проверка принадлежности форме
				$oAdmin_Form_Field = $this->getAdminFormFieldById($sortingFieldId);

				$bCorrect = $oAdmin_Form_Field && $this->_Admin_Form->id == $oAdmin_Form_Field->admin_form_id;
			}

			$bCorrect
				&& $this->sortingFieldId = $sortingFieldId;
		}

		return $bCorrect;
	}

	/**
	 * Set sorting direction
	 * @param int $sortingDirection direction
	 * @return self
	 */
	public function sortingDirection($sortingDirection)
	{
		!is_null($sortingDirection)
			&& $this->sortingDirection = intval($sortingDirection);

		return $this;
	}

	public function showSettings()
	{
		$aTmp = array();
		$aTmp[] = "path:'" . Core_Str::escapeJavascriptVariable($this->_path) . "'";
		$aTmp[] = "action:'" . Core_Str::escapeJavascriptVariable($this->action) . "'";
		$aTmp[] = "operation:'" . Core_Str::escapeJavascriptVariable($this->operation) . "'";
		$aTmp[] = "additionalParams:'" . Core_Str::escapeJavascriptVariable($this->additionalParams) . "'";
		$aTmp[] = "limit:'" . Core_Str::escapeJavascriptVariable($this->limit) . "'";
		$aTmp[] = "current:'" . Core_Str::escapeJavascriptVariable($this->current) . "'";
		$aTmp[] = "sortingFieldId:'" . Core_Str::escapeJavascriptVariable($this->sortingFieldId) . "'";
		$aTmp[] = "sortingDirection:'" . Core_Str::escapeJavascriptVariable($this->sortingDirection) . "'";
		$aTmp[] = "view:'" . Core_Str::escapeJavascriptVariable($this->view) . "'";
		$aTmp[] = "windowId:'" . Core_Str::escapeJavascriptVariable($this->windowId) . "'";

		?><script>//<![CDATA[
var _windowSettings={<?php echo implode(',', $aTmp)?>}
//]]></script>
		<?php

		return $this;
	}

	/**
	 * Backend callback method
	 * @param array $options array('path', 'action', 'operation', 'datasetKey', 'datasetValue', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
	 * @return string
	 */
	public function getAdminActionModalLoad($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $path, $action, $operation, $datasetKey, $datasetValue, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL
			$options = array();

			$options['path'] = $args[0];
			isset($args[1]) && $options['action'] = $args[1];
			isset($args[2]) && $options['operation'] = $args[2];
			isset($args[3]) && $options['datasetKey'] = $args[3];
			isset($args[4]) && $options['datasetValue'] = $args[4];
			$options['additionalParams'] = isset($args[5]) ? $args[5] : NULL;
			isset($args[6]) && $options['limit'] = $args[6];
			isset($args[7]) && $options['current'] = $args[7];
			isset($args[8]) && $options['sortingFieldId'] = $args[8];
			isset($args[9]) && $options['sortingDirection'] = $args[9];
			isset($args[10]) && $options['view'] = $args[10];
		}

		if (!isset($options['datasetKey']) || !isset($options['datasetValue']))
		{
			throw new Core_Exception("getAdminActionModalLoad() needs at least 'datasetKey' and 'datasetValue' options");
		}

		$options += array('additionalParams' => NULL);

		$windowId = Core_Str::escapeJavascriptVariable(isset($options['window'])
			? $options['window']
			: $this->getWindowId()
		);
		$datasetKey = Core_Str::escapeJavascriptVariable($this->jQueryEscape($options['datasetKey']));
		$datasetValue = Core_Str::escapeJavascriptVariable($this->jQueryEscape($options['datasetValue']));

		is_null($options['additionalParams']) && $options['additionalParams'] = $this->additionalParams;

		// remove parentWindowId=...
		$options['additionalParams'] = preg_replace('/&parentWindowId=[A-Za-z0-9_-]*/', '', $options['additionalParams']);

		$options['additionalParams'] .= '&hostcms[checked][' . $datasetKey . '][' . $datasetValue . ']=1';
		strlen($windowId) && $options['additionalParams'] .= '&parentWindowId=' . $windowId;

		return $this->getModalLoad($options);
	}

	/**
	 * Backend callback method
	 * @param array $options array('path', 'action', 'operation', 'datasetKey', 'datasetValue', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
	 * @return string
	 */
	public function getAdminActionLoadAjax($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $path, $action, $operation, $datasetKey, $datasetValue, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL
			$options = array();

			$options['path'] = $args[0];
			isset($args[1]) && $options['action'] = $args[1];
			isset($args[2]) && $options['operation'] = $args[2];
			isset($args[3]) && $options['datasetKey'] = $args[3];
			isset($args[4]) && $options['datasetValue'] = $args[4];
			$options['additionalParams'] = isset($args[5]) ? $args[5] : NULL;
			isset($args[6]) && $options['limit'] = $args[6];
			isset($args[7]) && $options['current'] = $args[7];
			isset($args[8]) && $options['sortingFieldId'] = $args[8];
			isset($args[9]) && $options['sortingDirection'] = $args[9];
			isset($args[10]) && $options['view'] = $args[10];
		}

		if (!isset($options['datasetKey']) || !isset($options['datasetValue']))
		{
			throw new Core_Exception("getAdminActionLoadAjax() needs at least 'datasetKey' and 'datasetValue' options");
		}

		$options += array('additionalParams' => NULL);

		$windowId = Core_Str::escapeJavascriptVariable(isset($options['window'])
			? $options['window']
			: $this->getWindowId()
		);
		$datasetKey = intval($options['datasetKey']);

		return "$('#{$windowId} #row_{$datasetKey}_" . Core_Str::escapeJavascriptVariable($this->jQueryEscape($options['datasetValue'])) . "').toggleHighlight(); "
			. "$.adminCheckObject({objectId: 'check_{$datasetKey}_" . Core_Str::escapeJavascriptVariable($options['datasetValue']) . "', windowId: '{$windowId}'}); "
			. $this->getAdminLoadAjax($options);
	}

	/**
	 * Backend callback method
	 * @param array $options array('path', 'action', 'operation', 'datasetKey', 'datasetValue', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
	 * @return string
	 */
	public function getAdminActionLoadHref($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $path, $action, $operation, $datasetKey, $datasetValue, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL
			$options = array();

			$options['path'] = $args[0];
			isset($args[1]) && $options['action'] = $args[1];
			isset($args[2]) && $options['operation'] = $args[2];
			isset($args[3]) && $options['datasetKey'] = $args[3];
			isset($args[4]) && $options['datasetValue'] = $args[4];
			$options['additionalParams'] = isset($args[5]) ? $args[5] : NULL;
			isset($args[6]) && $options['limit'] = $args[6];
			isset($args[7]) && $options['current'] = $args[7];
			isset($args[8]) && $options['sortingFieldId'] = $args[8];
			isset($args[9]) && $options['sortingDirection'] = $args[9];
			isset($args[10]) && $options['view'] = $args[10];
		}

		if (!isset($options['datasetKey']) || !isset($options['datasetValue']))
		{
			throw new Core_Exception("getAdminActionLoadHref() needs at least 'datasetKey' and 'datasetValue' options");
		}

		$options += array('additionalParams' => NULL);

		is_null($options['additionalParams']) && $options['additionalParams'] = $this->additionalParams;

		$datasetKey = intval($options['datasetKey']);
		$datasetValue = Core_Str::escapeJavascriptVariable($options['datasetValue']);
		$options['additionalParams'] .= '&hostcms[checked][' . $datasetKey . '][' . $datasetValue . ']=1';

		return $this->getAdminLoadHref($options);
	}

	/**
	 * Получение кода вызова adminLoad для события onclick
	 * @param array $options array('path', 'action', 'operation', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
	 * @return string
	*/
	public function getAdminLoadAjax($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $path, $action = NULL, $operation = NULL, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL
			$options = array();

			$options['path'] = $args[0];
			isset($args[1]) && $options['action'] = $args[1];
			isset($args[2]) && $options['operation'] = $args[2];
			$options['additionalParams'] = isset($args[3]) ? $args[3] : NULL;
			isset($args[4]) && $options['limit'] = $args[4];
			isset($args[5]) && $options['current'] = $args[5];
			isset($args[6]) && $options['sortingFieldId'] = $args[6];
			isset($args[7]) && $options['sortingDirection'] = $args[7];
			isset($args[8]) && $options['view'] = $args[8];
		}

		if (!isset($options['path']))
		{
			throw new Core_Exception("getAdminLoadAjax() needs at least 'path' option");
		}

		$options += array('additionalParams' => NULL);

		is_null($options['additionalParams']) && $options['additionalParams'] = $this->additionalParams;

		$aData = $this->_prepareAjaxRequest($options);

		$path = Core_Str::escapeJavascriptVariable($options['path']);
		$aData[] = "path: '{$path}'";

		return "$.adminLoad({" . implode(',', $aData) . "}); return false";
	}

	/**
	 * Получение кода вызова modalLoad для события onclick
	 * @param array $options array('path', 'action', 'operation', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window', 'onHide')
	 * @return string
	*/
	public function getModalLoad($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $path, $action = NULL, $operation = NULL, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL
			$options = array();

			$options['path'] = $args[0];
			isset($args[1]) && $options['action'] = $args[1];
			isset($args[2]) && $options['operation'] = $args[2];
			$options['additionalParams'] = isset($args[3]) ? $args[3] : NULL;
			isset($args[4]) && $options['limit'] = $args[4];
			isset($args[5]) && $options['current'] = $args[5];
			isset($args[6]) && $options['sortingFieldId'] = $args[6];
			isset($args[7]) && $options['sortingDirection'] = $args[7];
			isset($args[8]) && $options['view'] = $args[8];
		}

		if (!isset($options['path']))
		{
			throw new Core_Exception("getModalLoad() needs at least 'path' option");
		}

		$options += array('additionalParams' => NULL);

		is_null($options['additionalParams']) && $options['additionalParams'] = $this->additionalParams;

		$aData = $this->_prepareAjaxRequest($options);

		$path = Core_Str::escapeJavascriptVariable($options['path']);
		$aData[] = "path: '{$path}'";

		isset($options['onHide'])
			&& $aData[] = "onHide: {$options['onHide']}";

		isset($options['width'])
			&& $aData[] = "width: '" . $options['width'] . "'";

		return "$.modalLoad({" . implode(',', $aData) . "}); return false";
	}

	/**
	 * Подготовка массива опций для AJAX-запроса
	 * @param array $options array('action', 'operation', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
	 * @return array
	*/
	protected function _prepareAjaxRequest($options)
	{
		$aData = array();

		if (isset($options['action']) && !is_null($options['action']))
		{
			$action = Core_Str::escapeJavascriptVariable(htmlspecialchars($options['action']));
			$aData[] = "action: '{$action}'";
		}

		if (isset($options['operation']) && !is_null($options['operation']))
		{
			$operation = Core_Str::escapeJavascriptVariable(htmlspecialchars($options['operation']));
			$aData[] = "operation: '{$operation}'";
		}

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), Core_Array::get($options, 'additionalParams', '', 'str'))
		);
		$aData[] = "additionalParams: '{$additionalParams}'";

		//is_null($limit) && $limit = $this->limit;
		if (isset($options['limit']) && !is_null($options['limit']))
		{
			$limit = intval($options['limit']);
			$limit && $aData[] = "limit: '{$limit}'";
		}

		$current = Core_Array::get($options, 'current');
		is_null($current) && $current = $this->current;
		$current = intval($current);
		$aData[] = "current: '{$current}'";

		$sortingFieldId = Core_Array::get($options, 'sortingFieldId');
		is_null($sortingFieldId) && $sortingFieldId = $this->sortingFieldId;
		//$sortingFieldId = intval($sortingFieldId);
		$aData[] = "sortingFieldId: '{$sortingFieldId}'";

		$sortingDirection = Core_Array::get($options, 'sortingDirection');
		is_null($sortingDirection) && $sortingDirection = $this->sortingDirection;
		$sortingDirection = intval($sortingDirection);
		$aData[] = "sortingDirection: '{$sortingDirection}'";

		$view = Core_Array::get($options, 'view', '', 'str');
		//is_null($view) && $view = $this->view;
		if (strlen($view))
		{
			$view = Core_Str::escapeJavascriptVariable(htmlspecialchars($view));
			$aData[] = "view: '{$view}'";
		}

		$windowId = Core_Str::escapeJavascriptVariable(htmlspecialchars(isset($options['window'])
			? $options['window']
			: $this->getWindowId()
		));
		$aData[] = "windowId: '{$windowId}'";

		return $aData;
	}

	/**
	* Получение кода вызова adminLoad для href
	* @param array $options array('action', 'operation', 'additionalParams', 'limit', 'current', 'sortingFieldId', 'sortingDirection', 'view', 'window')
	* @return string
	*/
	public function getAdminLoadHref($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $path, $action = NULL, $operation = NULL, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL
			$options = array();

			$options['path'] = $args[0];
			isset($args[1]) && $options['action'] = $args[1];
			isset($args[2]) && $options['operation'] = $args[2];
			$options['additionalParams'] = isset($args[3]) ? $args[3] : NULL;
			isset($args[4]) && $options['limit'] = $args[4];
			isset($args[5]) && $options['current'] = $args[5];
			isset($args[6]) && $options['sortingFieldId'] = $args[6];
			isset($args[7]) && $options['sortingDirection'] = $args[7];
			isset($args[8]) && $options['view'] = $args[8];
			// 'window' see
		}

		if (!isset($options['path']))
		{
			throw new Core_Exception("getAdminLoadHref() needs at least 'path' option");
		}

		$options += array('additionalParams' => NULL);

		is_null($options['additionalParams']) && $options['additionalParams'] = $this->additionalParams;

		$aData = array();

		if (isset($options['action']) && !is_null($options['action']))
		{
			$aData[] = "hostcms[action]=" . rawurlencode($options['action']);
		}

		if (isset($options['operation']) && !is_null($options['operation']))
		{
			$aData[] = "hostcms[operation]=" . rawurlencode($options['operation']);
		}

		// is_null($limit) && $limit = $this->limit;
		if (isset($options['limit']) && !is_null($options['limit']))
		{
			$limit = intval($options['limit']);
			$limit && $aData[] = "hostcms[limit]={$limit}";
		}

		$current = Core_Array::get($options, 'current');
		is_null($current) && $current = $this->current;
		$current = intval($current);
		$aData[] = "hostcms[current]={$current}";

		$sortingFieldId = Core_Array::get($options, 'sortingFieldId');
		is_null($sortingFieldId) && $sortingFieldId = $this->sortingFieldId;
		//$sortingFieldId = intval($sortingFieldId);
		$aData[] = "hostcms[sortingfield]={$sortingFieldId}";

		$sortingDirection = Core_Array::get($options, 'sortingDirection');
		is_null($sortingDirection) && $sortingDirection = $this->sortingDirection;
		$sortingDirection = intval($sortingDirection);
		$aData[] = "hostcms[sortingdirection]={$sortingDirection}";

		$windowId = rawurlencode(isset($options['window'])
			? $options['window']
			: $this->getWindowId()
		);
		strlen($windowId) && $aData[] = "hostcms[window]={$windowId}";

		$view = Core_Array::get($options, 'view', '', 'str');
		//is_null($view) && $view = $this->view;
		if (strlen($view))
		{
			$aData[] = "hostcms[view]=" . rawurlencode($view);
		}

		!is_null($this->filterId)
			&& $aData[] = "hostcms[filterId]=" . rawurlencode($this->filterId);

		// Filter values for paginations and etc.
		foreach ($_REQUEST as $key => $value)
		{
			if (!is_array($value) && $value !== ''
				&& !isset($_GET[$key])
				&& (strpos($key, 'admin_form_filter_') === 0 || strpos($key, 'topFilter_') === 0)
				&& $value != 'HOST_CMS_ALL'
				&& $key != 'secret_csrf'
			)
			{
				$aData[] = htmlspecialchars($key) . '=' . rawurlencode($value);
			}
		}

		//$options['additionalParams'] = str_replace(array("'", '"'), array("\'", '&quot;'), $options['additionalParams']);
		if ($options['additionalParams'])
		{
			// Уже содержит перечень параметров, которые должны быть экранированы
			//$options['additionalParams'] = rawurlencode($options['additionalParams']);
			$aData[] = $options['additionalParams'];
		}

		return $options['path'] . '?' . implode('&', $aData);
	}

	/**
	* Получение кода вызова adminLoad для события onclick
	* @param array $options
	* @return string
	*/
	public function getAdminSendForm($options)
	{
		$args = func_get_args();

		if (!is_array($args[0]))
		{
			// $action = NULL, $operation = NULL, $additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $buttonObject = 'this', $view = NULL
			$options = array();

			isset($args[0]) && $options['action'] = $args[0];
			isset($args[1]) && $options['operation'] = $args[1];
			$options['additionalParams'] = isset($args[2]) ? $args[2] : NULL;
			isset($args[3]) && $options['limit'] = $args[3];
			isset($args[4]) && $options['current'] = $args[4];
			isset($args[5]) && $options['sortingFieldId'] = $args[5];
			isset($args[6]) && $options['sortingDirection'] = $args[6];
			isset($args[7]) && $options['buttonObject'] = $args[7];
			isset($args[8]) && $options['view'] = $args[8];
		}

		$options += array('additionalParams' => NULL, 'buttonObject' => NULL, 'action' => NULL);

		is_null($options['additionalParams']) && $options['additionalParams'] = $this->additionalParams;

		is_null($options['buttonObject']) && $options['buttonObject'] = 'this';
		is_null($options['action']) && $options['action'] = $this->action;

		// Выбранные элементы для действия
		if (is_array($this->checked))
		{
			foreach ($this->checked as $datasetKey => $checkedItems)
			{
				foreach ($checkedItems as $checkedItemId => $v1)
				{
					$options['additionalParams'] .= empty($options['additionalParams']) ? '' : '&';
					$options['additionalParams'] .= 'hostcms[checked][' . intval($datasetKey) . '][' . Core_Str::escapeJavascriptVariable($checkedItemId) . ']=1';
				}
			}
		}

		$aData = $this->_prepareAjaxRequest($options);

		$aData[] = "buttonObject: {$options['buttonObject']}";

		return "$.adminSendForm({" . implode(',', $aData) . "}); return false";
	}

	/**
	 * Get field name, cut table name. E.g. table.field => field
	 * @param string $fieldName
	 * @return string
	 */
	public function getFieldName($fieldName)
	{
		strpos($fieldName, '.') !== FALSE && list(, $fieldName) = explode('.', $fieldName);

		return $fieldName;
	}

	/**
	 * Filter input callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackInput($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);
		$value = htmlspecialchars($value);
		?><input type="text" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" value="<?php echo $value?>" style="width: 100%" class="form-control input-sm" /><?php
	}

	/**
	 * Filter checkbox callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackCheckbox($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = intval($value);
		?><select name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" class="form-control">
			<option value="0" <?php echo $value == 0 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected_all'))?></option>
			<option value="1" <?php echo $value == 1 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected'))?></option>
			<option value="2" <?php echo $value == 2 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_not_selected'))?></option>
		</select><?php
	}

	/**
	 * Filter datetime callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackDatetime($date_from, $date_to, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$date_from = htmlspecialchars((string) $date_from);
		$date_to = htmlspecialchars((string) $date_to);

		$divClass = is_null($tabName) ? 'col-xs-12' : 'col-xs-6 col-sm-4';

		$sCurrentLng = Core_I18n::instance()->getLng();

		$windowId = $this->getWindowId();

		?><div class="row">
			<div class="date <?php echo $divClass?>">
				<input name="<?php echo $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_from?>" class="form-control input-sm" type="text"/>
			</div>
			<div class="date <?php echo $divClass?>">
				<input name="<?php echo $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_to?>" class="form-control input-sm" type="text"/>
			</div>
		</div>
		<script>
		(function($) {
			$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['dateTimePickerFormat']?>', showTodayButton: true, showClear: true}).on('dp.show', datetimepickerOnShow);
			$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['dateTimePickerFormat']?>', showTodayButton: true, showClear: true}).on('dp.show', datetimepickerOnShow);
		})(jQuery);
		</script><?php
	}

	/**
	 * Date-filed (from-to)
	 */
	protected function _filterCallbackDate($date_from, $date_to, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$date_from = htmlspecialchars((string) $date_from);
		$date_to = htmlspecialchars((string) $date_to);

		$divClass = is_null($tabName) ? 'col-xs-12' : 'col-xs-6 col-sm-4 col-md-3';

		$sCurrentLng = Core_I18n::instance()->getLng();

		$windowId = $this->getWindowId();

		?><div class="row">
			<div class="date <?php echo $divClass?>">
				<input type="text" name="<?php echo $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_from?>" class="form-control input-sm" />
			</div>
			<div class="date <?php echo $divClass?>">
				<input type="text" name="<?php echo $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_to?>" class="form-control input-sm" />
			</div>
		</div>
		<script>
		(function($) {
			$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['datePickerFormat']?>', showTodayButton: true, showClear: true}).on('dp.show', datetimepickerOnShow);

			$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['datePickerFormat']?>', showTodayButton: true, showClear: true}).on('dp.show', datetimepickerOnShow);
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Date-filed (single-mode)
	 */
	protected function _filterCallbackDateSingle($date_from, $date_to, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$divClass = is_null($tabName) ? 'col-xs-12' : 'col-xs-6 col-sm-4 col-md-3';

		$sCurrentLng = Core_I18n::instance()->getLng();

		$windowId = $this->getWindowId();

		?><div class="row">
			<div class="date <?php echo $divClass?>">
				<input type="text" name="<?php echo $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo htmlspecialchars($date_from)?>" class="form-control input-sm" />
			</div>
		</div>
		<script>
		(function($) {
			$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['datePickerFormat']?>'}).on('dp.show', datetimepickerOnShow);
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Filter select callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackSelect($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);

		$oSelect = Admin_Form_Entity::factory('Select')
			->name($filterPrefix . $oAdmin_Form_Field->id)
			->id($tabName . $filterPrefix . $oAdmin_Form_Field->id)
			// ->class('no-padding-left no-padding-right')
			->style('width: 100%')
			->divAttr(array())
			->value($value);

		if (is_array($oAdmin_Form_Field->list))
		{
			$aValue = $oAdmin_Form_Field->list;
		}
		else
		{
			$aValue = array();

			$aListExplode = explode("\n", $oAdmin_Form_Field->list);
			foreach ($aListExplode as $str_value)
			{
				// Каждую строку разделяем по равно
				$str_explode = explode('=', $str_value);

				if (count($str_explode) > 1 && $str_explode[1] != '…')
				{
					// сохраняем в массив варинаты значений и ссылки для них
					$aValue[trim($str_explode[0])] = trim($str_explode[1]);
				}
			}
		}

		$oSelect
			->options(array('HOST_CMS_ALL' => Core::_('Admin_Form.filter_selected_all')) + $aValue)
			->execute();
	}

	/**
	 * Filter user callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackUser($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$iUserId = $value
			? intval($value)
			: 'HOST_CMS_ALL';

		$placeholder = Core::_('User.select_user');
		$language = Core_I18n::instance()->getLng();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);
		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		Core_Html_Entity::factory('Select')
			->id($tabName . $filterPrefix . $oAdmin_Form_Field->id)
			->options($aSelectResponsibleUsers)
			->name($filterPrefix . $oAdmin_Form_Field->id)
			->value($iUserId)
			->execute();

		$windowId = $this->getWindowId();
		?><script>
		$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectUser({
				language: '<?php echo $language?>',
				placeholder: '<?php echo $placeholder?>',
				dropdownParent: $('#<?php echo $windowId?>')
			})
			.val('<?php echo $iUserId?>')
			.trigger('change.select2')
			.on('select2:unselect', function (){
				$(this)
					.next('.select2-container')
					.find('.select2-selection--single')
					.removeClass('user-container');
			});
			//$(".select2-container").css('width', '100%');
		</script><?php
	}

	/**
	 * Filter siteuser callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackSiteuser($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		if (Core::moduleIsActive('siteuser'))
		{
			$siteuser_id = intval($value);

			$placeholder = Core::_('Siteuser.select_siteuser');
			$language = Core_I18n::instance()->getLng();

			$oSiteuser = Core_Entity::factory('Siteuser')->getById($siteuser_id);
			$sOptions = !is_null($oSiteuser)
				? '<option value=' . $oSiteuser->id . ' selected="selected">' . htmlspecialchars($oSiteuser->login) . ' [' . $oSiteuser->id . ']</option>'
				: '<option></option>';

			$windowId = $this->getWindowId();
			?>
			<select id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>">
				<?php echo $sOptions?>
			</select>
			<script>
				$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectSiteuser({
					url: '/admin/siteuser/index.php?loadSiteusers&types[]=siteuser',
					language: '<?php echo $language?>',
					placeholder: '<?php echo $placeholder?>',
					dropdownParent: $('#<?php echo $windowId?>')
				});
				$("#<?php echo $windowId?> .select2-container").css('width', '100%');
			</script>
			<?php
		}
		else
		{
			?>—<?php
		}
	}

	/**
	 * Filter counterparty callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackCounterparty($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);

		if (Core::moduleIsActive('siteuser'))
		{
			$placeholder = Core::_('Siteuser.select_siteuser');
			$language = Core_I18n::instance()->getLng();

			$aTmpValue = explode('_', $value);

			$sOptions = '';

			if (count($aTmpValue) == 2 && ($aTmpValue[0] == 'company' || $aTmpValue[0] == 'person'))
			{
				$iCounterpartyId = intval($aTmpValue[1]);

				$oSelectedSiteuser = $aTmpValue[0] == 'company'
					? Core_Entity::factory('Siteuser_Company', $iCounterpartyId)->Siteuser
					: Core_Entity::factory('Siteuser_Person', $iCounterpartyId)->Siteuser;

				if (!is_null($oSelectedSiteuser->id))
				{
					$aSiteuserCompanies = $oSelectedSiteuser->Siteuser_Companies->findAll();

					foreach ($aSiteuserCompanies as $oSiteuserCompany)
					{
						$sOptionValue = 'company_' . $oSiteuserCompany->id;

						$sOptions .= '<option value="' . $sOptionValue . '" class="siteuser-company" ' . ($value == $sOptionValue ? 'selected="selected"' : '') . '>' . htmlspecialchars($oSiteuserCompany->name) . ' [' . htmlspecialchars($oSelectedSiteuser->login) . ']' . '%%%' . htmlspecialchars($oSiteuserCompany->getAvatar()) . '</option>';
					}

					$aSiteuserPersons = $oSelectedSiteuser->Siteuser_People->findAll();
					foreach ($aSiteuserPersons as $oSiteuserPerson)
					{
						$sOptionValue = 'person_' . $oSiteuserPerson->id;

						$sOptions .= '<option value="' . $sOptionValue . '" class="siteuser-person"' . ($value == $sOptionValue ? 'selected="selected"' : '') . '>' . htmlspecialchars($oSiteuserPerson->getFullName()) . ' [' . htmlspecialchars($oSelectedSiteuser->login) . ']' . '%%%' . htmlspecialchars($oSiteuserPerson->getAvatar()) . '</option>';
					}

					$sOptions = '<optgroup label="' . htmlspecialchars($oSelectedSiteuser->login) . '" class="siteuser">' . $sOptions . '</optgroup>';
				}
			}
			// Может быть только компания или представитель
			/*else
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getById($value);
				$sOptions = !is_null($oSiteuser)
					? '<option value=' . $oSiteuser->id . ' selected="selected">' . htmlspecialchars($oSiteuser->login) . ' [' . $oSiteuser->id . ']</option>'
					: '<option></option>';
			}*/

			$windowId = $this->getWindowId();
			?>
			<select id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>">
				<?php echo $sOptions?>
			</select>
			<script>
				$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectPersonCompany({
					url: '/admin/siteuser/index.php?loadSiteusers&types[]=person&types[]=company',
					language: '<?php echo $language?>',
					placeholder: '<?php echo $placeholder?>',
					dropdownParent: $('#<?php echo $windowId?>')
				});
			</script>
			<?php
		}
		else
		{
			?>—<?php
		}
	}

	/**
	 * Filter counterparty callback
	 * @param string $value
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param string $filterPrefix
	 * @param string $tabName
	 */
	protected function _filterCallbackSiteuserCompany($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);

		if (Core::moduleIsActive('siteuser'))
		{
			$placeholder = Core::_('Siteuser.select_siteuser');
			$language = Core_I18n::instance()->getLng();

			$aTmpValue = explode('_', $value);

			$sOptions = '';

			if (count($aTmpValue) == 2 && $aTmpValue[0] == 'company')
			{
				$iCounterpartyId = intval($aTmpValue[1]);

				$oSelectedSiteuser = $aTmpValue[0] == 'company'
					? Core_Entity::factory('Siteuser_Company', $iCounterpartyId)->Siteuser
					: NULL;

				if (!is_null($oSelectedSiteuser->id))
				{
					$aSiteuserCompanies = $oSelectedSiteuser->Siteuser_Companies->findAll();

					foreach ($aSiteuserCompanies as $oSiteuserCompany)
					{
						$sOptionValue = 'company_' . $oSiteuserCompany->id;

						$sOptions .= '<option value="' . $sOptionValue . '" class="siteuser-company" ' . ($value == $sOptionValue ? 'selected="selected"' : '') . '>' . htmlspecialchars($oSiteuserCompany->name) . ' [' . htmlspecialchars($oSelectedSiteuser->login) . ']' . '%%%' . htmlspecialchars($oSiteuserCompany->getAvatar()) . '</option>';
					}

					$sOptions = '<optgroup label="' . htmlspecialchars($oSelectedSiteuser->login) . '" class="siteuser">' . $sOptions . '</optgroup>';
				}
			}

			$windowId = $this->getWindowId();
			?>
			<select id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>">
				<?php echo $sOptions?>
			</select>
			<script>
				$('#<?php echo $windowId?> #<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectPersonCompany({
					url: '/admin/siteuser/index.php?loadSiteusers&types[]=company',
					language: '<?php echo $language?>',
					placeholder: '<?php echo $placeholder?>',
					dropdownParent: $('#<?php echo $windowId?>')
				});
			</script>
			<?php
		}
		else
		{
			?>—<?php
		}
	}

	/**
	 * Отображает поле фильтра (верхнего или основного)
	 */
	public function showFilterField($oAdmin_Form_Field, $filterPrefix, $tabName = NULL)
	{
		if (is_null($tabName))
		{
			$mValue = Core_Array::get($this->request, "{$filterPrefix}{$oAdmin_Form_Field->id}", '', 'trim');
		}
		else
		{
			$aTabs = Core_Array::get($this->filterSettings, 'tabs', array());

			$bHide = isset($aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['show'])
				&& $aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['show'] == 0;

			$bMain = $tabName === 'main';

			$bCurrent = $this->filterId === $tabName || $this->filterId === '' && $bMain;

			// Значение вначале берется из $this->request, если его там нет, то из данных в JSON
			$mValue = !$bHide
				? (array_key_exists('topFilter_' . $oAdmin_Form_Field->id, $this->request) && $bCurrent
					? Core_Array::get($this->request, 'topFilter_' . $oAdmin_Form_Field->id)
					: (
						isset($aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['value'])
							? $aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['value']
							: ''
					)
				)
				: '';
		}

		switch ($oAdmin_Form_Field->type)
		{
			case 1: // Строка
			case 2: // Поле ввода
			case 4: // Ссылка
			case 10: // Функция обратного вызова
				$this->_filters += array($oAdmin_Form_Field->name => array($this, '_filterCallbackInput'));
			break;

			case 3: // Checkbox
				$this->_filters += array($oAdmin_Form_Field->name => array($this, '_filterCallbackCheckbox'));
			break;

			case 5: // Дата-время
				$this->_filters += array($oAdmin_Form_Field->name => array($this, '_filterCallbackDatetime'));
			break;

			case 6: // Дата
				$this->_filters += array($oAdmin_Form_Field->name => array($this, '_filterCallbackDate'));
			break;
			case 7: // Картинка-ссылка
				if (is_null($tabName) || !strlen($oAdmin_Form_Field->list))
				{
					break;
				}
			case 8: // Выпадающий список
				$this->_filters += array($oAdmin_Form_Field->name => array($this, '_filterCallbackSelect'));
			break;
			default:
				?><div style="color: #CEC3A3; text-align: center">—</div><?php
			break;
		}

		if (isset($this->_filters[$oAdmin_Form_Field->name]))
		{
			switch ($oAdmin_Form_Field->type)
			{
				case 1: // Строка
				case 2: // Поле ввода
				case 4: // Ссылка
				case 10: // Функция обратного вызова
				case 3: // Checkbox.
				case 7: // Картинка-ссылка
				case 8: // Выпадающий список
					echo call_user_func($this->_filters[$oAdmin_Form_Field->name], $mValue, $oAdmin_Form_Field, $filterPrefix, $tabName);
				break;

				case 5: // Дата-время.
				case 6: // Дата.
					$date_from = Core_Array::get($this->request, "{$filterPrefix}from_{$oAdmin_Form_Field->id}", NULL);
					$date_to = Core_Array::get($this->request, "{$filterPrefix}to_{$oAdmin_Form_Field->id}", NULL);

					echo call_user_func($this->_filters[$oAdmin_Form_Field->name], $date_from, $date_to, $oAdmin_Form_Field, $filterPrefix, $tabName);
				break;
			}
		}

		return $this;
	}

	/**
	 * Check is callable method
	 * @param object $oEntity
	 * @param string $fieldName
	 * @return boolean
	 */
	public function isCallable($oEntity, $fieldName)
	{
		return method_exists($oEntity, $fieldName)
			|| method_exists($oEntity, 'isCallable') && $oEntity->isCallable($fieldName);
	}

	/**
	 * Convert %LIKE%
	 * @param string $str
	 * @return string
	 */
	public function convertLike($str)
	{
		return str_replace(array('*', '?'), array('%', '_'), Core_DataBase::instance()->escapeLike(trim($str)));
	}

	/**
	 * setDatasetConditions() has been called
	 * @var boolean
	 */
	protected $_setDatasetConditions = FALSE;

	/**
	 * Set dataset conditions
	 * @return self
	 */
	public function setDatasetConditions()
	{
		if ($this->_setDatasetConditions)
		{
			return $this;
		}

		$aAdmin_Form_Fields = $this->getAdminFormFields();

		$oAdmin_Form_Field_Sorting = $this->getAdminFormFieldById($this->sortingFieldId);

		foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
		{
			try {
				$oEntity = $oAdmin_Form_Dataset->getEntity();

				if ($oAdmin_Form_Field_Sorting && $oAdmin_Form_Field_Sorting->allow_sorting)
				{
					// Check field exists in the model
					$fieldName = $this->getFieldName($oAdmin_Form_Field_Sorting->name);

					if (isset($oEntity->$fieldName)
						|| method_exists($oEntity, $fieldName)
						// Для сортировки должно существовать св-во модели
						// || property_exists($oEntity, $fieldName)
						|| $oAdmin_Form_Dataset->issetExternalField($fieldName)
						|| strpos($oAdmin_Form_Field_Sorting->name, '.') !== FALSE
						|| $oAdmin_Form_Field_Sorting->filter_type == 1 // поле использует псевдоним и фильтруется через HAVING
					)
					{
						$oAdmin_Form_Dataset->addCondition(array(
								'orderBy' => array($oAdmin_Form_Field_Sorting->name, $this->sortingDirection
									? 'DESC'
									: 'ASC'
								)
							)
						);
					}
				}

				$aFieldIDs = $aAnotherDatasetFieldIDs = array();
				foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
				{
					// Перекрытие параметров для данного поля
					$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;
					foreach ($this->_datasets as $datasetKey => $oTmpAdmin_Form_Dataset)
					{
						$oAdmin_Form_Field_Changed = $this->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
					}

					if (is_string($oAdmin_Form_Field->id) && strpos($oAdmin_Form_Field->id, 'uf_') === 0
						&& $oEntity instanceof Core_Entity
					)
					{
						// Пользовательское свойство соответствует модели из датасета
						$oEntity->getModelName() == $oAdmin_Form_Field->_model_name
							? $aFieldIDs[] = substr($oAdmin_Form_Field->id, 3)
							: $aAnotherDatasetFieldIDs[] = substr($oAdmin_Form_Field->id, 3);
					}

					if ($oAdmin_Form_Field_Changed->allow_filter)
					{
						// Если имя поля counter_pages.date, то остается date
						$fieldName = $this->getFieldName($oAdmin_Form_Field_Changed->name);

						$filterPrefix = $this->filterId === ''
							// Main Filter
							? 'admin_form_filter_'
							// Top Filter
							: 'topFilter_';

						$mFilterValue = Core_Array::get($this->request, "{$filterPrefix}{$oAdmin_Form_Field_Changed->id}", NULL);
						$filterCondition = Core_Array::get($this->request, "{$filterPrefix}{$oAdmin_Form_Field_Changed->id}_condition", '=');

						// Функция обратного вызова для значения в фильтре
						if (isset($this->_filterCallbacks[$oAdmin_Form_Field_Changed->name]))
						{
							$mFilterValue = call_user_func(
								$this->_filterCallbacks[$oAdmin_Form_Field_Changed->name], $mFilterValue, $oAdmin_Form_Field_Changed, $filterPrefix
							);
						}

						if ($fieldName != '')
						{
							$sFilterType = $oAdmin_Form_Field_Changed->filter_type == 0
								? 'where'
								: 'having';

							// для HAVING не проверяем наличие поля
							if ($oAdmin_Form_Field_Changed->filter_type == 1
								|| isset($oEntity->$fieldName)
								|| method_exists($oEntity, $fieldName)
								|| property_exists($oEntity, $fieldName)
								|| $oAdmin_Form_Dataset->issetExternalField($fieldName)
							)
							{
								// Тип поля.
								switch ($oAdmin_Form_Field_Changed->type)
								{
									case 1: // Строка
									case 2: // Поле ввода
									case 4: // Ссылка
									case 10: // Вычислимое поле
										if (is_null($mFilterValue) || $mFilterValue == '' || mb_strlen($mFilterValue) > 255)
										{
											break;
										}

										$mLikeFilterValue = $this->convertLike($mFilterValue);

										$condition = in_array($filterCondition, array('=', '<', '>', '<=', '>='))
											? $filterCondition
											: '=';

										$oAdmin_Form_Dataset->addCondition(
											array($sFilterType => array($oAdmin_Form_Field_Changed->name,
												$mFilterValue !== $mLikeFilterValue
													? 'LIKE'
													: $condition, // : '=',
												$mLikeFilterValue))
										);
									break;
									case 3: // Checkbox.
										if (!$mFilterValue)
										{
											break;
										}

										if ($mFilterValue != 1)
										{
											$openName = $oAdmin_Form_Field_Changed->filter_type == 0
												? 'open'
												: 'havingOpen';

											$closeName = $oAdmin_Form_Field_Changed->filter_type == 0
												? 'close'
												: 'havingClose';

											$oAdmin_Form_Dataset
												->addCondition(array($openName => array()))
												->addCondition(
													array($sFilterType => array($oAdmin_Form_Field_Changed->name, '=', 0))
												)
												->addCondition(array('setOr' => array()))
												->addCondition(
													array($sFilterType => array($oAdmin_Form_Field_Changed->name, 'IS', NULL))
												)
												->addCondition(array($closeName => array()));
										}
										else
										{
											$oAdmin_Form_Dataset->addCondition(
												array($sFilterType =>
													array($oAdmin_Form_Field_Changed->name, '!=', 0)
												)
											);
										}
									break;
									case 5: // Дата-время.
									case 6: // Дата.

										// Дата от
										$dateFrom = Core_Array::get($this->request, "{$filterPrefix}from_{$oAdmin_Form_Field_Changed->id}");

										// Дата до
										$dateTo = Core_Array::get($this->request, "{$filterPrefix}to_{$oAdmin_Form_Field_Changed->id}");

										if ($dateFrom != '')
										{
											$sDateFrom = trim(
												$oAdmin_Form_Field_Changed->type == 5
													? Core_Date::datetime2sql($dateFrom)
													: date('Y-m-d 00:00:00', Core_Date::date2timestamp($dateFrom))
												);

											// Если не задана конечная дата, то ищем только за дату form (см. counter)
											//$sCondition = is_null($dateTo) ? '=' : '>=';
											if (is_null($dateTo))
											{
												if ($oAdmin_Form_Field_Changed->type == 5 && strpos($dateFrom, ' ') === FALSE)
												{
													$sCondition = 'BETWEEN';
													$sDateFrom = array($sDateFrom, date('Y-m-d 23:59:59', Core_Date::date2timestamp($dateFrom)));
												}
												else
												{
													$sCondition = '=';
												}
											}
											else
											{
												$sCondition = '>=';
											}

											$oAdmin_Form_Dataset->addCondition(
												array($sFilterType =>
													array($oAdmin_Form_Field_Changed->name, $sCondition, $sDateFrom)
												)
											);
										}

										if ($dateTo != '')
										{
											$sDateTo = trim(
												$oAdmin_Form_Field_Changed->type == 5
													// Преобразуем из d.m.Y H:i:s в SQL формат
													? Core_Date::datetime2sql($dateTo)
													// Преобразуем из d.m.Y в SQL формат
													: date('Y-m-d 23:59:59', Core_Date::date2timestamp($dateTo))
												);

											$oAdmin_Form_Dataset->addCondition(
												array($sFilterType =>
													array($oAdmin_Form_Field_Changed->name, '<=', $sDateTo)
												)
											);
										}
									break;
									case 7: // Картинка-ссылка
										if (!strlen($oAdmin_Form_Field_Changed->list))
										{
											break;
										}
									case 8: // Список
										if (is_null($mFilterValue))
										{
											break;
										}

										if ($mFilterValue != '' && $mFilterValue != 'HOST_CMS_ALL')
										{
											$oAdmin_Form_Dataset->addCondition(
												array($sFilterType =>
													array($oAdmin_Form_Field_Changed->name, is_array($mFilterValue) ? 'IN' : '=', $mFilterValue)
												)
											);
										}

									break;
								}
							}
						}
					}
				}
				// var_dump($aFieldIDs);

				// Необходимо объединять с таблицами пользовательских полей
				if (count($aFieldIDs) || count($aAnotherDatasetFieldIDs))
				{
					//$aTables = array();
					$entityTableName = $oEntity->getTableName();

					$aConditions = $oAdmin_Form_Dataset->getConditions();

					$issetSelect = $issetGroupBy = FALSE;
					foreach ($aConditions as $condition)
					{
						if (isset($condition['select']))
						{
							$issetSelect = TRUE;
						}
						elseif (isset($condition['groupBy']))
						{
							$issetGroupBy = TRUE;
						}

						if ($issetSelect && $issetGroupBy)
						{
							break;
						}
					}

					if (!$issetSelect)
					{
						$oAdmin_Form_Dataset->addCondition(
							array('select' => array($entityTableName . '.*'))
						);
					}

					$bMultiple = FALSE;
					// Поля, связанные с датасетом
					foreach ($aFieldIDs as $field_id)
					{
						$oField = Core_Entity::factory('Field', $field_id);

						if ($oField->type != 2)
						{
							$tableName = Field_Controller_Value::factory($oField->type)->getTableName();

							$tableNameAlias = 'uf' . $field_id;

							$oField->multiple && $bMultiple = TRUE;

							$oAdmin_Form_Dataset->addCondition(
								array('select' => array(array($oField->multiple ? Core_QueryBuilder::expression('GROUP_CONCAT(DISTINCT `' . $tableNameAlias . '`.`value` SEPARATOR ", ")') : $tableNameAlias . '.value', 'datauf_' . $field_id)))
							)
							->addCondition(
								array(
									'leftJoin' => array(array($tableName, $tableNameAlias), $tableNameAlias . '.entity_id', '=', $entityTableName . '.id', array(
										array('AND' => array($tableNameAlias . '.field_id', '=', $field_id))
									))
								)
							);
						}
					}

					// Поля других датасетов
					foreach ($aAnotherDatasetFieldIDs as $field_id)
					{
						$oAdmin_Form_Dataset->addCondition(
							array('select' => array(array(Core_QueryBuilder::expression("''"), 'datauf_' . $field_id)))
						);
					}

					if ($bMultiple && !$issetGroupBy)
					{
						// Необходима группировка для GROUP_CONCAT(DISTINCT ...)
						$oAdmin_Form_Dataset->addCondition(
							array('groupBy' => array($entityTableName . '.' . $oEntity->getPrimaryKeyName()))
						);
					}
				}
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		$this->_setDatasetConditions = TRUE;

		return $this;
	}

	/**
	 * Set dataset limits
	 * @return self
	 */
	public function setDatasetLimits()
	{
		// begin
		$offset = $this->limit * ($this->current - 1);

		// Корректируем лимиты, если они указаны общие для N источников
		//if (isset($this->form_params['limit']['all']))
		//{
		// Сумируем общее количество элементов из разных источников
		// и проверяем, меньше ли они $this->form_params['limit']['all']['begin']
		// если меньше - то расчитываем корректный begin
		//if ($datasetKey == 0)
		//{
			if (count($this->_datasets) == 1)
			{
				reset($this->_datasets);

				try {
					$oAdmin_Form_Dataset = current($this->_datasets);

					!is_null($this->limit) && $oAdmin_Form_Dataset
						->limit($this->limit)
						->offset($offset);

					$oAdmin_Form_Dataset->load();
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}

				// Данные уже были загружены при первом применении лимитов и одном источнике
				$bLoaded = TRUE;
			}
			else
			{
				$bLoaded = FALSE;
			}

			$iTotalCount = $this->getTotalCount();

			if ($iTotalCount < $offset)
			{
				$current = floor($iTotalCount / $this->limit);

				if ($current <= $this->current)
				{
					$current = 1;
					$offset = 0;
					$bLoaded = FALSE;
				}

				$this->current($current);
			}
			elseif ($iTotalCount == $offset && $offset >= $this->limit)
			{
				$offset -= $this->limit;
				$bLoaded = FALSE;
			}
		//}

		try
		{
			foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				try {
					$datasetCount = $oAdmin_Form_Dataset->getCount();

					if ($datasetCount > $offset)
					{
						!is_null($this->limit) && $oAdmin_Form_Dataset
							->limit($this->limit)
							->offset($offset)
							->loaded($bLoaded);
					}
					else // Не показывать, т.к. очередь другого датасета
					{
						$oAdmin_Form_Dataset
							->limit(0)
							->offset(0)
							->loaded($bLoaded);
					}

					// Предыдущие можем смотреть только для 1-го источника и следующих
					if (!is_null($this->limit) && $datasetKey > 0)
					{
						// Если число элементов предыдущего источника меньше текущего начала
						$prevDatasetCount = $this->_datasets[$datasetKey - 1]->getCount();

						if ($prevDatasetCount - $offset // 17 - 10 = 7
							< $this->limit // 10
						)
						{
							$begin = $offset - $prevDatasetCount;

							if ($begin < 0)
							{
								$begin = 0;
							}

							$oAdmin_Form_Dataset
								->limit($this->limit - ($prevDatasetCount - $offset) - $begin)
								->offset($begin)
								->loaded($bLoaded);
						}
						else
						{
							$oAdmin_Form_Dataset
								->limit(0)
								->offset(0)
								->loaded($bLoaded);
						}
					}
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}
			}
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}

		return $this;
	}

	/**
	 * Apply external changes for fields
	 * @param Admin_Form_Dataset $oAdmin_Form_Dataset dataset
	 * @param Admin_Form_Field $oAdmin_Form_Field field
	 * @return object
	 */
	public function changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field)
	{
		// Проверяем, установлено ли пользователем перекрытие параметров для данного поля.
		$aChangedFields = $oAdmin_Form_Dataset->getFieldChanges($oAdmin_Form_Field->name);

		if ($aChangedFields)
		{
			$aChanged = $aChangedFields + (
				$oAdmin_Form_Field instanceof stdClass
					? (array)$oAdmin_Form_Field
					: $oAdmin_Form_Field->toArray()
			);
			$oAdmin_Form_Field_Changed = (object)$aChanged;
		}
		else
		{
			$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;
		}

		return $oAdmin_Form_Field_Changed;
	}

	/**
	 * Apply external changes for actions
	 * @param Admin_Form_Dataset $oAdmin_Form_Dataset dataset
	 * @param Admin_Form_Action $oAdmin_Form_Action actions
	 * @return object
	 */
	public function changeAction($oAdmin_Form_Dataset, $oAdmin_Form_Action)
	{
		// Проверяем, установлено ли пользователем перекрытие параметров для данного поля.
		$aChangedActions = $oAdmin_Form_Dataset->getActionChanges($oAdmin_Form_Action->name);

		if ($aChangedActions)
		{
			$aChanged = $aChangedActions + (
				$oAdmin_Form_Action instanceof stdClass
					? (array)$oAdmin_Form_Action
					: $oAdmin_Form_Action->toArray()
			);
			$oAdmin_Form_Action_Changed = (object)$aChanged;
		}
		else
		{
			$oAdmin_Form_Action_Changed = $oAdmin_Form_Action;
		}

		return $oAdmin_Form_Action_Changed;
	}
}