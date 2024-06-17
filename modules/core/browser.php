<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Browser
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Browser
{
	/**
	 * Get device type by User Agent
	 * @param string $userAgent
	 * @return int 0 - desktop, 1 - tablet, 2 - phone, 3 - tv, 4 - watch
	 */
	static public function getDevice($userAgent)
	{
		if (is_null($userAgent) || $userAgent === '')
		{
			return 0;
		}

		// Tablet
		if (preg_match('/iP(a|ro)d|FOLIO|playbook/i', $userAgent)
			|| preg_match('/tablet/i', $userAgent) && !preg_match('/RX-34/i', $userAgent)
		)
		{
			return 1;
		}
		// nexus & motorola
		elseif (preg_match('/nexus 7|nexus 9|nexus 10|xoom/i', $userAgent))
		{
			return 1;
		}
		// Android Tablet
		elseif (preg_match('/Linux/i', $userAgent)
			&& preg_match('/Android/i', $userAgent)
			&& !preg_match('/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i', $userAgent)
		)
		{
			return 1;
		}
		// Kindle or Kindle Fire
		elseif (preg_match('/Kindle/i', $userAgent)
			|| preg_match('/Mac.OS/i', $userAgent) && preg_match('/Silk/i', $userAgent)
		)
		{
			return 1;
		}
		// pre Android 3.0 Tablet
		elseif (preg_match('/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i', $userAgent)
			|| preg_match('/MB511/i', $userAgent) && preg_match('/RUTEM/i', $userAgent))
		{
			return 1;
		}
		// unique Mobile User Agent
		elseif (preg_match('/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder|sd4930ur/i', $userAgent))
		{
			return 2;
		}
		elseif (preg_match('/Opera/i', $userAgent)
			&& preg_match('/Windows.NT.5/i', $userAgent)
			&& preg_match('/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i', $userAgent)
		)
		{
			return 2;
		}
		// cros - Chromeos
		elseif (preg_match('/Windows (NT|XP|ME|9)/i', $userAgent) || preg_match('/(?!Mi)CrOS(?!oft)/i', $userAgent))
		{
			return preg_match('/Phone|mobile|touch/i', $userAgent)
				? 2
				: 0;
		}
		// Mac Desktop
		elseif (preg_match('/Macintosh/i', $userAgent)
			&& !preg_match('/Silk/i', $userAgent))

		{
			return 0;
		}
		// Linux Desktop
		elseif (preg_match('/Linux/i', $userAgent) && preg_match('/X11/i', $userAgent))
		{
			return 0;
		}
		// Solaris, SunOS, BSD Desktop
		elseif (preg_match('/Solaris|SunOS|BSD/i', $userAgent))
		{
			return 0;
		}
		elseif (preg_match('/GoogleTV|SmartTV|smart\-tv|tuner|crkey|aftb|hbbtv|Internet.TV|adt\-|dtv|NetCast|vizio|NETTV|AppleTV|boxee|Kylo|Roku|viera|aquos|DLNADOC|CE\-HTML/i', $userAgent))
		{
			return 3;
		}
		// TV Based Gaming Console
		elseif (preg_match('/Xbox|playstation|vita|psp|Wii|nintendo/i', $userAgent))
		{
			return 3;
		}
		elseif (preg_match('/mobile|touch| mobi|phone/i', $userAgent))
		{
			return 2;
		}
		elseif (preg_match('/glass|watch|sm\-v/i', $userAgent))
		{
			return 4;
		}

		return 0;
	}

	/**
	 * Get browser name
	 * @param string $userAgent User agent
	 * @return string
	 */
	static public function getBrowser($userAgent)
	{
		if (is_null($userAgent) || $userAgent === '')
		{
			return '-';
		}

		if (preg_match('#Firefox/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Firefox '. $matches[1];
		}
		elseif (preg_match('#(?:YaBrowser|YaSearchBrowser)/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Yandex Browser '. $matches[1];
		}
		elseif (preg_match('#SamsungBrowser/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Samsung Browser '. $matches[1];
		}
		elseif (preg_match('#HuaweiBrowser/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Huawei Browser '. $matches[1];
		}
		elseif (preg_match('#MiuiBrowser/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Miui Browser '. $matches[1];
		}
		elseif (preg_match('#(?:Edge|Edg)/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Edge '. $matches[1];
		}
		elseif (preg_match('#Trident/([0-9]*)#', $userAgent, $matches))
		{
			switch ($matches[1])
			{
				case '4.0':
					$browser = 'MS IE 8';
				break;
				case '5.0':
					$browser = 'MS IE 9';
				break;
				case '6.0':
					$browser = 'MS IE 10';
				break;
				case '7.0':
					$browser = 'MS IE 11';
				break;
				case '8.0':
					$browser = 'MS IE 12';
				break;
				default:
					$browser = 'MS IE';
			}
		}
		elseif (preg_match('#Opera Mini/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Opera Mini '. $matches[1];
		}
		elseif (// (9.80) взято в скобки, чтобы индекс был [2], т.к. во втором выражении он [2]
		preg_match('#Opera/(9.80).*Version\/([0-9]*)#', $userAgent, $matches)
		|| preg_match('#Opera[/\s]([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Opera '. $matches[1];
		}
		elseif (preg_match('#OPR/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Opera '. $matches[1];
		}
		// Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) GSA/302.0.603406840 Mobile/15E148 Safari/604.1
		elseif (preg_match('#GSA/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Google Search App '. $matches[1];
		}
		// до Safari, т.к.: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16
		elseif (preg_match('#Chrome/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Chrome '. $matches[1];
		}
		elseif (preg_match('#UCBrowser/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'UCBrowser '. $matches[1];
		}
		elseif (preg_match('#Vivaldi/([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'Vivaldi '. $matches[1];
		}
		elseif (preg_match('#MSIE ([0-9]*)#', $userAgent, $matches))
		{
			$browser = 'MS IE '. $matches[1];
		}
		// Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1
		elseif (preg_match('#Version\/([0-9\.]*).*Safari/[0-9\.]#', $userAgent, $matches))
		{
			$browser = 'Safari '. $matches[1];
		}
		// Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/114.0.5735.124 Mobile/15E148 Safari/604.1
		elseif (preg_match('#CriOS\/([0-9]*).*Safari/[0-9\.]#', $userAgent, $matches))
		{
			$browser = 'Chrome ' . $matches[1] . ' for iOS';
		}
		// Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/114.0 Mobile/15E148 Safari/605.1.15
		elseif (preg_match('#FxiOS\/([0-9\.]*).*Safari/[0-9\.]#', $userAgent, $matches))
		{
			$browser = 'Firefox ' . $matches[1] . ' on iOS';
		}
		elseif (preg_match('#Netscape/([0-9].[0-9]{1,2})#', $userAgent, $matches))
		{
			$browser = 'Netscape '. $matches[1];
		}
		elseif (preg_match('#OmniWeb/([0-9].[0-9]{1,2})#', $userAgent, $matches))
		{
			$browser = 'Omniweb '. $matches[1];
		}
		elseif (preg_match('#Konqueror/([0-9].[0-9]{1,2})#', $userAgent, $matches))
		{
			$browser ='Konqueror '. $matches[1];
		}
		else
		{
			$browser = '-';
		}

		return $browser;
	}

	/**
	 * Get browser ICO
	 * @param string $browser Browser name
	 * @return string
	 */
	static public function getBrowserIco($browser)
	{
		if (strpos($browser, 'Chrome') !== FALSE)
		{
			$return = 'fab fa-chrome fa-fw green';
		}
		elseif (strpos($browser, 'Firefox') !== FALSE)
		{
			$return = 'fab fa-firefox-browser fa-fw warning';
		}
		elseif (strpos($browser, 'Yandex Browser') !== FALSE)
		{
			$return = 'fab fa-yandex fa-fw darkorange';
		}
		elseif (strpos($browser, 'Safari') !== FALSE)
		{
			$return = 'fab fa-safari fa-fw blue';
		}
		elseif (strpos($browser, 'Opera') !== FALSE)
		{
			$return = 'fab fa-opera fa-fw red';
		}
		elseif (strpos($browser, 'Edge') !== FALSE)
		{
			$return = 'fab fa-edge fa-fw sky';
		}
		elseif (strpos($browser, 'MS IE') !== FALSE)
		{
			$return = 'fab fa-internet-explorer fa-fw blue';
		}
		elseif (strpos($browser, 'Google Search App') !== FALSE)
		{
			$return = 'fab fa-google fa-fw green';
		}
		else
		{
			$return = NULL;
		}

		return $return;
	}

	/**
	 * Get OS name
	 * @param string $userAgent User agent
	 * @return string
	 */
	static public function getOs($userAgent)
	{
		if (is_null($userAgent) || $userAgent === '')
		{
			return '-';
		}

		// before Mac
		// Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.1 Mobile/15E148 Safari/604.1
		if (preg_match("/CPU (iPhone )?OS/i", $userAgent))
		{
			$os = "iOS";
		}
		elseif (preg_match("/Mac|Darwin/", $userAgent) || preg_match("/PPC/", $userAgent))
		{
			$os = "Mac";
		}
		elseif (preg_match("/Android|ADR /i", $userAgent))
		{
			$os = "Android";
		}
		elseif (preg_match("/AmigaOS/i", $userAgent))
		{
			$os = "AmigaOS";
		}
		elseif (preg_match("/BB10/i", $userAgent))
		{
			$os = "BlackBerry OS";
		}
		elseif (preg_match("/\b(?!Mi)CrOS(?!oft)/i", $userAgent))
		{
			$os = "Google Chrome OS";
		}
		elseif (preg_match("/FreeBSD/", $userAgent))
		{
			$os = "FreeBSD";
		}
		elseif (preg_match("/Linux/", $userAgent))
		{
			$os = "Linux";
		}
		elseif (preg_match("/SunOS/", $userAgent))
		{
			$os = "SunOS";
		}
		elseif (preg_match("/IRIX/", $userAgent))
		{
			$os = "IRIX";
		}
		elseif (preg_match("/BeOS/", $userAgent))
		{
			$os = "BeOS";
		}
		elseif (preg_match("#OS/2#", $userAgent))
		{
			$os = "OS/2";
		}
		elseif (preg_match("/AIX/", $userAgent))
		{
			$os = "AIX";
		}
		elseif (preg_match("/Windows Phone|WPDesktop|ZuneWP7|WP7/i", $userAgent))
		{
			$os = "Windows Phone";
		}
		elseif (preg_match("/Windows|Win(NT|32|95|98|16)/", $userAgent))
		{
			$os = "Windows";
		}
		else
		{
			$os = '-';
		}

		return $os;
	}

	/**
	 * Check if browser is correct
	 * @deprecated 6.7.0
	 */
	static public function check()
	{
		/*if (strpos(Core_Array::get($_SERVER, 'HTTP_USER_AGENT'), 'MSIE') !== FALSE)
		{
			?><!--[if lt IE 8]><div style='border: 1px solid #F7941D; background: #FEEFDA; text-align: center; clear: both; height: 75px; position: relative;'><div style='position: absolute; right: 3px; top: 3px; font-family: courier new; font-weight: bold;'><a href='#' onclick='javascript:this.parentNode.parentNode.style.display="none"; return false;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-cornerx.jpg' style='border: none' alt='Close this notice'/></a></div><div style='width: 640px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;'><div style='width: 75px; float: left;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-warning.jpg' alt='Warning!'/></div><div style='width: 275px; float: left; font-family: Arial, sans-serif;'><div style='font-size: 14px; font-weight: bold; margin-top: 12px;'><?php echo Core::_('Core_Browser.title')?></div><div style='font-size: 12px; margin-top: 6px; line-height: 12px;'><?php echo Core::_('Core_Browser.description')?></div></div><div style='width: 75px; float: left;'><a href='http://www.firefox.com' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-firefox.jpg' style='border: none' alt='Get Firefox 3.5'/></a></div><div style='width: 75px; float: left;'><a href='http://www.browserforthebetter.com/download.html' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-ie8.jpg' style='border: none' alt='Get Internet Explorer 8'/></a></div><div style='width: 73px; float: left;'><a href='http://www.apple.com/safari/download/' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-safari.jpg' style='border: none' alt='Get Safari 4'/></a></div><div style='float: left;'><a href='http://www.google.com/chrome' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-chrome.jpg' style='border: none' alt='Get Google Chrome'/></a></div></div></div><![endif]--><?php
		}*/
	}
}