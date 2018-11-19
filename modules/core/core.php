<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core
{
	/**
	 * Core::init() has been called
	 */
	static protected $_init = NULL;

	/**
	 * Fragments of URL path
	 * @var array
	 */
	static public $url = array(
		'scheme' => '',
		'host' => '',
		'port' => '',
		'user' => '',
		'pass' => '',
		'path' => '',
		'query' => '',
		'fragment' => ''
	);

	/**
	 * Core_Config object
	 * @var Core_Config
	 */
	static public $config = NULL;

	/**
	 * Core_Log object
	 * @var Core_Log
	 */
	static public $log = NULL;

	/**
	 * Main config located /modules/core/config/config.php
	 * @var array
	 */
	static public $mainConfig = array();

	/**
	 * List of modules, key $oModule->path, value $oModule
	 * @var array
	 */
	static public $modulesList = array();

	/**
	 * Modules path, e.g. CMS_FOLDER . 'modules' . DIRECTORY_SEPARATOR
	 * @var string
	 */
	static public $modulesPath = NULL;

	/**
	 * Check if self::init() has been called
	 * @return boolean
	 */
	static public function isInit()
	{
		return self::$_init;
	}

	/**
	 * User Logged
	 * @var NULL|boolean
	 */
	static protected $_logged = NULL;

	/**
	 * Initialization
	 * @return boolean
	 */
	static public function init()
	{
		if (self::isInit())
		{
			return TRUE;
		}

		$fBeginTime = Core::getmicrotime();

		self::setModulesPath();
		self::registerCallbackFunction();

		// Observers
		Core_Event::attach('Core_DataBase.onBeforeConnect', array('Core_Database_Observer', 'onBeforeConnect'));
		Core_Event::attach('Core_DataBase.onAfterConnect', array('Core_Database_Observer', 'onAfterConnect'));
		Core_Event::attach('Core_DataBase.onBeforeSelectDb', array('Core_Database_Observer', 'onBeforeSelectDb'));
		Core_Event::attach('Core_DataBase.onAfterSelectDb', array('Core_Database_Observer', 'onAfterSelectDb'));

		mb_internal_encoding('UTF-8');

		self::$config = Core_Config::instance();

		// Main config
		self::mainConfig();

		self::$log = Core_Log::instance();

		// Constants init
		$oConstants = Core_Entity::factory('Constant');
		$oConstants->queryBuilder()->where('active', '=', 1);
		$aConstants = $oConstants->findAll();
		foreach ($aConstants as $oConstant)
		{
			$oConstant->define();
		}

		!defined('TMP_DIR') && define('TMP_DIR', 'hostcmsfiles/tmp/');
		!defined('DEFAULT_LNG') && define('DEFAULT_LNG', 'ru');
		!defined('BACKUP_DIR') && define('BACKUP_DIR', CMS_FOLDER . 'hostcmsfiles' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR);

		// Если есть ID сессии и сессия еще не запущена - то стартуем ее
		// Запускается здесь для получения языка из сессии.
		/* && !isset($_SESSION)*/
		(isset($_REQUEST[session_name()]) || isset($_COOKIE[session_name()])) && Core_Session::start();

		self::$_logged = Core_Auth::logged();

		// Before _loadModuleList()
		if (isset($_REQUEST['lng_value']) && self::$_logged)
		{
			Core_Auth::setCurrentLng($_REQUEST['lng_value']);
		}

		self::_loadModuleList();

		self::$_init = TRUE;

		Core_Registry::instance()->set('Core_Statistics.totalTimeBegin', $fBeginTime);

		return TRUE;
	}

	/**
	 * Load configuration for core
	 */
	static public function mainConfig()
	{
		// Main config
		self::$mainConfig = self::$config->get('core_config') + array(
			'skin' => 'default',
			'dateFormat' => 'd.m.Y',
			'dateTimeFormat' => 'd.m.Y H:i:s',
			'datePickerFormat' => 'DD.MM.YYYY',
			'dateTimePickerFormat' => 'DD.MM.YYYY HH:mm:ss',
			'availableExtension' => array ('JPG', 'JPEG', 'GIF', 'PNG', 'PDF', 'ZIP'),
			'defaultCache' => 'file',
			'timezone' => 'America/Los_Angeles',
			'translate' => TRUE,
			'chat' => TRUE,
			'switchSelectToAutocomplete' => 100,
			'autocompleteItems' => 10,
			'session' => array(
				'driver' => 'database',
				'class' => 'Core_Session_Database',
			),
			'backendSessionLifetime' => 14400
		);
	}

	/**
	 * Set path to the modules
	 */
	static public function setModulesPath()
	{
		self::$modulesPath = CMS_FOLDER . 'modules' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Register all callback functions
	 */
	static public function registerCallbackFunction()
	{
		spl_autoload_register(array('Core', '_autoload'));
		set_exception_handler(array('Core', '_exception'));
		register_shutdown_function(array('Core', '_shutdown'));
		set_error_handler(array('Core', '_error'));
	}

	/**
	 * Load Module Time
	 */
	static protected $_loadModuleTime = 0;

	/**
	 * Get Load Module Time
	 * @return float
	 */
	static public function getLoadModuleTime()
	{
		return self::$_loadModuleTime;
	}

	/**
	 * Load modules list
	 * @hostcms-event Core.onBeforeLoadModuleList
	 * @hostcms-event Core.onAfterLoadModuleList
	 */
	static protected function _loadModuleList()
	{
		self::$_logged && $fBeginTime = Core::getmicrotime();

		Core_Event::notify('Core.onBeforeLoadModuleList');

		// List of modules
		$aModules = Core_Entity::factory('Module')->findAll();
		foreach ($aModules as $oModule)
		{
			self::$modulesList[$oModule->path] = $oModule;
			
			// Call module's __construct()
			$oModule->active && $oModule->loadModule();
		}

		Core_Event::notify('Core.onAfterLoadModuleList');

		/*self::$_logged && Core_Page::instance()->addFrontendExecutionTimes(
			Core::_('Core.time_load_modules', Core::getmicrotime() - $fBeginTime)
		);*/
		self::$_logged && self::$_loadModuleTime += Core::getmicrotime() - $fBeginTime;
	}

	/**
	 * Deinitialization
	 */
	static public function deinit()
	{
		if (self::isInit())
		{
			spl_autoload_unregister(array('Core', 'autoload'));
			restore_exception_handler();
			self::$modulesList = array();
			self::$_init = FALSE;
		}
	}

	/**
	 * Callback function
	 *
	 * @param int $code код ошибки - E_ERROR и т.д.
	 * @param string $msg сообщение об ошбике
	 * @param string $file имя файла, в котором произошла ошибка
	 * @param int $line строка, в котором произошла ошибка
	 */
	static public function _error($code, $msg, $file, $line)
	{
		// Не выводим ошибки, если режим сообщения об ошибках отключен
		// или код ошибки меньше кода вывода ошибок
		$error_reporting = error_reporting();

		$bShowError = $error_reporting != 0 && $error_reporting >= $code;

		// Уровень критичности ошибки
		$error_level = array
		(
			E_ERROR => 4,
			E_WARNING => 3,
			E_PARSE => 4,
			E_NOTICE => 2,
			E_CORE_ERROR => 4,
			E_CORE_WARNING => 4,
			E_COMPILE_ERROR => 4,
			E_COMPILE_WARNING => 4,
			E_USER_ERROR => 4,
			E_USER_WARNING => 3,
			E_USER_NOTICE => 2,
			//2048 => 0
			2048 => -1
		);

		// Название типа ошибки
		$error_name = array
		(
			E_ERROR => Core::_('Core.E_ERROR'),
			E_WARNING => Core::_('Core.E_WARNING'),
			E_PARSE => Core::_('Core.E_PARSE'),
			E_NOTICE => Core::_('Core.E_NOTICE'),
			E_CORE_ERROR => Core::_('Core.E_CORE_ERROR'),
			E_CORE_WARNING => Core::_('Core.E_CORE_WARNING'),
			E_COMPILE_ERROR => Core::_('Core.E_COMPILE_ERROR'),
			E_COMPILE_WARNING => Core::_('Core.E_COMPILE_WARNING'),
			E_USER_ERROR => Core::_('Core.E_USER_ERROR'),
			E_USER_WARNING => Core::_('Core.E_USER_WARNING'),
			E_USER_NOTICE => Core::_('Core.E_USER_NOTICE'),
			2048 => Core::_('Core.E_STRICT')
		);

		$current_error_level = isset($error_level[$code])
			? $error_level[$code]
			: 0;

		// Определяем название ошибки (Error/Warning/etc)
		$current_error_name = isset($error_name[$code])
			? $error_name[$code]
			: 'Undefined error';

		$aStack = array();

		if (function_exists('debug_backtrace'))
		{
			$debug_backtrace = debug_backtrace();

			foreach ($debug_backtrace as $history)
			{
				if (isset($history['file']) && isset($history['line']))
				{
					// Отрезаем полный путь к файлу, если есть, dirname для приведения слэшей к нужному виду
					if (strpos($history['file'], dirname(CMS_FOLDER)) === 0)
					{
						$history['file'] = substr($history['file'], strlen(CMS_FOLDER));
					}

					$aStack[] = Core::_('Core.error_log_message_stack', $history['file'], $history['line']);
				}
			}
		}

		$sStack = implode(",\n", $aStack);

		// Если ошибка уровня E_USER_ERROR
		$message = $code == E_USER_ERROR
			? '<strong>Ошибка!</strong> Сообщение об ошибке занесено в журнал.'
			: Core::_('Core.error_log_message_short', $current_error_name, $msg, $file, $line);

		// Если показывать ошибки и текущий уровень ошибки не E_STRICT
		if ($bShowError && $current_error_level != -1)
		{
			echo $message;
		}

		// В лог не пишем, если ошибка E_STRICT и запрещен вывод ее в лог
		/*if (!($current_error_level == -1
		&& (!defined('DENY_ADD_STRICT_INTO_LOG') || DENY_ADD_STRICT_INTO_LOG)))
		{*/
			Core_Log::instance()->clear()
				->status($current_error_level)
				->notify($bShowError)
				->write(Core::_('Core.error_log_message', $current_error_name, $msg, $file, $line, $sStack));
		//}

		// Если ошибка уровня E_ERROR или E_USER_ERROR - завершаем выполнение скрипта
		if ($code == E_ERROR || $code == E_USER_ERROR)
		{
			exit();
		}
	}

	/**
	 * Create class $className
	 * @param string $className class name
	 * @return mixed
	 */
	static public function factory($className)
	{
		return new $className();
	}

	/**
	 * Get path to class file by class name
	 * @param string $class name of the class
	 * @return string
	 */
	static public function getClassPath($class)
	{
		$aClassName = explode('_', strtolower($class));

		$sFileName = array_pop($aClassName);

		// If class name doesn't have '_'
		$path = empty($aClassName)
			? $sFileName . DIRECTORY_SEPARATOR
			: implode(DIRECTORY_SEPARATOR, $aClassName) . DIRECTORY_SEPARATOR;

		$path .= $sFileName . '.php';

		//$path = Core_File::pathCorrection($path);

		return $path;
	}

	/**
	 * Callback function
	 */
	static public function _shutdown()
	{
		// Явно закрываем сессию до закрытия соединения с БД в деструкторе
		Core_Session::close();

		$lastError = error_get_last();
		if ($lastError && in_array($lastError['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE)))
		{
			ob_get_length() && ob_end_flush();
			exit();
		}
	}

	/**
	 * _autoload cache
	 * @var array
	 */
	static protected $_autoloadCache = array();

	/**
	 * Callback function
	 * @param string $class path to class file
	 * @return mixed
	 */
	static public function _autoload($class)
	{
		$class = basename($class);

		if (isset(self::$_autoloadCache[$class]))
		{
			return self::$_autoloadCache[$class];
		}

		self::$_logged && $fBeginTime = Core::getmicrotime();

		// Cut _Model if check module available
		$classCheck = substr($class, -6) == '_Model'
			? strtolower(substr($class, 0, -6))
			: $class;

		$return = FALSE;

		if (isset(self::$modulesList[$classCheck])
			&& self::$modulesList[$classCheck]->active == 0)
		{
			self::$_autoloadCache[$class] = $return;
			return $return;
		}

		$path = self::$modulesPath . self::getClassPath($class);

		if (is_file($path))
		{
			include($path);
			$return = TRUE;
		}

		self::$_autoloadCache[$class] = $return;

		self::$_logged && self::$_loadModuleTime += Core::getmicrotime() - $fBeginTime;

		return $return;
	}

	/**
	 * Checks if module exists and is active
	 * @param string $moduleName module name
	 * @return boolean
	 */
	static public function moduleIsActive($moduleName)
	{
		return isset(self::$modulesList[$moduleName]) && self::$modulesList[$moduleName]->active == 1;
	}

	/**
	 * Callback function
	 * @param Exception $exception
	 */
	static public function _exception($exception)
	{
		echo "Exception: ", $exception->getMessage(), "\n";
	}

	/**
	 * Returns a string produced according to the formatting string $key.
	 * @param string $key source string
	 *
	 * <code>
	 * echo Core::_('constant.name', 'value1', 'value2');
	 * // Same code
	 * // echo sprintf(Core::_('constant.name'), 'value1', 'value2');
	 * </code>
	 * @see Core_I18n::get()
	 */
	static public function _($key)
	{
		$args = func_get_args();

		if (count($args) > 1)
		{
			// Shift the first element off
			$key = array_shift($args);
			$value = Core_I18n::instance()->get($key);
			array_unshift($args, $value);

			foreach ($args as $argKey => $argValue)
			{
				$argKey > 0 && $args[$argKey] = htmlspecialchars($argValue);
			}

			return call_user_func_array('sprintf', $args);
		}

		return Core_I18n::instance()->get($key);
	}

	/**
	 * Site LNG
	 */
	static protected $_lng = NULL;

	/**
	 * Get Site Language
	 * @return string
	 */
	static public function getLng()
	{
		return self::$_lng;
	}

	/**
	 * Set Site Language
	 * @param sting $lng
	 */
	static public function setLng($lng)
	{
		self::$_lng = $lng;
	}

	/**
	 * Initialize constants for site
	 * @param Site_Model $oSite site
	 * @hostcms-event Core.onBeforeInitConstants
	 * @hostcms-event Core.onAfterInitConstants
	 */
	static public function initConstants(Site_Model $oSite)
	{
		Core_Event::notify('Core.onBeforeInitConstants', $oSite);

		!defined('UPLOADDIR') && define('UPLOADDIR', $oSite->uploaddir);

		define('SITE_LOCAL', $oSite->locale);

		if (!defined('ALLOW_SET_LOCALE') || ALLOW_SET_LOCALE)
		{
			setlocale(LC_ALL, SITE_LOCAL);
			setlocale(LC_NUMERIC, 'C'); // POSIX depends on OS settings
		}

		// Временная зона сайта
		$timezone = trim($oSite->timezone);
		if ($timezone != '')
		{
			date_default_timezone_set($timezone);
			define('SITE_TIMEZONE', $timezone);
		}

		// Язык
		self::setLng($oSite->lng);
		define('SITE_LNG', $oSite->lng);

		// Кодировка
		define('SITE_CODING', $oSite->coding);

		// Максимальный размер в одном из измерений при преобразовании загруженных изображений (малое изображение)
		define('MAX_SIZE_LOAD_IMAGE', $oSite->max_size_load_image);
		// Максимальный размер в одном из измерений при преобразовании загруженных изображений (большое изображение)
		define('MAX_SIZE_LOAD_IMAGE_BIG', $oSite->max_size_load_image_big);
		// Адрес эл. почты администратора
		define('EMAIL_TO', $oSite->getFirstEmail());

		// Права доступа к директории
		define('CHMOD', octdec($oSite->chmod)); // octdec - преобразование 8-ричного в 10-тичное

		// Права доступа к файлу
		define('CHMOD_FILE', octdec($oSite->files_chmod)); // octdec - преобразование 8-ричного в 10-тичное

		// Формат вывода даты
		define('DATE_FORMAT', $oSite->date_format);

		// Формат вывода даты и времени
		define('DATE_TIME_FORMAT', $oSite->date_time_format);

		// Обработка 404 ошибки
		define('ERROR404_STRUCTURE_ID', $oSite->error404);

		// Обработка 403 ошибки
		define('ERROR403_STRUCTURE_ID', $oSite->error403);

		// Число уровней вложенности для UPLOADDIR
		define('SITE_NESTING_LEVEL', $oSite->nesting_level);

		// Объявляем константу SITE_ERROR
		$error_level = trim($oSite->error);
		@eval("define('SITE_ERROR', $error_level);");

		// если произошла ошибка в объявлении константы, пишем ее значение по умолчанию
		// иначе определяем по умолчанию
		!defined('SITE_ERROR') && define('SITE_ERROR', E_ERROR);

		// Изменяем уровень вывода ошибок
		error_reporting(SITE_ERROR);

		Core_Event::notify('Core.onAfterInitConstants', $oSite);
	}

	/**
	 * Checks if HTTPS is used
	 * @return boolean
	 */
	static public function httpsUses()
	{
		return Core_Array::get($_SERVER, 'SERVER_PORT') == 443 || Core_Array::get($_SERVER, 'HTTP_PORT') == 443
			|| strtolower(Core_Array::get($_SERVER, 'HTTPS')) == 'on' || Core_Array::get($_SERVER, 'HTTPS') == '1'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_PROTO')) == 'https'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_X_SCHEME')) == 'https'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_X_HTTPS')) == 'on' || Core_Array::get($_SERVER, 'HTTP_X_HTTPS') == '1';
	}

	/**
	 * Checks if admin panel is possible to show
	 * @return boolean
	 */
	static public function checkPanel()
	{
		return (!defined('ALLOW_PANEL') || ALLOW_PANEL) && Core_Session::isStarted() && Core_Auth::logged();
	}

	/**
	 * Checks if function $function_name is enabled
	 * @param string $function_name name of the function
	 * @return boolean
	 */
	static public function isFunctionEnable($function_name)
	{
		$disabled = explode(',', str_replace(' ', '', ini_get('disable_functions')));
		return !in_array($function_name, $disabled);
	}

	/**
	 * Get microtime
	 * @return float
	 */
	static public function getmicrotime()
	{
		/*list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);*/
		return microtime(TRUE);
	}

	/**
	 * 64 bit to 32
	 * @param int $int
	 * @return int
	 */
	static public function convert64b32($int)
	{
		/*	11111111111111111111111111111111 10000011110111001110111110110111
			XOR
			11111111111111111111111111111111 00000000000000000000000000000000 */
		if ($int > 2147483647 || $int < -2147483648)
		{
			$int = $int ^ 18446744069414584320;
		}

		return $int;
	}

	/**
	 * Get CRC32 from source string
	 * @param string $value value
	 * @return int
	 */
	static public function crc32($value)
	{
		return self::convert64b32(crc32($value));
	}

	/**
	 * Parse URL and set controller properties
	 * @return Core::$url
	 */
	static public function parseUrl()
	{
		$aDomain = explode(':', strtolower(Core_Array::get($_SERVER, 'HTTP_HOST', '')));

		if (strlen($aDomain[0]))
		{
			$scheme = /*(Core_Array::get($_SERVER, 'HTTPS') == 'on' || Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_PROTO') == 'https')*/
				self::httpsUses() ? 'https' : 'http';
			
			$sUrl = $scheme . '://' . $aDomain[0];
			if (!empty($_SERVER['HTTP_X_ORIGINAL_URL']))
			{
				$sUrl .= $_SERVER['HTTP_X_ORIGINAL_URL'];
			}
			elseif (!empty($_SERVER['HTTP_X_REWRITE_URL']))
			{
				$sUrl .= $_SERVER['HTTP_X_REWRITE_URL'];
			}
			else
			{
				$sUrl .= $_SERVER['REQUEST_URI'];
			}

			self::$url = parse_url($sUrl) + array(
				'scheme' => 'http',
				'host' => '',
				'path' => ''
			);

			// Decode parts of URL
			foreach (self::$url as $key => $value)
			{
				$value = urldecode($value);

				// В перечень не включаем ASCII, т.к. тогда просто англ. текст будет определятся как ASCII
				if (strtoupper(mb_detect_encoding($value, 'UTF-8', TRUE)) != 'UTF-8')
				{
					$value = trim(mb_convert_encoding($value, 'UTF-8', 'windows-1251'));

					// Если текст не в UTF-8, заменяем $_GET
					if ($key == 'query')
					{
						if (function_exists('mb_parse_str'))
						{
							mb_parse_str($value, $_GET);
							$_REQUEST = $_GET + $_REQUEST;
						}
					}
				}

				self::$url[$key] = $value;
			}

			// Исключаем редирект /index.php --> /index.php/ для IIS
			if (strtolower(Core_Array::get(self::$url, 'path')) == '/index.php' && self::isIIS())
			{
				self::$url['path'] = '/';
			}
		}

		return self::$url;
	}

	/**
	 * Check if server is Microsoft IIS
	 * @return boolean
	 */
	static public function isIIS()
	{
		return strpos(strtolower(Core_Array::get($_SERVER, 'SERVER_SOFTWARE')), 'microsoft-iis') !== FALSE;
	}

	/**
	 * Get HostCMS fingerprint
	 * @return string
	 */
	static public function xPoweredBy()
	{
		return 'HostCMS ' . self::crc32(CMS_FOLDER) . ' ' . self::crc32(Core_Array::get(self::$config->get('core_hostcms'), 'hostcms')) . ' ' . self::crc32(CURRENT_VERSION);
	}

	/**
	 * Get PCRE version
	 * @return string
	 */
	static public function getPcreVersion()
	{
		defined('PCRE_VERSION')
			? list($version) = explode(' ', constant('PCRE_VERSION'))
			: $version = NULL;

		return $version;
	}

	/**
	 * Set cookie
	 * @param string $name cookie name
	 * @param string $value cookie value
	 * @param int $expire cookie expire date
	 * @param string $path cookie path
	 * @param string $domain cookie domain
	 * @param boolean $secure cookie secure
	 * @param boolean $httponly http only
	 * @param boolean $replace replace exists cookie
	 */
	static public function setCookie($name, $value, $expire = 0, $path = '', $domain = '', $secure = FALSE, $httponly = FALSE, $replace = FALSE)
	{
		header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
		 . (empty($expire) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $expire) . ' GMT')
		 . (empty($path) ? '' : '; path=' . $path)
		 . (empty($domain) ? '' : '; domain=' . $domain)
		 . (!$secure ? '' : '; secure')
		 . (!$httponly ? '' : '; HttpOnly'), $replace);
	}

	/**
	 * Show headers and JSON
	 * @param mixed $content
	 */
	static public function showJson($content)
	{
		header('Pragma: no-cache');
		header('Cache-Control: private, no-cache');
		header('Content-Disposition: inline; filename="files.json"');
		header('Vary: Accept');

		if (is_null(Core_Array::get($_SERVER, 'HTTP_ACCEPT'))
			|| strpos(Core_Array::get($_SERVER, 'HTTP_ACCEPT', ''), 'application/json') !== FALSE)
		{
			header('Content-type: application/json; charset=utf-8');
		}
		else
		{
			header('X-Content-Type-Options: nosniff');
			header('Content-type: text/plain; charset=utf-8');
		}

		// bug in Chrome
		//header("Content-Length: " . strlen($content));
		echo json_encode($content);

		exit();
	}
}