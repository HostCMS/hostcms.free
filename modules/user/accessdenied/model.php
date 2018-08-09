<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Accessdenied_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Accessdenied_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	protected $_deltaTime = NULL;

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		$this->_deltaTime = 60*60*24;
	}

	/**
	 * Get last by IP
	 * @param string $ip IP
	 * @return User_Accessdenied_Model|NULL
	 */
	public function getLastByIp($ip)
	{
		$date = Core_Date::timestamp2sql(time() - $this->_deltaTime);

		$this->queryBuilder()
			->clear()
			->where('datetime', '>=', $date)
			->where('ip', '=', $ip)
			->orderBy('datetime', 'DESC')
			->limit(1);

		$aUser_Accessdenied = $this->findAll(FALSE);

		return isset($aUser_Accessdenied[0])
			? $aUser_Accessdenied[0]
			: NULL;
	}

	/**
	 * Get by IP and datetime
	 * @param string $ip IP
	 * @return int
	 */
	public function getCountByIp($ip)
	{
		$date = Core_Date::timestamp2sql(time() - $this->_deltaTime);

		$this->queryBuilder()
			->clear()
			->where('datetime', '>=', $date)
			->where('ip', '=', $ip);

		return $this->getCount(FALSE);
	}
}