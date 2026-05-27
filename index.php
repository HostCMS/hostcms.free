<?php
/**
 * HostCMS frontend.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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

$sRemoteAddr = Core::getClientIp();

// Check IP addresses
$bBlockedIp = $bBlockedFilter = $bBlockedVisitorFilter = $bCaptchaVisitorFilter = $bCheckBrowser = FALSE;

if (Core::moduleIsActive('ipaddress'))
{
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

$oIpaddress_Visitor = NULL;

if (!$bBlockedIp)
{
	$userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');
	$requestURI = urldecode(Core_Array::get($_SERVER, 'REQUEST_URI', '', 'str'));

	// Static files that should be ignored
	Core_Event::notify('Ipaddress.isStaticFile', NULL, array($requestURI));
	$eventResult = Core_Event::getLastReturn();

	$bStaticFiles = is_bool($eventResult)
		? $eventResult
		: in_array($requestURI, array('/favicon.ico', '/favicon.gif', '/favicon.png', '/favicon.svg', '/robots.txt', '/hostcms-benchmark.php'))
			|| strpos($requestURI, '/apple-touch-icon') === 0
			|| strpos($requestURI, '/.well-known') === 0;

	if (!$bBlockedFilter && !$bStaticFiles)
	{
		$bSearchEngineBot = Core::checkSearchEngineBot($userAgent);

		!$bSearchEngineBot
			&& $bCheckBrowser = $oSite->check_browser == 1;

		if (Core::moduleIsActive('counter'))
		{
			if (!$bSearchEngineBot && Core::moduleIsActive('ipaddress'))
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

			// Нет определенного $oIpaddress_Visitor или уже статус проверки браузера и первый запрос
			if (!$oIpaddress_Visitor || $oIpaddress_Visitor->result !== 3 || $oIpaddress_Visitor->visits == 1)
			{
				$oCounter_Controller = Counter_Controller::instance()
					->site($oSite)
					->referrer(urldecode(Core_Array::get($_SERVER, 'HTTP_REFERER', '', 'trim')))
					->page((Core::httpsUses() ? 'https' : 'http') . '://' . strtolower(Core_Array::get($_SERVER, 'HTTP_HOST')) . $requestURI)
					->counterId(0)
					->applyData();

				// Проверку на посетителей делаем после учета данных статистики текущего посещения, а также если заданы фильтры
				if ($bUseIpaddressVisitors)
				{
					// Нет расчитанного результата для посетителя
					if ($oIpaddress_Visitor->result_expired == 0 || $oIpaddress_Visitor->result_expired < $timeGmt)
					{
						Core_Event::notify('Ipaddress.onGetVisitorBlockMode', NULL, array($oIpaddress_Visitor));
						$eventResult = Core_Event::getLastReturn();

						if (!is_integer($eventResult))
						{
							if ($oIpaddress_Visitor_Filter_Controller->isBlocked())
							{
								// 0 - забанен, 1 - разрешен
								switch ($oIpaddress_Visitor_Filter_Controller->getBlockMode())
								{
									case 0:
										$checkResult = 0; // Block
									break;
									case 1:
										$checkResult = 2; // Captcha
									break;
									default:
										$checkResult = 1; // Не заблокирован
									break;
								}
							}
							else
							{
								// Не заблокирован
								$checkResult = 1;
							}
						}
						else
						{
							$checkResult = $eventResult;
						}

						$oIpaddress_Visitor->result = $checkResult;
						$oIpaddress_Visitor->ipaddress_visitor_filter_id = $oIpaddress_Visitor_Filter_Controller->getFilterId();

						if ($oIpaddress_Visitor->result)
						{
							// Результат проверки на 10 минут
							$oIpaddress_Visitor->result_expired = $timeGmt + 60 * 10;
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
					
					if ($oSite->check_browser)
					{
						// Браузер проверяем если для посетителя статус (1 - разрешен доступ, 3 - проверка браузера) и не было применено какого-то фильтра (например резрешительного или для капчи)
						// Т.е. посетитель не подошел ни под одни из проверок, заданных в фильтре и ему не была показана капча
						Core_Event::notify('Ipaddress.onCheckBrowser', NULL, array($oIpaddress_Visitor));
						$eventResult = Core_Event::getLastReturn();
						
						$bCheckBrowser = !is_bool($eventResult)
							? $oIpaddress_Visitor->result == 1 && !$oIpaddress_Visitor->ipaddress_visitor_filter_id || $oIpaddress_Visitor->result == 3
							: $eventResult;
					}
					else
					{
						$bCheckBrowser = FALSE;
					}
				}
				
				if (!$bBlockedVisitorFilter && !$bCaptchaVisitorFilter && !$bCheckBrowser)
				{
					// Учесть данные о визите и применить накопительную статистику за день
					$oCounter_Controller->applySummary();
				}
				/*else
				{
					// Откат частных данных о посещенной странице
					$oCounter_Controller->rollbackData();
				}*/
			}
		}
	}
}

if (Core::moduleIsActive('ipaddress'))
{
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

if ($bCheckBrowser)
{
	$cookieName = '_h_ct';

	$showChallenge = TRUE;

	if (isset($_COOKIE[$cookieName]))
	{
		$parts = explode('.', $_COOKIE[$cookieName], 2);
		
		if (count($parts) == 2)
		{
			list($clientHash, $payloadBase64) = $parts;
			$payloadJson = @base64_decode($payloadBase64);
			
			if ($payloadJson != '')
			{
				$payload = @json_decode($payloadJson, TRUE);

				if ($payload && isset($payload['fp'], $payload['bh'], $payload['ts']))
				{
					// 1. Проверка срока жизни токена
					$tokenAge = time() - intval($payload['ts']);
					
					if ($tokenAge > 0 && $tokenAge < Core_Command_Controller_Check_Challenge::$maxAge)
					{
						// 2. Явный запрет, если JS сообщил о наличии WebDriver (puppeteer, selenium)
						//if (!isset($payload['bot']) || $payload['bot'] === 0)
						//{
							// 3. Проверка целостности хеша (защита от подделки)
							$expectedCombinedCurrent = $payload['fp'] . '::' . $payload['bh'] . '::' . $payload['ts'] . '::' . Core_Command_Controller_Check_Challenge::getChallengeNonce(0);
							$expectedHashCurrent = hash('sha256', $expectedCombinedCurrent);

							// Проверяем также предыдущий период на случай, если запрос попал на стык периодов
							$expectedCombinedPrev = $payload['fp'] . '::' . $payload['bh'] . '::' . $payload['ts'] . '::' . Core_Command_Controller_Check_Challenge::getChallengeNonce(-1);
							$expectedHashPrev = hash('sha256', $expectedCombinedPrev);

							// Хеш совпал, кука не подделана и не перенесена с другого устройства
							if (hash_equals($expectedHashCurrent, $clientHash) || hash_equals($expectedHashPrev, $clientHash))
							{
								// Расшифровываем строку поведения: moves|distance|scrolls|scrollDelta|keys|elapsed
								$behaviorParts = explode('|', $payload['bh']);

								if (count($behaviorParts) === 11)
								{
									$moves = intval($behaviorParts[0]);
									$distance = intval($behaviorParts[1]);
									$keys = intval($behaviorParts[4]);
									
									$taps = intval($behaviorParts[5]);
									$touchMoves = intval($behaviorParts[6]);
									$wheelAttempts = intval($behaviorParts[7]);
									
									$elapsed = intval($behaviorParts[8]);
									$audio = strval($behaviorParts[9]); // e.g. supported-48000-2-suspended
									$fonts = intval($behaviorParts[10]);

									// Защита от ботов, которые пытаются сдать токен слишком быстро (быстрее 1.5 секунд)
									if ($elapsed >= 1350 && $fonts >= 6)
									{
										$userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');

										// Исключен macintosh, т.к. невозможно по UA отделить iPad от Mac Os, UA одинаковы
										$bDesktopBrowser = $userAgent == '' || preg_match('/^(?!.*(ipad|android|iphone|ipod|windows phone|blackberry|playbook|kindle|silk|mobile|iemobile|opera mini|touch|tablet)).*(windows nt|linux).*$/i', $userAgent) === 1;

										// Проверяем, мобильное ли это устройство (на телефонах нет мыши, там нулевые движения)
										// Если это десктоп и за 1.5 секунды мышь не сдвинулась ни на пиксель или не было нажатия — бракуем токен
										//if (Core_Browser::getDevice($userAgent) != 0 || $moves > 0 && $distance > 0 || $keys > 0)
										if (!$bDesktopBrowser
											|| $moves > 0 && $distance > 0 || $keys > 0
											|| $taps > 0 && $touchMoves > 0 || $wheelAttempts > 0
										)
										{
											$showChallenge = FALSE;
											
											if (Core::moduleIsActive('ipaddress'))
											{
												// Access granted
												$oIpaddress_Visitor = Ipaddress_Visitor_Controller::getIpaddressVisitorByCookie();
												if ($oIpaddress_Visitor)
												{
													$oIpaddress_Visitor->result = 1; // Access granted
													$oIpaddress_Visitor->visits = 1; // Reset number of visits
													$oIpaddress_Visitor->save();
													
													if (Core::moduleIsActive('counter'))
													{
														$oCounter_Controller = Counter_Controller::instance()
															->site($oSite)
															->counterId(0)
															->applySummary();
													}
												}
											}
										}
									}
									else
									{
										Core_Log::instance()->clear()
											->status(Core_Log::$MESSAGE)
											->write("Challenge failure; elapsed: {$elapsed}, fonts: {$fonts}, ip: {$sRemoteAddr}");
									}
								}
							}
						//}
					}
				}
			}
		}
	}

	// Show Challenge
	if ($showChallenge)
	{
		if (!is_null($oIpaddress_Visitor))
		{
			$oIpaddress_Visitor->result = 3;
			$oIpaddress_Visitor->save();

			Core_Command_Controller_Check_Challenge::$attempt = $oIpaddress_Visitor->visits;
		}
		
		Core_Router::add('check-challenge', '()')
			->setUri(Core::$url['path'])
			->controller('Core_Command_Controller_Check_Challenge')->execute()
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