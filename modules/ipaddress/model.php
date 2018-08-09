<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Model
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['deny_access'] = 1;
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Change access mode
	 * @return self
	 */
	public function changeAccess()
	{
		$this->deny_access = 1 - $this->deny_access;
		$this->save();
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
		return $this;
	}

	/**
	 * Check if there another ip with this address is
	 * @return self
	 */
	protected function _checkDuplicate()
	{
		$oIpaddressDublicate = Core_Entity::factory('Ipaddress')->getByIp($this->ip);

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
}