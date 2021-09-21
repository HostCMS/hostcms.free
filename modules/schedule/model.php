<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule_Model
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	public function actionNameBackend()
	{
		$return = htmlspecialchars($this->getActionName());

		$this->entity_id
			&& $return .= ' <span class="badge badge-square badge-info">' . htmlspecialchars($this->entity_id) . '</span>';

		return $return;
	}

	/**
	 * Get interval
	 * @return array
	 */
	public function getInterval()
	{
		$aReturn = array(
			'value' => $this->interval,
			'type' => 0
		);

		if ($this->interval == 0)
		{
			return $aReturn;
		}

		// Days
		if ($this->interval % 86400 == 0)
		{
			$aReturn['value'] = $this->interval / 86400;
			$aReturn['type'] = 3;
		}
		// Hours
		elseif ($this->interval % 3600 == 0)
		{
			$aReturn['value'] = $this->interval / 3600;
			$aReturn['type'] = 2;
		}
		// Minutes
		elseif ($this->interval % 60 == 0)
		{
			$aReturn['value'] = $this->interval / 60;
			$aReturn['type'] = 1;
		}
		// Seconds
		else
		{
			$aReturn['value'] = $this->interval;
			$aReturn['type'] = 0;
		}

		return $aReturn;
	}

	/**
	 * Convert interval by type
	 * @param int $type type of interval
	 * @return self
	 */
	public function convertInterval($type)
	{
		$this->interval < 0 && $this->interval = 0;

		switch ($type)
		{
			case 1: // Минуты
				$this->interval = $this->interval * 60;
			break;
			case 2: // Часы
				$this->interval = $this->interval * 60 * 60;
			break;
			case 3: // Дни
				$this->interval = $this->interval * 60 * 60 * 24;
			break;
		}

		return $this->save();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function intervalBackend()
	{
		$aInterval = $this->getInterval();

		switch ($aInterval['type'])
		{
			case 0: // Секунды
				$type = Core::_('Core.shortTitleSeconds');
			break;
			case 1: // Минуты
				$type = Core::_('Core.shortTitleMinutes');
			break;
			case 2: // Часы
				$type = Core::_('Core.shortTitleHours');
			break;
			case 3: // Дни
				$type = Core::_('Core.shortTitleDays');
			break;
		}

		return $this->interval
			? $aInterval['value'] . ' ' . $type
			: '';
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event schedule.onBeforeGetRelatedSite
	 * @hostcms-event schedule.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}