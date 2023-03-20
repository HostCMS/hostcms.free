<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module_Model
 *
 * @package HostCMS
 * @subpackage Module
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Module_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'company_department_module' => array(),
		'notification' => array(),
		'notification_subscriber' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	protected $_version = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	protected $_date = NULL;

	/**
	 * Backend property
	 * @var Core_Module
	 */
	public $Core_Module = NULL;

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'modules.sorting' => 'ASC'
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}

		//$this->_getModuleInformation();
	}

	/**
	 * Get module name
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->_moduleName;
	}

	/**
	 * __isset
	 * @param string $property
	 * @return boolean
	 */
	public function __isset($property)
	{
		if ($property == 'version' || $property == 'date')
		{
			return TRUE;
		}

		return parent::__isset($property);
	}

	/**
	 * __get
	 * @param string $property
	 * @return string
	 */
	public function __get($property)
	{
		if ($property == 'version' || $property == 'date')
		{
			$this->_getModuleInformation();

			// protected property
			$fieldName = '_' . $property;

			return $this->$fieldName;
		}

		return parent::__get($property);
	}

	/**
	 * Load Core_Module and call __construct
	 */
	public function loadModule()
	{
		if (is_null($this->Core_Module))
		{
			$this->Core_Module = Core_Module::factory($this->path);
		}

		return $this;
	}

	/**
	 * Get information about module
	 */
	protected function _getModuleInformation()
	{
		if ($this->active && is_null($this->_version) && !is_null($this->path))
		{
			$this->loadModule();

			if ($this->Core_Module)
			{
				$this->_version = $this->Core_Module->version;
				$this->_date = $this->Core_Module->date;
			}
		}

		return $this;
	}

	/**
	 * Find object in database and load one
	 * @param mixed $primaryKey default NULL
	 * @param bool $bCache use cache
	 * @return Core_ORM
	 */
	public function find($primaryKey = NULL, $bCache = TRUE)
	{
		$return = parent::find($primaryKey, $bCache);
		//$this->_getModuleInformation();
		return $return;
	}

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0
	);

	/**
	 * Get path to config file
	 * @return string
	 */
	public function getConfigFilePath()
	{
		return Core_Config::instance()->getPath($this->path . '_config');
	}

	/**
	 * Save module config file
	 * @param string $content file content
	 */
	public function saveConfigFile($content)
	{
		$this->save();
		$configFilePath = $this->getConfigFilePath();

		$content = trim($content);
		if (strlen($content))
		{
			$dir = dirname($configFilePath);
			if (!Core_File::isDir($dir))
			{
				Core_File::mkdir($dir, CHMOD, $recursive = TRUE);
			}


			Core_File::write($configFilePath, $content);
		}
		elseif (Core_File::isFile($configFilePath))
		{
			Core_File::delete($configFilePath);
		}
	}

	/**
	 * Load module config file
	 * @return string|NULL
	 */
	public function loadConfigFile()
	{
		$path = $this->getConfigFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Change module status
	 * @return Module_Model
	 * @hostcms-event module.onBeforeChangeActive
	 * @hostcms-event module.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		$this->setupModule();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Change indexing status
	 * @return Module_Model
	 */
	public function changeIndexing()
	{
		$this->indexing = 1 - $this->indexing;
		return $this->save();
	}

	/**
	 * Call install() or uninstall() that depends on active
	 */
	public function setupModule()
	{
		$this->active
			? $this->install()
			: $this->uninstall();

		return $this;
	}

	/**
	 * Install module
	 * @return self
	 */
	public function install()
	{
		$path = $this->path . '_Module';
		if (class_exists($path))
		{
			$objectModule = new $path();
			method_exists($objectModule, 'install') && $objectModule->install();
		}

		return $this;
	}

	/**
	 * Uninstall module
	 * @return self
	 */
	public function uninstall()
	{
		$path = $this->path . '_Module';
		if (class_exists($path))
		{
			$objectModule = new $path();
			method_exists($objectModule, 'uninstall') && $objectModule->uninstall();
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event module.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('company'))
		{
			$this->Company_Department_Modules->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('notification'))
		{
			$this->Notifications->deleteAll(FALSE);
			$this->Notification_Subscribers->deleteAll(FALSE);
		}

		$this->uninstall();

		return parent::delete($primaryKey);
	}
}