<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Redis Sessions using phpredis
 * https://github.com/phpredis/phpredis
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Session_Phpredis extends Core_Session
{
	/**
	 * Redis instance
	 * @var Redis
	 */
	protected $_redis = NULL;

	/**
	 * Session has been read
	 * @var boolean
	 */
	protected $_read = FALSE;

	/**
	 * Lock prefix
	 * @var string
	 */
	protected $_prefix = NULL;

	/**
	 * Next step delay (microseconds)
	 * Default 0,5 sec - 500000 microseconds
	 * @var int
	 */
	protected $_nextStepDelay = 500000;

	/**
	 * Lock timeout
	 * @var int
	 */
	protected $_lockTimeout = 10;

	/**
	 * Lock Token
	 */
	protected $_lockToken = NULL;

	/**
	 * Lock Key
	 */
	protected $_lockKey = NULL;

	/**
	 * Pack format
	 * @var string
	 */
	protected $_format = 'i*';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_redis = new Redis();

		$aConfig = Core::$mainConfig['session'] + array(
			'server' => '127.0.0.1',
			'port' => 6379,
			'auth' => NULL
		);

		if (!$this->_redis->connect($aConfig['server'], $aConfig['port']))
		{
			$this->_error('Redis connection error. Check \'session\' section, see modules/core/config/config.php');
		}

		if (!is_null($aConfig['auth']) && !$this->_redis->auth($aConfig['auth']))
		{
			$this->_error('Redis connection authenticate error. Check \'session\' section, see modules/core/config/config.php');
		}

		if (is_null($this->_prefix))
		{
			$this->_prefix = Core::crc32(CMS_FOLDER);
		}

		$this->_ttl = ini_get('session.gc_maxlifetime');
	}

	/**
	 * The open callback works like a constructor in classes and is executed when the session is being opened.
	 * @param string $save_path save path
	 * @param string $session_name session name
	 * @return boolean
	 */
	public function sessionOpen($save_path, $session_name)
	{
		return TRUE;
	}

	/**
	 * The close callback works like a destructor in classes and is executed after the session write callback has been called.
	 * @return boolean
	 */
	public function sessionClose()
	{
		return TRUE;
	}

	protected function _getKey($id)
	{
		return $this->_prefix . '#' . $id;
	}

	/**
	 * The read callback must always return a session encoded (serialized) string, or an empty string if there is no data to read.
	 * @param string $id session ID
	 * @return string
	 */
	public function sessionRead($id)
	{
		$key = $this->_getKey($id);

		if ($this->_lock($id))
		{
			$value = $this->_redis->get($key);

			$this->_read = TRUE;

			if ($value !== FALSE)
			{
				$aUnpackedHash = unpack($this->_format, substr($value, 0, 4));

				if (isset($aUnpackedHash[1]))
				{
					$this->_ttl = $aUnpackedHash[1];

					$this->_redis->setTimeout($key, $this->_ttl);
				}

				return substr($value, 4);
			}
		}

		return '';
	}

	/**
	 * The write callback is called when the session needs to be saved and closed.
	 * @param string $id session ID
	 * @param string $value data
	 * @return boolean
	 */
	public function sessionWrite($id, $value)
	{
		$key = $this->_getKey($id);

		if ($this->_read/* && $this->_lock($id)*/)
		{
			$this->_redis->set($key, pack($this->_format, $this->_ttl) . $value, $this->_ttl);

			$this->_unlock($id);

			$this->_read = FALSE;
		}

		return TRUE;
	}

	/**
	 * This callback is executed when a session is destroyed with session_destroy()
	 * @param string $id session ID
	 * @return boolean
	 */
	public function sessionDestroyer($id)
	{
		$key = $this->_getKey($id);

		if ($this->_lock($id))
		{
			$this->_redis->del($key);

			// для предотвращения автоматической повторной регистрации сеанса
			$_SESSION = array();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * This callback is executed when a session sets maxlifetime
	 * @param int $maxlifetime
	 * @param bool $overwrite overwrite previous maxlifetime
	 * @return boolean
	 */
	public function sessionMaxlifetime($maxlifetime, $overwrite = FALSE)
	{
		if ($maxlifetime > $this->_ttl || $overwrite)
		{
			$key = $this->_getKey(session_id());

			$this->_ttl = $maxlifetime;

			$this->_redis->setTimeout($key, $this->_ttl);
		}

		// Set cookie with expiration date
		//self::_setCookie();

		return TRUE;
	}

	/**
	 * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
	 * @param string $maxlifetime max life time
	 * @return boolean
	 */
	public function sessionGc($maxlifetime)
	{
		// Nothing to do
		return TRUE;
	}

	/**
	 * Show error
	 * @param string $content
	 */
	protected function _error($content)
	{
		if (Core_Array::getRequest('_', FALSE))
		{
			Core::showJson(array('error' => Core_Message::get($content, 'error'), 'form_html' => NULL));
		}
		else
		{
			throw new Core_Exception($content);
		}
	}

	/**
	 * Lock session
	 * @param int $id session ID
	 * @return boolean
	 */
	protected function _lock($id)
	{
		$iStartTime = time();

		$this->_lockToken = uniqid();
		$this->_lockKey = $this->_getKey($id) . '.lock';

		while (!connection_aborted())
		{
			if ($this->_redis->set($this->_lockKey, $this->_lockToken, array('NX')))
			{
				return TRUE;
			}

			$iTime = time() - $iStartTime;

			if ($iTime > $this->_lockTimeout)
			{
				$this->_error('HostCMS session lock error: Timeout. Please wait!');
			}

			usleep($this->_nextStepDelay);
		}

		return FALSE;
	}

	/**
	 * Unlock session
	 * @param int $id session ID
	 * @return boolean
	 */
	protected function _unlock($id)
	{
		// Удаляем блокировку, если в ней наш токен
		$script = 'if redis.call("GET", KEYS[1]) == ARGV[1] then
			return redis.call("DEL", KEYS[1])
		else
			return 0
		end';

		$this->_redis->eval($script, array($this->_lockKey, $this->_redis->_serialize($this->_lockToken)), 1);

		$this->_lockKey = NULL;

		return TRUE;
	}
}