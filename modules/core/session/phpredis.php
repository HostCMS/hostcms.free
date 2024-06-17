<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Redis Sessions using phpredis
 * https://github.com/phpredis/phpredis
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Session_Phpredis extends Core_Session
{
	/**
	 * Redis instance
	 * @var Redis
	 */
	static protected $_redis = NULL;

	/**
	 * Redis instance
	 * @var Redis
	 */
	static protected $_config = NULL;

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
	 * TTL
	 * @var int
	 */
	protected $_ttl = 0;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		self::_connect();

		is_null($this->_prefix)
			&& $this->_prefix = Core::crc32(CMS_FOLDER);

		// Should be INT
		$this->_ttl = intval(ini_get('session.gc_maxlifetime'));
	}

	/**
	 * Connect to the Redis
	 */
	static protected function _connect()
	{
		if (is_null(self::$_redis))
		{
			self::$_redis = new Redis();

			self::$_config = Core::$mainConfig['session'] + array(
				'server' => '127.0.0.1',
				'port' => 6379,
				'auth' => NULL,
				'database' => NULL
			);

			if (!self::$_redis->connect(self::$_config['server'], self::$_config['port']))
			{
				self::_error('Redis connection error. Check \'session\' section, see modules/core/config/config.php');
				self::$_redis = NULL;

				return FALSE;
			}

			if (!is_null(self::$_config['auth']) && !self::$_redis->auth(self::$_config['auth']))
			{
				self::_error('Redis connection authenticate error. Check \'session\' section, see modules/core/config/config.php');
				self::$_redis = NULL;

				return FALSE;
			}

			if (!is_null(self::$_config['database']) && !self::$_redis->select(self::$_config['database']))
			{
				self::_error('Redis changing the selected database error. Check \'session\' section, see modules/core/config/config.php');
				self::$_redis = NULL;

				return FALSE;
			}
		}

		return TRUE;
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
			$value = self::$_redis->get($key);

			$this->_read = TRUE;

			self::$_started = TRUE;

			if ($value !== FALSE)
			{
				$aUnpackedHash = unpack($this->_format, substr($value, 0, 4));

				if (isset($aUnpackedHash[1]))
				{
					// Should be INT
					$this->_ttl = intval($aUnpackedHash[1]);

					self::$_redis->expire($key, $this->_ttl);
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
		if ($this->_read/* && $this->_lock($id)*/)
		{
			$key = $this->_getKey($id);

			self::$_redis->set($key, pack($this->_format, $this->_ttl) . $value, $this->_ttl);

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
		if ($this->_read || $this->_lock($id))
		{
			$key = $this->_getKey($id);

			self::$_redis->del($key);

			$this->_unlock($id);

			// для предотвращения автоматической повторной регистрации сеанса
			// при регенерации идентификаора очищать не следует
			//$_SESSION = array();

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

			// Should be INT
			$this->_ttl = intval($maxlifetime);

			self::$_redis->expire($key, $this->_ttl);
		}

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
	 * This callback is executed when a new session ID is required.
	 * @return string
	 */
	public function sessionCreateSid()
	{
		return session_create_id();
	}

	/**
	 * This callback is executed when a session is to be started, a session ID is supplied and session.use_strict_mode is enabled
	 * @param string $id Session ID
	 * @return bool
	 */
	public function sessionValidateSid($id)
	{
		$key = $this->_getKey($id);
		$value = self::$_redis->get($key);

		return $value === FALSE;
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

		while (!is_null(self::$_redis) && !connection_aborted())
		{
			// Redis 2.6.12+
			if (self::$_redis->set($this->_lockKey, $this->_lockToken, array('NX')))
			//if (self::$_redis->setNx($this->_lockKey, $this->_lockToken))
			{
				return TRUE;
			}

			$iTime = time() - $iStartTime;

			if ($iTime > $this->_lockTimeout)
			{
				self::_error('HostCMS session lock error: Timeout. Please wait! Refreshing page ... <script>setTimeout(function() {window.location.reload(true);}, 1000);</script>');
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

		self::$_redis->eval($script, array($this->_lockKey, self::$_redis->_serialize($this->_lockToken)), 1);

		$this->_lockKey = NULL;

		return TRUE;
	}

	/**
	 * Delete all sessions from Redis
	 */
	static public function flushAll()
	{
		self::_connect();

		!is_null(self::$_config['database'])
			? $redis->flushDb()
			: $redis->flushAll();
	}
}