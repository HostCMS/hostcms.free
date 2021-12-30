<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HostCMS administration center authorization
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Check Blocked Ip. Break if IP blocked
	 */
	static public function checkBackendBlockedIp()
	{
		// Check IP addresses
		$ip = Core::getClientIp();
		$aIp = array($ip);
		$HTTP_X_FORWARDED_FOR = Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_FOR');
		if (!is_null($HTTP_X_FORWARDED_FOR) && $ip != $HTTP_X_FORWARDED_FOR)
		{
			$aIp[] = $HTTP_X_FORWARDED_FOR;
		}

		if (Core::moduleIsActive('ipaddress'))
		{
			$oIpaddress_Controller = new Ipaddress_Controller();

			$bBlocked = $oIpaddress_Controller->isBackendBlocked($aIp);

			if ($bBlocked)
			{
				$oCore_Response = new Core_Response();

				$oCore_Response
					->status(403)
					->header('Pragma', 'no-cache')
					->header('Cache-Control', 'private, no-cache')
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
	 * Authorization
	 * @param mixed $aModuleNames name of the module
	 */
	static public function authorization($aModuleNames)
	{
		self::checkBackendBlockedIp();

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
				ob_start();

				try
				{
					// При HTTP-Авторизации сессию привязываем к IP
					self::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $assignSessionToIp = TRUE);
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}

				// Авторизация не произошла по причине неправильных данных
				if (!self::logged() && self::$_lastError == 'wrong data')
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write(Core::_('Core.error_log_attempt_to_access', $sModuleName));
				}

				$message = ob_get_clean();
			}
			else
			{
				$message = '';
			}

			if (!self::logged())
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
					->status(401)
					->header('Pragma', 'no-cashe')
					->header('WWW-authenticate', "basic realm='HostCMS'")
					->header('Content-Type', "text/html; charset=UTF-8")
					->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
					->header('X-Powered-By', 'HostCMS');

				// Выводим страницу, которая отобразится, если пользователь нажмет "Отмена"
				$title = Core::_('Core.error_log_access_was_denied', $sModuleName);

				ob_start();
				$oSkin = Core_Skin::instance()
					->title($title)
					->setMode('authorization')
					->header();

				Core::factory('Core_Html_Entity_Div')
					->class('indexMessage')
					->add(Core::factory('Core_Html_Entity_H1')->value($title))
					->execute();

				$oSkin->footer();

				$oCore_Response->body(ob_get_clean());

				$oCore_Response
					->sendHeaders()
					->showBody();

				exit();
			}

			// Флаг того, что окно авторизации было выведено удаляем
			$_SESSION['HOSTCMS_HTTP_AUTH_FLAG'] = FALSE;
			unset($_SESSION['HOSTCMS_HTTP_AUTH_FLAG']);
		}

		try
		{
			// Устанавливаем текущий сайт
			self::setCurrentSite();

			$oUser = Core_Entity::factory('User')->getByLogin(
				$_SESSION['valid_user']
			);

			if (is_null($oUser))
			{
				unset($_SESSION['valid_user']);
				throw new Core_Exception(
					'User not found, please relogin.'
				);
			}

			$oSite = Core_Entity::factory('Site', $_SESSION['current_site_id']);

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
			$url = strtolower(Core_Array::get($_SERVER, 'SERVER_NAME')) . $_SERVER['REQUEST_URI'];
			$url = str_replace(array("\r", "\n", "\0"), '', $url);

			header("HTTP/1.1 302 Found");
			header("Location: https://{$url}");

			exit();
		}

		header('Content-type: text/html; charset=UTF-8');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('X-Frame-Options: SAMEORIGIN');
		header('X-Content-Type-Options: nosniff');
		header('X-XSS-Protection: 1; mode=block');
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

	static public function setCurrentLng($lng)
	{
		if (!defined('CURRENT_LNG'))
		{
			// Выбираем
			empty($lng) && $lng = strtolower(htmlspecialchars(
				substr(Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2)
			));
			$oAdmin_Language = Core_Entity::factory('Admin_Language')->getByShortname($lng);
			!$oAdmin_Language && $lng = NULL;

			// Устанавливаем полученный язык
			define('CURRENT_LNG', !is_null($lng) ? $lng : DEFAULT_LNG);
			Core_I18n::instance()->setLng(CURRENT_LNG);

			$oAdmin_Language = Core_Entity::factory('Admin_Language')->getByShortname(CURRENT_LNG);
			define('CURRENT_LANGUAGE_ID', !is_null($oAdmin_Language) && $oAdmin_Language->active
				? $oAdmin_Language->id
				: 0
			);
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

					// Привязки к IP не было или IP совпадают
					if (!isset($_SESSION['current_user_ip']) || $_SESSION['current_user_ip'] == $ip)
					{
						// Пользователь существует
						$oUser = Core_Entity::factory('User')->getCurrent();
						if ($oUser && $oUser->active)
						{
							self::$_logged = TRUE;
							self::$_currentUser = $oUser;

							$sessionId = session_id();
							$userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', NULL);
							$oDataBase = Core_QueryBuilder::update('user_sessions')
								->set('user_id', self::$_currentUser->id)
								->set('time', time())
								->set('user_agent', $userAgent)
								->set('ip', $ip)
								->where('id', '=', $sessionId)
								->execute();

							// Returns the number of rows affected by the last SQL statement
							// If nothing's really was changed affected rowCount will return 0.
							if ($oDataBase->getAffectedRows() == 0)
							{
								Core_QueryBuilder::insert('user_sessions')
									->ignore()
									->columns('id', 'user_id', 'time', 'user_agent', 'ip')
									->values($sessionId, self::$_currentUser->id, time(), $userAgent, $ip)
									->execute();
							}
						}
						else
						{
							Core_Auth::logout();
						}
					}
					else
					{
						Core_Auth::logout();
					}
				}

				!$isActive && Core_Session::close();
			}
		}

		return self::$_logged;
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

			// Если нет выбранного сайта
			if (!isset($_SESSION['current_site_id']))
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

						$site_id = isset($aSites[0])
							? $aSites[0]->id
							: NULL;
					}
				}

				if (!$site_id)
				{
					exit('Site does not exist! Check aliases and permissions for a users.');
				}

				// Заносим значение в сессию
				$_SESSION['current_site_id'] = $site_id;
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
	 * @return mixed
	 * <br />true -- автооризация произведена успешно
	 * <br />false -- неправильные данные доступа
	 * <br />-1 -- не истекло время до следующей попытки авторизации
	 */
	static public function login($login, $password, $assignSessionToIp = TRUE)
	{
		Core_Event::notify('Core_Auth.onBeforeLogin', NULL, array($login));

		$ip = Core::getClientIp();

		// Получаем количество неудачных попыток
		$iCountAccessdenied = Core_Entity::factory('User_Accessdenied')->getCountByIp($ip);

		// Были ли у данного пользователя неудачные попытки входа в систему администрирования за последние 24 часа?
		if ($iCountAccessdenied)
		{
			// Last User_Accessdenied by IP
			$oUser_Accessdenied = Core_Entity::factory('User_Accessdenied')->getLastByIp($ip);

			if (!is_null($oUser_Accessdenied))
			{
				// определяем интервал времени между последней неудачной попыткой входа в систему
				// и текущим временем входа в систему
				$delta = time() - Core_Date::sql2timestamp($oUser_Accessdenied->datetime);

				// определяем период времени, в течении которого пользователю, имевшему неудачные
				// попытки доступа в систему запрещен вход в систему
				$delta_access_denied = $iCountAccessdenied > 2
					? 5 * exp(2 * log($iCountAccessdenied - 1))
					: 5;

				// если период запрета доступа в систему не истек
				if ($delta_access_denied > $delta)
				{
					self::$_lastError = 'access temporarily unavailable';

					throw new Core_Exception(
						Core::_('Admin.authorization_error_access_temporarily_unavailable'),
							array('%s' => round($delta_access_denied - $delta)), 0, $bShowDebugTrace = FALSE
					);
				}
			}
		}

		if (strlen($login) > 255)
		{
			self::$_lastError = 'login too long';
			return FALSE;
		}

		$oUser = Core_Entity::factory('User')->getByLoginAndPassword($login, $password);

		if ($oUser)
		{
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

			$assignSessionToIp && $_SESSION['current_user_ip'] = $ip;

			self::$_logged = TRUE;
			self::$_currentUser = $oUser;

			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->notify(FALSE)
				->write(Core::_('Core.error_log_logged'));

			// Удаление всех неудачных попыток входа систему за период ранее 24 часов с момента удачного входа в систему
			$oUser_Accessdenied = Core_Entity::factory('User_Accessdenied');
			$oUser_Accessdenied->queryBuilder()
				->clear()
				->where('datetime', '<', Core_Date::timestamp2sql(time() - 86400))
				// Удаляем все попытки доступа с текущего IP
				->setOr()
				->where('ip', '=', $ip);

			$aUser_Accessdenieds = $oUser_Accessdenied->findAll(FALSE);
			foreach ($aUser_Accessdenieds as $oUser_Accessdenied)
			{
				$oUser_Accessdenied->delete();
			}
		}
		else
		{
			// Запись в базу об ошибке доступа
			$oUser_Accessdenied = Core_Entity::factory('User_Accessdenied');
			$oUser_Accessdenied->datetime = Core_Date::timestamp2sql(time());
			$oUser_Accessdenied->ip = $ip;
			$oUser_Accessdenied->save();

			self::$_lastError = 'wrong data';

			return FALSE;
		}

		Core_Event::notify('Core_Auth.onAfterLogin', NULL, array($login));

		return TRUE;
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
			'current_user_ip'
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

		Core_Session::regenerateId(TRUE);
	}
}