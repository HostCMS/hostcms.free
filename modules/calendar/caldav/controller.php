<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract Calendar Caldav
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Calendar_Caldav_Controller extends Core_Servant_Properties
{
	protected $_errno = NULL;
	protected $_error = NULL;
	protected $_client = NULL;
	protected $_calendar_url = NULL;
	protected $_url = NULL;
	protected $_username = NULL;
	protected $_password = NULL;
	protected $_host = NULL;
	protected $_data = NULL;
	protected $_list_events = array();

	/**
	 * The singleton instances.
	 * @var array
	 */
	static public $instance = array();

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return 'Calendar_Caldav_' . ucfirst($driver) . '_Controller';
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name)
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$driver = self::_getDriverName($name);

			if (class_exists($driver))
			{
				self::$instance[$name] = new $driver();
			}
			else
			{
				throw new Core_Exception(
					sprintf('Calendar CalDAV driver "%s" does not exist!', htmlspecialchars($name)), array(), 0, FALSE
				);
			}
		}

		return self::$instance[$name];
	}

	/**
	 * Set calendar url
	 */
	public function setUrl($sUrl)
	{
		$this->_url = strval($sUrl);

		preg_match( '#^((https?)://([a-z0-9.-]+)(:([0-9]+))?)(/.*)$#', $this->_url, $matches) && $this->_host = $matches[1];

		return $this;
	}

	/**
	 * Set calendar username
	 */
	public function setUsername($sUsername)
	{
		$this->_username = strval($sUsername);

		return $this;
	}

	/**
	 * Set calendar password
	 */
	public function setPassword($sPassword)
	{
		$this->_password = strval($sPassword);

		return $this;
	}

	/**
	 * Set calendar data
	 */
	public function setData($sData)
	{
		$this->_data = strval($sData);

		return $this;
	}
	
	/**
	 * Get calendar data
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Returns the numerical value of the error message from previous operation
	 * Default NULL
	 * @return mixed
	 */
	public function getErrno()
	{
		return $this->_errno;
	}

	/**
	 *Returns the text of the error message from previous operation
	 * Default NULL
	 * @return mixed
	 */
	public function getError()
	{
		return $this->_error;
	}

	/*
	 * Get current calendar url
	 * @return string
	 */
	public function getCalendar()
	{
		return $this->_calendar_url;
	}

	/*
	 * Get all events
	 * @return array
	 */
	public function getListEvents()
	{
		return $this->_list_events;
	}

	/**
	 * XML to array
	 * @param SimpleXMLElement $xmlObject XML
	 * @return array
	 */
	public function xml2array($xmlObject)
	{
		$aReturn = array();

		foreach ((array)$xmlObject as $index => $node)
		{
			$aReturn[$index] = (is_object($node))
				? $this->xml2array($node)
				: $node;
		}

		return $aReturn;
	}

	abstract public function connect();

	abstract public function findCalendars();

	abstract public function setCalendar(array $aCalendar);

	abstract public function save($sCalendar);

	abstract public function delete($sUrl);
	
	abstract public function applyObjectProperty($oCalendar_Caldav_User_Controller_Edit);
	
	abstract public function showSecondWindow($oCalendar_Caldav_User_Controller_Edit);
}