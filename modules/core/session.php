<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sessions
 *
 * Start Session
 * <code>
 * Core_Session::start();
 * </code>
 *
 * Close Session
 * <code>
 * Core_Session::close();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Session
{
	/**
	 * Session has been started
	 * @var boolean
	 */
	static protected $_started = FALSE;

	/**
	 * Checks if the session was started
	 * @return boolean
	 */
	static public function isStarted()
	{
		return self::$_started;
	}

	/**
	 * Start session
	 * @return boolean
	 */
	static public function start()
	{
		if (!self::isStarted())
		{
			// Destroy existing session started by session.auto_start
			if (is_null(self::$_handler) && session_id())
			{
				session_unset();
				session_destroy();
			}

			self::_setSessionHandler();

			//$expires = self::getMaxLifeTime();
			$expires = 31536000;

			if (!defined('DENY_INI_SET') || !DENY_INI_SET)
			{
				ini_set('session.cookie_lifetime', $expires);
				//ini_set('session.gc_maxlifetime', $expires);
			}

			list($domain) = explode(':', strtolower(Core_Array::get($_SERVER, 'HTTP_HOST')));

			if (!empty($domain) && !headers_sent())
			{
				// Обрезаем www у домена
				strpos($domain, 'www.') === 0 && $domain = substr($domain, 4);

				// Явное указание domain возможно только для домена второго и более уровня
				$domain = strpos($domain, '.') !== FALSE && !Core_Valid::ip($domain)
					? '.' . $domain
					: '';

				session_set_cookie_params($expires, '/', $domain, FALSE, TRUE);
			}

			// При повторном запуске $_SESSION уже будет
			//if (Core_Array::getRequest(self::getName())/* && !isset($_SESSION)*/)
			//{
				@session_start();
				self::$_started = TRUE;
				self::$_hasSessionId = TRUE;
			//}

			//self::_setCookie();
		}

		return TRUE;
	}

	/**
	 * Get session name
	 * @return string
	 */
	static public function getName()
	{
		return session_name();
	}

	/**
	 * Checks if the session enabled and exists
	 * @return boolean
	 */
	static public function isAcive()
	{
		return function_exists('session_status')
			? session_status() === PHP_SESSION_ACTIVE
			: session_id() !== '';
	}

	/**
     * Is the current request has sent the session ID.
	 * @return boolean|NULL
	 */
	static protected $_hasSessionId = NULL;

	/**
     * The is the current request has sent the session ID.
	 * @return boolean
	 */
	static public function hasSessionId()
	{
		if (is_null(self::$_hasSessionId))
		{
			$sessionName = self::getName();

			if (!empty($_COOKIE[$sessionName]) && ini_get('session.use_cookies'))
			{
				self::$_hasSessionId = TRUE;
			}
			elseif (!ini_get('session.use_only_cookies') && ini_get('session.use_trans_sid') && isset($_REQUEST[$sessionName]))
			{
				self::$_hasSessionId = !empty($_REQUEST[$sessionName]);
			}
			else
			{
				self::$_hasSessionId = FALSE;
			}
		}

		return self::$_hasSessionId;
	}

	/**
	 * Regenerate session ID
	 * @param bool $delete_old_session Whether to delete the old associated session file or not, default FALSE
	 */
	static public function regenerateId($delete_old_session = FALSE)
	{
		if (self::isAcive())
		{
			!headers_sent()
				? session_regenerate_id($delete_old_session)
				: @session_regenerate_id($delete_old_session);
		}
	}

	static protected $_handler = NULL;

	/**
	 * Registers session handler
	 */
	static protected function _setSessionHandler()
	{
		if (!isset(Core::$mainConfig['session']['driver']))
		{
			throw new Core_Exception('Wrong Session config, needs driver');
		}

		$sessionClass = isset(Core::$mainConfig['session']['class'])
			? Core::$mainConfig['session']['class']
			: self::_getDriverName(Core::$mainConfig['session']['driver']);

		//if (is_null(self::$_handler))
		//{
			$oCore_Session = self::$_handler = new $sessionClass();

			session_set_save_handler(
				array($oCore_Session, 'sessionOpen'),
				array($oCore_Session, 'sessionClose'),
				array($oCore_Session, 'sessionRead'),
				array($oCore_Session, 'sessionWrite'),
				array($oCore_Session, 'sessionDestroyer'),
				array($oCore_Session, 'sessionGc')
			);
		//}
	}

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
	 * Set cookie with expiration date
	 */
	/*static protected function _setCookie()
	{
		$domain = strtolower(Core_Array::get($_SERVER, 'HTTP_HOST'));
		if (!empty($domain) && !headers_sent())
		{
			// Обрезаем www у домена
			strpos($domain, 'www.') === 0 && $domain = substr($domain, 4);

			// Явное указание domain возможно только для домена второго и более уровня
			// http://wp.netscape.com/newsref/std/cookie_spec.html
			// http://web-notes.ru/2008/07/cookies_within_local_domains/
			$domain = strpos($domain, '.') !== FALSE && !Core_Valid::ip($domain)
				? '.' . $domain
				: '';

			$expires = self::getMaxLifeTime();

			setcookie(self::getName(), session_id(), time() + $expires, '/', $domain, FALSE, TRUE);

			// Заменяем заголовок ($replace = TRUE)
			//Core::setcookie(self::getName(), session_id(), time() + $expires, '/', $domain, FALSE, TRUE, $replace = TRUE);
			//session_set_cookie_params(time() + $expires, '/', $domain);
			//session_id(session_id());
		}
	}*/

	/**
	 * Close session
	 * @return boolean
	 */
	static public function close()
	{
		//if (self::$_started)
		//{
			self::$_started = FALSE;

			if (self::isAcive())
			{
				session_write_close();
			}

			// cause session_destroy(): Trying to destroy uninitialized session
			//self::$_handler = NULL;
		//}

		return TRUE;
	}

	/**
	 * Session maxlifetime
	 * @var int
	 */
	static protected $_maxlifetime = NULL;

	/**
	 * Set session maxlifetime
	 * @param int $maxlifetime
	 * @param boolean $overwrite Overwrite maximum lifetime, default FALSE
	 * @return TRUE
	 */
	static public function setMaxLifeTime($maxlifetime, $overwrite = FALSE)
	{
		self::$_maxlifetime = $maxlifetime;

		if (!self::isStarted() && (!defined('DENY_INI_SET') || !DENY_INI_SET))
		{
			ini_set('session.gc_maxlifetime', $maxlifetime);
		}

		// Для уже запущенной сесии обновляем время жизни
		if (self::isStarted())
		{
			self::$_handler->sessionMaxlifetime($maxlifetime, $overwrite = FALSE);
		}

		return TRUE;
	}

	/**
	 * Get session maxlifetime
	 * @return int
	 */
	static public function getMaxLifeTime()
	{
		return !is_null(self::$_maxlifetime)
			? intval(self::$_maxlifetime)
			: intval(ini_get('session.gc_maxlifetime'));
	}


	/**
	 * The open callback works like a constructor in classes and is executed when the session is being opened.
	 * @param string $save_path save path
	 * @param string $session_name session name
	 * @return boolean
	 */
	abstract public function sessionOpen($save_path, $session_name);

	/**
	 * The close callback works like a destructor in classes and is executed after the session write callback has been called.
	 * @return boolean
	 */
	abstract public function sessionClose();

	/**
	 * The read callback must always return a session encoded (serialized) string, or an empty string if there is no data to read.
	 * @param string $id session ID
	 * @return string
	 */
	abstract public function sessionRead($id);

	/**
	 * The write callback is called when the session needs to be saved and closed.
	 * @param string $id session ID
	 * @param string $value data
	 * @return boolean
	 */
	abstract public function sessionWrite($id, $value);

	/**
	 * This callback is executed when a session is destroyed with session_destroy()
	 * @param string $id session ID
	 * @return boolean
	 */
	abstract public function sessionDestroyer($id);

	/**
	 * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
	 * @param string $maxlifetime max life time
	 * @return boolean
	 */
	abstract public function sessionGc($maxlifetime);

	/**
	 * This callback is executed when a session sets maxlifetime
	 * @param int $maxlifetime
	 * @param bool $overwrite overwrite previous maxlifetime
	 * @return boolean
	 */
	abstract public function sessionMaxlifetime($maxlifetime, $overwrite = FALSE);
}