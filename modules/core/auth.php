<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HostCMS administration center authorization
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Auth
{
	/**
	 * User logged
	 * @var boolean|NULL
	 */
	static protected $_logged = NULL;

	/**
	 * Current User
	 * @var User_Model
	 */
	static protected $_currentUser = NULL;

	/**
	 * Last Error
	 * @var string
	 */
	static protected $_lastError = NULL;

	/**
	 * Regenerate Session Id
	 * @var bool
	 */
	static protected $_regenerateId = TRUE;

	/**
	 * Check Blocked Ip. Break if IP blocked
	 */
	static public function checkBackendBlockedIp()
	{
		if (Core::moduleIsActive('ipaddress'))
		{
			// Check IP addresses
			$ip = Core::getClientIp();
			$aIp = array($ip);
			$HTTP_X_FORWARDED_FOR = Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_FOR');
			if (!is_null($HTTP_X_FORWARDED_FOR) && $ip != $HTTP_X_FORWARDED_FOR)
			{
				$aIp[] = $HTTP_X_FORWARDED_FOR;
			}

			$bBlockedIp = Ipaddress_Controller::instance()->isBackendBlocked($aIp);
			$bBlockedFilter = Ipaddress_Filter_Controller::instance()->isBlocked();

			if ($bBlockedIp || $bBlockedFilter)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write($bBlockedIp
						? Core::_('Core.error_log_backend_blocked_ip', implode(',', $aIp))
						: Core::_('Core.error_log_backend_blocked_useragent', implode(',', $aIp), Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str'))
					);

				$oCore_Response = new Core_Response();
				$oCore_Response
					->status(403)
					->header('Cache-Control', 'private, no-cache')
					->header('Pragma', 'no-cache') // для старых систем
					->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
					->header('X-Powered-By', 'HostCMS')
					->body('HostCMS: Error 403. Access Forbidden!')
					->sendHeaders()
					->showBody();

				exit();
			}
		}
	}

	/**
	 * Change Regenerate Session Id option
	 * @param bool $regenerateId
	 */
	static public function setRegenerateId($regenerateId = TRUE)
	{
		self::$_regenerateId = $regenerateId;
	}

	/**
	 * Authorization
	 * @param mixed $aModuleNames name of the module
	 */
	static public function authorization($aModuleNames)
	{
		!self::logged() && self::checkBackendBlockedIp();

		self::systemInit();

		if (!is_array($aModuleNames))
		{
			$aModuleNames = array($aModuleNames);
		}

		$sModuleName = implode(', ', $aModuleNames);

		if (!self::logged())
		{
			if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])
				&& Core_Type_Conversion::toBool($_SESSION['HOSTCMS_HTTP_AUTH_FLAG']) == TRUE)
			{
				//ob_start();

				try
				{
					// При HTTP-Авторизации сессию привязываем к IP
					self::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $assignSessionToIp = TRUE);
				}
				catch (Exception $e)
				{
					//Core_Message::show($e->getMessage(), 'error');
				}

				// Авторизация не произошла по причине неправильных данных
				/*if (!self::logged() && self::$_lastError == 'wrong data')
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write(Core::_('Core.error_log_attempt_to_access', $sModuleName));
				}

				ob_get_clean();
				$message = ob_get_clean();*/
			}
			/*else
			{
				$message = '';
			}*/

			if (!self::logged())
			{
				// webauthn
				if (Core_Array::getRequest('_', FALSE) && is_null(Core_Array::getGet('noWebauthns')))
				{
					Core::showJson(array(
						'action' => 'showWebauthn'
					));
				}
				else
				{
					// Нужен старт сессии, чтобы записать в нее HOSTCMS_HTTP_AUTH_FLAG
					if (@session_id() == '')
					{
						@session_start();
					}

					// Флаг начала HTTP-авторизации
					$_SESSION['HOSTCMS_HTTP_AUTH_FLAG'] = TRUE;

					$oCore_Response = new Core_Response();
					$oCore_Response
						->header('Content-Type', "text/html; charset=UTF-8")
						->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
						->header('X-Powered-By', 'HostCMS');

					// Not 'cgi', 'cgi-fcgi'
					if (substr(php_sapi_name(), 0, 3) != 'cgi')
					{
						$oCore_Response
							->status(401)
							->header('Pragma', 'no-cashe')
							->header('WWW-authenticate', "basic realm='HostCMS'");
					}
					else
					{
						$oCore_Response->status(403);
					}

					// Выводим страницу, которая отобразится, если пользователь нажмет "Отмена"
					$title = Core::_('Core.error_log_access_was_denied', $sModuleName);

					ob_start();
					$oSkin = Core_Skin::instance()
						->title($title)
						->setMode('authorization')
						->header();

					Core_Html_Entity::factory('Div')
						->class('indexMessage')
						->add(Core_Html_Entity::factory('H1')->value($title))
						->add(Core_Html_Entity::factory('Script')->value('checkRegistration(location)'))
						->execute();

					$oSkin->footer();

					$oCore_Response
						->body(ob_get_clean())
						->sendHeaders()
						->showBody();

					exit();
				}
			}

			// Флаг того, что окно авторизации было выведено удаляем
			$_SESSION['HOSTCMS_HTTP_AUTH_FLAG'] = FALSE;
			unset($_SESSION['HOSTCMS_HTTP_AUTH_FLAG']);
		}

		// Сбросим все уровни буферов PHP, созданные на данный момент
		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		try
		{
			// Устанавливаем текущий сайт
			self::setCurrentSite();

			$oUser = Core_Entity::factory('User')->getCurrent();

			if (is_null($oUser))
			{
				self::logout();

				throw new Core_Exception(
					'User not found, please relogin.'
				);
			}

			$oSite = Core_Entity::factory('Site', $_SESSION['current_site_id']);

			// Временная зона центра администрирования устанавливается отдельно
			$timezone = trim($oSite->timezone);
			if ($timezone != '')
			{
				date_default_timezone_set($timezone);
			}

			$bModuleAccess = $oUser->checkModuleAccess($aModuleNames, $oSite);

			if (!$bModuleAccess)
			{
				$sMessage = Core::_('Core.error_log_access_was_denied', $sModuleName);

				Core_Log::instance()->clear()
					->status(Core_Log::$NOTICE)
					->write($sMessage);

				$oAdmin_Answer = Core_Skin::instance()->answer();
				$oAdmin_Answer
					->ajax(Core_Array::getRequest('_', FALSE))
					->content(Core_Message::get($sMessage, 'error'))
					//->message($sMessage)
					->title($sMessage)
					->execute();

				exit();
			}

			$oUser->updateLastActivity();
		}
		catch (Exception $e)
		{
			$oAdmin_Answer = Core_Skin::instance()->answer();
			$oAdmin_Answer
				->ajax(Core_Array::getRequest('_', FALSE))
				->message(
					Core_Message::get($e->getMessage(), 'error')
				)
				->title($e->getMessage())
				->execute();

			exit();
		}

		Core_Log::instance()->clear()
			->status(Core_Log::$SUCCESS)
			->write(Core::_('Core.error_log_module_access_allowed', $sModuleName));

		Core_Session::close();
	}

	/**
	 * System initialization
	 */
	static public function systemInit()
	{
		Core_Event::notify('Core_Auth.onBeforeSystemInit');

		// Если не используется HTTPS-доступ
		if (defined('USE_ONLY_HTTPS_AUTHORIZATION') && USE_ONLY_HTTPS_AUTHORIZATION && !Core::httpsUses())
		{
			//$url = strtolower(Core_Array::get($_SERVER, 'HTTP_HOST')) . $_SERVER['REQUEST_URI'];
			$url = Core_Http::sanitizeHeader(
				strtolower(Core_Array::get($_SERVER, 'SERVER_NAME')) . $_SERVER['REQUEST_URI']
			);

			header("HTTP/1.1 302 Found");
			header("Location: https://{$url}");

			exit();
		}

		header('Content-type: text/html; charset=UTF-8');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache'); // для старых систем
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('X-Frame-Options: SAMEORIGIN');
		header('X-Content-Type-Options: nosniff');
		header('X-XSS-Protection: 1; mode=block');
		header('Strict-Transport-Security: max-age=0');
		header('Content-Security-Policy: ' . Core::$mainConfig['backendContentSecurityPolicy']);

		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			ini_set('display_errors', 1);
		}

		// Если есть ID сессии и сессия еще не запущена - то стартуем ее,
		// первоначальный старт осуществляется при авторизации пользователя
		self::adminSessionStart();

		define('IS_ADMIN_PART', TRUE);

		self::setCurrentLng(Core_Array::getSession('current_lng'));

		// Записываем в сессию язык, содержащийся в константе
		$_SESSION['current_lng'] = CURRENT_LNG;

		Core_Event::notify('Core_Auth.onAfterSystemInit');
	}

	/**
	 * Define CURRENT_LNG constant
	 * @param string $lng
	 */
	static public function setCurrentLng($lng)
	{
		if (!defined('CURRENT_LNG'))
		{
			empty($lng) && $lng = strtolower(
				substr(Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '', 'str'), 0, 2)
			);

			$oAdmin_Language = $lng !== ''
				? Core_Entity::factory('Admin_Language')->getByShortname($lng)
				: NULL;

			!$oAdmin_Language && $lng = NULL;

			define('CURRENT_LNG', !is_null($lng) ? $lng : DEFAULT_LNG);
			Core_I18n::instance()->setLng(CURRENT_LNG);

			!$oAdmin_Language
				&& $oAdmin_Language = Core_Entity::factory('Admin_Language')->getByShortname(CURRENT_LNG);

			define('CURRENT_LANGUAGE_ID', !is_null($oAdmin_Language) ? $oAdmin_Language->id : 0);
		}
	}

	/**
	 * Starts the session
	 */
	static public function adminSessionStart()
	{
		Core_Session::setMaxLifeTime(Core::$mainConfig['backendSessionLifetime'], TRUE);
		Core_Session::start();
	}

	/**
	 * Get Current User
	 * @return User_Model|NULL
	 */
	static public function getCurrentUser()
	{
		return self::$_currentUser;
	}

	/**
	 * Checks user's authorization
	 * Проверка авторизации пользователя
	 * @return boolean
	 */
	static public function logged()
	{
		if (is_null(self::$_logged))
		{
			self::$_logged = FALSE;

			// Идентификатор сессии уже был установлен
			if (Core_Session::hasSessionId())
			{
				$isActive = Core_Session::isActive();
				!$isActive && Core_Session::start();

				if (isset($_SESSION['valid_user']) && strlen($_SESSION['valid_user']) > 0
					&& isset($_SESSION['date_user']) && strlen($_SESSION['date_user']) > 0
					&& isset($_SESSION['current_users_id']) && $_SESSION['current_users_id'] > 0
					&& isset($_SESSION['is_superuser']))
				{
					$ip = Core::getClientIp();

					// Привязки к IP не было или сети для IP совпадают
					if (!isset($_SESSION['current_user_ip'])
						|| isset($_SESSION['current_bf']) && $_SESSION['current_bf'] === self::_getBrowserFingerprint()
						|| (
							// IPv4 network comparison
							Core_Valid::ipv4($_SESSION['current_user_ip']) && Core_Valid::ipv4($ip)
								&& Core_Ip::ipv4Network($_SESSION['current_user_ip'], Core::$mainConfig['backendAssignSessionIpMask']) === Core_Ip::ipv4Network($ip, Core::$mainConfig['backendAssignSessionIpMask'])
							// or IPv6 comparison
							|| $_SESSION['current_user_ip'] === $ip
						)
					)
					{
						// Пользователь существует
						$oUser = Core_Entity::factory('User')->getCurrent();
						if ($oUser && $oUser->active && !$oUser->dismissed)
						{
							self::$_logged = TRUE;
							self::$_currentUser = $oUser;

							// Log to the `user_sessions` table
							User_Controller::logToUserSession();
						}
						else
						{
							self::logout();
						}
					}
					else
					{
						Core_Log::instance()->clear()
							->status(Core_Log::$ERROR)
							->notify(FALSE)
							->write(Core::_('Core.session_change_ip', session_id(), $ip));

						self::logout();
					}
				}

				!$isActive && Core_Session::close();
			}
		}

		return self::$_logged;
	}

	/**
	 * Get hash for Browser Fingerprint
	 * @return string
	 */
	static protected function _getBrowserFingerprint()
	{
		return hash('sha256', Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '')
			. '|' . Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '')
			. '|' . Core_Array::get($_SERVER, 'HTTP_ACCEPT_ENCODING', '')
			//. '|' . Core_Array::get($_SERVER, 'HTTP_ACCEPT', '')
		);
	}

	/**
	 * Метод устанавливает текущий сайт, обрабатывает изменение текущего сайта
	 */
	static public function setCurrentSite()
	{
		Core_Event::notify('Core_Auth.onBeforeSetCurrentSite');

		// Выполняем только после регистрации пользователя
		if (self::logged())
		{
			// Выбранный в меню сайт
			$iSelectedSite = Core_Array::getGet('changeSiteId');

			if (!is_null($iSelectedSite))
			{
				$_SESSION['current_site_id'] = intval($iSelectedSite);
			}

			$site_id = NULL;

			// Если нет выбранного сайта
			if (isset($_SESSION['current_site_id']))
			{
				// Check site exists
				$oSite = Core_Entity::factory('Site')->getById($_SESSION['current_site_id']);

				if (is_null($oSite))
				{
					unset($_SESSION['current_site_id']);
				}
				else
				{
					$site_id = $oSite->id;
				}
			}

			if (!$site_id)
			{
				if (is_null(self::$_currentUser))
				{
					exit('User does not exist!');
				}

				$domain = strtolower(Core_Array::get($_SERVER, 'HTTP_HOST'));

				$oSite_Alias = Core_Entity::factory('Site_Alias')->findAlias($domain);
				if (!is_null($oSite_Alias)
					&& (self::$_currentUser->superuser || self::$_currentUser->checkSiteAccess($oSite_Alias->Site))
				)
				{
					$site_id = $oSite_Alias->site_id;
				}
				else
				{
					// Для суперпользователя выбираем все сайты
					if (self::$_currentUser->superuser)
					{
						$oSite = Core_Entity::factory('Site')->getFirstSite();
						$site_id = $oSite->id;
					}
					else
					{
						$oSites = Core_Entity::factory('Site');

						$oSites->queryBuilder()
							->select('sites.*')
							->join('company_department_modules', 'company_department_modules.site_id', '=', 'sites.id')
							->join('company_department_post_users', 'company_department_modules.company_department_id', '=', 'company_department_post_users.company_department_id')
							->where('company_department_post_users.user_id', '=', self::$_currentUser->id)
							->groupBy('sites.id')
							->limit(1);

						$aSites = $oSites->findAll(FALSE);

						if (isset($aSites[0]))
						{
							$site_id = $aSites[0]->id;
						}
						else
						{
							self::logout();
							exit('No site is available! Check company structure.');
						}
					}
				}

				// Заносим значение в сессию
				$_SESSION['current_site_id'] = $site_id;
			}

			if (!$site_id)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->notify(FALSE)
					->write('Site does not exist! Check aliases and permissions for a users.');

				self::logout();

				header("HTTP/1.1 302 Found");
				header(Admin_Form_Controller::correctBackendPath("Location: /{admin}/"));

				exit();
			}

			// Определяем константу
			if (!defined('CURRENT_SITE'))
			{
				define('CURRENT_SITE', $_SESSION['current_site_id']);
			}
		}

		Core_Event::notify('Core_Auth.onAfterSetCurrentSite');
	}

	/**
	 * Метод производит авторизацию пользователя в разделе администрирования
	 *
	 * @param string $login логин
	 * @param string $password пароль
	 * @param boolean $assignSessionToIp привязать сессию к IP-адресу
	 * @return bool
     * <br />true -- автооризация произведена успешно
	 * <br />false -- неправильные данные доступа
	 * <br />-1 -- не истекло время до следующей попытки авторизации
	 */
	static public function login($login, $password, $assignSessionToIp = TRUE)
	{
		Core_Event::notify('Core_Auth.onBeforeLogin', NULL, array($login));

		$ip = Core::getClientIp();

		if (Core::$mainConfig['timeoutAfterFailedAccessAttempts'])
		{
			// Получаем количество неудачных попыток
			$iCountAccessdenied = Core_Entity::factory('User_Accessdenied')->getCountByIp($ip);

			// Были ли у данного пользователя неудачные попытки входа в систему администрирования за последние 24 часа?
			if ($iCountAccessdenied)
			{
				// Last User_Accessdenied by IP
				$oUser_Accessdenied = Core_Entity::factory('User_Accessdenied')->getLastByIp($ip);

				if (!is_null($oUser_Accessdenied))
				{
					// Определяем интервал времени между последней неудачной попыткой входа в систему
					// и текущим временем входа в систему
					$delta = time() - Core_Date::sql2timestamp($oUser_Accessdenied->datetime);

					// Определяем период времени, в течении которого пользователю, имевшему неудачные
					// попытки доступа в систему запрещен вход в систему
					$delta_access_denied = $iCountAccessdenied > 2
						? 5 * exp(2.2 * log($iCountAccessdenied - 1))
						: 5 * $iCountAccessdenied;

					// Если период запрета доступа в систему не истек
					if ($delta_access_denied > $delta)
					{
						$iCountAccessdenied++;

						// Проверяем количество доступных ошибочных попыток
						if (Core::$mainConfig['banAfterFailedAccessAttempts']
							&& $iCountAccessdenied > Core::$mainConfig['banAfterFailedAccessAttempts']
							&& Core::moduleIsActive('ipaddress')
						)
						{
							$banIp = strpos($ip, ':') === FALSE
								// IPv4
								? substr($ip, 0, strrpos($ip, '.')) . '.0/24'
								// IPv6
								: $ip;

							$oIpaddress = Core_Entity::factory('Ipaddress')->getByIp($banIp, FALSE);

							if (!$oIpaddress)
							{
								$oIpaddress = Core_Entity::factory('Ipaddress');
								$oIpaddress->ip = $banIp;
								$oIpaddress->deny_access = 0;
								$oIpaddress->deny_backend = 1;
								$oIpaddress->comment = sprintf('IP %s blocked after %d failed attempts', $ip, $iCountAccessdenied);
								$oIpaddress->save();
							}
						}

						self::$_lastError = 'access temporarily unavailable';

						throw new Core_Exception(
							Core::_('Admin.authorization_error_access_temporarily_unavailable', $login, round($delta_access_denied - $delta)),
							array(), 0, $bShowDebugTrace = FALSE
						);
					}
				}
			}
		}

		if (strlen($login) > 255)
		{
			self::$_lastError = 'login too long';
			return FALSE;
		}

		// С доп.проверкой на active и dismissed в методе getByLoginAndPassword()
		$oUser = Core_Entity::factory('User')->getByLoginAndPassword($login, $password);

		if ($oUser)
		{
			self::setCurrentUser($oUser, $assignSessionToIp);
		}
		else
		{
			// Запись в базу об ошибке доступа
			self::_addUserAccessdenied($ip);

			self::$_lastError = 'wrong data';

			return FALSE;
		}

		Core_Event::notify('Core_Auth.onAfterLogin', NULL, array($login));

		return TRUE;
	}

	/**
	 * Set current user
	 * @param User_Model $oUser
	 * @param boolean $assignSessionToIp привязать сессию к IP-адресу
	 */
	static public function setCurrentUser(User_Model $oUser, $assignSessionToIp)
	{
		$ip = Core::getClientIp();

		// Сессия может быть уже запущена и при повторном отправке данных POST-ом при авторизации
		//if (!isset($_SESSION['valid_user']))
		if (@session_id() == '')
		{
			Core_Session::start();
		}

		// Записываем ID пользователя
		$_SESSION['current_users_id'] = $oUser->id;
		$_SESSION['valid_user'] = $oUser->login;
		$_SESSION['date_user'] = date('d.m.Y H:i:s');
		$_SESSION['is_superuser'] = $oUser->superuser;
		$_SESSION['current_bf'] = self::_getBrowserFingerprint();

		$assignSessionToIp && $_SESSION['current_user_ip'] = $ip;

		// Создаем новый ID сессии без уничтожения предыдущей
		self::$_regenerateId && Core_Session::regenerateId(FALSE);

		self::$_logged = TRUE;
		self::$_currentUser = $oUser;

		// Destroy Old Sessions
		User_Controller::destroyOldUserSessions();

		// Log to the `user_sessions` table
		User_Controller::logToUserSession();

		Core_Log::instance()->clear()
			->status(Core_Log::$ERROR)
			->notify(FALSE)
			->write(Core::_('Core.error_log_logged'));

		// Удаление всех неудачных попыток входа систему за период ранее 24 часов с момента успешного входа
		$limit = 500;
		do {
			$oUser_Accessdenieds = Core_Entity::factory('User_Accessdenied');
			$oUser_Accessdenieds->queryBuilder()
				->clear()
				->where('datetime', '<', Core_Date::timestamp2sql(time() - 86400))
				// Удаляем все попытки доступа с текущего IP
				->setOr()
				->where('ip', '=', $ip)
				->limit($limit);

			$aUser_Accessdenieds = $oUser_Accessdenieds->findAll(FALSE);
			foreach ($aUser_Accessdenieds as $oUser_Accessdenied)
			{
				$oUser_Accessdenied->delete();
			}
		} while (count($aUser_Accessdenieds) == $limit);
	}

	/**
	 * Add User_Accessdenied
	 * @param string $ip
	 */
	static protected function _addUserAccessdenied($ip)
	{
		$oUser_Accessdenied = Core_Entity::factory('User_Accessdenied');
		$oUser_Accessdenied->datetime = Core_Date::timestamp2sql(time());
		$oUser_Accessdenied->ip = $ip;
		$oUser_Accessdenied->save();
	}

	/**
	 * Logout current user
	 */
	static public function logout()
	{
		Core_Session::start();

		$aUnsets = array(
			'current_users_id',
			'valid_user',
			'date_user',
			'is_superuser',
			'current_user_ip',
			'current_bf'
		);

		foreach ($aUnsets as $sUnsetName)
		{
			if (isset($_SESSION[$sUnsetName]))
			{
				unset($_SESSION[$sUnsetName]);
			}
		}

		self::$_logged = FALSE;
		self::$_currentUser = NULL;

		// regenerateId осуществляется при новой авторизации
		/*$sessionId = session_id();
		Core_Session::destroy($sessionId);
		self::$_regenerateId && Core_Session::regenerateId(TRUE);*/
	}
}