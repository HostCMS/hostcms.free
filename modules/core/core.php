<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		try
		{
			Core_DataBase::instance()->connect();

			// Constants init
			$oConstants = Core_Entity::factory('Constant');
			$oConstants
				->queryBuilder()
				->where('active', '=', 1);

			$aConstants = $oConstants->findAll();
			foreach ($aConstants as $oConstant)
			{
				$oConstant->define();
			}

			!defined('TMP_DIR') && define('TMP_DIR', 'hostcmsfiles/tmp/');
			!defined('DEFAULT_LNG') && define('DEFAULT_LNG', 'ru');
			!defined('BACKUP_DIR') && define('BACKUP_DIR', CMS_FOLDER . 'hostcmsfiles' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR);

			// Права доступа к директории
			define('CHMOD', self::$mainConfig['dirMode']);

			// Права доступа к файлу
			define('CHMOD_FILE', self::$mainConfig['fileMode']);

			// Если есть ID сессии и сессия еще не запущена - то стартуем ее
			// Запускается здесь для получения языка из сессии.
			//Core_Session::hasSessionId() && Core_Session::start();

			self::$_logged = Core_Auth::logged();

			// Before _loadModuleList()
			if (self::$_logged && isset($_REQUEST['lng_value']))
			{
				Core_Auth::setCurrentLng($_REQUEST['lng_value']);
			}

			self::_loadModuleList();
		}
		catch (Exception $e)
		{
			// Service Unavailable
			Core_Response::sendHttpStatusCode(503);

			$file503 = CMS_FOLDER . self::$mainConfig['errorDocument503'];

			if (is_file($file503))
			{
				include($file503);
			}
			else
			{
				echo $e->getMessage();
			}

			die();
		}

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
			'timePickerFormat' => 'HH:mm:ss',
			'availableExtension' => array ('JPG', 'JPEG', 'GIF', 'PNG', 'WEBP', 'PDF', 'ZIP', 'DOC', 'DOCX', 'XLS', 'XLSX'),
			'defaultCache' => 'file',
			'timezone' => 'America/Los_Angeles',
			'translate' => TRUE,
			'chat' => TRUE,
			'switchSelectToAutocomplete' => 100,
			'autocompleteItems' => 10,
			'cms_folders' => NULL,
			'dirMode' => 0755,
			'fileMode' => 0644,
			'errorDocument503' => 'hostcmsfiles/503.htm',
			'compressionJsDirectory' => 'hostcmsfiles/js/',
			'compressionCssDirectory' => 'hostcmsfiles/css/',
			'sitemapDirectory' => 'hostcmsfiles/sitemap/',
			'banAfterFailedAccessAttempts' => 5,
			'session' => array(
				'driver' => 'database',
				'class' => 'Core_Session_Database',
				'subdomain' => TRUE,
			),
			'headers' => array(
				'X-Content-Type-Options' => 'nosniff',
				'X-XSS-Protection' => '1;mode=block',
			),
			'backendSessionLifetime' => 14400,
			'backendContentSecurityPolicy' => "default-src 'self' www.hostcms.ru www.youtube.com youtube.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: *.cloudflare.com *.kaspersky-labs.com; img-src 'self' chart.googleapis.com data: blob: www.hostcms.ru; font-src 'self'; connect-src 'self' blob:; style-src 'self' 'unsafe-inline'"
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

		// First of all load the list of modules
		foreach ($aModules as $oModule)
		{
			self::$modulesList[$oModule->path] = $oModule;
		}

		// Then load each module
		foreach ($aModules as $oModule)
		{
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
	 * @param int $errno код ошибки - E_ERROR и т.д.
	 * @param string $msg сообщение об ошбике
	 * @param string $file имя файла, в котором произошла ошибка
	 * @param int $line строка, в котором произошла ошибка
	 */
	static public function _error($errno, $msg, $file, $line)
	{
		// Не выводим ошибки, если режим сообщения об ошибках отключен
		// или код ошибки меньше кода вывода ошибок
		$error_reporting = error_reporting();

		// The @ operator will no longer silence fatal errors (E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE). Error handlers that expect error_reporting to be 0 when @ is used, should be adjusted to use a mask check
		$bShowError = PHP_VERSION_ID >= 80000
			? (error_reporting() & $errno) && $error_reporting >= $errno
			: $error_reporting && $error_reporting >= $errno;

		// The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING independent of where they were raised, and most of E_STRICT raised in the file where set_error_handler() is called.
		$error_level = array(
			//E_ERROR => 4,
			E_WARNING => 3,
			//E_PARSE => 4,
			E_NOTICE => 2,
			//E_CORE_ERROR => 4,
			//E_CORE_WARNING => 4,
			//E_COMPILE_ERROR => 4,
			//E_COMPILE_WARNING => 4,
			E_USER_ERROR => 4,
			E_USER_WARNING => 3,
			E_USER_NOTICE => 2,
			2048 => -1, // 0
			8192 => 2,
		);

		// Название типа ошибки
		$error_name = array(
			//E_ERROR => Core::_('Core.E_ERROR'),
			E_WARNING => Core::_('Core.E_WARNING'),
			//E_PARSE => Core::_('Core.E_PARSE'),
			E_NOTICE => Core::_('Core.E_NOTICE'),
			//E_CORE_ERROR => Core::_('Core.E_CORE_ERROR'),
			//E_CORE_WARNING => Core::_('Core.E_CORE_WARNING'),
			//E_COMPILE_ERROR => Core::_('Core.E_COMPILE_ERROR'),
			//E_COMPILE_WARNING => Core::_('Core.E_COMPILE_WARNING'),
			E_USER_ERROR => Core::_('Core.E_USER_ERROR'),
			E_USER_WARNING => Core::_('Core.E_USER_WARNING'),
			E_USER_NOTICE => Core::_('Core.E_USER_NOTICE'),
			2048 => Core::_('Core.E_STRICT'),
			8192 => Core::_('Core.E_DEPRECATED'), // since PHP 7.4 E_DEPRECATED detected during parsing, not at runtime
		);

		$current_error_level = isset($error_level[$errno])
			? $error_level[$errno]
			: 0;

		// Определяем название ошибки (Error/Warning/etc)
		$current_error_name = isset($error_name[$errno])
			? $error_name[$errno]
			: 'Undefined error';

		$aStack = array();

		$aDebugTrace = Core::debugBacktrace();
		foreach ($aDebugTrace as $aTrace)
		{
			$aStack[] = Core::_('Core.error_log_message_stack', $aTrace['file'], $aTrace['line']);
		}

		$sStack = implode(",\n", $aStack);

		// Если ошибка уровня E_USER_ERROR
		$message = $errno == E_USER_ERROR
			? Core::_('Core.error_log_add_message')
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
		if ($errno == E_ERROR || $errno == E_USER_ERROR)
		{
			exit();
		}
	}

	/**
	 * Get debug trace
	 * @return array
	 */
	static public function debugBacktrace()
	{
		$aDebugTrace = array();

		if (function_exists('debug_backtrace'))
		{
			$debug_backtrace = debug_backtrace();
			array_shift($debug_backtrace);

			foreach ($debug_backtrace as $history)
			{
				if (isset($history['file']) && isset($history['line']))
				{
					$history['file'] = self::cutRootPath($history['file']);

					$aDebugTrace[] = array(
						'file' => $history['file'],
						'line' => $history['line'],
						'function' => (isset($history['class']) && isset($history['type']) ? $history['class'] . $history['type'] : '') . $history['function']
					);
				}
			}
		}

		return $aDebugTrace;
	}

	/**
	 * Cut CMS_FOLDER from $path
	 * @param string $path path
	 * @return string
	 */
	static public function cutRootPath($path)
	{
		if (strpos((string) $path, dirname(CMS_FOLDER)) === 0)
		{
			$path = substr($path, strlen(CMS_FOLDER));
		}

		return $path;
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
		// $class = basename($class);
		$aNamespaces = explode('\\', $class);
		$aNamespaces = array_map('basename', $aNamespaces);

		$class = array_pop($aNamespaces);

		$aClassName = explode('_', strtolower($class));

		$sFileName = array_pop($aClassName);

		// Path from Namespace
		$path = empty($aNamespaces)
			? ''
			: implode(DIRECTORY_SEPARATOR, $aNamespaces);

		// If class name doesn't have '_'
		$path .= empty($aClassName) && empty($aNamespaces)
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
		class_exists('Core_Session') && Core_Session::close();

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
	 * @param boolean $convertSpecialCharacters Convert special characters, default TRUE
	 *
	 * <code>
	 * // with convert special characters
	 * echo Core::_('constant.name', 'value1', 'value2');
	 * // without convert special characters
	 * echo Core::_('constant.name', 'value1', 'value2', FALSE);
	 * // Same code
	 * // echo sprintf(Core::_('constant.name'), 'value1', 'value2');
	 * </code>
	 * @return string
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

			$convertSpecialCharacters = is_bool(end($args)) ? array_pop($args) : TRUE;

			if ($convertSpecialCharacters)
			{
				foreach ($args as $argKey => $argValue)
				{
					$argKey > 0 && $args[$argKey] = htmlspecialchars(strval($argValue));
				}
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

		// Технический адрес эл. почты
		define('ERROR_EMAIL', $oSite->getErrorEmail());

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
			|| strtolower(Core_Array::get($_SERVER, 'HTTPS', '')) == 'on' || Core_Array::get($_SERVER, 'HTTPS') == '1'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_PROTO', '')) == 'https'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_X_SCHEME', '')) == 'https'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_X_HTTPS', '')) == 'on' || Core_Array::get($_SERVER, 'HTTP_X_HTTPS') == '1'
			|| strtolower(Core_Array::get($_SERVER, 'HTTP_CF_VISITOR', '')) == '{"scheme":"https"}';
	}

	/**
	 * Checks if admin panel is possible to show
	 * @return boolean
	 */
	static public function checkPanel()
	{
		return (!defined('ALLOW_PANEL') || ALLOW_PANEL) && Core_Auth::logged();
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
	 * Generate an unique ID
	 * @param int $bytes default 16
	 * @return string
	 */
	static public function generateUniqueId($bytes = 16)
	{
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$raw = openssl_random_pseudo_bytes($bytes);
		}
		elseif (function_exists('random_bytes'))
		{
			$raw = random_bytes($bytes);
		}
		else
		{
			$raw = hash($bytes == 16 ? 'md5' : 'sha256', uniqid(strval(mt_rand()), TRUE), TRUE);
		}

		return bin2hex($raw);
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
		//var_dump($int); die();

		if (PHP_INT_SIZE > 4)
		{
			($int > 2147483647 || $int < -2147483648)
				&& $int = $int ^ -4294967296;
		}
		elseif (!is_int($int))
		{
			$int = intval($int);
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
	 * @return array Core::$url
	 * @hostcms-event Core.onAfterParseUrl
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

		Core_Event::notify('Core.onAfterParseUrl');

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
		return 'HostCMS ' . self::crc32(CMS_FOLDER) . ' ' . self::crc32(Core_Array::get(self::$config->get('core_hostcms'), 'hostcms')) . ' ' . self::crc32(self::getVersion());
	}

	/**
	 * Get HostCMS Version
	 * @return string
	 */
	static public function getVersion()
	{
		return defined('CURRENT_VERSION') ? CURRENT_VERSION : '7.0';
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
		if (!headers_sent())
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
		}

		// bug in Chrome
		//header("Content-Length: " . strlen($content));
		echo json_encode($content);

		exit();
	}

	/**
	 * Get Real Client Ip
	 * @return string
	 */
	static public function getClientIp()
	{
		// CF-Connecting-IP provides the client IP address, connecting to Cloudflare, to the origin web server.
		// This header will only be sent on the traffic from Cloudflare's edge to your origin webserver.
		if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		}
		elseif (isset($_SERVER['HTTP_DDG_CONNECTING_IP']))
		{
			return $_SERVER['HTTP_DDG_CONNECTING_IP'];
		}
		elseif (isset($_SERVER['HTTP_X_QRATOR_IP_SOURCE']))
		{
			return $_SERVER['HTTP_X_QRATOR_IP_SOURCE'];
		}

		return Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
	}

	/**
	* Проверка user-agent на принадлежность к ботам
	*
	* @param string $agent user-agent
	* @return bool
	*
	* <code>
	* <?php
	* $agent = 'YANDEX';
	*
	* $is_bot = Core::checkBot($agent);
	*
	* // Распечатаем результат
	* var_dump($is_bot);
	* ?>
	* </code>
	*/
	static public function checkBot($agent)
	{
		return is_string($agent)
			? (bool) preg_match('/http|bot|spide|craw|finder|curl|mail|yandex|rambler|seach|seek|site|sogou|yahoo|msnbot|snoopy|google|bing/iu', $agent)
			: FALSE;
	}

	/**
	 * Get callable name
	 * @param callable $callable
	 * @return string
	 */
	static public function getCallableName($callable)
	{
		switch (TRUE)
		{
			case is_string($callable) && strpos($callable, '::'):
				return '[static] ' . $callable;
			case is_string($callable):
				return '[function] ' . $callable;
			case is_array($callable) && is_object($callable[0]):
				return '[method] ' . get_class($callable[0])  . '->' . $callable[1];
			case is_array($callable):
				return '[static] ' . $callable[0]  . '::' . $callable[1];
			case $callable instanceof Closure:
				return '[closure]';
			case is_object($callable):
				return '[invokable] ' . get_class($callable);
			default:
				return '[unknown]';
		}
	}
}