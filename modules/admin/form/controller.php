<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Admin_Form_Controller extends Core_Servant_Properties
{
	/**
	 * Use skin
	 * @var boolean
	 */
	protected $_skin = TRUE;

	/**
	 * Use AJAX
	 * @var boolean
	 */
	//protected $_ajax = FALSE;

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
	 * @var string
	 */
	protected $_Admin_Language = NULL;

	/**
	 * Default form sorting field
	 * @var string
	 */
	//protected $_sortingAdmin_Form_Field = NULL;

	/**
	 * String of additional parameters
	 * @var string
	 */
	//protected $_additionalParams = NULL;

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
	 */
	static protected $_icon = array('fa fa-address-book', 'fa fa-address-card', 'fa fa-barcode', 'fa fa-bars', 'fa fa-beer', 'fa fa-bell', 'fa fa-bicycle', 'fa fa-binoculars', 'fa fa-birthday-cake', 'fa fa-bolt', 'fa fa-book', 'fa fa-bookmark', 'fa fa-briefcase', 'fa fa-bullseye', 'fa fa-camera', 'fa fa-car', 'fa fa-certificate', 'fa fa-cloud', 'fa fa-code', 'fa fa-coffee', 'fa fa-cube', 'fa fa-dashboard', 'fa fa-database', 'fa fa-dot-circle-o', 'fa fa-flask', 'fa fa-futbol-o', 'fa fa-gift', 'fa fa-glass', 'fa fa-heart', 'fa fa-hourglass', 'fa fa-leaf', 'fa fa-location-arrow', 'fa fa-magic', 'fa fa-magnet', 'fa fa-paper-plane', 'fa fa-paw', 'fa fa-plane', 'fa fa-plug', 'fa fa-road', 'fa fa-rocket', 'fa fa-smile-o', 'fa fa-snowflake-o', 'fa fa-space-shuttle', 'fa fa-star', 'fa fa-thumbs-up', 'fa fa-tree', 'fa fa-trophy', 'fa fa-wrench');

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

		Core::initConstants(Core_Entity::factory('Site', CURRENT_SITE));

		$aTmp = array();
		foreach ($_GET as $key => $value)
		{
			if (!is_array($value) && $key != '_' && strpos($key, 'admin_form_filter_') === FALSE && strpos($key, 'topFilter_') === FALSE)
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
		return str_replace('%', '\\%', $str);
	}

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
	);

	/**
	 * Apply form settings
	 * @return self
	 */
	public function formSettings()
	{
		$this->request = $_REQUEST;

		$formSettings = Core_Array::get($this->request, 'hostcms', array())
			+ array(
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

		$this
			->limit($formSettings['limit'] !== '' ? $formSettings['limit'] : NULL)
			->current($formSettings['current'] !== '' ? $formSettings['current'] : NULL)
			->sortingDirection($formSettings['sortingdirection'] !== '' ? $formSettings['sortingdirection'] : NULL)
			->sortingFieldId($formSettings['sortingfield'] !== '' ? $formSettings['sortingfield'] : NULL)
			->filterId($formSettings['filterId'])
			->action($formSettings['action'] !== '' ? $formSettings['action'] : NULL)
			->operation($formSettings['operation'] !== '' ? $formSettings['operation'] : NULL)
			->view($formSettings['view'] !== '' ? $formSettings['view'] : NULL)
			->checked($formSettings['checked'])
			->window($formSettings['window'])
			->ajax(Core_Array::get($this->request, '_', FALSE));

		$oUserCurrent = Core_Auth::getCurrentUser();

		if ($oUserCurrent && $this->_Admin_Form)
		{
			$user_id = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;

			is_null($this->_oAdmin_Form_Setting)
				&& $this->_oAdmin_Form_Setting = $this->_Admin_Form->getSettingForUser($user_id);

			$bAdmin_Form_Setting_Already_Exists = is_object($this->_oAdmin_Form_Setting);

			if (!$bAdmin_Form_Setting_Already_Exists)
			{
				$this->_oAdmin_Form_Setting = Core_Entity::factory('Admin_Form_Setting');

				// Связываем с формой и пользователем сайта
				$this->_Admin_Form->add($this->_oAdmin_Form_Setting);
				$oUserCurrent->add($this->_oAdmin_Form_Setting);
			}

			!is_null($this->limit)
				&& $this->_oAdmin_Form_Setting->on_page = intval($this->limit);

			!is_null($this->current)
				&& $this->_oAdmin_Form_Setting->page_number = intval($this->current);

			if (!is_null($this->sortingFieldId))
			{
				$this->_oAdmin_Form_Setting->order_field_id = $this->sortingFieldId;
			}
			elseif ($bAdmin_Form_Setting_Already_Exists)
			{
				$this->sortingFieldId(intval($this->_oAdmin_Form_Setting->order_field_id));
			}

			// Set sorting field
			/*$this->sortingFieldId
				&& $this->_sortingAdmin_Form_Field = $this->_Admin_Form->Admin_Form_Fields->getById($this->sortingFieldId);*/

			if (!is_null($this->sortingDirection))
			{
				$this->_oAdmin_Form_Setting->order_direction = $this->sortingDirection;
			}
			elseif ($bAdmin_Form_Setting_Already_Exists)
			{
				$this->sortingDirection(intval($this->_oAdmin_Form_Setting->order_direction));
			}

			if (!is_null($this->view))
			{
				$this->_oAdmin_Form_Setting->view = strval($this->view);
			}
			elseif ($bAdmin_Form_Setting_Already_Exists)
			{
				$this->view($this->_oAdmin_Form_Setting->view);
			}

			$this->view == '' && $this->view = 'list';

			$this->_oAdmin_Form_Setting->save();
		}

		// Добавляем замену для windowId
		$this->_externalReplace['{windowId}'] = $this->getWindowId();
		return $this;
	}

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
	 * Array of checked items
	 * @var array
	 */
	//protected $_checked = array();

	/**
	 * Set checked items on the form
	 * Выбранные элементы в форме
	 * @param array $checked checked items
	 * @return self
	 */
	/*public function checked(array $checked)
	{
		$this->checked = $checked;
		return $this;
	}*/

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
	public function module(Core_Module $oModule)
	{
		$this->module = $oModule;
		return $this;
	}

	public function getModule()
	{
		return $this->module;
	}

	public function getSortingField()
	{
		// Set sorting field
		if ($this->sortingFieldId)
		{
			$sortingAdmin_Form_Field = $this->_Admin_Form->Admin_Form_Fields->getById($this->sortingFieldId);
		}
		else
		{
			// Default sorting field
			$sortingFieldName = $this->_Admin_Form->default_order_field;
			$sortingAdmin_Form_Field = $this->_Admin_Form->Admin_Form_Fields->getByName($sortingFieldName);

			if (is_null($sortingAdmin_Form_Field))
			{
				throw new Core_Exception("Default form sorting field '%sortingFieldName' does not exist.",
					array ('%sortingFieldName' => $sortingFieldName)
				);
			}
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

		$this->limit = ON_PAGE;
		$this->current = 1; // счет с 1

		$this->showOperations = TRUE;

		// Default View Is 'list' Mode
		$this->addView('list');
		$this->view = 'list';

		$this->_Admin_Form = $oAdmin_Form;

		//$this->_Admin_Form->_load();

		if ($oAdmin_Form)
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

			// Default sorting field
			/*$sortingFieldName = $this->_Admin_Form->default_order_field;
			$this->_sortingAdmin_Form_Field = $this->_Admin_Form->Admin_Form_Fields->getByName($sortingFieldName);

			if (is_null($this->_sortingAdmin_Form_Field))
			{
				throw new Core_Exception("Default form sorting field '%sortingFieldName' does not exist.",
					array ('%sortingFieldName' => $sortingFieldName)
				);
			}*/

			$oUserCurrent = Core_Auth::getCurrentUser();
			$user_id = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;

			$this->_oAdmin_Form_Setting = $this->_Admin_Form->getSettingForUser($user_id);

			// Данные поля сортировки и направления из настроек пользователя
			if ($this->_oAdmin_Form_Setting)
			{
				$aFilter = $this->getFilterJson();

				$this
					->filterSettings(is_array($aFilter) ? $aFilter : array())
					->limit($this->_oAdmin_Form_Setting->on_page)
					->current($this->_oAdmin_Form_Setting->page_number)
					->sortingFieldId($this->_oAdmin_Form_Setting->order_field_id)
					->sortingDirection($this->_oAdmin_Form_Setting->order_direction);
			}
			else
			{
				// Данные по умолчанию из настроек формы
				$this->sortingFieldId(
					$this->_Admin_Form->Admin_Form_Fields
						->getByName($this->_Admin_Form->default_order_field)->id
				)
				->sortingDirection($this->_Admin_Form->default_order_direction);
			}
		}

		// Current path
		$this->path($_SERVER['PHP_SELF']);
	}

	public function getFilterJson()
	{
		return $this->_oAdmin_Form_Setting->filter != ''
			? json_decode($this->_oAdmin_Form_Setting->filter, TRUE)
			: array();
	}

	/**
	 * Is showing operations necessary
	 * @var boolean
	 */
	//protected $_showOperations = TRUE;

	/**
	 * Display operations of the form
	 * @param boolean $showOperations mode
	 * @return self
	 */
	/*public function showOperations($showOperations)
	{
		$this->showOperations = $showOperations;
		return $this;
	}*/

	/**
	 * Get display operations of the form
	 * @return showOperations mode
	 */
	/*public function getShowOperations()
	{
		return $this->showOperations;
	}*/

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
	 * Is showing list of action necessary
	 * @var boolean
	 */
	//protected $_showBottomActions = TRUE;

	/**
	 * Display actions of the bottom of the form
	 * @param boolean $showBottomActions mode
	 * @return self
	 */
	/*public function showBottomActions($showBottomActions)
	{
		$this->_showBottomActions = $showBottomActions;
		return $this;
	}*/

	/**
	 * Page title <h1>
	 */
	//protected $_title = NULL;

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
	 * Page title <title>
	 * @var string
	 */
	//protected $_pageTitle = NULL;

	/**
	 * Set page <title>
	 * @param $pageTitle title
	 * @return self
	 */
	public function pageTitle($pageTitle)
	{
		$this->pageTitle = html_entity_decode($pageTitle);
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
	 * Limits elements on page
	 * @var int
	 */
	//protected $_limit = ON_PAGE;


	/**
	 * Set limit of elements on page
	 * @param int $limit count
	 * @return self
	 */
	public function limit($limit)
	{
		$limit = intval($limit);
		$limit && $this->limit = $limit;

		return $this;
	}

	/**
	 * Current page
	 * @var int
	 */
	//protected $_current = 1; // счет с 1


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
	 * Message text
	 * @var string
	 */
	protected $_message = NULL;

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

		$viewAdmin_Form_Controller = new $className($this);
		$viewAdmin_Form_Controller->execute();

		Core_Event::notify('Admin_Form_Controller.onAfterShowContent', $this);

		$this
			->addContent(ob_get_clean());

		return $this;
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
					$aAdmin_Form_Actions = $this->_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

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
									$oObject = $this->_datasets[$datasetKey]->getObject($checkedItemId);

									// Проверка на наличие объекта и доступность действия к dataset
									if (!is_object($oObject) || $oAdmin_Form_Action->dataset != -1
										&& $oAdmin_Form_Action->dataset != $datasetKey)
									{
										break;
									}

									// Проверка через user_id на право выполнения действия над объектом
									$bAccessToObject = $oUser->checkObjectAccess($oObject);

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
										$actionResult = $this->_actionHandlers[$actionName]
											->setDatasetId($datasetKey)
											->setObject($oObject)
											->execute($this->operation);

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
										$this->addMessage(ob_get_clean())
											/*->addContent('')
											->pageTitle('')
											->title('')*/;

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

			$aData = array();

			$aAdmin_Form_Fields = $this->_Admin_Form->Admin_Form_Fields->findAll();
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				// 0 - Столбец и фильтр, 2 - Столбец
				if ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2)
				{
					$Admin_Word_Value = $oAdmin_Form_Field
						->Admin_Word
						->getWordByLanguage($oAdmin_Language->id);

					$fieldName = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
						? $Admin_Word_Value->name
						: NULL;

					$aData[] = $this->prepareString($fieldName);
				}
			}
			$this->_printRow($aData);

			$this->setDatasetConditions();
			foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
			{
				$aEntities = $oAdmin_Form_Dataset->load();

				foreach ($aEntities as $oEntity)
				{
					$aData = array();
					foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
					{
						// 0 - Столбец и фильтр, 2 - Столбец
						if ($oAdmin_Form_Field->view == 0 || $oAdmin_Form_Field->view == 2)
						{
							// Перекрытие параметров для данного поля
							$oAdmin_Form_Field_Changed = $this->changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field);

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

							$sFormat = $oAdmin_Form_Field_Changed->format;

							switch ($oAdmin_Form_Field_Changed->type)
							{
								case 1: // Текст.
								case 2: // Поле ввода.
								case 4: // Ссылка.
								case 7: // Картинка-ссылка.
									if (!is_null($value))
									{
										$value = $this->applyFormat($value, $sFormat);
									}
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

							$aData[] = $value;
						}
					}

					$this->_printRow($aData);
				}
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
		return str_replace('"', '""', trim($string));
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

		// Текущий пользователь
		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		// Доступные действия для пользователя
		$aAllowed_Admin_Form_Actions = $this->_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

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
			Core::factory('Core_Html_Entity_Script')
				->value("(function($){
					$('#{$windowId} .editable').editable({windowId: '{$windowId}', path: '{$path}'});
				})(jQuery);")
				->execute();
		}

		Core::factory('Core_Html_Entity_Script')
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
			$subject = str_replace($replace_key, $replace_value, $subject);
		}

		$aColumns = $oEntity->getTableColumns();
		foreach ($aColumns as $columnName => $columnArray)
		{
			$subject = str_replace(
				'{' . $columnName . '}',
				$mode == 'link'
					? htmlspecialchars($oEntity->$columnName)
					: Core_Str::escapeJavascriptVariable($this->jQueryEscape($oEntity->$columnName)),
				$subject
			);
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
		return !empty($format)
			? sprintf($format, $str)
			: $str;
	}

	/**
	 * ID of sorting field
	 * @var int
	 */
	//protected $_sortingFieldId = NULL;

	/**
	 * Set sorting field by ID
	 * @param int $sortingFieldId field ID
	 * @return self
	 */
	public function sortingFieldId($sortingFieldId)
	{
		if (!is_null($sortingFieldId) && $this->_Admin_Form)
		{
			// Проверка принадлежности форме
			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field')->find($sortingFieldId);

			if ($oAdmin_Form_Field && $this->_Admin_Form->id == $oAdmin_Form_Field->admin_form_id)
			{
				$this->sortingFieldId = $sortingFieldId;
			}
			else
			{
				$this->sortingFieldId = $this->sortingDirection = NULL;
			}
		}
		return $this;
	}

	/**
	 * Sorting direction
	 * @var int
	 */
	//protected $_sortingDirection = NULL;

	/**
	 * Set sorting direction
	 * @param int $sortingDirection direction
	 * @return self
	 */
	public function sortingDirection($sortingDirection)
	{
		if (!is_null($sortingDirection))
		{
			$this->sortingDirection = intval($sortingDirection);
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @param string $path path
	 * @param string $action action
	 * @param string $operation operation
	 * @param string $datasetKey dataset key
	 * @param string $datasetValue dataset value
	 * @param string $additionalParams additional params
	 * @param string $limit limit
	 * @param string $current current
	 * @param int $sortingFieldId sorting field ID
	 * @param string $sortingDirection sorting direction
	 * @return string
	 */
	public function getAdminActionLoadAjax($path, $action, $operation, $datasetKey, $datasetValue,
		$additionalParams = NULL, $limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL)
	{
		$windowId = Core_Str::escapeJavascriptVariable($this->getWindowId());
		$datasetKey = Core_Str::escapeJavascriptVariable($this->jQueryEscape($datasetKey));
		$datasetValue = Core_Str::escapeJavascriptVariable($this->jQueryEscape($datasetValue));

		return "$('#{$windowId} #row_{$datasetKey}_{$datasetValue}').toggleHighlight(); "
			. "$.adminCheckObject({objectId: 'check_{$datasetKey}_{$datasetValue}', windowId: '{$windowId}'}); "
			. $this->getAdminLoadAjax($path, $action, $operation, $additionalParams, $limit, $current, $sortingFieldId, $sortingDirection, $view);
	}

	/**
	 * Получение кода вызова adminLoad для события onclick
	 * @param string $path path
	 * @param string $action action
	 * @param string $operation operation
	 * @param string $additionalParams additional params
	 * @param string $limit limit
	 * @param mixed $current current
	 * @param int $sortingFieldId sorting field ID
	 * @param mixed $sortingDirection sorting direction
	 * @param string $view view mode
	 * @return string
	*/
	public function getAdminLoadAjax($path, $action = NULL, $operation = NULL, $additionalParams = NULL,
		$limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL)
	{
		/*if ($path)
		{
			// Нельзя, т.к. при изменении у предыдущего параметра URL-а, то действия сломаются
			//$this->AAction = str_replace("'", "\'", $AAction);
		}
		else
		{
			$path = '';
		}*/

		$path = Core_Str::escapeJavascriptVariable($path);
		$action = Core_Str::escapeJavascriptVariable(htmlspecialchars($action));
		$operation = Core_Str::escapeJavascriptVariable(htmlspecialchars($operation));
		$windowId = Core_Str::escapeJavascriptVariable(htmlspecialchars($this->getWindowId()));

		$aData = array();

		$aData[] = "path: '{$path}'";

		/*if (is_null($action))
		{
			$action = $this->action;
		}*/
		$aData[] = "action: '{$action}'";

		/*if (is_null($operation))
		{
			$operation = $this->operation;
		}*/
		$aData[] = "operation: '{$operation}'";

		is_null($additionalParams) && $additionalParams = $this->additionalParams;

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $additionalParams)
		);
		$aData[] = "additionalParams: '{$additionalParams}'";

		/*if (is_null($limit))
		{
			$limit = $this->limit;
		}*/
		$limit = intval($limit);
		$limit && $aData[] = "limit: '{$limit}'";

		if (is_null($current))
		{
			$current = $this->current;
		}
		$current = intval($current);
		$aData[] = "current: '{$current}'";

		if (is_null($sortingFieldId))
		{
			$sortingFieldId = $this->sortingFieldId;
		}
		$sortingFieldId = intval($sortingFieldId);
		$aData[] = "sortingFieldId: '{$sortingFieldId}'";

		is_null($sortingDirection) && $sortingDirection = $this->sortingDirection;
		$sortingDirection = intval($sortingDirection);
		$aData[] = "sortingDirection: '{$sortingDirection}'";

		is_null($view) && $view = $this->view;
		$view = Core_Str::escapeJavascriptVariable(htmlspecialchars($view));
		$aData[] = "view: '{$view}'";

		$aData[] = "windowId: '{$windowId}'";

		return "$.adminLoad({" . implode(',', $aData) . "}); return false";
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
	 * Для действия из списка элементов
	 * @param string $path path
	 * @param string $action action
	 * @param string $operation operation
	 * @param string $datasetKey dataset key
	 * @param string $datasetValue dataset value
	 * @param string $additionalParams additional params
	 * @param string $limit limit
	 * @param string $current current
	 * @param int $sortingFieldId sorting field ID
	 * @param string $sortingDirection sorting direction
	 * @return string
	 */
	public function getAdminActionLoadHref($path, $action, $operation, $datasetKey, $datasetValue, $additionalParams = NULL,
		$limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL)
	{
		is_null($additionalParams) && $additionalParams .= $this->additionalParams;

		$datasetKey = Core_Str::escapeJavascriptVariable($datasetKey);
		$datasetValue = Core_Str::escapeJavascriptVariable($datasetValue);

		$additionalParams .= '&hostcms[checked][' . $datasetKey . '][' . $datasetValue . ']=1';

		return $this->getAdminLoadHref($path, $action, $operation, $additionalParams, $limit, $current, $sortingFieldId, $sortingDirection, $view);
	}

	/**
	* Получение кода вызова adminLoad для href
	* @param string $path path
	* @param string $action action name
	* @param string $operation operation name
	* @param string $additionalParams additional params
	* @param int $limit count of items on page
	* @param int $current current page number
	* @param int $sortingFieldId ID of sorting field
	* @param int $sortingDirection sorting direction
	* @param string $view view mode
	* @return string
	*/
	public function getAdminLoadHref($path, $action = NULL, $operation = NULL, $additionalParams = NULL,
		$limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $view = NULL)
	{
		$aData = array();

		$action = rawurlencode($action);
		$aData[] = "hostcms[action]={$action}";

		$operation = rawurlencode($operation);
		$aData[] = "hostcms[operation]={$operation}";

		/*if (is_null($limit))
		{
			$limit = $this->limit;
		}*/
		$limit = intval($limit);
		$limit && $aData[] = "hostcms[limit]={$limit}";

		if (is_null($current))
		{
			$current = $this->current;
		}
		$current = intval($current);
		$aData[] = "hostcms[current]={$current}";

		if (is_null($sortingFieldId))
		{
			$sortingFieldId = $this->sortingFieldId;
		}
		$sortingFieldId = intval($sortingFieldId);
		$aData[] = "hostcms[sortingfield]={$sortingFieldId}";

		is_null($sortingDirection) && $sortingDirection = $this->sortingDirection;
		$sortingDirection = intval($sortingDirection);
		$aData[] = "hostcms[sortingdirection]={$sortingDirection}";

		$windowId = rawurlencode($this->getWindowId());
		strlen($windowId) && $aData[] = "hostcms[window]={$windowId}";

		is_null($view) && $view = $this->view;
		$view = rawurlencode($view);
		$aData[] = "hostcms[view]={$view}";

		$aData[] = "hostcms[filterId]=" . rawurlencode($this->filterId);

		is_null($additionalParams) && $additionalParams = $this->additionalParams;

		// Filter values for paginations and etc.
		foreach ($_REQUEST as $key => $value)
		{
			if (!is_array($value) && $value !== ''
				&& !isset($_GET[$key])
				&& (strpos($key, 'admin_form_filter_') === 0 || strpos($key, 'topFilter_') === 0)
				&& $value != 'HOST_CMS_ALL'
			)
			{
				$aData[] = htmlspecialchars($key) . '=' . rawurlencode($value);
			}
		}

		//$additionalParams = str_replace(array("'", '"'), array("\'", '&quot;'), $additionalParams);
		if ($additionalParams)
		{
			// Уже содержит перечень параметров, которые должны быть экранированы
			//$additionalParams = rawurlencode($additionalParams);
			$aData[] = $additionalParams;
		}

		return $path . '?' . implode('&', $aData);
	}

	/**
	* Получение кода вызова adminLoad для события onclick
	* @param string $action action name
	* @param string $operation operation name
	* @param string $additionalParams additional params
	* @param int $limit count of items on page
	* @param int $current current page number
	* @param int $sortingFieldId ID of sorting field
	* @param int $sortingDirection sorting direction
	* @return string
	*/
	public function getAdminSendForm($action = NULL, $operation = NULL, $additionalParams = NULL,
		$limit = NULL, $current = NULL, $sortingFieldId = NULL, $sortingDirection = NULL, $buttonObject = 'this', $view = NULL)
	{
		$aData = array();

		$aData[] = "buttonObject: {$buttonObject}";

		// add
		if (is_null($action))
		{
			$action = $this->action;
		}
		$action = Core_Str::escapeJavascriptVariable(htmlspecialchars($action));
		$aData[] = "action: '{$action}'";

		/*if (is_null($operation))
		{
			$operation = $this->operation;
		}*/
		$operation = Core_Str::escapeJavascriptVariable(htmlspecialchars($operation));
		$aData[] = "operation: '{$operation}'";

		is_null($additionalParams) && $additionalParams = $this->additionalParams;

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $additionalParams)
		);
		// Выбранные элементы для действия

		if (is_array($this->checked))
		{
			foreach ($this->checked as $datasetKey => $checkedItems)
			{
				foreach ($checkedItems as $checkedItemId => $v1)
				{
					$datasetKey = intval($datasetKey);
					$checkedItemId = htmlspecialchars($checkedItemId);

					$additionalParams .= empty($additionalParams) ? '' : '&';
					$additionalParams .= 'hostcms[checked][' . $datasetKey . '][' . $checkedItemId . ']=1';
				}
			}
		}
		$aData[] = "additionalParams: '{$additionalParams}'";

		/*if (is_null($limit))
		{
			$limit = $this->limit;
		}*/
		$limit && $limit = intval($limit);
		$aData[] = "limit: '{$limit}'";

		if (is_null($current))
		{
			$current = $this->current;
		}
		$current = intval($current);
		$aData[] = "current: '{$current}'";

		if (is_null($sortingFieldId))
		{
			$sortingFieldId = $this->sortingFieldId;
		}
		$sortingFieldId = intval($sortingFieldId);
		$aData[] = "sortingFieldId: '{$sortingFieldId}'";

		is_null($sortingDirection) && $sortingDirection = $this->sortingDirection;
		$sortingDirection = intval($sortingDirection);
		$aData[] = "sortingDirection: '{$sortingDirection}'";

		is_null($view) && $view = $this->view;
		$view = Core_Str::escapeJavascriptVariable(htmlspecialchars($view));
		$aData[] = "view: '{$view}'";

		$windowId = Core_Str::escapeJavascriptVariable(htmlspecialchars($this->getWindowId()));
		$aData[] = "windowId: '{$windowId}'";

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

	protected function _filterCallbackInput($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);
		$value = htmlspecialchars($value);
		?><input type="text" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" value="<?php echo $value?>" style="width: 100%" class="form-control input-sm" /><?php
	}

	protected function _filterCallbackCheckbox($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = intval($value);
		?><select name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" class="form-control">
			<option value="0" <?php echo $value == 0 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected_all'))?></option>
			<option value="1" <?php echo $value == 1 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected'))?></option>
			<option value="2" <?php echo $value == 2 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_not_selected'))?></option>
		</select><?php
	}

	protected function _filterCallbackDatetime($date_from, $date_to, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$date_from = htmlspecialchars($date_from);
		$date_to = htmlspecialchars($date_to);

		$divClass = is_null($tabName) ? 'col-xs-12' : 'col-xs-6 col-sm-4';

		$sCurrentLng = Core_I18n::instance()->getLng();

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
			$('#<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['dateTimePickerFormat']?>', showTodayButton: true, showClear: true});
			$('#<?php echo $tabName . $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['dateTimePickerFormat']?>', showTodayButton: true, showClear: true});
		})(jQuery);
		</script><?php
	}

	/**
	 * Date-filed (from-to)
	 */
	protected function _filterCallbackDate($date_from, $date_to, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$date_from = htmlspecialchars($date_from);
		$date_to = htmlspecialchars($date_to);

		$divClass = is_null($tabName) ? 'col-xs-12' : 'col-xs-6 col-sm-4 col-md-3';

		$sCurrentLng = Core_I18n::instance()->getLng();

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
			$('#<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['datePickerFormat']?>', showTodayButton: true, showClear: true});
			$('#<?php echo $tabName . $filterPrefix?>to_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['datePickerFormat']?>', showTodayButton: true, showClear: true});
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

		?><div class="row">
			<div class="date <?php echo $divClass?>">
				<input type="text" name="<?php echo $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" id="<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo htmlspecialchars($date_from)?>" class="form-control input-sm" />
			</div>
		</div>
		<script>
		(function($) {
			$('#<?php echo $tabName . $filterPrefix?>from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: '<?php echo Core::$mainConfig['datePickerFormat']?>'});
		})(jQuery);
		</script>
		<?php
	}

	protected function _filterCallbackSelect($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);

		$oSelect = Admin_Form_Entity::factory('Select');
		$oSelect
			->name($filterPrefix . $oAdmin_Form_Field->id)
			->id($tabName . $filterPrefix . $oAdmin_Form_Field->id)
			->class('no-padding-left no-padding-right')
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

	protected function _filterCallbackUser($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$iUserId = $value
			? intval($value)
			: 'HOST_CMS_ALL';

		$placeholder = Core::_('User.select_user');
		$language = Core_i18n::instance()->getLng();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);
		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		Core::factory('Core_Html_Entity_Select')
			->id($tabName . $filterPrefix . $oAdmin_Form_Field->id)
			->options($aSelectResponsibleUsers)
			->name($filterPrefix . $oAdmin_Form_Field->id)
			->value($iUserId)
			->execute();

		?><script>
		$('#<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectUser({
				language: '<?php echo $language?>',
				placeholder: '<?php echo $placeholder?>'
			}).val('<?php echo $iUserId?>').trigger('change.select2');

		//$(".select2-container").css('width', '100%');

		$('#<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>')
			.on('select2:unselect', function (){
				$(this)
					.next('.select2-container')
					.find('.select2-selection--single')
					.removeClass('user-container');
			});
		</script><?php
	}

	protected function _filterCallbackSiteuser($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		if (Core::moduleIsActive('siteuser'))
		{
			$siteuser_id = intval($value);

			$placeholder = Core::_('Siteuser.select_siteuser');
			$language = Core_i18n::instance()->getLng();

			$oSiteuser = Core_Entity::factory('Siteuser')->getById($siteuser_id);
			$sOptions = !is_null($oSiteuser)
				? '<option value=' . $oSiteuser->id . ' selected="selected">' . htmlspecialchars($oSiteuser->login) . ' [' . $oSiteuser->id . ']</option>'
				: '<option></option>';
			?>
			<select id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>">
				<?php echo $sOptions?>
			</select>
			<script>
				$('#<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectSiteuser({
					language: '<?php echo $language?>',
					placeholder: '<?php echo $placeholder?>'
				});
				$(".select2-container").css('width', '100%');
			</script>
			<?php
		}
		else
		{
			?>—<?php
		}
	}

	protected function _filterCallbackCounterparty($value, $oAdmin_Form_Field, $filterPrefix, $tabName)
	{
		$value = strval($value);
		if (Core::moduleIsActive('siteuser'))
		{
			$placeholder = Core::_('Siteuser.select_siteuser');
			$language = Core_i18n::instance()->getLng();

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

						$sOptions .= '<option value="' . $sOptionValue . '" class="siteuser-company" ' . ($value == $sOptionValue ? 'selected="selected"' : '') . '>' . htmlspecialchars($oSiteuserCompany->name) . '[' . htmlspecialchars($oSelectedSiteuser->login) . ']' . '%%%' . htmlspecialchars($oSiteuserCompany->getAvatar()) . '</option>';
					}

					$aSiteuserPersons = $oSelectedSiteuser->Siteuser_People->findAll();
					foreach ($aSiteuserPersons as $oSiteuserPerson)
					{
						$sOptionValue = 'person_' . $oSiteuserPerson->id;

						$sOptions .= '<option value="' . $sOptionValue . '" class="siteuser-person"' . ($value == $sOptionValue ? 'selected="selected"' : '') . '>' . htmlspecialchars($oSiteuserPerson->getFullName()) . '[' . htmlspecialchars($oSelectedSiteuser->login) . ']' . '%%%' . htmlspecialchars($oSiteuserPerson->getAvatar()) . '</option>';
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
			?>
			<select id="<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>" name="<?php echo $filterPrefix . $oAdmin_Form_Field->id?>">
				<?php echo $sOptions?>
			</select>
			<script>
				$('#<?php echo $tabName . $filterPrefix . $oAdmin_Form_Field->id?>').selectPersonCompany({
					url: '/admin/siteuser/index.php?loadSiteusers&types[]=person&types[]=company',
					language: '<?php echo $language?>',
					placeholder: '<?php echo $placeholder?>'
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
			$mValue = trim(Core_Array::get($this->request, "{$filterPrefix}{$oAdmin_Form_Field->id}"));
		}
		else
		{
			$aTabs = Core_Array::get($this->filterSettings, 'tabs', array());

			$bHide = isset($aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['show'])
				&& $aTabs[$tabName]['fields'][$oAdmin_Form_Field->name]['show'] == 0;

			$bMain = $tabName === 'main';

			$bCurrent = $this->filterId === $tabName || $this->filterId === '' && $bMain;

			// Значение вначале берется из POST, если его там нет, то из данных в JSON
			$mValue = !$bHide
				? (isset($_POST['topFilter_' . $oAdmin_Form_Field->id]) && $bCurrent
					? $_POST['topFilter_' . $oAdmin_Form_Field->id]
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

	public function isCallable($oEntity, $fieldName)
	{
		return method_exists($oEntity, $fieldName)
			|| method_exists($oEntity, 'isCallable') && $oEntity->isCallable($fieldName);
	}

	public function convertLike($str)
	{
		return str_replace(array('*', '?'), array('%', '_'), Core_DataBase::instance()->escapeLike(trim($str)));
	}

	/**
	 * Set dataset conditions
	 * @return self
	 */
	public function setDatasetConditions()
	{
		$aAdmin_Form_Fields = $this->_Admin_Form->Admin_Form_Fields->findAll();

		$oAdmin_Form_Field_Order = Core_Entity::factory('Admin_Form_Field', $this->sortingFieldId);

		foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
		{
			$oEntity = $oAdmin_Form_Dataset->getEntity();

			if ($oAdmin_Form_Field_Order->allow_sorting)
			{
				// Check field exists in the model
				$fieldName = $this->getFieldName($oAdmin_Form_Field_Order->name);
				if (isset($oEntity->$fieldName) || method_exists($oEntity, $fieldName)
					// Для сортировки должно существовать св-во модели
					// || property_exists($oEntity, $fieldName)
					|| $oAdmin_Form_Dataset->issetExternalField($fieldName)
					|| strpos($oAdmin_Form_Field_Order->name, '.') !== FALSE
				)
				{
					$oAdmin_Form_Dataset->addCondition(array(
							'orderBy' => array($oAdmin_Form_Field_Order->name, $this->sortingDirection
								? 'DESC'
								: 'ASC'
							)
						)
					);
				}
			}

			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				// Перекрытие параметров для данного поля
				$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;
				foreach ($this->_datasets as $datasetKey => $oTmpAdmin_Form_Dataset)
				{
					$oAdmin_Form_Field_Changed = $this->changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field_Changed);
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

									$mFilterValue = $this->convertLike($mFilterValue);

									$oAdmin_Form_Dataset->addCondition(
										array($sFilterType => array($oAdmin_Form_Field_Changed->name, 'LIKE', $mFilterValue))
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
		}

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
				$oAdmin_Form_Dataset = current($this->_datasets);

				!is_null($this->limit) && $oAdmin_Form_Dataset
					->limit($this->limit)
					->offset($offset);

				$oAdmin_Form_Dataset->load();

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
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}

		return $this;
	}

	/**
	 * Apply external changes for filter
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
					? (array) $oAdmin_Form_Field
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
}