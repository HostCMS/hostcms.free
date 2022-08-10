<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cookie
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Cookie
{
	/**
	 * Send a cookie
	 * @param string $name The name of the cookie.
	 * @param string $value The value of the cookie. Default ''
	 * @param array $options array('expires' => time() + 3600, 'path' => '/', 'domain' => '', 'secure' => FALSE, 'httponly' => FALSE, 'samesite' => 'Lax')
	 * @return boolean
	 */
	static public function set($name, $value = '', array $options = array())
	{
		$options += array(
			'expires' => 0,
			'path' => '',
			'domain' => '',
			'secure' => FALSE,
			'httponly' => FALSE,
			'samesite' => 'Lax'
		);
		
		$bSendSameSite = Core_Cookie::sendSameSite($options['samesite']);

		if (!$bSendSameSite)
		{
			unset($options['samesite']);
		}

		return PHP_VERSION_ID >= 70300
			? setcookie($name, $value, $options)
			: setcookie($name, $value, $options['expires'], isset($options['samesite']) ? $options['path'] . '; SameSite=' . $options['samesite'] : $options['path'], $options['domain'], $options['secure'], $options['httponly']);
	}

	/**
	 * SameSite=None: Known Incompatible Clients
	 * @param string $samesite
	 * @param string $userAgent
	 * @return boolean
	 */
	static public function sendSameSite($samesite, $userAgent = NULL)
	{
		// SameSite attribute of Lax or Strict is OK
		return strcasecmp($samesite, 'lax') === 0 || strcasecmp($samesite, 'strict') === 0
			? TRUE
			: !Core_Cookie::isSameSiteIncompatible(!is_null($userAgent) ? $userAgent : Core_Array::get($_SERVER, 'HTTP_USER_AGENT'));
	}

	/**
	 * SameSite=None: Known Incompatible Clients
	 * @param string $userAgent
	 * @return boolean
	 */
	static public function isSameSiteIncompatible($userAgent)
	{
		// https://docs.microsoft.com/ru-ru/aspnet/samesite/owin-samesite#sob
		// https://www.chromium.org/updates/same-site/incompatible-clients/

		/**
		 * Versions of Safari and embedded browsers on MacOS 10.14 and all browsers on iOS 12.
		 * These versions will erroneously treat cookies marked with `SameSite=None` as if they were marked `SameSite=Strict`.
		 */

		// iOS 12
		if (preg_match('/\(iP.+; CPU .*OS 12[_\d]*.*\) AppleWebKit\//', $userAgent))
		{
			return TRUE;
		}

		// Mac OS X 10.14
		// Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15
		if (self::macOsXVersionCompare($userAgent, 10, 14))
		{
			// Safari or Mac embedded browser
			if (self::safari($userAgent) || preg_match('~\(Macintosh;.*Mac OS X [_\d]+\) AppleWebKit/~', $userAgent))
			{
				return TRUE;
			}
		}

		// Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3205.0 Safari/537.36
		// Versions of Chrome from Chrome 51 to Chrome 66 (inclusive on both ends)
		if (self::chromiumBased($userAgent))
		{
			if (self::incompatibleChromiumBasedVersion($userAgent, 51, 66))
			{
				return TRUE;
			}
		}

		// Mozilla/5.0 (Linux; U; Android 8.1.0; zh-CN; EML-AL00 Build/HUAWEIEML-AL00) AppleWebKit/537.36 (KHTML, like Gecko) baidu.sogo.uc.UCBrowser/11.9.4.974 UWS/2.13.1.48 Mobile Safari/537.36 AliApp(DingTalk/4.5.11) com.alibaba.android.rimet/10487439 Channel/227200 language/zh-CN
		// Versions of UC Browser on Android prior to version 12.13.2. Older versions will reject a cookie with `SameSite=None`
		if (self::incompatibleUCBrowser($userAgent, '12.13.2'))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Determine if a browser is Chrome (or Chromium) based.
	 * @param string $userAgent
	 * @return boolean
	 */
	static public function chromiumBased($userAgent)
	{
		return preg_match('/Chrom(e|ium)/', $userAgent) > 0;
	}

	/**
	 * Determine if a user agent matches a Safari
	 * @param string $userAgent
	 * @return boolean
	 */
	static public function safari($userAgent)
	{
		if (preg_match('~Version/.* Safari/~', $userAgent))
		{
			if (self::chromiumBased($userAgent) === FALSE)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Determine if a user agent matches a particular version of Mac OS X
	 * @param string $userAgent
	 * @param int $majorVerison, e.g. 10 for 10.14
	 * @param int $minorVerison, e.g. 14 for 10.14
	 * @return boolean
	 */
	static public function macOsXVersionCompare($userAgent, $majorVerison, $minorVerison)
	{
		preg_match_all('/\(Macintosh;.*Mac OS X (\d+)_(\d+)[_\d]*.*\) AppleWebKit\//', $userAgent, $matches);
		$major = isset($matches[1][0]) ? intval($matches[1][0]) : '';
		$minor = isset($matches[2][0]) ? intval($matches[2][0]) : '';

		return $majorVerison === $major && $minorVerison === $minor;
	}

	/**
	 * Determine if a user agent matches a particular version of Chromium
	 * @param string $userAgent
	 * @param int $min, e.g. 51
	 * @param int $max, e.g. 66
	 * @return boolean
	 */
	static public function incompatibleChromiumBasedVersion($userAgent, $min, $max)
	{
		preg_match_all('/Chrom[^ \/]+\/(\d+)[\.\d]*/', $userAgent, $matches);

		// unknown version number
		if (empty($matches[1][0]))
		{
			return TRUE;
		}

		$iVersion = intval($matches[1][0]);

		return $iVersion >= $min && $iVersion <= $max;
	}

	/**
	 * Determine if a user agent matches a particular version of UCBrowser
	 * @param string $userAgent
	 * @param string $max, e.g. '12.13.2'
	 * @return boolean
	 */
	static public function incompatibleUCBrowser($userAgent, $max)
	{
		preg_match_all('/UCBrowser\/(\d+)\.(\d+)\.(\d+)[\.\d]*/', $userAgent, $matches);

		if (!empty($matches[1][0]) && !empty($matches[2][0]) && !empty($matches[3][0]))
		{
			$sVersion = $matches[1][0] . '.' . $matches[2][0] . '.' . $matches[3][0];

			return version_compare($sVersion, $max) === -1;
		}

		return FALSE;
	}
}