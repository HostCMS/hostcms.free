<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Log system. Notify if status >= MAIL_EVENTS_STATUS. To disable notification call ->notify(FALSE)
 *
 * <code>
 * Core_Log::instance()->clear()
 * 	->status(Core_Log::$MESSAGE)
 * 	->write('text');
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
abstract class Core_Log
{
	/**
	 * Backend property
	 * @var int
	 */
	static public $MESSAGE = 0;

	/**
	 * Backend property
	 * @var int
	 */
	static public $SUCCESS = 1;

	/**
	 * Backend property
	 * @var int
	 */
	static public $NOTICE = 2;

	/**
	 * Backend property
	 * @var int
	 */
	static public $WARNING = 3;

	/**
	 * Backend property
	 * @var int
	 */
	static public $ERROR = 4;

	/**
	 * The singleton instance.
	 * @var array
	 */
	static private $_instance = array();

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$_instance[$name]))
		{
			$aConfig = Core::$config->get('core_log', array('default' => array('driver' => 'default')));

			if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
			{
				throw new Core_Exception('Core_Log "%name" configuration doesn\'t defined', array('%name' => $name));
			}

			// Base config of the $name + driver's default config
			$aConfigDriver = $aConfig[$name] + Core_Array::get($aConfig, $aConfig[$name]['driver'], array());

			$driver = isset($aConfigDriver['class'])
				? $aConfigDriver['class']
				: self::_getDriverName($aConfigDriver['driver']);

			self::$_instance[$name] = new $driver($aConfigDriver);
		}

		return self::$_instance[$name];
	}

	abstract public function write($message);

	/**
	 * Status
	 * @var int
	 */
	protected $_status = 0;

	/**
	 * Set status
	 * @param string $status status
	 * @return self
	 */
	public function status($status)
	{
		$this->_status = intval($status);
		return $this;
	}

	/**
	 * User login
	 * @var string
	 */
	protected $_login = NULL;

	/**
	 * Set login
	 * @param string $login login
	 * @return self
	 */
	public function login($login)
	{
		$this->_login = $login;
		return $this;
	}

	/**
	 * Name of the site
	 * @var string
	 */
	protected $_site = NULL;

	/**
	 * Set site name
	 * @param string $site name
	 * @return self
	 */
	public function site($site)
	{
		$this->_site = $site;
		return $this;
	}

	/**
	 * E-mail notify mode
	 * @var boolean
	 */
	protected $_notify = TRUE;

	/**
	 * Set notify mode
	 * @param int $notify mode
	 * @return self
	 */
	public function notify($notify)
	{
		$this->_notify = $notify;
		return $this;
	}
	
	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_status = 0;
		$this->_site = $this->_login = NULL;
		$this->_notify = TRUE;

		return $this;
	}
}