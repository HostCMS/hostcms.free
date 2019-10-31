<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule_Model
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'schedule';

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'module' => array(),
		'user' => array()
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
			$this->_preloadValues['start_datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function moduleName()
	{
		$oModule = Core_Entity::factory('Module')->find($this->module_id);

		if (!is_null($oModule->id))
		{
			return htmlspecialchars($oModule->name);
		}
	}

	/**
	 * Get action name
	 * @return string|NULL
	 */
	public function getActionName()
	{
		$oModule = Core_Entity::factory('Module')->find($this->module_id);

		if (!is_null($oModule->id))
		{
			$oSchedule_Controller = new Schedule_Controller();
			$aModuleActions = $oSchedule_Controller->getModuleActions($oModule->id);

			return isset($aModuleActions[$this->action])
				? $aModuleActions[$this->action]
				: NULL;
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function actionName()
	{
		return htmlspecialchars($this->getActionName());
	}
}