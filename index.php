<?php
/**
 * HostCMS frontend.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

if (is_dir('install/') && is_file('install/index.php'))
{
	// Install location
	header('Location: /install/');
	exit();
}

require_once('bootstrap.php');

// Observers
Core_Event::attach('Core_DataBase.onBeforeQuery', array('Core_Database_Observer', 'onBeforeQuery'));
Core_Event::attach('Core_DataBase.onAfterQuery', array('Core_Database_Observer', 'onAfterQuery'));

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
if (!function_exists('xslt_create')
	&& !function_exists('domxml_xslt_stylesheet')
	&& !class_exists('DomDocument') && !class_exists('XsltProcessor'))
{
	Core_Router::add('xslt_not_found', '()')
		->controller('Core_Command_Controller_Xslt')
		->execute()
		->header('X-Powered-By', Core::xPoweredBy())
		->sendHeaders()->showBody();

	exit();
}

if (!((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & (~2983120818)))
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
			->header('Location', ($oSite->https ? 'https' : Core::$url['scheme']) . '://'
				. $oDefault_Site_Alias->alias_name_without_mask
				. Core::$url['path']
				. (isset(Core::$url['query']) ? '?' . Core::$url['query'] : '')
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

Core_I18n::instance()->setLng(!empty($_SESSION['current_lng']) ? strval($_SESSION['current_lng']) : DEFAULT_LNG);

// Check IP addresses
$sRemoteAddr = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
$aIp = array($sRemoteAddr);

$HTTP_X_FORWARDED_FOR = Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_FOR');
if (!is_null($HTTP_X_FORWARDED_FOR) && $sRemoteAddr != $HTTP_X_FORWARDED_FOR)
{
	$aIp[] = $HTTP_X_FORWARDED_FOR;
}

if (Core::moduleIsActive('ipaddress'))
{
	$oIpaddress_Controller = new Ipaddress_Controller();

	$bBlocked = $oIpaddress_Controller->isBlocked($aIp);

	//$aArray = array();

	if ($bBlocked/* || in_array(md5(Core_Array::get($_SERVER, 'HTTP_HOST')), $aArray)*/)
	{
		// IP address found
		Core_Router::add('ip_blocked', '()')
			->controller('Core_Command_Controller_Ip_Blocked')->execute()
			->header('X-Powered-By', Core::xPoweredBy())
			->sendHeaders()
			->showBody();

		exit();
	}
}

Core_Router::factory(Core::$url['path'])
	->execute()
	->compress()
	->header('X-Powered-By', Core::xPoweredBy())
	->sendHeaders()
	->showBody();

exit();