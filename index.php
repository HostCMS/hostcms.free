<?php
/**
 * HostCMS frontend.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */

if (is_dir('install/') && is_file('install/index.php'))
{
	// Install location
	header('Location: /install/');
	exit();
}

require_once('bootstrap.php');

// Observers
if (Core_Auth::logged())
{
	Core_Event::attach('Core_DataBase.onBeforeQuery', array('Core_Database_Observer', 'onBeforeQuery'));
	Core_Event::attach('Core_DataBase.onAfterQuery', array('Core_Database_Observer', 'onAfterQuery'));
}

if (!((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & (~1835217467)))
{
	$oSite = Core_Entity::factory('Site');
	$oSite->queryBuilder()
		->where('active', '=', 1);
	$count = $oSite->getCount();

	if ($count > 2)
	{
		Core_Router::add('sitecount', '()')
			->controller('Core_Command_Controller_Sitecount')
			->execute()
			->header('X-Powered-By', Core::xPoweredBy())
			->sendHeaders()->showBody();
		exit();
	}
}

// XSLT not found
if (!class_exists('DomDocument') && !class_exists('XsltProcessor')
	//&& !function_exists('xslt_create')
	//&& !function_exists('domxml_xslt_stylesheet')
)
{
	Core_Router::add('xslt_not_found', '()')
		->controller('Core_Command_Controller_Xslt')
		->execute()
		->header('X-Powered-By', Core::xPoweredBy())
		->sendHeaders()->showBody();

	exit();
}

//if (!((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & (~2983120818)))
if (!((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & (~-1311846478)))
{
	$oSite = Core_Entity::factory('Site');
	$oSite->queryBuilder()
		->where('active', '=', 1);
	$count = $oSite->getCount();

	if ($count > 1)
	{
		Core_Router::add('sitecount', '()')
			->controller('Core_Command_Controller_Sitecount')
			->execute()
			->header('X-Powered-By', Core::xPoweredBy())
			->sendHeaders()->showBody();
		exit();
	}
}

Core::parseUrl();

$oSite_Alias = Core_Entity::factory('Site_Alias')->findAlias(Core::$url['host']);

if (is_null($oSite_Alias))
{
	// Site not found
	Core_Router::add('domain_not_found', '()')
		->controller('Core_Command_Controller_Domain_Not_Found')
		->execute()
		->header('X-Powered-By', Core::xPoweredBy())
		->sendHeaders()->showBody();
	exit();
}

$oSite = $oSite_Alias->Site;

define('CURRENT_SITE', $oSite->id);
Core::initConstants($oSite);

$d = explode('.', Core::$url['host']);
$e = $oSite->getKeys();
do {
	$b = implode('.', $d);

	foreach ($e as $sKey)
	{
		$a = explode('-', $sKey) + array(0, 0, 0, 0);

		strlen($a[2]) == 8 && strlen($a[3]) == 8 && !(Core::convert64b32(Core::convert64b32(hexdec($a[3])) ^ abs(Core::crc32($b))) ^ ~(Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms')) & abs(Core::crc32($b)) ^ Core::convert64b32(hexdec($a[2])))) && Core::$url['key'] = $sKey;
	}
	array_shift($d);
} while(count($d) > 1);

if (((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & 1176341605) && !Core_Array::get(Core::$url, 'key'))
{
	Core_Router::add('key_not_found', '()')
		->controller('Core_Command_Controller_Key_Not_Found')
		->execute()
		->header('X-Powered-By', Core::xPoweredBy())
		->sendHeaders()->showBody();

	exit();
}

Core_Router::add('robots.txt', '/robots.txt')
	->controller('Core_Command_Controller_Robots');

Core_Router::add('favicon.ico', '/favicon.ico')
	->controller('Core_Command_Controller_Favicon');

Core_Router::add('favicon.png', '/favicon.png')
	->controller('Core_Command_Controller_Favicon');

Core_Router::add('favicon.gif', '/favicon.gif')
	->controller('Core_Command_Controller_Favicon');

Core_Router::add('favicon.svg', '/favicon.svg')
	->controller('Core_Command_Controller_Favicon');

Core_Router::add('edit-in-place.php', '/edit-in-place.php')
	->controller('Core_Command_Controller_Edit_In_Place');

Core_Router::add('hostcms-benchmark.php', '/hostcms-benchmark.php')
	->controller('Core_Command_Controller_Benchmark');

Core_Router::add('sitemap.xml', '/sitemap.xml')
	->controller('Core_Command_Controller_Sitemap');

Core_Router::add('default', '()')
	->controller('Core_Command_Controller_Default');

// Site is closed, after Core_Router::add('default', '()')!
if ($oSite->active == 0 && !Core_Auth::logged())
{
	Core_Router::add('site_is_closed', '()')
		->controller('Core_Command_Controller_Site_Closed')
		->execute()
		->header('X-Powered-By', Core::xPoweredBy())
		->sendHeaders()->showBody();

	exit();
}

if ($oSite_Alias->redirect)
{
	$oDefault_Site_Alias = $oSite_Alias->Site->getCurrentAlias();

	if (!is_null($oDefault_Site_Alias)
			&& $oSite_Alias->alias_name_without_mask != $oDefault_Site_Alias->alias_name_without_mask
	)
	{
		$oCore_Response = new Core_Response();
		$oCore_Response
			->status(301)
			->header('X-Powered-By', Core::xPoweredBy())
			->header('Location', str_replace(array("\r", "\n", "\0"), '', ($oSite->https ? 'https' : Core::$url['scheme']) . '://'
					. $oDefault_Site_Alias->alias_name_without_mask
					. Core::$url['path']
					. (isset(Core::$url['query']) ? '?' . Core::$url['query'] : '')
				)
			)
			->sendHeaders();

		exit();
	}
}

if (strtoupper($oSite->coding) != 'UTF-8')
{
	function iconvArray(&$array, $in_charset, $out_charset = 'UTF-8')
	{
		if (is_array($array) && count($array) > 0)
		{
			foreach ($array as $key => $value)
			{
				!is_array($value)
					? $array[$key] = @iconv($in_charset, $out_charset . "//IGNORE//TRANSLIT", $value)
					: iconvArray($array[$key], $in_charset, $out_charset);
			}
		}
	}
	// GET has already changed, see $bUtf8
	//iconvArray($_GET, $oSite->coding);
	iconvArray($_POST, $oSite->coding);
	iconvArray($_REQUEST, $oSite->coding);
	iconvArray($_COOKIES, $oSite->coding);
	iconvArray($_FILES, $oSite->coding);
}

if (!empty($_SESSION['current_lng']))
{
	Core_I18n::instance()->setLng(strval($_SESSION['current_lng']));
}

// Check IP addresses
$bBlockedIp = $bBlockedFilter = $bBlockedVisitorFilter = $bCaptchaVisitorFilter = FALSE;

if (Core::moduleIsActive('ipaddress'))
{
	$sRemoteAddr = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
	$aIp = array($sRemoteAddr);

	$HTTP_X_FORWARDED_FOR = Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_FOR');
	if (!is_null($HTTP_X_FORWARDED_FOR) && $sRemoteAddr != $HTTP_X_FORWARDED_FOR)
	{
		$aIp[] = $HTTP_X_FORWARDED_FOR;
	}

	Core_Event::notify('Ipaddress.onIpIsBlocked', NULL, array($aIp));
	$eventResult = Core_Event::getLastReturn();

	$bBlockedIp = !is_bool($eventResult)
		? Ipaddress_Controller::instance()->isBlocked($aIp)
		: $eventResult;

	Core_Event::notify('Ipaddress.onFilterIsBlocked');
	$eventResult = Core_Event::getLastReturn();

	$bBlockedFilter = !is_bool($eventResult)
		? !$bBlockedIp && Ipaddress_Filter_Controller::instance()->isBlocked()
		: $eventResult;
}

$userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');
$requestURI = Core_Array::get($_SERVER, 'REQUEST_URI', '', 'str');

// Static files that should be ignored
Core_Event::notify('Ipaddress.isStaticFile', NULL, array($requestURI));
$eventResult = Core_Event::getLastReturn();

$bStaticFiles = is_bool($eventResult)
	? $eventResult
	: in_array($requestURI, array('/favicon.ico', '/favicon.gif', '/favicon.png', '/favicon.svg', '/robots.txt', '/hostcms-benchmark.php'))
		|| strpos($requestURI, '/apple-touch-icon') === 0
		|| strpos($requestURI, '/.well-known') === 0;

if (!$bBlockedIp && !$bBlockedFilter && !$bStaticFiles && Core::moduleIsActive('counter'))
{
	if (Core::moduleIsActive('ipaddress') && !Core::checkBot($userAgent))
	{
		$oIpaddress_Visitor_Filter_Controller = Ipaddress_Visitor_Filter_Controller::instance();
		$bUseIpaddressVisitors = count($oIpaddress_Visitor_Filter_Controller->getFilters());

		if ($bUseIpaddressVisitors)
		{
			// Save and reset timezone to GMT
			$timezone = date_default_timezone_get();
			date_default_timezone_set('GMT');

			$timeGmt = time();

			$oIpaddress_Visitor = Ipaddress_Visitor_Controller::getCurrentIpaddressVisitor();

			// _h_tag устанавливаем до учета статистики
			$bSecure = Core::httpsUses();
			Core_Cookie::set('_h_tag', $oIpaddress_Visitor->id, array('expires' => $timeGmt + 2592000, 'path' => '/', 'samesite' => $bSecure ? 'None' : 'Lax', 'secure' => $bSecure));
			$_COOKIE['_h_tag'] = $oIpaddress_Visitor->id;

			// Restore timezone
			date_default_timezone_set($timezone);
		}
	}
	else
	{
		$bUseIpaddressVisitors = FALSE;
	}

	// Статистика учитывается для незаблокированных
	Counter_Controller::instance()
		->site($oSite)
		->referrer(Core_Array::get($_SERVER, 'HTTP_REFERER'))
		->page((Core::httpsUses() ? 'https' : 'http') . '://' . strtolower(Core_Array::get($_SERVER, 'HTTP_HOST')) . $requestURI)
		->ip(Core::getClientIp())
		->userAgent($userAgent)
		->counterId(0)
		->applyData();

	// Проверку на посетителей делаем после учета данных статистики текущего посещения, а также если заданы фильтры.
	if ($bUseIpaddressVisitors)
	{
		// Нет расчитанного результата для посетителя
		if ($oIpaddress_Visitor->result_expired == 0 || $oIpaddress_Visitor->result_expired < $timeGmt)
		{
			// 0 - забанен, 1 - разрешен
			if ($oIpaddress_Visitor_Filter_Controller->isBlocked())
			{
				$checkResult = $oIpaddress_Visitor_Filter_Controller->getBlockMode() == 0
					? 0 // Block
					: 2; // Captcha
			}
			else
			{
				// Не заблокирован
				$checkResult = 1;
			}

			$oIpaddress_Visitor->result = $checkResult;
			$oIpaddress_Visitor->ipaddress_visitor_filter_id = $oIpaddress_Visitor_Filter_Controller->getFilterId();

			if ($oIpaddress_Visitor->result)
			{
				// Результат проверки на 5 минут
				$oIpaddress_Visitor->result_expired = $timeGmt + 60 * 5;
			}
			else
			{
				// Результат проверки на $hours дней
				$hours = $oIpaddress_Visitor_Filter_Controller->getHoursToBlock();
				$oIpaddress_Visitor->result_expired = $timeGmt + 3600 * ($hours > 0 ? $hours : 24);
			}

			$oIpaddress_Visitor->save();
		}

		$bBlockedVisitorFilter = $oIpaddress_Visitor->result == 0;
		$bCaptchaVisitorFilter = $oIpaddress_Visitor->result == 2;
	}
}

if (Core::moduleIsActive('ipaddress'))
{
	/*Core_Log::instance()->clear()
		->status(Core_Log::$MESSAGE)
		->write($bBlockedIp
			? Core::_('Ipaddress.error_log_blocked_ip', implode(',', $aIp))
			: Core::_('Ipaddress.error_log_blocked_useragent', implode(',', $aIp), $userAgent)
		);*/

	if ($bBlockedIp || $bBlockedFilter || $bBlockedVisitorFilter)
	{
		// IP blocked
		Core_Router::add('ip_blocked', '()')
			->controller('Core_Command_Controller_Ip_Blocked')->execute()
			->header('X-Powered-By', Core::xPoweredBy())
			->sendHeaders()
			->showBody();

		exit();
	}

	if ($bCaptchaVisitorFilter)
	{
		if ($oSite->error_bot)
		{
			// Show captcha
			Core_Router::add('check-bots', '()')
				->setUri(Core::$url['path'])
				->controller('Core_Command_Controller_Check_Bots')->execute()
				->header('X-Powered-By', Core::xPoweredBy())
				->sendHeaders()
				->showBody();

			exit();
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Captcha is not configured, choose bot-page in the site settings!');
		}
	}
}

Core_Router::factory(Core::$url['path'])
	->execute()
	->compress()
	->header('X-Powered-By', Core::xPoweredBy())
	->sendHeaders()
	->showBody();

exit();