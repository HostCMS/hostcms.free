<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SEO.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк"(Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Count of pages
	 * @var int
	 */
	protected $_countPage = NULL;

	/**
	* Кэш запрошенных страниц
	*
	* @var array - массив кэшированных страниц
	* @access private
	*/
	protected $_CachePage = array();

	/**
	 * Проверка возможности использования Яндекс.XML
	 */
	protected function _allowYandexXml()
	{
		return defined('YANDEX_XML_USER')
			&& defined('YANDEX_XML_KEY')
			&& strlen(YANDEX_XML_USER)
			&& strlen(YANDEX_XML_KEY);
	}

	/**
	* Заменяет "млн" и "тыс" на соответствующее количество нулей
	*
	* @param string $str Строка с числом и наименованием разряда словами
	* @return int
	*/
	protected function _edZero($str)
	{
		$str = str_replace('&nbsp;', ' ', $str);
		$str = str_replace('&#160;', ' ', $str);
		$str = str_replace('тыс', '000', $str);
		$str = str_replace('млн', '000000', $str);
		$str = intval(preg_replace('/[^0-9]/u', '', $str));
		return $str;
	}

	/**
	 * Send XML-request to Yandex
	 * @param string $query query
	 * @param int $page page number
	 * @return boolean
	 */
	protected function _yandexXmlRequest($query, $page = 0)
	{
		$lr = $this->_getLr();

		$url = 'https://yandex.ru/search/xml?user=' . urlencode(YANDEX_XML_USER) .
		'&key=' . urlencode(YANDEX_XML_KEY) .
		'&query=' . urlencode($query) .
		'&lr=' . urlencode($lr) .
		'&l10n=ru&sortby=rlv&filter=none&groupby=attr%3Dd.mode%3Ddeep.groups-on-page%3D10.docs-in-group%3D1' .
		'&page=' . $page;

		try
		{
			$Core_Http = Core_Http::instance()
				->url($url)
				->port(80)
				->timeout(5)
				->execute();

			$response = $Core_Http->getBody();
		}
		catch (Exception $e) {
			$response = '';
		}

		if ($response)
		{
			$response = mb_strtolower($response);
			$xmldoc = new SimpleXMLElement($response);
			//$error = $xmldoc->response->error;
			return $xmldoc;
		}
		return FALSE;
	}

	/**
	 * Get Yandex region ID
	 * @return int
	 */
	protected function _getLr()
	{
		// &lr=1
		// Полный список регионов http://search.yaca.yandex.ru/geo.c2n
		return defined('SEO_YANDEX_LR')
			? SEO_YANDEX_LR
			: 213;
	}

	/**
	* Поисковый запрос URL и сохранение полученной страницы в кэше
	*
	* @param str $url URL-документа
	* @param boolean $useCache использовать ли кэширование запрошенной страницы
	* @param mixed $referer Referer
	* @param mixed $cookie Cookie
	*
	* @return str контент документа
	*/
	protected function _getUrl($url, $useCache = TRUE, $referer = NULL, $cookie = NULL)
	{
		$md5 = md5($url);

		if ($useCache && isset($this->_cachePage[$md5]))
		{
			return $this->_cachePage[$md5];
		}

		try
		{
			$Core_Http = Core_Http::instance()
				->url($url)
				->timeout(3)
				->userAgent('Mozilla/5.0 (Windows NT 5.1; rv:32.0) Gecko/20100101 Firefox/32.0');

			!is_null($referer) && $Core_Http->referer($referer);
			!is_null($cookie) && $Core_Http->additionalHeader('Cookie', $cookie);

			$Core_Http->execute();

			$response = $Core_Http->getBody();
		}
		catch (Exception $e) {
			$response = '';
		}

		// Если разрешено кэшировать
		$useCache && $this->_cachePage[$md5] = $response;

		return $response;
	}

	/**
	 * Преобразование строки к 32-битному целому числу
	 * @param string $str source string
	 * @param int $check
	 * @param int $magic
	 * @return int
	 */
	protected function _string2int($str, $check, $magic)
	{
		$int32 = 4294967296;
		$iLen = strlen($str);
		for ($i = 0; $i < $iLen; $i++)
		{
			$check *= $magic;
			if ($check >= $int32)
			{
				$check = $check - $int32 * intval($check / $int32);
				$check = $check < -2147483648
					? $check + $int32
					: $check;
			}
			$check += ord($str{$i});
		}

		return $check;
	}

	/**
	 * Google hash
	 *
	 * @param str $string Строка
	 * @access private
	 * @return mixed
	 */
	protected function _googleHash($string)
	{
		$check1 = $this->_string2int($string, 0x1505, 0x21);
		$check2 = $this->_string2int($string, 0, 0x1003f);
		$check1 >>= 2;
		$check1 = (($check1 >> 4) & 0x3ffffc0 ) | ($check1 & 0x3f);
		$check1 = (($check1 >> 4) & 0x3ffc00 ) | ($check1 & 0x3ff);
		$check1 = (($check1 >> 4) & 0x3c000 ) | ($check1 & 0x3fff);
		$t1 = (((($check1 & 0x3c0) << 4) | ($check1 & 0x3c)) <<2 ) | ($check2 & 0xf0f);
		$t2 = (((($check1 & 0xffffc000) << 4) | ($check1 & 0x3c00)) << 0xa) | ($check2 & 0xf0f0000);

		$iCheckByte = 0;
		$iFlag = 0;
		$sHash = sprintf('%u', $t1 | $t2);
		$iHashLen = strlen($sHash);

		for ($i = $iHashLen - 1; $i >= 0; $i--)
		{
			$chr = $sHash{$i};
			if (1 === ($iFlag % 2))
			{
				$chr += $chr;
				$chr = intval($chr / 10) + ($chr % 10);
			}
			$iCheckByte += $chr;
			$iFlag++;
		}

		$iCheckByte %= 10;
		if ($iCheckByte !== 0)
		{
			$iCheckByte = 10 - $iCheckByte;
			if (($iFlag % 2) === 1)
			{
				($iCheckByte % 2) == 1
					? $iCheckByte += 9
					: $iCheckByte >>= 1;
			}
		}

		return '7' . $iCheckByte . $sHash;
	}

	/**
	 * Parse Google answer
	 * @param string $content answer
	 * @return mixed
	 */
	protected function _parseGoogle($content)
	{
		$str_res = 0;

		if ($content)
		{
			if ($str = strstr($content, "did not match any documents"))
			{
				$str_res = 0;
			}
			elseif ($str = strstr($content, "result"))
			{
				//About 1,540 results<
				//9 results<
				//>About 700 results<
				preg_match_all("#(About)?([^<]*)results<#s", $content, $matches);

				if (isset($matches[2][0]))
				{
					$str_res = strip_tags(trim($matches[2][0]));
					$str_res = $this->_edZero($str_res);
				}
			}
		}

		return $str_res;
	}

	/**
	 * Parse Yandex answer
	 * @param string $content answer
	 * @return mixed
	 */
	protected function _parseYandex($content)
	{
		$str_res = 0;

		if ($content)
		{
			if (strpos($content, 'http://captcha.yandex.ru') === FALSE)
			{
				/*Яндекс: нашлось 17 тыс. ответов*/
				preg_match_all('#нашлось\s*(.*?)\s*ответ#siu', $content, $matches);

				if (isset($matches[1][0]))
				{
					$str_res = $matches[1][0];
					$str_res = $this->_edZero($str_res);
				}
			}
		}

		return $str_res;
	}

	/**
	 * Parse Rambler answer
	 * @param string $content answer
	 * @return mixed
	 */
	protected function _parseRambler($content)
	{
		$str_res = 0;

		if ($content)
		{
			// <div class="info">Найдено 13 тыс. документов</div>
			preg_match_all("#<div class=\"info\">Найд[\W]*([^<]*)#su", $content, $matches);

			if (isset($matches[2][0]))
			{
				$str_res = strip_tags(trim($matches[2][0]));
				$str_res = $this->_edZero($str_res);
			}
		}

		return $str_res;
	}

	/**
	* Cut www.
	*
	* @param str $domain Анализируемый адрес домена
	* @return str Адрес домена без "www"
	*/
	protected function _cutDomain($domain)
	{
		if (strpos($domain, "www.") === 0)
		{
			$domain = substr($domain, 4);
		}
		return $domain;
	}

	/**
	 * Get pages count to analyze SERPs
	 * @return int
	 */
	public function getCountPage()
	{
		$this->_countPage = is_null($this->_countPage) && defined('SEO_SEARCH_PAGE_COUNT')
			? intval(SEO_SEARCH_PAGE_COUNT)
			: 5;

		return $this->_countPage;
	}

	/**
	* Поиск значения Google PageRank. Основан на данных Google toolbar. Возвращает значение PageRank страницы
	*
	* @param string $domain Адрес сайта
	* @return int PageRank значение
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* echo Seo_Controller::instance()->getPageRank($domain);
	* ?>
	* </code>
	*/
	public function getPageRank($domain)
	{
		$domain = trim($domain);

		$hash = $this->_googleHash($domain);

		//$url_page = "http://toolbarqueries.google.com/search?sourceid=navclient-ff&features=Rank&client=navclient-auto-ff&ch={$hash}&q=info:".rawurlencode($domain);
		$url_page = "http://toolbarqueries.google.com/tbr?features=Rank&sourceid=navclient-ff&client=navclient-auto-ff&ch={$hash}&q=info:" . rawurlencode($domain);

		$content = $this->_getUrl($url_page, false);

		if ($content)
		{
			$parts = explode(":", $content);

			if (isset($parts[count($parts) - 1]))
			{
				return intval($parts[count($parts) - 1]);
			}
		}

		return 0;
	}

	/**
	 * Определения наличия страницы в каталоге Yandex, тИЦ, темы, страны и региона страницы
	 *
	 * @param str $domain Адрес сайта
	 * @return array Массив значений
	 * - $info['tcy'] int тИЦ страницы
	 * - $info['tcy_topic'] str Тема страницы
	 * - $info['country'] str Страна
	 * - $info['region'] str Регион
	 * <code>
	 * <?php
	 * $domain = 'www.hostcms.ru';
	 * $array = Seo_Controller::instance()->getYandexCatalog($domain);
	 *
	 * // Распечатаем результат
	 * print_r($array);
	 * ?>
	 * </code>
	 */
	public function getYandexCatalog($domain)
	{
		$file = $this->_getUrl("http://bar-navig.yandex.ru/u?ver=2&show=32&url=http://" . rawurlencode($domain), FALSE);
		$info = array('tcy' => 0, 'tcy_topic' => '', 'country' => '', 'region' => '');

		if ($file)
		{
			//echo nl2br(htmlspecialchars(@iconv('Windows-1251', 'UTF-8//IGNORE//TRANSLIT', $file)));
			$oXml = new SimpleXMLElement($file);
			$info['tcy'] = (string)$oXml->tcy['value'];
			$info['tcy_topic'] = trim(str_replace('Тема:', '', (string)$oXml->topics->topic['title']));
		}

		return $info;
	}

	/**
	* Определение наличия страницы в каталоге Rambler
	*
	* @param string $domain Адрес сайта
	* @return bool
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getRamblerCatalog($domain);
	*
	* if ($result)
	* {
	* 	echo "Сайт присутствует в каталоге Rambler";
	* }
	* else
	* {
	* 	echo "Сайт отсутствует в каталоге Rambler";
	* }
	* ?>
	* </code>
	*/
	public function getRamblerCatalog($domain)
	{
		$domain = $this->_cutDomain($domain);

		$url = "http://top100.rambler.ru/?query=%22" . rawurlencode($domain) . "%22";
		$file = $this->_getUrl($url, false);

		return $file && strstr($file, "Найденных ресурсов");
	}

	/**
	* Определение наличия страницы в каталоге Dmoz.org
	*
	* @param string $domain Адрес сайта
	* @return bool
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getDmozCatalog($domain);
	*
	* if ($result)
	* {
	* 	echo "Сайт присутствует в каталоге Dmoz";
	* }
	* else
	* {
	* 	echo "Сайт отсутствует в каталоге Dmoz";
	* }
	* ?>
	* </code>
	*/
	public function getDmozCatalog($domain)
	{
		$domain = $this->_cutDomain($domain);

		$url = "http://www.dmoz.org/search/?q=u:" . rawurlencode($domain);
		$file = $this->_getUrl($url, false);

		return $file && strpos($file, "Open Directory Categories", 0) !== false;
	}

	/**
	* Определение наличия страницы в каталоге Mail.ru
	*
	* @param string $domain Адрес сайта
	* @return bool
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getMailCatalog($domain);
	*
	* if ($result)
	* {
	* 	echo "Сайт присутствует в каталоге Mail.ru";
	* }
	* else
	* {
	* 	echo "Сайт отсутствует в каталоге Mail.ru";
	* }
	* ?>
	* </code>
	*/
	public function getMailCatalog($domain)
	{
		$domain = $this->_cutDomain($domain);

		$file = $this->_getUrl("http://search.list.mail.ru/?q=www." . rawurlencode($domain), false);
		return $file && strstr($file, "Найдено сайтов");
	}

	/**
	* Определение количества проиндексированных странниц в Яндекс.ру
	*
	* @param string $domain Адрес сайта
	* @return int количество проиндексированных страниц
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getYandexIndex($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getYandexIndex($domain)
	{
		$domain = str_replace('*.', '', $domain);
		$domain = $this->_cutDomain($domain);
		$url_arr = explode(".", $domain);
		$url_arr = array_reverse($url_arr);

		$url = implode(".", $url_arr);

		$query = 'rhost:' . $url . ' | ' . 'rhost:' . $url . '.*';

		if ($this->_allowYandexXml())
		{
			$xmldoc = $this->_yandexXmlRequest($query);

			if (!$xmldoc)
			{
				Core_Message::show("Внутренняя ошибка сервера.", 'error');
			}
			elseif ($xmldoc->response->error)
			{
				Core_Message::show($xmldoc->response->error, 'error');
			}

			$found_all = is_object($xmldoc)
				? $xmldoc->response->found
				: 0;

			return intval($found_all);
		}
		else
		{
			$lr = $this->_getLr();
			$url = 'http://yandex.ru/yandsearch?text=' . rawurlencode($query) . '&lr=' . $lr;

			$referer = 'http://yandex.ru/showcaptcha?cc=1&retpath=http%3A//yandex.ru/yandsearch%3Flr%3D11053%26text%3Dhostcms%26csg%3D296%252C952%252C7%252C7%252C0%252C0%252C0_0dcda3d3f077b36f4d87985923d896fd&t=0/1413872716/9204adf17cf435f34e3fa4de2641f17b&s=907062f2f1b55ba757daef7f2a3c3bfd';

			$cookie = 'yandexuid=8581718501413872711; fuid01=5445fc4b2ed25581.eVTYImiKnIVjgTZsfnEtinTW-Jt2S-TIF1F7cGGoAx3PBMsrujGqkEeWYOVZNPTW5qrfoKOf4Zj_uCqVjWUIWTb1LJDtwY007X-6iu-7t0ycBZhTf0ROGMT3tRxu6wcr; yabs-frequency=/4/0000000000000000/sn2lS6WR8G00/; spravka=dD0xNDEzODcyNzIzO2k9ODcuMTE3LjQuMTI7dT0xNDEzODcyNzIzNDgyMzU2MzgwO2g9ZjQzOGM3ODNkODM1N2Q1N2M4OWRjZTM2M2YzNTAwZDI=; _ym_visorc_10630330=b';

			$file = $this->_getUrl($url, FALSE, $referer, $cookie);

			$str_res = $file
				? $this->_parseYandex($file)
				: 0;
		}

		return $str_res;
	}

	/**
	* Определение количества проиндексированных странниц сервисом Rambler
	*
	* @param string $domain Адрес сайта
	* @return int количество проиндексированных страниц
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getRamblerIndex($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	/*public function getRamblerIndex($domain)
	{
		$url = "http://nova.rambler.ru/srch?query=&filter=" . rawurlencode($domain);

		$file = $this->_getUrl($url, false);
		if ($file)
		{
			return $this->_parseRambler($file);
		}

		return 0;
	}*/

	/**
	* Определяет количество проиндексированных странниц сервисом Google
	*
	* @param string $domain Адрес сайта
	* @return int количество проиндексированных страниц
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getGoogleIndex($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getGoogleIndex($domain)
	{
		$url = "https://www.google.com/search?hl=en&q=site:" . rawurlencode($domain) . '&gws_rd=ssl';
		$file = $this->_getUrl($url, false);

		return $file
			? $this->_parseGoogle($file)
			: 0;
	}

	/**
	* Определение количества проиндексированных странниц сервисом Yahoo
	*
	* @param string $domain Адрес сайта
	* @return int количество проиндексированных страниц
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getYahooIndex($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getYahooIndex($domain)
	{
		$url = "http://search.yahoo.com/search?p=site%3A" . rawurlencode($domain);

		$file = $this->_getUrl($url, false);

		$str_res = 0;

		if ($file)
		{
			preg_match_all('#<span>(.*?)\s*result#siu', $file, $matches);

			if (isset($matches[1][0]))
			{
				$str_res = $matches[1][0];
				$str_res = html_entity_decode($str_res, ENT_COMPAT, 'UTF-8');
				$str_res = $this->_edZero($str_res);
			}
		}

		return $str_res;
	}

	/**
	* Определение количества проиндексированных странниц сервисом Bing.com
	*
	* @param string $domain Адрес сайта
	* @return int количество проиндексированных страниц
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getBingIndex($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getBingIndex($domain)
	{
		$domain = $this->_cutDomain($domain);
		$url = "http://www.bing.com/search?q=site%3A" . rawurlencode($domain) . "&setplang=ru-RU";
		$file = $this->_getUrl($url, false);

		$str_res = 0;

		if ($file)
		{
			preg_match_all('#<span[^>]*>Резуль.*?:\s([^<]*)</span>#siu', $file, $matches);

			if (isset($matches[1][0]))
			{
				$str_res = $matches[1][0];
				$str_res = html_entity_decode($str_res, ENT_COMPAT, 'UTF-8');
				$str_res = $this->_edZero($str_res);
			}
		}

		return $str_res;
	}

	/**
	 * Определение количества ссылающихся страниц с сервиса Yandex
	 *
	 * @param string $domain Адрес сайта
	 * @return int Количество ссылающихся страниц
	 * <code>
	 * <?php
	 * $domain = 'www.hostcms.ru';
	 * $result = Seo_Controller::instance()->getYandexLinks($domain);
	 *
	 * // Распечатаем результат
	 * echo $result;
	 * ?>
	 * </code>
	 */
	public function getYandexLinks($domain)
	{
		$domain = $this->_cutDomain($domain);

		if ($this->_allowYandexXml())
		{
			$query = '"*.' . $domain . '"';
			$xmldoc = $this->_yandexXmlRequest($query);

			if (!$xmldoc)
			{
				Core_Message::show("Внутренняя ошибка сервера.", 'error');
			}
			elseif ($xmldoc->response->error)
			{
				Core_Message::show($xmldoc->response->error, 'error');
			}

			$found_all = $xmldoc->response->found;

			return intval($found_all);
		}

		$url = "http://yandex.ru/yandsearch?text=%22*." . rawurlencode($domain) . "%22&lr=1";

		$file = $this->_getUrl($url, false);

		return $file
			? $this->_parseYandex($file)
			: 0;
	}

	/**
	 * Определение количества ссылающихся страниц с сервиса Google
	 *
	 * @param string $domain Адрес сайта
	 * @return int Количество ссылающихся страниц
	 * <code>
	 * <?php
	 * $domain = 'www.hostcms.ru';
	 *
	 * $result = Seo_Controller::instance()->getGoogleLinks($domain);
	 *
	 * // Распечатаем результат
	 * echo $result;
	 * ?>
	 * </code>
	 */
	public function getGoogleLinks($domain)
	{
		$url = "https://www.google.ru/search?hl=en&newwindow=1&filter=1&q=link:" . rawurlencode($domain)/* . '*'*/ . '&gws_rd=ssl';
		$file = $this->_getUrl($url, false);

		return $file
			? $this->_parseGoogle($file)
			: 0;
	}

	/**
	 * Определение количества ссылающихся страниц с сервиса Yahoo
	 *
	 * @param string $domain Адрес сайта
	 * @return int Количество ссылающихся страниц
	 * <code>
	 * <?php
	 * $domain = 'www.hostcms.ru';
	 *
	 * $result = Seo_Controller::instance()->getYahooLinks($domain);
	 *
	 * // Распечатаем результат
	 * echo $result;
	 * ?>
	 * </code>
	 */
	/*public function getYahooLinks($domain)
	{
		$url = "http://siteexplorer.search.yahoo.com/search?p=".rawurlencode($domain)."&bwm=i&bwmf=s&bwmo=d";
		$file = $this->_getUrl($url, false);

		if ($file)
		{
			if ($str = strstr($file, '<span class="btn">Inlinks '))
			{
				$str_res = mb_substr($str, 26);
				$end_pos = mb_strpos($str_res, ')');
				$str_res = mb_substr($str_res, 0, $end_pos);
				//$str_res = str_replace(',', '', $str_res);
				$str_res = $this->_edZero($str_res);

				return $str_res;
			}
		}

		return 0;
	}*/

	/**
	* Определение количества ссылающихся страниц с сервиса Bing.com
	*
	* @param string $domain Адрес сайта
	* @return int Количество ссылающихся страниц
	* <code>
	* <?php

	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getBingLinks($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getBingLinks($domain)
	{
		$domain = $this->_cutDomain($domain);

		$url = "http://www.bing.com/search?q=" . rawurlencode($domain) . "+-%73%69%74%65%3awww." . rawurlencode($domain) . "&FORM=QBRE&setplang=ru-RU";

		$file = $this->_getUrl($url, false);

		if ($file)
		{
			preg_match_all('#<span[^>]*>Резуль.*?:\s([^<]*)</span>#siu', $file, $matches);

			if (isset($matches[1][0]))
			{
				return $this->_edZero($matches[1][0]);
			}
		}

		return 0;
	}

	/**
	* Проверка наличия счетчика статистики SpyLog
	*
	* @param string $domain Адрес сайта
	* @return mixed номер счетчика или false
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getSpyLogCounter($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getSpyLogCounter($domain)
	{
		$file = $this->_getUrl("http://$domain");

		if ($file)
		{
			$str = strstr($file, ".spylog.com/cnt?cid=");
			if ($str)
			{
				$end_pos = mb_strpos($str, "&");
				$str_res = mb_substr($str, 20, $end_pos - 20);

				return $str_res;
			}
			elseif (preg_match("'<span[^>]*?id=\"spylog[\d]*?\">.*?var[^<]*?spylog[^<]*?=[^<]*?{[^<]*?counter:[^<]*?([\d]*?),'u", $file, $preg))
			{
				return Core_Type_Conversion::toInt($preg[1]);
			}
		}

		return false;
	}

	/**
	 * Проверка наличия счетчика статистики Rambler
	 *
	 * @param string $domain Адрес сайта
	 * @return mixed Номер счетчика, или false
	 * <code>
	 * <?php
	 * $domain = 'www.hostcms.ru';
	 *
	 * $result = Seo_Controller::instance()->getRamblerCounter($domain);
	 *
	 * // Распечатаем результат
	 * echo $result;
	 * ?>
	 * </code>
	 */
	public function getRamblerCounter($domain)
	{
		$file = $this->_getUrl("http://$domain");

		if ($file)
		{
			if ($str = strstr($file, ".rambler.ru/top100.cnt?"))
			{
				$end_pos = mb_strpos($str, '"');
				$str_res = mb_substr($str, 23, $end_pos - 23);

				return $str_res;
			}
		}

		return false;
	}

	/**
	* Проверка наличия счетчика статистики Mail
	*
	* @param string $domain Адрес сайта
	* @return mixed Номер счетчика, или false
	* <code>
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getMailCounter($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getMailCounter($domain)
	{
		$file = $this->_getUrl("http://$domain");

		if ($file)
		{
			if ($str = strstr($file, "top.mail.ru/jump?from="))
			{
				$end_pos = mb_strpos($str, '"');
				$str_res = mb_substr($str, 22, $end_pos - 22);

				return $str_res;
			}
		}

		return false;
	}

	/**
	* Проверка наличия счетчика статистики HotLog
	*
	* @param string $domain Адрес сайта
	* @return mixed Номер счетчика, или false
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getHotLogCounter($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getHotLogCounter($domain)
	{
		$file = $this->_getUrl("http://$domain");

		if ($file)
		{
			if ($str = strstr($file, ".hotlog.ru/cgi-bin/hotlog/count?s="))
			{
				$end_pos = mb_strpos($str, '&');
				$str_res = mb_substr($str, 34, $end_pos - 34);

				return $str_res;
			}
		}

		return false;
	}

	/**
	* Проверка наличия счетчика статистики LiveInternet
	*
	* @param string $domain Адрес сайта
	* @return mixed Адрес на страницу со статистикой, или false
	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	*
	* $result = Seo_Controller::instance()->getLiveInternetCounter($domain);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getLiveInternetCounter($domain)
	{
		// $domain = $this->_cutDomain($domain);
		$file = $this->_getUrl("http://$domain");

		if ($file)
		{
			if ($str = strstr($file, "counter.yadro.ru/hit"))
			{
				$str_res = "http://www.liveinternet.ru/?" . $domain;
				return $str_res;
			}
		}

		return false;
	}

	/**
 	* Определение позиции сайта в поисковой системе Yandex
 	*
 	* @param string $domain Адрес сайта
 	* @param string $text Поисковый запрос
 	* @param array $param массив дополнительных параметров
 	* - $param['search_subdomain'] искать ли поддомены переданного домена. по умолчанию TRUE
 	* - $param['page_count'] количество просматриваемых страниц. по умолчанию 5
 	* @return mixed номер позиции или false
 	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	* $text = 'cms';
	*
	* $result = Seo_Controller::instance()->getYandexPosition($domain, $text);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
 	*/
	public function getYandexPosition($domain, $text, $param = array())
	{
		// Передан url c www
		$domain = $this->_cutDomain($domain);

		// Искать поддомены
		if (!isset($param['search_subdomain']))
		{
			$param['search_subdomain'] = TRUE;
		}

		// Количество страниц, по которым идет проверка
		if (!isset($param['page_count']))
		{
			$param['page_count'] = $this->getCountPage();
		}
		else
		{
			$param['page_count'] = intval($param['page_count']);
		}

		// Искать поддомены
		$subreg = $param['search_subdomain']
			? '([\w]*?\.)*?'
			: $subreg;

		$lr = $this->_getLr();

		for($page = 0; $page < $param['page_count']; $page++)
		{
			if ($this->_allowYandexXml())
			{
				$xmldoc = $this->_yandexXmlRequest($text, $page);

				if (!$xmldoc)
				{
					Core_Message::show("Внутренняя ошибка сервера.", 'error');
				}
				elseif ($xmldoc->response->error)
				{
					Core_Message::show($xmldoc->response->error, 'error');
				}

				if (is_object($xmldoc))
				{
					$aHost = $xmldoc->xpath("response/results/grouping/group/doc");

					if (count($aHost) > 0)
					{
						$li = 1;
						foreach($aHost as $item)
						{
							if (strpos($item->domain, $domain) === 0
							|| strpos($item->domain, "www." . $domain) === 0)
							{
								$position = $page * 10 + $li;
								return $position;
							}

							$li++;
						}
					}
				}
				else
				{
					return 0;
				}
			}
			else
			{
				$url = "http://yandex.ru/yandsearch?p={$page}&text=" . rawurlencode($text) . "&lr=" . $lr;

				$referer = 'http://yandex.ru/showcaptcha?cc=1&retpath=http%3A//yandex.ru/yandsearch%3Flr%3D11053%26text%3Dhostcms%26csg%3D296%252C952%252C7%252C7%252C0%252C0%252C0_0dcda3d3f077b36f4d87985923d896fd&t=0/1413872716/9204adf17cf435f34e3fa4de2641f17b&s=907062f2f1b55ba757daef7f2a3c3bfd';

				$cookie = 'yandexuid=8581718501413872711; fuid01=5445fc4b2ed25581.eVTYImiKnIVjgTZsfnEtinTW-Jt2S-TIF1F7cGGoAx3PBMsrujGqkEeWYOVZNPTW5qrfoKOf4Zj_uCqVjWUIWTb1LJDtwY007X-6iu-7t0ycBZhTf0ROGMT3tRxu6wcr; yabs-frequency=/4/0000000000000000/sn2lS6WR8G00/; spravka=dD0xNDEzODcyNzIzO2k9ODcuMTE3LjQuMTI7dT0xNDEzODcyNzIzNDgyMzU2MzgwO2g9ZjQzOGM3ODNkODM1N2Q1N2M4OWRjZTM2M2YzNTAwZDI=; _ym_visorc_10630330=b';

				$file = $this->_getUrl($url, FALSE, $referer, $cookie);

				if ($file)
				{
					if (preg_match_all("'<b[^>]*>([\d]*?)(\.)?</b>\s*<a\s[^>]*tabindex=\"[\d]*?\"[^<>]*?href=\"http://{$subreg}{$domain}[^<>]*?\"[^<>]*?>'siu", $file, $matches))
					{
						return $matches[1][0];
					}
				}

				usleep(1000000);
			}
		}

		return 0;
	}

	/**
 	* Определение позиции сайта в поисковой системе Rambler
 	*
 	* @param string $domain Адрес сайта
 	* @param string $text Поисковый запрос
 	* @param array $param Массив дополнительных параметров
 	* - $param['search_subdomain'] Искать ли поддомены переданного домена. по умолчанию TRUE
 	* - $param['page_count'] Количество просматриваемых страниц. по умолчанию 5
 	* @return mixed Номер позиции или false
 	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	* $text = 'cms';
	*
	* $result = Seo_Controller::instance()->getRamblerPosition($domain, $text);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
 	*/
	/*public function getRamblerPosition($domain, $text, $param = array())
	{
		$domain = $this->_cutDomain($domain);

		if (!isset($param['search_subdomain']))
		{
			$param['search_subdomain'] = TRUE;
		}

		if (!isset($param['page_count']))
		{
			$param['page_count'] = $this->getCountPage();
		}
		else
		{
			$param['page_count'] = intval($param['page_count']);
		}

		if ($param['search_subdomain'])
		{
			$subreg = '([\w]*?\.)*?';
		}
		else
		{
			$subreg = '';
		}

		for($i = 0; $i < $param['page_count']; $i++)
		{
			$url = "http://nova.rambler.ru/srch?btnG=%D0%9D%D0%B0%D0%B9%D1%82%D0%B8%21&query=" . rawurlencode($text) . "&page=" . ($i + 1) . "&start=" . ($i * 10 + 1);

			$content = $this->_getUrl($url, false);

			if ($content)
			{
				preg_match_all("#<a\s*target=\"_blank\"\s*href=\"(http://[^\"]*?)\"\s*#siu", $content, $matches);

				if (isset($matches[1][0]))
				{
					foreach($matches[1] as $key => $val)
					{
						if (preg_match("'http://{$subreg}{$domain}[^<>]*?'siu", $val))
						{
							return(10 * $i + $key + 1);
						}
					}
				}
			}

			usleep(1000000);
		}

		return 0;
	}*/

	/**
 	* Определение позиции сайта в поисковой системе Google
 	*
 	* @param string $domain Адрес сайта
 	* @param string $text Поисковый запрос
 	* @param array $param Массив дополнительных параметров
 	* - $param['search_subdomain'] Искать ли поддомены переданного домена. по умолчанию TRUE
 	* - $param['page_count'] Количество просматриваемых страниц. по умолчанию 5
 	* @return mixed Номер позиции или false
 	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	* $text = 'cms';
	*
	* $result = Seo_Controller::instance()->getGooglePosition($domain, $text);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
 	*/
	public function getGooglePosition($domain, $text, $param = array())
	{
		$domain = $this->_cutDomain($domain);

		!isset($param['search_subdomain']) && $param['search_subdomain'] = TRUE;

		$param['page_count'] = !isset($param['page_count'])
			? $this->getCountPage()
			: intval($param['page_count']);

		$subreg = $param['search_subdomain'] ? '([\w]*?\.)*?' : '';

		// На странице может быть не 10, а меньше элементов
		$pastPages = 0;

		for($i = 0; $i < $param['page_count']; $i++)
		{
			$url = "https://www.google.ru/search?complete=1&hl=ru&newwindow=1&q=" . rawurlencode($text) . "&start=" .(10* $i) . "&sa=N&gws_rd=ssl";
			$content = $this->_getUrl($url, false);

			if ($content)
			{
				// <h3 class="r"><a href="http://hosting.nic.ru/cms/hostcms.shtml" onmousedown=
				preg_match_all("#<h3 class=\"r\"><a\s*href=\"(.*?)\"\s*onmousedown=#siu", $content, $matches);

				if (isset($matches[1]))
				{
					foreach($matches[1] as $key => $value)
					{
						if (preg_match("'http://{$subreg}{$domain}[^<>]*?'siu", $value))
						{
							return $pastPages + $key + 1;
						}
					}

					$pastPages += count($matches[1]);
				}
			}

			usleep(1000000);
		}
		return 0;
	}

	/**
 	* Определение позиции сайта в поисковой системе Yahoo
 	*
 	* @param string $domain Адрес сайта
 	* @param string $text Поисковый запрос
 	* @param array $param Массив дополнительных параметров
 	* - $param['search_subdomain'] Искать ли поддомены переданного домена. по умолчанию TRUE
 	* - $param['page_count'] Количество просматриваемых страниц. по умолчанию 5
 	* @return mixed Номер позиции или false
 	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	* $text = 'cms';
	*
	* $result = Seo_Controller::instance()->getYahooPosition($domain, $text);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
 	*/
	public function getYahooPosition($domain, $text, $param = array())
	{
		$domain = $this->_cutDomain($domain);

		!isset($param['search_subdomain']) && $param['search_subdomain'] = TRUE;

		$param['page_count'] = !isset($param['page_count'])
			? $this->getCountPage()
			: intval($param['page_count']);

		$subreg = $param['search_subdomain']
			? '([\w]*?\.)*?'
			: '';

		for($i = 0; $i < $param['page_count']; $i++)
		{
			$url = "http://ru.search.yahoo.com/search?p=" . rawurlencode($text) . "&xargs=0&pstart=1&b=" .($i* 10 + 1);
			$content = $this->_getUrl($url, false);

			if ($content)
			{
				/*preg_match_all("#<li[^<>]*?>[^<]*?<div class=\"res\">[^<]*?<div>[^<]*?<h3>[^<]*?<a class=\"yschttl([^\"]*?spt)?\" href=\"(http://[^<>\"]*?)\"[^<>]*?>#siu", $content, $matches); */
				preg_match_all("#<a[^>]*?class=\"yschttl[^\"]*?\"\s*href=\"([^\"]*?)\"#siu", $content, $matches);

				if (isset($matches[1]) && count($matches[1]))
				{
					foreach($matches[1] as $key => $val)
					{
						if (preg_match("'{$subreg}{$domain}[^<>]*?'siu", $val))
						{
							return $i * 10 + $key + 1;
						}
					}
				}
			}
			usleep(1000000);
		}
		return 0;
	}

	/**
 	* Определение позиции сайта в поисковой системе Bing.com
 	*
 	* @param string $domain Адрес сайта
 	* @param string $text Поисковый запрос
 	* @param array $param Массив дополнительных параметров
 	* - $param['search_subdomain'] Искать ли поддомены переданного домена. по умолчанию TRUE
 	* - $param['page_count'] Количество просматриваемых страниц. по умолчанию 5
 	* @return mixed Номер позиции или false
 	* <code>
	* <?php
	* $domain = 'www.hostcms.ru';
	* $text = 'cms';
	*
	* $result = Seo_Controller::instance()->getBingPosition($domain, $text);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
 	*/
	public function getBingPosition($domain, $text, $param = array())
	{
		$domain = $this->_cutDomain($domain);

		if (!isset($param['search_subdomain']))
		{
			$param['search_subdomain'] = TRUE;
		}

		if (!isset($param['page_count']))
		{
			$param['page_count'] = $this->getCountPage();
		}
		else
		{
			$param['page_count'] = intval($param['page_count']);
		}

		if ($param['search_subdomain'])
		{
			$subreg = '([\w]*?\.)*?';
		}
		else
		{
			$subreg = '';
		}

		for($i = 0; $i < $param['page_count']; $i++)
		{
			$url = "http://www.bing.com/search?q=" . rawurlencode($text) . "&first=" .($i* 10 + 1) . "&FORM=PERE" . $i;

			$content = $this->_getUrl($url, false);

			if ($content)
			{
				if (preg_match_all("#<li class=\"b_algo\"><h2><a href=\"(http://[^\"]*?)\"\s*h=#siu", $content, $matches))
				{
					if (isset($matches[1]))
					{
						foreach($matches[1] as $key => $val)
						{
							if (preg_match("'http://{$subreg}{$domain}[^<>]*?'siu", $val))
							{
								return(10 * $i + $key + 1);
							}
						}
					}
				}
			}
			usleep(1000000);
		}

		return 0;
	}

	/**
	 * Request site characteristics
	 * @param Seo_Model $oSeo destination object
	 * @return self
	 */
	public function requestSiteCharacteristics(Seo_Model $oSeo)
	{
		$oSite_Alias = $oSeo->Site->getCurrentAlias();

		if ($oSite_Alias)
		{
			$sAliasName = $oSite_Alias->name;

			if (trim($sAliasName) != '')
			{
				$oSeo_Controller = self::instance();

				$aYandexCatalog = $oSeo_Controller->getYandexCatalog($sAliasName);
				$oSeo->tcy = $aYandexCatalog['tcy'];
				$oSeo->tcy_topic = $aYandexCatalog['tcy_topic'];

				if (trim($oSeo->tcy) != '')
				{
					$oSeo->yandex_catalog = 1;
				}

				// $oSeo->pr = $oSeo_Controller->GetPageRank($sAliasName);
				$oSeo->yandex_links = $oSeo_Controller->getYandexLinks($sAliasName);
				$oSeo->google_links = $oSeo_Controller->getGoogleLinks($sAliasName);
				//$oSeo->yahoo_links = $oSeo_Controller->getYahooLinks($sAliasName);
				$oSeo->bing_links = $oSeo_Controller->getBingLinks($sAliasName);

				$oSeo->mail_catalog = intval($oSeo_Controller->getMailCatalog($sAliasName));
				$oSeo->dmoz_catalog = intval($oSeo_Controller->getDmozCatalog($sAliasName));
				$oSeo->rambler_catalog = intval($oSeo_Controller->getRamblerCatalog($sAliasName));
				$oSeo->yandex_indexed = $oSeo_Controller->getYandexIndex($sAliasName);
				//$oSeo->rambler_indexed = $oSeo_Controller->getRamblerIndex($sAliasName);
				$oSeo->bing_indexed = $oSeo_Controller->getBingIndex($sAliasName);
				$oSeo->google_indexed = $oSeo_Controller->getGoogleIndex($sAliasName);

				$oSeo->rambler_counter = $oSeo_Controller->getRamblerCounter($sAliasName) ? 1 : 0;
				$oSeo->mail_counter = $oSeo_Controller->getMailCounter($sAliasName) ? 1 : 0;
				$oSeo->spylog_counter = $oSeo_Controller->getSpyLogCounter($sAliasName) ? 1 : 0;
				$oSeo->hotlog_counter = $oSeo_Controller->getHotLogCounter($sAliasName) ? 1 : 0;
				$oSeo->liveinternet_counter = $oSeo_Controller->getLiveInternetCounter($sAliasName) ? 1 : 0;

				//usleep(500000);
				// Вынесено ниже для защиты от блокировки Yahoo запросов.
				$oSeo->yahoo_indexed = $oSeo_Controller->getYahooIndex($sAliasName);

				$oSeo->save();
			}
		}

		return $this;
	}

	/**
	 * Request site positions for $oSite
	 * @param Site_Model $oSite site
	 * @return self
	 */
	public function requestSitePositions(Site_Model $oSite)
	{
		$oSite_Alias = $oSite->getCurrentAlias();

		if ($oSite_Alias)
		{
			$sAliasName = $oSite_Alias->name;

			if (trim($sAliasName) != '')
			{
				$oSeo_Controller = Seo_Controller::instance();

				foreach($oSite->Seo_Queries->findAll() as $oSeo_Query)
				{
					$oSeo_Query_Position = Core_Entity::factory('Seo_Query_Position');

					$oSeo_Query_Position->yandex = $oSeo_Controller->getYandexPosition($sAliasName, $oSeo_Query->query);
					//$oSeo_Query_Position->rambler = $oSeo_Controller->getRamblerPosition($sAliasName, $oSeo_Query->query);
					$oSeo_Query_Position->google = $oSeo_Controller->getGooglePosition($sAliasName, $oSeo_Query->query);
					$oSeo_Query_Position->yahoo = $oSeo_Controller->getYahooPosition($sAliasName, $oSeo_Query->query);
					$oSeo_Query_Position->bing = $oSeo_Controller->getBingPosition($sAliasName, $oSeo_Query->query);

					$oSeo_Query->add($oSeo_Query_Position);
				}
			}
		}

		return $this;
	}
}