<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Default extends Core_Command_Controller
{
	/**
	 * Check possibility of using static cache
	 */
	protected function _checkCache()
	{
		return (
			!isset($_SESSION)
			|| !isset($_SESSION['siteuser_id']) && !Core_Auth::logged() && empty($_SESSION['SCART'])
			)
			&& empty($_COOKIE['CART']) && count($_POST) == 0;
	}

	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Default.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Default.onAfterShowAction
	 * @hostcms-event Core_Command_Controller_Default.onBeforeContentCreation
	 * @hostcms-event Core_Command_Controller_Default.onBeforeSetTemplate
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		$oCore_Page = Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response->header('X-Powered-By', 'HostCMS');

		if (isset(Core::$mainConfig['headers']) && is_array(Core::$mainConfig['headers']))
		{
			foreach (Core::$mainConfig['headers'] as $headerName => $headerValue)
			{
				$oCore_Response->header($headerName, $headerValue);
			}
		}

		$this->_uri == '' && $this->_uri = '/';

		if ($this->_uri == '/index.php' && !Core::isIIS()
			|| $this->_uri == '/index.htm'
			|| $this->_uri == '/index.html')
		{
			$oCore_Response
				->status(301)
				->header('Location', '/');

			return $oCore_Response;
		}

		$sLastChar = substr($this->_uri, -1);

		// Shortlink
		if ($sLastChar != '/' && Core::moduleIsActive('shortlink'))
		{
			$oShortlinks = Core_Entity::factory('Shortlink');
			$oShortlinks->queryBuilder()
				->where('active', '=', 1)
				->where('shortlink', '=', ltrim($this->_uri, '/'))
				->limit(1);

			$aShortlinks = $oShortlinks->findAll(FALSE);

			if (isset($aShortlinks[0]))
			{
				$sQuery = "UPDATE `shortlinks` SET `hits` = `hits` + 1 WHERE `id` = " . intval($aShortlinks[0]->id);

				Core_DataBase::instance()
					->setQueryType(2)
					->query($sQuery);

				if ($aShortlinks[0]->log && !Core::checkBot(Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str')))
				{
					$oShortlink_Stat = Core_Entity::factory('Shortlink_Stat');
					$oShortlink_Stat->ip = Core::getClientIp();
					$oShortlink_Stat->referrer = Core_Array::get($_SERVER, 'HTTP_REFERER', '', 'str');
					$oShortlink_Stat->useragent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');

					$aShortlinks[0]->add($oShortlink_Stat);
				}

				$oCore_Response
					->status($aShortlinks[0]->type)
					->header('Location', $aShortlinks[0]->source);

				return $oCore_Response;
			}
		}

		// Путь заканчивается на слэш
		if ($sLastChar == '/'
		// или передаются данные методом GET
		// || isset(Core::$url['query']) // style.css?1341303578 doesn't work
		// или запрет редирект к последнему слэшу
		|| defined('OPTIONAL_TRAILING_SLASH') && OPTIONAL_TRAILING_SLASH)
		{
			// Получаем ID текущей страницы для указанного сайта по массиву
			$oStructure = $this->getStructure($this->_uri, CURRENT_SITE);

			if (is_null($oStructure) && $this->_uri == '/')
			{
				// Index page not found
				$oCore_Router_Route = new Core_Router_Route('()');
				return $oCore_Router_Route
					->controller('Core_Command_Controller_Index_Not_Found')
					->execute();
			}
		}
		else
		{
			// Если после последнего слэша указывается имя файла с расширением в два или более символов
			if (!defined('NOT_EXISTS_FILE_404_ERROR') || NOT_EXISTS_FILE_404_ERROR)
			{
				$aPath = explode('/', $this->_uri);

				// file.txt
				if (preg_match("/[а-яА-ЯёЁa-zA-Z0-9_\.\-]+\.[a-zA-Z0-9\-\.]{2,}$/Du", end($aPath)))
				{
					$oCore_Response
						->status(404)
						->body('HostCMS: File \'' . htmlspecialchars($this->_uri) . '\' not found.');

					return $oCore_Response;
				}
			}

			// Редирект на пусть с добавлением слэша
			$uri = str_replace(array("\r", "\n"), '', $this->_uri);

			if ($uri != '/' && strpos($uri, '//') !== 0)
			{
				$oCore_Response
					->status(301)
					->header('Location', $uri . '/');
			}
			else
			{
				$oCore_Response
					->status(404)
					->body('HostCMS: File not found.');
			}

			return $oCore_Response;
		}

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		// Content-Security-Policy
		$oSite->csp != ''
			&& $oCore_Response->header('Content-Security-Policy', $oSite->csp);

		// XSS protect
		if ($oSite->protect)
		{
			$bXSS = FALSE;
			foreach ($_GET as $getKey => $getValue)
			{
				if (Core_Security::checkXSS($getValue))
				{
					unset($_GET[$getKey]);
					unset($_REQUEST[$getKey]);
					$bXSS = TRUE;
				}
			}

			if ($bXSS)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write('XSS attempt was detected');
			}
		}

		// Отдача статичного кэша в случае, если правила mod_rewrite не сработали
		// из-за %{HTTP_COOKIE} !^.*PHPSESSID=.*$
		$bUseStaticCache = Core::moduleIsActive('cache') && $oSite->html_cache_use == 1;
		if ($bUseStaticCache)
		{
			$Core_Cache = Core_Cache::instance('static');

			if ($this->_checkCache())
			{
				$result = $Core_Cache->get($this->_uri);

				if ($result !== FALSE)
				{
					$oCore_Response
						->header('Content-Type', 'text/html; charset=' . $oSite->coding)
						->body($result);

					return $oCore_Response;
				}
			}

			define('STATIC_CACHE', TRUE);
		}

		if (((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & 1176341605))
		{
			$b = explode('.', Core_Array::get(Core::$url, base64_decode('aG9zdA=='), '', 'str'));

			do {
				$a = explode('-', Core_Array::get(Core::$url, base64_decode('a2V5'), '', 'str')) + array(0, 0, 0, 0);
				$c = implode('.', $b);

				if (!(Core::convert64b32(hexdec($a[3]) ^ abs(Core::crc32($c))) ^ ~(Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms')) & abs(Core::crc32($c)) ^ Core::convert64b32(hexdec($a[2])))))
				{
					break;
				}
				array_shift($b);
			} while (count($b) > 1);

			if (hexdec($a[1]) & (~(Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms')) & abs(Core::crc32($c)) ^ Core::convert64b32(hexdec($a[2])))))
			{
				Core_Router::add('key_not_found', '()')
					->controller('Core_Command_Controller_Key_Not_Found')
					->execute()
					->header('X-Powered-By', Core::xPoweredBy())
					->sendHeaders()
					->showBody();

				exit();
			}
		}

		if (!is_null($oStructure))
		{
			$oCore_Response->status(200);
		}
		else
		{
			$oCore_Response->status(404);

			// Если определена константа с ID страницы для 404 ошибки и она не равна нулю
			if ($oSite->error404)
			{
				$oStructure = Core_Entity::factory('Structure')->find($oSite->error404);

				// страница с 404 ошибкой не найдена
				if (is_null($oStructure->id))
				{
					return $oCore_Response;
				}
			}
			else
			{
				// Редирект на главную страницу
				$this->_uri != '/' && $oCore_Response->header('Location', '/');

				return $oCore_Response;
			}
		}

		// Openstat, UTM and From
		if (!is_null(Core_Array::getGet('_openstat')))
		{
			$aOpenstat = explode(';', base64_decode(Core_Array::getGet('_openstat')));

			$oSource_Controller = new Source_Controller();
			$oSource_Controller
				->type(0)
				->service(Core_Array::get($aOpenstat, 0))
				->campaign(Core_Array::get($aOpenstat, 1))
				->ad(Core_Array::get($aOpenstat, 2))
				->source(Core_Array::get($aOpenstat, 3))
				->apply();
		}
		elseif (!is_null(Core_Array::getGet('utm_source')))
		{
			$oSource_Controller = new Source_Controller();
			$oSource_Controller
				->type(1)
				->service(Core_Array::getGet('utm_source'))
				->medium(Core_Array::getGet('utm_medium'))
				->campaign(Core_Array::getGet('utm_campaign'))
				->content(Core_Array::getGet('utm_content'))
				->term(Core_Array::getGet('utm_term'))
				->apply();
		}
		elseif (!is_null(Core_Array::getGet('from')))
		{
			$oSource_Controller = new Source_Controller();
			$oSource_Controller
				->type(2)
				->service(Core_Array::getGet('from'))
				->apply();
		}
		elseif (!is_null(Core_Array::getGet('gclid')))
		{
			$oSource_Controller = new Source_Controller();
			$oSource_Controller
				->type(3)
				->service('google')
				->apply();
		}

		// Если доступ к узлу структуры только по HTTPS, а используется HTTP,
		// то делаем 301 редирект
		if ($oStructure->https == 1 && !Core::httpsUses())
		{
			$url = Core::$url['host'] . $this->_uri;
			isset(Core::$url['query']) && $url .= '?' . Core::$url['query'];

			$oCore_Response
				->status(301)
				->header('Location', 'https://' . str_replace(array("\r", "\n", "\0"), '', $url));

			return $oCore_Response;
		}

		$oCore_Response
			->header('Content-Type', 'text/html; charset=' . $oSite->coding);

		// Текущий узел структуры
		define('CURRENT_STRUCTURE_ID', $oStructure->id);

		// Проверка на доступ пользователя к странице
		$iStructureAccess = $oStructure->getSiteuserGroupId();

		$aSiteuserGroups = array(0);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
				foreach ($aSiteuser_Groups as $aSiteuserGroup)
				{
					$aSiteuserGroups[] = $aSiteuserGroup->id;
				}
			}
		}

		if (!in_array($iStructureAccess, $aSiteuserGroups))
		{
			$oCore_Response->status(403);

			// Если определена страница для 403 ошибки
			if ($oSite->error403)
			{
				$oStructure = Core_Entity::factory('Structure')->find($oSite->error403);

				// страница с 403 ошибкой не найдена
				if (is_null($oStructure))
				{
					return $oCore_Response;
				}
			}
			else
			{
				// Access forbidden
				$oCore_Router_Route = new Core_Router_Route('()');
				return $oCore_Router_Route
					->controller('Core_Command_Controller_Access_Forbidden')
					->execute();
			}
		}

		/*if (Core_Array::get(Core::$config->get('core_hostcms'), 'integration') == 0 && $this->_uri == '/' // Free
			|| strtoupper($oSite->coding) != 'UTF-8'
			// Включено кэширование в статичные файлы
			|| Core::moduleIsActive('cache') && $oSite->html_cache_use == 1
			// Включена защита e-mail
			|| $oSite->safe_email
		)
		{*/
			// Старт в любом случае, т.к. содержимое идет в Core_Response->body($sContent);
			ob_start();
			ob_implicit_flush(0);
			define('OB_START', TRUE);
		//}

		/*
		Тип раздела
		0 - Страница из документооборота
		1 - Динамическая страница
		2 - Типовая динамическая страница
		3 - Ссылка на вшений ресурс
		*/

		// Если тип - страница
		if ($oStructure->type == 0)
		{
			$oTemplate = $oStructure->Document->Template;
		}
		// Если динамическая страница или типовая дин. страница
		elseif ($oStructure->type == 1 || $oStructure->type == 2)
		{
			$oTemplate = $oStructure->Template;
		}
		// Ссылка на внешний файл (тип 3)
		else
		{
			$oCore_Response->status(301);

			// If page is not a child of the given
			//if (mb_strpos($this->_uri, $oStructure->url) !== 0)
			if (trim($this->_uri, '/') != trim($oStructure->url, '/'))
			{
				$oCore_Response
					->header('Location', $oStructure->url);
			}
			else
			{
				$oCore_Response->body(
					'HostCMS: This page has moved. <a href="' . htmlspecialchars($oStructure->url) . '">Click here.</a>'
				);
			}

			return $oCore_Response;
		}

		if (is_null($oTemplate->id))
		{
			// Template not found
			$oCore_Router_Route = new Core_Router_Route('()');
			return $oCore_Router_Route
				->controller('Core_Command_Controller_Template_Not_Found')
				->execute();
		}

		$oCore_Page
			->template($oTemplate)
			->structure($oStructure);

		$oStructure->setCorePageSeo($oCore_Page);
		$oCore_Page->addChild($oStructure->getRelatedObjectByType());

		// CDN
		if (Core::moduleIsActive('cdn'))
		{
			$oCdn_Site = Cdn_Controller::getDefaultCdnSite();

			if (!is_null($oCdn_Site) && $oCdn_Site->active)
			{
				$oCdn = $oCdn_Site->Cdn;

				if (!is_null($oCdn->driver))
				{
					$oCdn_Controller = Cdn_Controller::instance($oCdn->driver);
					$oCdn_Controller->setCdnSite($oCdn_Site);

					$oCdn_Site->css
						&& $oCore_Page->cssCDN = '//' . htmlspecialchars($oCdn_Controller->getCssDomain());
					$oCdn_Site->js
						&& $oCore_Page->jsCDN = '//' . htmlspecialchars($oCdn_Controller->getJsDomain());
					$oCdn_Site->informationsystem
						&& $oCore_Page->informationsystemCDN = '//' . htmlspecialchars($oCdn_Controller->getInformationsystemDomain());
					$oCdn_Site->shop
						&& $oCore_Page->shopCDN = '//' . htmlspecialchars($oCdn_Controller->getShopDomain());
					$oCdn_Site->structure
						&& $oCore_Page->structureCDN = '//' . htmlspecialchars($oCdn_Controller->getStructureDomain());
				}
			}
		}

		// Counter is active and it's a bot
		if (Core::moduleIsActive('counter') && Counter_Controller::checkBot(Core_Array::get($_SERVER, 'HTTP_USER_AGENT')))
		{
			Counter_Controller::instance()
				->site($oSite)
				->page('http://' . strtolower(Core_Array::get($_SERVER, 'HTTP_HOST')) . Core_Array::get($_SERVER, 'REQUEST_URI'))
				->ip(Core::getClientIp())
				->userAgent(Core_Array::get($_SERVER, 'HTTP_USER_AGENT'))
				->counterId(0)
				->buildCounter();
		}

		$bLogged = Core_Auth::logged();

		if ($bLogged)
		{
			$hostcmsAction = Core_Array::getGet('hostcmsAction');
			if (!is_null($hostcmsAction))
			{
				Core_Session::start();

				$oUser = Core_Auth::getCurrentUser();

				if ($hostcmsAction == 'SHOW_XML'
					&& Core::moduleIsActive('xsl')
					&& $oUser && $oUser->checkModuleAccess(array('xsl'), $oSite)
				)
				{
					$_SESSION['HOSTCMS_SHOW_XML'] = $hostcmsAction == 'SHOW_XML';
				}
				elseif (isset($_SESSION['HOSTCMS_SHOW_XML']))
				{
					unset($_SESSION['HOSTCMS_SHOW_XML']);
				}
			}
		}

		// Проверка на передачу GET-параметров для статичного документа
		if (defined('ERROR_404_GET_REQUESTS') && ERROR_404_GET_REQUESTS
			&& $oStructure->type == 0 && count($_GET) && !($bLogged && isset($_GET['hostcmsAction']))
			&& !isset($_GET['_openstat']) && !isset($_GET['utm_source'])
			&& !isset($_GET['gclid']) && !isset($_GET['from'])
		)
		{
			$oCore_Page->error404();
		}

		Core_Event::notify(get_class($this) . '.onBeforeContentCreation', $this, array($oCore_Page, $oCore_Response));

		// isn't document
		if ($oStructure->type != 0)
		{
			$bLogged && $fBeginTimeConfig = Core::getmicrotime();

			// Динамическая страница
			if ($oStructure->type == 1)
			{
				$StructureConfig = $oStructure->getStructureConfigFilePath();

				if (Core_File::isFile($StructureConfig) && is_readable($StructureConfig))
				{
					include $StructureConfig;
				}
			}
			elseif ($oStructure->type == 2)
			{
				$oCore_Page->libParams
					= $oStructure->Lib->getDat($oStructure->id);

				$LibConfig = $oStructure->Lib->getLibConfigFilePath();

				if (Core_File::isFile($LibConfig) && is_readable($LibConfig))
				{
					include $LibConfig;
				}
			}

			$bLogged && Core_Page::instance()->addFrontendExecutionTimes(
				Core::_('Core.time_page_config', Core::getmicrotime() - $fBeginTimeConfig)
			);
		}

		$bLogged && $fBeginTime = Core::getmicrotime();

		// Headers
		$iExpires = time() + (defined('EXPIRES_TIME')
			? EXPIRES_TIME
			: 300);

		if (!defined('SET_EXPIRES') || SET_EXPIRES)
		{
			$oCore_Response
				->header('Expires', gmdate("D, d M Y H:i:s", $iExpires) . " GMT");
		}

		if (!defined('SET_LAST_MODIFIED') || SET_LAST_MODIFIED)
		{
			$iLastModified = time() + (defined('LAST_MODIFIED_TIME')
				? LAST_MODIFIED_TIME
				: 0);

			$oCore_Response
				->header('Last-Modified', gmdate("D, d M Y H:i:s", $iLastModified) . " GMT");
		}

		if (!defined('SET_CACHE_CONTROL') || SET_CACHE_CONTROL)
		{
			$sCacheControlType = $iStructureAccess == 0
				? 'public'
				: 'private';

			// Расчитываем максимальное время истечения
			$max_age = $iExpires > time()
				? $iExpires - time()
				: 0;

			$oCore_Response
				->header('Cache-control', "{$sCacheControlType}, max-age={$max_age}");
		}

		Core_Event::notify(get_class($this) . '.onBeforeSetTemplate', $this, array($oCore_Page, $oCore_Response));

		// Template might be changed at lib config
		$oTemplate = $oCore_Page->template;

		$oCore_Page
			->addTemplates($oTemplate)
			->buildingPage(TRUE)
			->execute();

		if ($bLogged)
		{
			Core_Page::instance()->addFrontendExecutionTimes(
				Core::_('Core.time_load_modules', Core::getLoadModuleTime())
			);

			Core_Page::instance()->addFrontendExecutionTimes(
				Core::_('Core.time_template', Core::getmicrotime() - $fBeginTime)
			);
		}

		!defined('CURRENT_VERSION') && define('CURRENT_VERSION', '7.0');

		$bIsUtf8 = strtoupper($oSite->coding) == 'UTF-8';

		// End all another opened and hadn't closed. On the right way ob_get_level() == 1
		/*while (ob_get_level() > 1)
		{
			ob_end_flush();
		}*/

		//if (defined('OB_START'))
		//{
		$sContent = ob_get_clean();

		// PHP Bug: pcre.recursion_limit too large.
		substr(PHP_OS, 0, 3) == 'WIN' && ini_set('pcre.recursion_limit', '524');

		// Если необходимо защищать электронные адреса, опубликованные на сайте
		if ($oSite->safe_email && strlen($sContent) < 204800)
		{
			/**
			 * Strip \n, \r, \ in $text
			 * @param string $text text
			 * @return string
			 */
			/*function stripNl($text)
			{
				return str_replace(array("\n", "\r", "'"), array('', '', "\'"), $text);
			}*/

			$sTmpContent = preg_replace_callback('/<a\s([^>]*)?href=[\'|\"]?(mailto:[^\"|\']*)[\"|\']?([^>]*)?>(.*?)<\/a>/is', array($this, '_safeEmailCallback'), $sContent); // без /u

			strlen($sTmpContent) && $sContent = $sTmpContent;
		}

		if (Core_Array::get($_SERVER, 'REQUEST_URI') == '/' && !((~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))) & (~1835217467)) && strlen($sContent) < 204800)
		{
			$search = array(
				"'<!--.*?-->'siu", // <!-- delete first!
				"'<script[^>]*?>.*?</script\s*?>'siu",
				"'<noscript[^>]*?>.*?</noscript\s*?>'siu",
				"'<style[^>]*?>.*?</style\s*?>'siu",
				"'<select[^>]*?>.*?</select\s*?>'siu",
				"'<head[^>]*?>.*?</head\s*?>'siu"
			);

			$sTmpContent = preg_replace($search, ' ', str_replace(array("\r", "\n"), ' ', $sContent));

			$pattern_index = "(?<!noindex)(?<!display)(?<!visible)";
			$pat = "#<a(?:[^>]{$pattern_index})*?href=[\"]?http[s]?://(?:www.)?hostcms.(?:ru|org)[/]?[\"]?(?:[^>]{$pattern_index})*?>(.{3,})</a>#si";

			if (!Core_Auth::logged() && !preg_match_all($pat, $sTmpContent, $matches))
			{
				$sContent = '<div style="box-sizing: border-box; border: 1px solid #E83531; z-index: 999999; border-radius: 5px; background: #FEEFDA; text-align: center; clear: both; height: 120px; position: relative;' . (Core::checkPanel() ? 'margin-top: 38px;' : '') . '">
					<div style="position: absolute; right: 3px; top: 3px; font-family: courier new; font-weight: bold;"><a href="#" onclick="javascript:this.parentNode.parentNode.style.display=\'none\'; return false;"><img src="/admin/images/wclose.gif" style="border: none;" alt="Close this notice"/></a></div>
					<div style="box-sizing: border-box; width: 740px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;"><div style="width: 75px; float: left"><img src="https://www.hostcms.ru/images/free-notice/warning.jpg" alt="Warning!"/></div>
					<div style="width: 600px; float: left; font-family: Arial, sans-serif"><div style="font-size: 14px; font-weight: bold; margin-top: 12px;">Нарушение п. 3.3 лицензионого договора присоединения</div>
					<div style="font-size: 12px; margin-top: 6px; line-height: 12px">Пользователь бесплатной редакции HostCMS.Старт обязуется разместить на каждом сайте, работающем с использованием Программного продукта, активную, индексируемую и видимую при просмотре сайта ссылку
					<div><b>' . htmlspecialchars('Система управления сайтом <a href="https://www.hostcms.ru" target="_blank">HostCMS</a>') . '</b></div> на сайт производителя <a href="https://www.hostcms.ru" target="_blank">https://www.hostcms.ru</a>.</div>
					</div>
					</div>
				</div>' . $sContent;
			}
		}

		!$bIsUtf8 && $sContent = $this->_iconv($oSite->coding, $sContent);

		if ($bUseStaticCache && $oCore_Response->getStatus() == 200)
		{
			if ($oSite->html_cache_clear_probability > 0 && rand(0, $oSite->html_cache_clear_probability) == 0)
			{
				// Clear static cache
				$Core_Cache->deleteAll($oSite->id);
			}

			if ($this->_checkCache() && strlen($sContent) > 0)
			{
				$Core_Cache->insert($this->_uri, $sContent);
			}
		}

		$oCore_Response->body($sContent);

		$bLogged && Core_Registry::instance()->set('Core_Statistics.totalTime',
			Core::getmicrotime() - Core_Registry::instance()->get('Core_Statistics.totalTimeBegin')
		);

		// Top panel
		if (Core::checkPanel())
		{
			ob_start();

			Core_Skin::instance()->frontend();

			$sContent = ob_get_clean();

			!$bIsUtf8 && $sContent = $this->_iconv($oSite->coding, $sContent);

			$oCore_Response->body($sContent);
		}

		// Benchmark
		if (defined('BENCHMARK_ENABLE') && BENCHMARK_ENABLE
			&& defined('BENCHMARK_ADD_COUNTER') && BENCHMARK_ADD_COUNTER
			&& Core::moduleIsActive('benchmark')
		)
		{
			ob_start();
			Benchmark_Controller::show();
			$oCore_Response->body(ob_get_clean());
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}

	/**
	 * Safe Email Callback Function
	 * @param array $matches matches
	 * @return string
	 */
	protected function _safeEmailCallback($matches)
	{
		ob_start();
		?><a <?php echo $matches[1]?>href="<?php echo str_rot13($matches[2])?>"<?php echo $matches[3]?>><?php echo str_rot13($matches[4])?></a><script><?php
		?>function hostcmsEmail(c){return c.replace(/[a-zA-Z]/g, function(c){return String.fromCharCode((c <= "Z" ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c-26);})}<?php
		?>var o = document.currentScript.previousElementSibling; o.href = hostcmsEmail(o.href); o.innerHTML = hostcmsEmail(o.innerHTML);</script><?php
		return ob_get_clean();
	}

	/**
	 * Get Structure_Model which satisfy URI $path
	 * @param string $path URI
	 * @param int $site_id site ID
	 * @return Structure_Model
	 */
	public function getStructure($path, $site_id)
	{
		$path = Core_Str::ltrimUri($path);

		if ($path === '/')
		{
			return NULL;
		}

		//$aPath = explode('/', trim($path, '/'));
		$aPath = explode('/', Core_Str::rtrimUri($path));

		// Index page
		if (count($aPath) == 1 && $aPath[0] == '')
		{
			$aPath[0] = '/';
		}

		$oSite = Core_Entity::factory('Site', $site_id);

		$bINDEX_PAGE_IS_DEFAULT = defined('INDEX_PAGE_IS_DEFAULT') && INDEX_PAGE_IS_DEFAULT;

		$parent_id = 0;
		foreach ($aPath as $sPath)
		{
			$oStructure = $oSite->Structures->getByPathAndParentId($sPath, $parent_id);

			// Found
			if (!is_null($oStructure) && $oStructure->active == 1)
			{
				$parent_id = $oStructure->id;
			}
			// Not found
			else
			{
				// Parent node
				$oStructure = $parent_id
					? Core_Entity::factory('Structure')->find($parent_id)
					: ($bINDEX_PAGE_IS_DEFAULT
						// Получаем главную страницу
						? $oSite->Structures->getByPath('/')
						: NULL
					);

				// Обработчик и константа необходима на случай размещения инфосистемы на главной страницы
				/*if ($bINDEX_PAGE_IS_DEFAULT && $parent_id == 0)
				{
					// Получаем главную страницы
					$oStructure = $oSite->Structures->getByPath('/');
				}*/

				// Parent node is static page
				if (is_null($oStructure)
					|| !is_null($oStructure->id) && $oStructure->type == 0)
				{
					// structure node not found
					return NULL;
				}

				// Прерываем, если у страницы нет таких дочерних
				break;
			}
		}

		return $oStructure;
	}

	/**
	 * Convert string to requested character encoding
	 * @param string $out_charset The output charset.
	 * @param string $content The string to be converted.
	 * @return string
	 */
	protected function _iconv($out_charset, $content)
	{
		// Delete BOM (EF BB BF)
		//$sContent = Core_Str::removeBOM($sContent);
		return @iconv('UTF-8', $out_charset . '//IGNORE//TRANSLIT', $content);
	}
}