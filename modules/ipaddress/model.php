<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Ipaddress_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'ip';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'ipaddress_dir' => array(),
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
			$this->_preloadValues['deny_access'] = 1;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend property
	 */
	public function imgBackend()
	{
		return strpos($this->ip, '/') === FALSE
			? '<b>IP</b>'
			: '<i class="fa-solid fa-network-wired"></i>';
	}

	/**
	 * Backend property
	 */
	public function bannedBackend()
	{
		return '<span title="' . $this->banned . '">' . Core_Str::getTextCount($this->banned) . '</span>';
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		parent::markDeleted();

		$this->clearCache();

		return $this;
	}

	/**
	 * Change access mode
	 * @return self
	 */
	public function changeAccess()
	{
		$this->deny_access = 1 - $this->deny_access;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Change backend access mode
	 * @return self
	 */
	public function changeBackendAccess()
	{
		$this->deny_backend = 1 - $this->deny_backend;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Change statistic mode
	 * @return self
	 */
	public function changeStatistic()
	{
		$this->no_statistic = 1 - $this->no_statistic;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Check if there another ip with this address is
	 * @return self
	 */
	protected function _checkDuplicate()
	{
		$oIpaddressDublicate = Core_Entity::factory('Ipaddress')->getByIp($this->ip, FALSE);

		if (!is_null($oIpaddressDublicate) && $oIpaddressDublicate->id != $this->id)
		{
			$this->id = $oIpaddressDublicate->id;
		}

		return $this;
	}

	/**
	 * Update object data into database
	 * @return Core_ORM
	 */
	public function update()
	{
		$this->_checkDuplicate();
		return parent::update();
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		$this->_checkDuplicate();
		return parent::save();
	}

	/**
	 * Get entity description
	 * @return string
	 */
	public function getTrashDescription()
	{
		return htmlspecialchars(
			Core_Str::cut($this->comment, 255)
		);
	}

	/**
	 * Deny access
	 * @return self
	 */
	public function denyAllAccess()
	{
		$this->deny_access = 1;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Allow access
	 * @return self
	 */
	public function allowAllAccess()
	{
		$this->deny_access = 0;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Deny access
	 * @return self
	 */
	public function denyAllBackendAccess()
	{
		$this->deny_backend = 1;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Allow access
	 * @return self
	 */
	public function allowAllBackendAccess()
	{
		$this->deny_backend = 0;
		$this->save();

		$this->clearCache();

		return $this;
	}

	/**
	 * Clear Cache
	 */
	public function clearCache()
	{
		Ipaddress_Controller::instance()->clearCache();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->getShortName());
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function commentBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<span class="editable" data-editable-type="textarea" id="apply_check_1_' . $this->id . '_fv_90">' . nl2br(htmlspecialchars((string) $this->comment)) . '</span>';
	}

	/**
	 * Get short name of IPs list
	 * @return string
	 */
	public function getShortName()
	{
		$aIp = explode(',', $this->ip);

		$iCount = count($aIp);
		if ($iCount > 8)
		{
			return implode(',', array_slice($aIp, 0, 7)) . ' … +' . ($iCount - 7) . ' IPs';
		}

		return $this->ip;
	}

	/**
	 * Move item to another group
	 * @param int $ipaddress_dir_id target group id
	 * @return Core_Entity
	 * @hostcms-event ipaddress.onBeforeMove
	 * @hostcms-event ipaddress.onAfterMove
	 */
	public function move($ipaddress_dir_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($ipaddress_dir_id));

		$this->ipaddress_dir_id = $ipaddress_dir_id;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Merge ip-addresses
	 * @param Ipaddress_Model $oObject
	 * @return self
	 */
	public function merge(Ipaddress_Model $oObject)
	{
		$aIps = array_merge(
			array_map('trim', explode(',', $this->ip)),
			array_map('trim', explode(',', $oObject->ip))
		);

		$aIps = array_unique($aIps);

		$this->ip = implode(', ', $aIps);
		$this->banned += $oObject->banned;
		$this->save();

		$oObject->markDeleted();

		Ipaddress_Controller::instance()->clearCache();

		return $this;
	}
}