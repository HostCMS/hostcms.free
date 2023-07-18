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
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Debug
	 * @var boolean
	 */
	static protected $_debug = FALSE;

	/**
	 * Set debug
	 * @param boolean $debug
	 */
	static public function debug($debug = TRUE)
	{
		self::$_debug = $debug;
	}

	/**
	 * Log
	 * @param string $actionName
	 */
	static protected function _log($actionName)
	{
		$aDebugTrace = Core::debugBacktrace();

		$message = "Core_Session::{$actionName}()";

		foreach ($aDebugTrace as $aTrace)
		{
			$message .= "\n{$aTrace['file']}:{$aTrace['line']} {$aTrace['function']}";
		}

		Core_Log::instance()->clear()->status(0)->write($message);
	}

	/**
	 * Cookie Lifetime, default 31536000
	 * @var int
	 */
	static protected $_cookieLifetime = 31536000;

	/**
	 * Set Cookie Lifetime
	 * @param int $lifetime
	 */
	static public function cookieLifetime($lifetime)
	{
		self::$_cookieLifetime = $lifetime;
	}

	/**
	 * Domain, auto-detected by default
	 * @var string
	 */
	static protected $_domain = NULL;

	/**
	 * Set Domain
	 * @param string $domain
	 */
	static public function domain($domain)
	{
		self::$_domain = $domain;
	}

	/**
	 * Get domain
	 * @return string
	 */
	static public function getDomain()
	{
		is_null(self::$_domain)
			? list($domain) = explode(':', strtolower(Core_Array::get($_SERVER, 'HTTP_HOST', '', 'str')))
			: $domain = self::$_domain;

		return $domain;
	}

	/**
	 * Correct domain
	 * @param str domain
	 * @return string
	 */
	static public function correctDomain($domain)
	{
		// Cut 'www.'
		strpos($domain, 'www.') === 0 && $domain = substr($domain, 4);

		// Явное указание domain возможно только для домена второго и более уровня
		return strpos($domain, '.') !== FALSE && !Core_Valid::ip($domain)
			? '.' . $domain
			: '';
	}

	/**
	 * Get Session Config
	 * @rerurn array
	 */
	static public function getConfig()
	{
		return Core::$mainConfig['session'] + array(
			'driver' => 'database',
			'class' => 'Core_Session_Database',
			'subdomain' => TRUE,
			'samesite' => 'Lax',
			'secure' => FALSE,
			'httponly' => TRUE,
		);
	}

	/**
	 * Start session
	 * @return boolean
	 */
	static public function start()
	{
		if (!self::isStarted())
		{
			self::$_debug && self::_log('start');

			// Destroy existing session started by session.auto_start
			if (is_null(self::$_handler) && session_id())
			{
				session_unset();
				session_destroy();
			}

			self::_setSessionHandler();

			if (!defined('DENY_INI_SET') || !DENY_INI_SET)
			{
				ini_set('session.cookie_lifetime', self::$_cookieLifetime);
				//ini_set('session.gc_maxlifetime', self::$_cookieLifetime);
			}

			$domain = self::getDomain();

			if (!empty($domain) && !headers_sent())
			{
				$aConfig = self::getConfig();

				$domain = self::correctDomain($domain);

				if (!$aConfig['subdomain'])
				{
					// Delete old subdomain cookie
					$domain != '' && Core_Cookie::set(session_name(), '', array(
						'expires' => 0,
						'path' => '/',
						'domain' => $domain,
						'secure' => $aConfig['secure'],
						'httponly' => $aConfig['httponly'])
					);

					$domain = NULL;
				}

				// SameSite=None; Secure
				$aConfig['samesite'] == 'None' && $aConfig['secure'] = TRUE;

				// SameSite attribute of Lax or Strict is OK
				$bSendSameSite = Core_Cookie::sendSameSite($aConfig['samesite']);

				// Браузер совместим
				if ($bSendSameSite)
				{
					if (PHP_VERSION_ID >= 70300)
					{
						session_set_cookie_params(array(
							'lifetime' => self::$_cookieLifetime,
							'path' => '/',
							'domain' => $domain,
							'secure' => $aConfig['secure'],
							'httponly' => $aConfig['httponly'],
							'samesite' => $aConfig['samesite']
						));
					}
					else
					{
						session_set_cookie_params(self::$_cookieLifetime, '/; SameSite=' . $aConfig['samesite'], $domain, $aConfig['secure'], $aConfig['httponly']);
					}
				}
				else
				{
					session_set_cookie_params(self::$_cookieLifetime, '/', $domain, $aConfig['secure'], $aConfig['httponly']);
				}
			}

			// При повторном запуске $_SESSION уже будет
			//if (Core_Array::getRequest(self::getName())/* && !isset($_SESSION)*/)
			//{
			@session_start();

			if (!self::$_started)
			{
				//echo $error = error_get_last();
				if (Core_Array::getRequest('_', FALSE))
				{
					Core::showJson(array('error' => Core_Message::get(self::$_error, 'error'), 'form_html' => NULL));
				}
				else
				{
					// Service Unavailable
					Core_Response::sendHttpStatusCode(503);
					@header('Content-Type: text/html; charset=UTF-8');

					throw new Core_Exception(self::$_error . 'Please wait! Refreshing page ... <script>setTimeout(function() {window.location.reload(true);}, 3000);</script>');
				}
			}

			//self::$_started = TRUE; // Moved to Read & Lock
			self::$_hasSessionId = TRUE;
			//}
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
	static public function isActive()
	{
		return function_exists('session_status')
			? session_status() === PHP_SESSION_ACTIVE
			: session_id() !== '';
	}

	/**
	 * Backward compatibility, see isActive()
	 * @return boolean
	 */
	static public function isAcive()
	{
		return self::isActive();
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
		if (self::isActive())
		{
			!headers_sent()
				? session_regenerate_id($delete_old_session)
				: @session_regenerate_id($delete_old_session);
		}
	}

	/**
	 * Handler
	 * @var mixed
	 */
	static protected $_handler = NULL;

	/**
	 * Registers session handler
	 */
	static protected function _setSessionHandler()
	{
		$aConfig = self::getConfig();

		if (!isset($aConfig['driver']))
		{
			throw new Core_Exception('Wrong Session config, needs driver');
		}

		$sessionClass = isset($aConfig['class'])
			? $aConfig['class']
			: self::_getDriverName($aConfig['driver']);

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
	 * Close session
	 * @return boolean
	 */
	static public function close()
	{
		if (self::$_started)
		{
			self::$_debug && self::_log('close');

			if (self::isActive())
			{
				session_write_close();
			}

			self::$_started = FALSE;

			// cause session_destroy(): Trying to destroy uninitialized session
			//self::$_handler = NULL;
		}

		return TRUE;
	}

	/**
	 * Destroy Session
	 * @param string $id session ID
	 * @return boolean
	 */
	static public function destroy($id)
	{
		$aConfig = self::getConfig();

		Core_Cookie::set(session_name(), '', array(
			'expires' => 0,
			'path' => '/',
			'domain' => NULL,
			'secure' => $aConfig['secure'],
			'httponly' => $aConfig['httponly'])
		);

		$domain = self::correctDomain(self::getDomain());

		Core_Cookie::set(session_name(), '', array(
			'expires' => 0,
			'path' => '/',
			'domain' => $domain,
			'secure' => $aConfig['secure'],
			'httponly' => $aConfig['httponly'])
		);

		return self::$_handler->sessionDestroyer($id);
	}

	/**
	 * Error
	 * @var mixed
	 */
	static protected $_error = NULL;

	/**
	 * Show error
	 * @param string $content
	 */
	static protected function _error($content)
	{
		self::$_error = $content;
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

		if (!self::isStarted() && !self::isActive() && (!defined('DENY_INI_SET') || !DENY_INI_SET))
		{
			ini_set('session.gc_maxlifetime', $maxlifetime);
		}

		// Для уже запущенной сесии обновляем время жизни
		if (self::isStarted())
		{
			self::$_handler->sessionMaxlifetime($maxlifetime, $overwrite);
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

	/**
	 * Delete all sessions
	 */
	static public function flushAll() { }
}