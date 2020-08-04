<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * String helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Str
{
	/**
	 * List of illegal characters
	 * @var array
	 */
	static protected $_xmlIllegalCharacters = NULL;

	/**
	 * Get XML illegal characters
	 * @return array
	 */
	static public function getXmlIllegalCharacters()
	{
		if (is_null(self::$_xmlIllegalCharacters))
		{
			$aTmp = array(
				chr(0x00), chr(0x01), chr(0x02), chr(0x03), chr(0x04), chr(0x05), chr(0x06), chr(0x07), chr(0x08),
				/*chr(0x09), chr(0x0A,*/ chr(0x0B), chr(0x0C), /*chr(0x0D,*/ chr(0x0E), chr(0x0F),
				chr(0x10), chr(0x11), chr(0x12), chr(0x13), chr(0x14), chr(0x15), chr(0x16), chr(0x17), chr(0x18),
				chr(0x19), chr(0x1A), chr(0x1B), chr(0x1C), chr(0x1D), chr(0x1E), chr(0x1F),
				// Заменяющий символ (REPLACEMENT CHARACTER)
				chr(0xEF) . chr(0xBF) . chr(0xBD)
			);

			self::$_xmlIllegalCharacters = array_combine($aTmp, array_fill_keys(array_keys($aTmp), ''));
		}

		return self::$_xmlIllegalCharacters;
	}

	/**
	 * Correct string to XML legal characters
	 * http://www.w3.org/TR/xml/#charsets
	 * @param string $string
	 * @return string
	 */
	static public function xml($string)
	{
		return htmlspecialchars(
			self::deleteIllegalCharacters($string)
		);
	}

	/**
	 * Delete illegal characters
	 * @param string $string
	 * @return string
	 */
	static public function deleteIllegalCharacters($string)
	{
		$string = strtr($string, self::getXmlIllegalCharacters());
		return @iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT",
			//str_replace(self::getXmlIllegalCharacters(), '', $string)
			$string
		);
	}

	/**
	 * Cut string to defined lenght
	 * @param string $string source string
	 * @param int $maxLen lenght
	 * @return string
	 */
	static public function cut($string, $maxLen = 15)
	{
		if (mb_strlen($string) > $maxLen)
		{
			$maxLen < 15 && $maxLen = 15;
			$string = mb_substr($string, 0, $maxLen - 10) . '…' . mb_substr($string, -7);
		}

		return $string;
	}

	/**
	 * String starts with the specified string
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	static public function startsWith($haystack, $needle)
	{
		// Binary safe
		return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
	}

	/**
	 * String end with the specified string
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	static public function endsWith($haystack, $needle)
	{
		// Binary safe
		return substr_compare($haystack, $needle, -strlen($needle)) === 0;
	}

	/**
	 * Cut length of words in a string
	 * @param string $string source string
	 * @param int $maxLen word lenght
	 * @return string
	 */
	static public function cutWords($string, $maxLen)
	{
		$string = trim($string);

		if (mb_strlen($string) > 0)
		{
			$aString = explode(' ', $string);

			if (count($aString) > 0)
			{
				foreach ($aString as $key => $value)
				{
					if (mb_strlen($value) > $maxLen)
					{
						$aString[$key] = self::cut($value, $maxLen);
					}
				}
			}

			$string = implode(' ', $aString);
		}

		return $string;
	}

	/**
	 * Укорачивает описание до заданного количества символов, оставляя целое число предложений
	 *
	 * @param string $text - text
	 * @param int $maxLen - max ken
	 * <code>
	 * <?php
	 * $text = 'Текст описания, который необходимо укоротить';
	 * $maxLen = 20;
	 * $cutText = Core_Str::cutSentences($text, $maxLen);
	 *
	 * // Распечатаем результат
	 * echo $cutText;
	 * ?>
	 * </code>
	 * @return string
	 */
	static public function cutSentences($text, $maxLen = 255)
	{
		$lenght = mb_strlen(strval($text));
		$maxLen = intval($maxLen);

		if ($lenght > $maxLen)
		{
			$text = mb_substr($text, 0, $maxLen);

			preg_match('/^((?:.*?[.!?。])*)/su', $text, $matches);

			$text = isset($matches[1]) && mb_strlen($matches[1])
				? $matches[1]
				: mb_substr($text, 0, mb_strrpos($text, ' '));
		}

		return $text;
	}

	/**
	 * Translation from russian to english
	 * @param string $string source string
	 * @return string
	 */
	static public function translate($string)
	{
		// Yandex Cloud Translate
		if (defined('YANDEX_CLOUD_SECRET_KEY') && strlen(YANDEX_CLOUD_SECRET_KEY)
			&& defined('YANDEX_CLOUD_FOLDER_ID') && strlen(YANDEX_CLOUD_FOLDER_ID))
		{
			try
			{
				$requestData = json_encode(
					array(
						'folder_id' => YANDEX_CLOUD_FOLDER_ID,
						'texts' => $string,
						'targetLanguageCode' => 'en'
					)
				);

				$Core_Http = Core_Http::instance('curl');
				$Core_Http
					->clear()
					->additionalHeader('Authorization', 'Api-Key ' . YANDEX_CLOUD_SECRET_KEY)
					->contentType('application/json')
					->method('POST')
					->url('https://translate.api.cloud.yandex.net/translate/v2/translate')
					->rawData($requestData)
					->execute();

				$data = trim($Core_Http->getDecompressedBody());

				if (strlen($data))
				{
					$oData = json_decode($data);

					if (is_object($oData) && isset($oData->translations[0]))
					{
						return $oData->translations[0]->text;
					}
				}
			}
			catch (Exception $e){}
		}
		// Yandex Translate available until Aug 15, 2020
		elseif (defined('YANDEX_TRANSLATE_KEY') && strlen(YANDEX_TRANSLATE_KEY))
		{
			try
			{
				$url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?' .
					'key=' . urlencode(YANDEX_TRANSLATE_KEY) .
					'&text=' . urlencode($string) .
					'&lang=en&format=plain';

				$Core_Http = Core_Http::instance()
					->url($url)
					->timeout(3)
					->execute();

				$data = trim($Core_Http->getDecompressedBody());

				if (strlen($data))
				{
					$oData = json_decode($data);

					if (is_object($oData) && $oData->code == 200 && isset($oData->text[0]))
					{
						return $oData->text[0];
					}
				}
			}
			catch (Exception $e){}
		}
		/*else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Can not translate. Constant YANDEX_TRANSLATE_KEY is undefined.');
		}*/

		return NULL;
	}

	/**
	 * Transliteration
	 * @param string $string source string
	 * @return string
	 */
	static public function transliteration($string)
	{
		$string = mb_strtolower(trim(strval($string)));

		$aConfig = Core::$config->get('core_str') + array(
			'spaceSeparator' => '-',
			// ISO 9
			'transliteration' => array(
				'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
				'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm',
				'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
				'ф' => 'f', 'х' => 'x', 'ч' => 'ch', 'ц' => 'cz', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '',
				'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
			)
		);

		$spaceSeparator = $aConfig['spaceSeparator'];

		// Умляуты и другие кодировки
		$uml_search = array('À','Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ',
		'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ',
		'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'ĸ',
		'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š',
		'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'Ǆ', 'ǅ', 'ǆ', 'Ǉ', 'ǈ', 'ǉ', 'Ǌ', 'ǋ', 'ǌ',
		'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'ǝ', 'Ǟ', 'ǟ', 'Ǡ', 'ǡ', 'Ǣ', 'ǣ', 'Ǥ', 'ǥ', 'Ǧ', 'ǧ', 'Ǩ', 'ǩ', 'Ǫ', 'ǫ', 'Ǭ', 'ǭ', 'Ǯ', 'ǯ', 'ǰ', 'Ǳ', 'ǲ', 'ǳ',
		'Ǵ', 'ǵ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ȁ', 'ȁ', 'Ȃ', 'ȃ', 'Ȅ', 'ґ', 'є', 'і', 'ї', 'Ґ', 'Є', 'І', 'Ї', 'ô');

		$uml_replace = array('a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'd', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'ss', 'a', 'a', 'a', 'a', 'a', 'a', 'ae',
		'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'd', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'd',
		'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'h', 'h', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'j', 'j', 'k', 'k', 'k',
		'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'ce', 'ce', 'r', 'r', 'r', 'r', 'r', 'r', 's', 's', 's', 's', 's', 's', 's', 's',
		't', 't', 't', 't', 't', 't', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'w', 'w', 'y', 'y', 'y', 'z', 'z', 'z', 'z', 'z', 'z', 'dz', 'dz', 'dz', 'lj', 'lj', 'kj', 'nj', 'nj', 'nj',
		'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'e', 'a', 'a', 'a', 'a', 'ae', 'ae', 'g', 'g', 'g', 'g', 'k', 'k', 'o', 'o', 'o', 'o', 'z', 'z', 'z', 'dz', 'dz', 'dz',
		'g', 'g', 'a', 'a', 'ae', 'ae', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'g', 'ye', 'i', 'yi', 'G', 'Ye', 'I', 'I', 'o');

		$string = str_replace($uml_search, $uml_replace, $string);

		// Transliteration
		$string = str_replace(array_keys($aConfig['transliteration']), array_values($aConfig['transliteration']), $string);

		// Space and no-break space (0x00A0)
		$string = str_replace(array(' ', ' '), $spaceSeparator, $string);

		// Cut another chars
		$string = preg_replace('/[^a-zA-Z0-9\-_]/u', '', $string);

		// Rerplace double $spaceSeparator
		while (mb_strpos($string, $spaceSeparator . $spaceSeparator) !== FALSE)
		{
			$string = str_replace($spaceSeparator . $spaceSeparator, $spaceSeparator, $string);
		}

		return $string;
	}

	/**
	 * Convert HTML entity to numeric character reference (NCR)
	 * @param string $str source string
	 * @return string
	 */
	static public function str2ncr($str)
	{
		$aEntities = array(
			'&amp;nbsp;', '&amp;iexcl;', '&amp;cent;', '&amp;pound;', '&amp;curren;', '&amp;yen;', '&amp;brvbar;', '&amp;sect;', '&amp;uml;', '&amp;copy;', '&amp;ordf;', '&amp;laquo;', '&amp;not;', '&amp;shy;', '&amp;reg;', '&amp;macr;', '&amp;deg;', '&amp;plusmn;', '&amp;sup2;', '&amp;sup3;', '&amp;acute;', '&amp;micro;', '&amp;para;', '&amp;middot;', '&amp;cedil;', '&amp;sup1;', '&amp;ordm;', '&amp;raquo;', '&amp;frac14;', '&amp;frac12;', '&amp;frac34;', '&amp;iquest;', '&amp;agrave;', '&amp;aacute;', '&amp;acirc;', '&amp;atilde;', '&amp;auml;', '&amp;aring;', '&amp;aelig;', '&amp;ccedil;', '&amp;egrave;', '&amp;eacute;', '&amp;ecirc;', '&amp;euml;', '&amp;igrave;', '&amp;iacute;', '&amp;icirc;', '&amp;iuml;', '&amp;eth;', '&amp;ntilde;', '&amp;ograve;', '&amp;oacute;', '&amp;ocirc;', '&amp;otilde;', '&amp;ouml;', '&amp;times;', '&amp;oslash;', '&amp;ugrave;', '&amp;uacute;', '&amp;ucirc;', '&amp;uuml;', '&amp;yacute;', '&amp;thorn;', '&amp;szlig;', '&amp;agrave;', '&amp;aacute;', '&amp;acirc;', '&amp;atilde;', '&amp;auml;', '&amp;aring;', '&amp;aelig;', '&amp;ccedil;', '&amp;egrave;', '&amp;eacute;', '&amp;ecirc;', '&amp;euml;', '&amp;igrave;', '&amp;iacute;', '&amp;icirc;', '&amp;iuml;', '&amp;eth;', '&amp;ntilde;', '&amp;ograve;', '&amp;oacute;', '&amp;ocirc;', '&amp;otilde;', '&amp;ouml;', '&amp;divide;', '&amp;oslash;', '&amp;ugrave;', '&amp;uacute;', '&amp;ucirc;', '&amp;uuml;', '&amp;yacute;', '&amp;thorn;', '&amp;yuml;', '&amp;quot;', '&amp;amp;', '&amp;lt;', '&amp;gt;', '&amp;oelig;', '&amp;oelig;', '&amp;scaron;', '&amp;scaron;', '&amp;yuml;', '&amp;circ;', '&amp;tilde;', '&amp;ensp;', '&amp;emsp;', '&amp;thinsp;', '&amp;zwnj;', '&amp;zwj;', '&amp;lrm;', '&amp;rlm;', '&amp;ndash;', '&amp;mdash;', '&amp;lsquo;', '&amp;rsquo;', '&amp;sbquo;', '&amp;ldquo;', '&amp;rdquo;', '&amp;bdquo;', '&amp;dagger;', '&amp;dagger;', '&amp;permil;', '&amp;lsaquo;', '&amp;rsaquo;', '&amp;euro;', '&amp;fnof;', '&amp;alpha;', '&amp;beta;', '&amp;gamma;', '&amp;delta;', '&amp;epsilon;', '&amp;zeta;', '&amp;eta;', '&amp;theta;', '&amp;iota;', '&amp;kappa;', '&amp;lambda;', '&amp;mu;', '&amp;nu;', '&amp;xi;', '&amp;omicron;', '&amp;pi;', '&amp;rho;', '&amp;sigma;', '&amp;tau;', '&amp;upsilon;', '&amp;phi;', '&amp;chi;', '&amp;psi;', '&amp;omega;', '&amp;alpha;', '&amp;beta;', '&amp;gamma;', '&amp;delta;', '&amp;epsilon;', '&amp;zeta;', '&amp;eta;', '&amp;theta;', '&amp;iota;', '&amp;kappa;', '&amp;lambda;', '&amp;mu;', '&amp;nu;', '&amp;xi;', '&amp;omicron;', '&amp;pi;', '&amp;rho;', '&amp;sigmaf;', '&amp;sigma;', '&amp;tau;', '&amp;upsilon;', '&amp;phi;', '&amp;chi;', '&amp;psi;', '&amp;omega;', '&amp;thetasym;', '&amp;upsih;', '&amp;piv;', '&amp;bull;', '&amp;hellip;', '&amp;prime;', '&amp;prime;', '&amp;oline;', '&amp;frasl;', '&amp;weierp;', '&amp;image;', '&amp;real;', '&amp;trade;', '&amp;alefsym;', '&amp;larr;', '&amp;uarr;', '&amp;rarr;', '&amp;darr;', '&amp;harr;', '&amp;crarr;', '&amp;larr;', '&amp;uarr;', '&amp;rarr;', '&amp;darr;', '&amp;harr;', '&amp;forall;', '&amp;part;', '&amp;exist;', '&amp;empty;', '&amp;nabla;', '&amp;isin;', '&amp;notin;', '&amp;ni;', '&amp;prod;', '&amp;sum;', '&amp;minus;', '&amp;lowast;', '&amp;radic;', '&amp;prop;', '&amp;infin;', '&amp;ang;', '&amp;and;', '&amp;or;', '&amp;cap;', '&amp;cup;', '&amp;int;', '&amp;there4;', '&amp;sim;', '&amp;cong;', '&amp;asymp;', '&amp;ne;', '&amp;equiv;', '&amp;le;', '&amp;ge;', '&amp;sub;', '&amp;sup;', '&amp;nsub;', '&amp;sube;', '&amp;supe;', '&amp;oplus;', '&amp;otimes;', '&amp;perp;', '&amp;sdot;', '&amp;lceil;', '&amp;rceil;', '&amp;lfloor;', '&amp;rfloor;', '&amp;lang;', '&amp;rang;', '&amp;loz;', '&amp;spades;', '&amp;clubs;', '&amp;hearts;', '&amp;diams;'
		);

		$aReplace = array(
			'&#160;', '&#161;', '&#162;', '&#163;', '&#164;', '&#165;', '&#166;', '&#167;', '&#168;', '&#169;', '&#170;', '&#171;', '&#172;', '&#173;', '&#174;', '&#175;', '&#176;', '&#177;', '&#178;', '&#179;', '&#180;', '&#181;', '&#182;', '&#183;', '&#184;', '&#185;', '&#186;', '&#187;', '&#188;', '&#189;', '&#190;', '&#191;', '&#192;', '&#193;', '&#194;', '&#195;', '&#196;', '&#197;', '&#198;', '&#199;', '&#200;', '&#201;', '&#202;', '&#203;', '&#204;', '&#205;', '&#206;', '&#207;', '&#208;', '&#209;', '&#210;', '&#211;', '&#212;', '&#213;', '&#214;', '&#215;', '&#216;', '&#217;', '&#218;', '&#219;', '&#220;', '&#221;', '&#222;', '&#223;', '&#224;', '&#225;', '&#226;', '&#227;', '&#228;', '&#229;', '&#230;', '&#231;', '&#232;', '&#233;', '&#234;', '&#235;', '&#236;', '&#237;', '&#238;', '&#239;', '&#240;', '&#241;', '&#242;', '&#243;', '&#244;', '&#245;', '&#246;', '&#247;', '&#248;', '&#249;', '&#250;', '&#251;', '&#252;', '&#253;', '&#254;', '&#255;', '&#34;', '&#38;', '&#60;', '&#62;', '&#338;', '&#339;', '&#352;', '&#353;', '&#376;', '&#710;', '&#732;', '&#8194;', '&#8195;', '&#8201;', '&#8204;', '&#8205;', '&#8206;', '&#8207;', '&#8211;', '&#8212;', '&#8216;', '&#8217;', '&#8218;', '&#8220;', '&#8221;', '&#8222;', '&#8224;', '&#8225;', '&#8240;', '&#8249;', '&#8250;', '&#8364;', '&#402;', '&#913;', '&#914;', '&#915;', '&#916;', '&#917;', '&#918;', '&#919;', '&#920;', '&#921;', '&#922;', '&#923;', '&#924;', '&#925;', '&#926;', '&#927;', '&#928;', '&#929;', '&#931;', '&#932;', '&#933;', '&#934;', '&#935;', '&#936;', '&#937;', '&#945;', '&#946;', '&#947;', '&#948;', '&#949;', '&#950;', '&#951;', '&#952;', '&#953;', '&#954;', '&#955;', '&#956;', '&#957;', '&#958;', '&#959;', '&#960;', '&#961;', '&#962;', '&#963;', '&#964;', '&#965;', '&#966;', '&#967;', '&#968;', '&#969;', '&#977;', '&#978;', '&#982;', '&#8226;', '&#8230;', '&#8242;', '&#8243;', '&#8254;', '&#8260;', '&#8472;', '&#8465;', '&#8476;', '&#8482;', '&#8501;', '&#8592;', '&#8593;', '&#8594;', '&#8595;', '&#8596;', '&#8629;', '&#8656;', '&#8657;', '&#8658;', '&#8659;', '&#8660;', '&#8704;', '&#8706;', '&#8707;', '&#8709;', '&#8711;', '&#8712;', '&#8713;', '&#8715;', '&#8719;', '&#8721;', '&#8722;', '&#8727;', '&#8730;', '&#8733;', '&#8734;', '&#8736;', '&#8743;', '&#8744;', '&#8745;', '&#8746;', '&#8747;', '&#8756;', '&#8764;', '&#8773;', '&#8776;', '&#8800;', '&#8801;', '&#8804;', '&#8805;', '&#8834;', '&#8835;', '&#8836;', '&#8838;', '&#8839;', '&#8853;', '&#8855;', '&#8869;', '&#8901;', '&#8968;', '&#8969;', '&#8970;', '&#8971;', '&#9001;', '&#9002;', '&#9674;', '&#9824;', '&#9827;', '&#9829;', '&#9830;'
		);

		return str_replace($aEntities, $aReplace, $str);
	}

	/**
	 * Convert IP into hexadecimal value
	 * @param string $ip IP
	 * @return mixed
	 */
	static public function ip2hex($ip)
	{
		if (Core_Valid::ip($ip))
		{
			$ip_code = explode('.', $ip);

			if (isset($ip_code[3]))
			{
				return sprintf('%02x%02x%02x%02x', $ip_code[0], $ip_code[1], $ip_code[2], $ip_code[3]);
			}
		}

		return NULL;
	}

	/**
	 * Convert hexadecimal value into IP
	 * @param string $hex source value
	 * @return string
	 */
	static public function hex2ip($hex)
	{
		$aHex = explode('.', chunk_split($hex, 2, '.'));

		$aReturn = array ();

		if (count($aHex) > 0)
		{
			foreach ($aHex as $field)
			{
				if (!empty($field))
				{
					$aReturn[] = hexdec($field);
				}
			}
		}

		return implode('.', $aReturn);
	}

	/**
	 * Callback function
	 */
	static protected function _callbackChr($matches)
	{
		return chr($matches[1]);
	}

	/**
	 * Метод очищает HTML от ненужных тегов, хеширует и возвращает массив хэшей слов
	 *
	 * @param string $text исходный текст;
	 * @param array $param массив дополнительных параметров
	 * - $param['hash_function'] = 'md5' {'md5','crc32',''} используемая ХЭШ-функция;
	 *
	 * @return array массив хэшей слов
	 */
	static public function getHashes($text, $param = array())
	{
		$aConfig = Core::$config->get('core_str') + array(
			'stopWords' => '/ (и|в|во|не|что|он|на|я|с|со|как|а|то|все|всё|она|так|его|но|да|ты|к|у|же|вы|за|бы|по|только|её|ее|мне|было|вот|от|меня|ещё|еще|нет|о|из|то|ему|теперь|когда|даже|ну|вдруг|ли|если|уже|или|ни|быть|был|него|до|вас|нибудь|опять|уж|вам|сказал|ведь|там|потом|себя|ничего|ей|может|они|тут|где|есть|надо|ней|для|мы|тебя|их|чем|была|сам|чтоб|без|будто|человек|чего|раз|тоже|себе|под|будет|ж|тогда|кто|этот|говорил|того|потому|этого|какой|совсем|ним|здесь|этом|один|почти|мой|тем|чтобы|нее|кажется|сейчас|были|куда|зачем|сказать|всех|никогда|сегодня|можно|при|наконец|два|об|другой|хоть|после|над|больше|тот|через|эти|нас|про|всего|них|какая|много|разве|сказала|три|эту|моя|впрочем|хорошо|свою|этой|перед|иногда|лучше|чуть|том|нельзя|такой|им|более|всегда|конечно|всю|между) /u',
			// 0xC2A0 (C2 A0) - NO-BREAK SPACE, http://www.utf8-chartable.de/
			'separators' => array("\"", "&", "|", "_", "#", "$", "/", "\\", "@", "<", ">", ".", ",", ";", "*", ":", "?", "!", "'", "-", "=", "{", "}", "(", ")", "«", "»", "…", chr(0xC2).chr(0xA0)),
			'splitNumberAndAlpha' => TRUE,
			'replaces' => array('ё' => 'е'),
		);

		$text = str_replace(array('<br', '<p', '<div'), array(' <br', ' <p', ' <div'), $text);
		$text = strip_tags($text);

		!isset($param['hash_function']) && $param['hash_function'] = 'md5';

		// Замены, например ё => 'е'
		if (count($aConfig['replaces']))
		{
			$text = str_replace(array_keys($aConfig['replaces']), array_values($aConfig['replaces']), $text);
		}

		$text = mb_strtolower($text);

		// Разделять числа и символы
		if ($aConfig['splitNumberAndAlpha'])
		{
			$replace = array(
				"/([a-zA-Zа-яА-ЯёЁ])([0-9])/iu" => '\\1 \\2',
				"/([0-9])([a-zA-Zа-яА-ЯёЁ])/iu" => '\\1 \\2'
			);

			$text = preg_replace(array_keys($replace), array_values($replace), $text);
		}

		$text = str_replace(array("\n", "\r"), array(' ', ''), $text);

		// Дополняем пробелами для правильного удаления стоп-слов '<пробел><стоп-слово><пробел>',
		// удаляем из текста стоп слова
		$text = preg_replace($aConfig['stopWords'], ' ', ' ' . $text . ' ');

		$text = str_replace('\\', '', $text);

		// Удаляем скрипты, теги и спецсимволы
		$search = array (/*"'<script[^>]*?>.*?</script>'siu",
		"'<style[^>]*?>.*?</style>'siu",
		"'<select[^>]*?>.*?</select>'siu",
		"'<head[^>]*?>.*?</head>'siu",*/
		"'<[^>]*?>'",
		"'([\r\n])[\s]+'",
		"'&(quot|#34);'i",
		"'&(amp|#38);'i",
		"'&(lt|#60);'i",
		"'&(gt|#62);'i",
		"'&(nbsp|#160);'i", // without 'u
		"'&(cent|#162);'i",
		"'&(pound|#163);'i",
		"'&(copy|#169);'i",
		"'&(laquo|#171);'i",
		"'&(raquo|#187);'i",
		"'[ ]+ '");

		$replace = array (/*" ",
		" ",
		" ",
		" ",*/
		" ",
		"\\1",
		"\"",
		"&",
		"<",
		">",
		" ",
		"¢",
		"£",
		"©",
		"«",
		"»",
		" ");

		$text = preg_replace($search, $replace, $text);

		$text = preg_replace_callback('(&#(\d+);)', 'Core_Str::_callbackChr', $text);

		$text = str_replace($aConfig['separators'], ' ', $text);

		// Убираем двойные пробелы
		while (strstr($text, '  '))
		{
			$text = str_replace('  ', ' ', $text);
		}

		$text = trim($text);
		$result = explode(' ', $text);

		// Нормализация и хеширование слов
		foreach ($result as $key => $res)
		{
			$word = $res;

			switch ($param['hash_function'])
			{
				case '' :
					$result[$key] = $word;
				break;
				default:
				case 'md5' :
					$result[$key] = md5($word);
				break;
				case 'crc32' :
					$result[$key] = Core::crc32($word);
				break;
			}
		}

		return $result;
	}

	/**
	 * Получение существительного в форме, соответствующей числу
	 *
	 * @param int $int число, с которым связано существительное
	 * @param string $word основа слова
	 * @param array $aEndings массив окончаний слова
	 * <code>
	 * <?php
	 *
	 * $word = 'новост';
	 * $aEndings = array('ей', 'ь', 'и', 'и', 'и', 'ей', 'ей', 'ей', 'ей', 'ей');
	 *
	 * for ($int = 0; $int < 100; $int++)
	 * {
	 * 	$result = Core_Str::declension($int, $word, $aEndings);
	 * 	echo "{$num} {$result} <br />";
	 * }
	 * ?>
	 * </code>
	 * @return string
	 */
	static public function declension($int, $word, $aEndings)
	{
		$lastInt = $int % 100;

		$lastInt = $lastInt > 10 && $lastInt < 20
			? 9
			: $int % 10;

		return $word . $aEndings[$lastInt];
	}

	/**
	 * Array for _stripTagsCallback()
	 * @var array
	 */
	static protected $_aDisabledAttributes = array();

	/**
	 * stripTags() callback
	 */
	static protected function _stripTagsCallback($matches)
	{
		return '<' . preg_replace(array('/javascript:[^"\']*/iu', '/(' . implode('|', self::$_aDisabledAttributes) . ')[ \t\n]*=[ \t\n]*["\'][^"\']*["\']/i', '/\s+/'), array('', '', ' '), stripslashes($matches[1])) . '>';
	}

	/**
	 * Удаление HTML-тегов вместе с атрибутами
	 *
	 * @param string $source Исходная строка
	 * @param string $allowedTags Список разрешенных тегов, например, "<b><i><strong>"
	 * @param array $aDisabledAttributes Массив запрещенных атрибутов тегов, например array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload')
	 * @return string
	 */
	static public function stripTags($source, $allowedTags = '', $aDisabledAttributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'))
	{
		$source = strval($source);
		$allowedTags = strval($allowedTags);

		if (empty($aDisabledAttributes))
		{
			$result = strip_tags($source, $allowedTags);
		}
		else
		{
			self::$_aDisabledAttributes = $aDisabledAttributes;

			$result = preg_replace_callback('/<(.*?)>/iu', 'Core_Str::_stripTagsCallback', strip_tags($source, $allowedTags));
		}

		return $result;
	}

	/**
	 * Escaping apostrophe ('), slashes (\), 'script' and line breaks.
	 * @param string $str source string
	 * @return string
	 */
	static public function escapeJavascriptVariable($str)
	{
		return str_replace(
			array("\\", "'", "\r", "\n", "script"),
			array("\\\\", "\'", '\r', '\n', "scr'+'ipt"),
			$str
		);
	}

	/**
	 * Convert HTML line breaks to newlines in a string
	 * @param string $string string
	 * @param string $lineBreak line break character
	 * @return string
	 */
	static public function br2nl($string, $lineBreak = PHP_EOL)
	{
		return preg_replace(array("/(<br>|<br \/>|<br\/>)\s*/i", "/(\r\n|\r|\n)/"), array(PHP_EOL, $lineBreak), $string);
	}

	/**
	 * Convert size from 10M to bytes
	 *
	 * @param string $str e.g. 10M
	 * @return int
	 */
	static public function convertSizeToBytes($str)
	{
		$str = trim($str);
		if (strlen($str) > 0)
		{
			$cLastAlpha = strtolower(substr($str, -1));
			$size = intval($str);
			switch ($cLastAlpha)
			{
				case 't':
					$size *= 1024;
				case 'g':
					$size *= 1024;
				case 'm':
					$size *= 1024;
				case 'k':
					$size *= 1024;
			}
		}
		else
		{
			$size = 0;
		}
		return $size;
	}

	/**
	 * Convert size from bytes to kb, mb, etc
	 *
	 * @param int $int e.g. 20480000
	 * @return int
	 */
	static public function getTextSize($size)
	{
		if ($size >= 1024)
		{
			$textSize = Core::_('Core.kbyte');
			$size = $size / 1024;

			if ($size >= 1024)
			{
				$textSize = Core::_('Core.mbyte');
				$size = $size / 1024;

				if ($size >= 1024)
				{
					$textSize = Core::_('Core.gbyte');
					$size = $size / 1024;
				}
			}

			$size = sprintf('%.2f', $size);
		}
		else
		{
			$textSize = Core::_('Core.byte');
		}

		return $size . ' ' . $textSize;
	}

	/**
	 * Cut first and last slash
	 * @param string URI
	 * @return string
	 */
	static public function trimUri($uri)
	{
		return self::ltrimUri(self::rtrimUri($uri));
	}


	/**
	 * Cut first slash
	 * @param string URI
	 * @return string
	 */
	static public function ltrimUri($uri)
	{
		$uri !== '' && substr($uri, 0, 1) == '/' && $uri = substr($uri, 1);

		return $uri;
	}

	/**
	 * Cut last slash
	 * @param string URI
	 * @return string
	 */
	static public function rtrimUri($uri)
	{
		$uri !== '' && substr($uri, -1, 1) == '/' && $uri = substr($uri, 0, -1);

		return $uri;
	}

	/**
	 * ucfirst for utf-8 string
	 * @param string $str source string
	 * @return string
	 */
	static public function ucfirst($str)
	{
		if (mb_strlen($str))
		{
			$str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
		}
		return $str;
	}

	/**
	 * lcfirst for utf-8 string
	 * @param string $str source string
	 * @return string
	 */
	static public function lcfirst($str)
	{
		if (mb_strlen($str))
		{
			$str = mb_strtolower(mb_substr($str, 0, 1)) . mb_substr($str, 1);
		}
		return $str;
	}

	/**
	 * Get Color By Entity ID
	 * @param int $id Entity ID
	 * @param int $maxColor Max color, 0-255, default 210
	 * @return string HEX color, e.g. #B781AF
	 */
	static public function createColor($id, $maxColor = 210)
	{
		$crc32 = abs(Core::crc32($id));

		return self::rgb2hex(
			Core_Array::randomShuffle(array($crc32 % ($maxColor / 4), $maxColor, $crc32 % $maxColor), $id % 6)
		);
	}

	/**
	 * Convert RGB to HEX color
	 * @param string $hex HEX color, e.g. #B781AF or #FF0
	 * @return string HEX color, e.g. #B781AF
	 */
	static public function rgb2hex(array $array)
	{
		return count($array) == 3
			? sprintf("#%02x%02x%02x", $array[0], $array[1], $array[2])
			: '#000';
	}

	/**
	 * Convert HEX color to RGB or RGBA
	 * @param string $hex HEX color, e.g. #B781AF or #FF0
	 * @param float|NULL opacity between 0 and 1, e.g. 0.85
	 * @return string
	 */
	static public function hex2rgba($hex, $opacity = NULL)
	{
		$default = 'rgb(0,0,0)';

		if (empty($hex))
		{
			return $default;
		}

		$hex = ltrim($hex, '#');

		//Check if color has 6 or 3 characters and get values
		if (strlen($hex) == 6)
		{
			$hex = str_split($hex, 2);
		}
		elseif (strlen($hex) == 3)
		{
			$hex = array($hex[0] . $hex[0], $hex[1] . $hex[1], $hex[2] . $hex[2]);
		}
		else
		{
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map('hexdec', $hex);

		//Check if opacity is set(rgba or rgb)
		if ($opacity)
		{
			abs($opacity) > 1 && $opacity = 1;

			$return = 'rgba(' . implode(',', $rgb) . ',' . $opacity . ')';
		}
		else
		{
			$return = 'rgb(' . implode(',', $rgb) . ')';
		}

		return $return;
	}

	/**
	 * Lighter HEX color
	 * @param string $hex HEX color, e.g. #B781AF or #FF0
	 * @param float opacity between 0 and 1, e.g. 0.85
	 */
	static public function hex2lighter($hex, $opacity)
	{
		$default = '#FFF';

		if (empty($hex))
		{
			return $default;
		}

		$hex = ltrim($hex, '#');

		// Check if color has 6 or 3 characters and get values
		if (strlen($hex) == 6)
		{
			$hex = str_split($hex, 2);
		}
		elseif (strlen($hex) == 3)
		{
			$hex = array($hex[0] . $hex[0], $hex[1] . $hex[1], $hex[2] . $hex[2]);
		}
		else
		{
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map('hexdec', $hex);

		foreach ($rgb as $key => $iColor)
		{
			$k = $iColor + floor((255 - $iColor) * $opacity);
			$rgb[$key] = $k < 255 ? $k : 255;
		}

		//$rgb = array_map('dechex', $rgb);
		//return '#' . implode('', $rgb);

		return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
	}

	/**
	 * Darker HEX color
	 * @param string $hex HEX color, e.g. #B781AF or #FF0
	 * @param float opacity between 0 and 1, e.g. 0.85
	 */
	static public function hex2darker($hex, $opacity)
	{
		$default = '#000';

		if (empty($hex))
		{
			return $default;
		}

		$hex = ltrim($hex, '#');

		// Check if color has 6 or 3 characters and get values
		if (strlen($hex) == 6)
		{
			$hex = str_split($hex, 2);
		}
		elseif (strlen($hex) == 3)
		{
			$hex = array($hex[0] . $hex[0], $hex[1] . $hex[1], $hex[2] . $hex[2]);
		}
		else
		{
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map('hexdec', $hex);

		foreach ($rgb as $key => $iColor)
		{
			$k = $iColor - floor((255 - $iColor) * $opacity);
			$rgb[$key] = $k > 0 ? $k : 0;
		}

		//$rgb = array_map('dechex', $rgb);
		//return '#' . implode('', $rgb);

		return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
	}

	/**
	 * Возвращает строку согласно падежам
	 * @param int $number number
	 * @param string $nominative Nominative case
	 * @param $genitive_singular Genitive singular case
	 * @param $genitive_plural Genitive plural case
	 * @return string
	 */
	static public function declensionNumber($number = 0, $nominative, $genitive_singular, $genitive_plural)
	{
		$last_digit = $number % 10;
		$last_two_digits = $number % 100;

		if ($last_digit == 1 && $last_two_digits != 11)
		{
			return $nominative;
		}
		elseif (($last_digit == 2 && $last_two_digits != 12) || ($last_digit == 3 && $last_two_digits != 13) || ($last_digit == 4 && $last_two_digits != 14))
		{
			return $genitive_singular;
		}
		else
		{
			return $genitive_plural;
		}
	}

	/**
	 * Remove emoji (UTF8 4 Byte characters)
	 * @param string $str source string
	 * @return string
	 */
	static public function removeEmoji($str)
	{
		 return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F415}](?:\x{200D}\x{1F9BA})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BD})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9AF})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}-\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6D5}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6FA}\x{1F7E0}-\x{1F7EB}\x{1F90D}-\x{1F93A}\x{1F93C}-\x{1F945}\x{1F947}-\x{1F971}\x{1F973}-\x{1F976}\x{1F97A}-\x{1F9A2}\x{1F9A5}-\x{1F9AA}\x{1F9AE}-\x{1F9CA}\x{1F9CD}-\x{1F9FF}\x{1FA70}-\x{1FA73}\x{1FA78}-\x{1FA7A}\x{1FA80}-\x{1FA82}\x{1FA90}-\x{1FA95}]/u', '', $str);

		//return preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]/u', '', $str);
	}

	static public function getInitials($fullName, $length = 2)
	{
		$fullName = mb_strtoupper(trim($fullName));
		$aFullName = explode(' ', $fullName);

		$initials = array_reduce(str_replace(array('*', '"'), '', $aFullName), array('Core_Str', '_getInitialsReduce'));

		$initials = mb_strlen($initials) < $length
			? mb_substr($fullName, 0, $length)
			: mb_substr($initials, 0, $length);

		return $initials;
	}

	static protected function _getInitialsReduce($str, $item)
	{
		return $str . mb_substr($item, 0, 1);
	}

	/**
	 * Decode Punycode IDN
	 * https://www.ietf.org/rfc/rfc3492.txt
	 *
	 * @param string $domain
	 * @return string
	 */
	static public function idnToUtf8($domain)
	{
		if (function_exists('idn_to_utf8'))
		{
			// fix INTL_IDNA_VARIANT_2003 is deprecated
			return version_compare(PHP_VERSION, '7.2.0', '>=') && version_compare(PHP_VERSION, '7.4.0', '<')
				? idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)
				: idn_to_utf8($domain);
		}

		$domain = mb_strtolower($domain);

		$aDomains = explode('.', $domain);
		if (count($aDomains) > 1)
		{
			$sTmp = '';
			foreach ($aDomains as $sSubdomain)
			{
				$sTmp .= '.' . self::idnToUtf8($sSubdomain);
			}
			return substr($sTmp, 1);
		}

		// search prefix
		if (substr($domain, 0, 4) != 'xn--')
		{
			return $domain;
		}
		else
		{
			$bad_input = $domain;
			$domain = substr($domain, 4);
		}

		$i = 0;
		$bias = 72;
		$initial_n = 128;
		$output = array();

		// search delimiter
		$delimiter = strrpos($domain, '-');

		if ($delimiter)
		{
			for ($j = 0; $j < $delimiter; $j++)
			{
				$c = $domain[$j];
				$output[] = $c;
				if ($c > 0x7F)
				{
					return $bad_input;
				}
			}
			$delimiter++;
		}
		else
		{
			$delimiter = 0;
		}

		while ($delimiter < strlen($domain))
		{
			$iPrev = $i;
			$w = 1;

			for ($k = 36;; $k += 36)
			{
				if ($delimiter == strlen($domain))
				{
					return $bad_input;
				}
				$c = $domain[$delimiter++];
				$c = ord($c);

				$digit = ($c - 48 < 10)
					? $c - 22
					: ($c - 65 < 26
						? $c - 65
						: ($c - 97 < 26 ? $c - 97 : 36)
					);

				if ($digit > (0x10FFFF - $i) / $w)
				{
					return $bad_input;
				}
				$i += $digit * $w;

				if ($k <= $bias)
				{
					$t = 1;
				}
				elseif ($k >= $bias + 26)
				{
					$t = 26;
				}
				else
				{
					$t = $k - $bias;
				}

				if ($digit < $t)
				{
					break;
				}

				$w *= 36 - $t;

			}

			$delta = $i - $iPrev;

			$delta = ($iPrev == 0) ? $delta / 700 : $delta >> 1;

			$count_output_plus_one = count($output) + 1;
			$delta += intval($delta / $count_output_plus_one);

			$k2 = 0;
			while ($delta > 455)
			{
				$delta /= 35;
				$k2 += 36;
			}
			$bias = intval($k2 + 36 * $delta / ($delta + 38));

			if ($i / $count_output_plus_one > 0x10FFFF - $initial_n)
			{
				return $bad_input;
			}
			$initial_n += intval($i / $count_output_plus_one);
			$i %= $count_output_plus_one;
			array_splice($output, $i, 0, html_entity_decode( '&#' . $initial_n . ';', ENT_NOQUOTES, 'UTF-8'));
			$i++;
		}

		return implode('', $output);
	}

	/**
	 * Encode to IDN ASCII
	 * https://www.ietf.org/rfc/rfc3492.txt
	 *
	 * @param string $domain
	 * @return string
	 */
	static public function idnToAscii($domain)
	{
		if (function_exists('idn_to_ascii'))
		{
			// fix INTL_IDNA_VARIANT_2003 is deprecated
			return version_compare(PHP_VERSION, '7.2.0', '>=') && version_compare(PHP_VERSION, '7.4.0', '<')
				? idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)
				: idn_to_ascii($domain);
		}

		$domain = mb_strtolower($domain);

		$aDomains = explode('.', $domain);
		if (count($aDomains) > 1)
		{
			$sTmp = '';
			foreach ($aDomains as $sSubdomain)
			{
				$sTmp .= '.' . self::idnToAscii($sSubdomain);
			}
			return substr($sTmp, 1);
		}

		// http://tools.ietf.org/html/rfc3492#section-6.3
		$delta = 0;
		$bias = 72;
		$initial_n = 128;
		$aOutput = array();

		$aStrAsArray = array();
		$sTmp = $domain;
		while (mb_strlen($sTmp))
		{
			array_push($aStrAsArray, mb_substr($sTmp, 0, 1));
			$sTmp = mb_substr($sTmp, 1);
		}

		// basic symbols
		$aBasicSymbols = preg_grep('/[\x00-\x7f]/', $aStrAsArray);

		if ($aBasicSymbols == $aStrAsArray)
		{
			return $domain;
		}

		$iBasicSymbols = count($aBasicSymbols);

		if ($iBasicSymbols > 0)
		{
			$aOutput = $aBasicSymbols;
			$aOutput[] = '-';
		}

		// add prefix
		array_unshift($aOutput, 'xn--');

		$iStrAsArray = count($aStrAsArray);
		$iPrev = $iBasicSymbols;

		while ($iPrev < $iStrAsArray)
		{
			$m = 0x10FFFF;

			for ($i = 0; $i < $iStrAsArray; $i++)
			{
				$ord_input[$i] = self::_idnOrd($aStrAsArray[$i]);
				if ($ord_input[$i] >= $initial_n && $ord_input[$i] < $m)
				{
					$m = $ord_input[$i];
				}
			}

			if ($m - $initial_n > 0x10FFFF / ($iPrev + 1))
			{
				return $domain;
			}

			$delta += ($m - $initial_n) * ($iPrev + 1);
			$initial_n = $m;

			for ($i = 0; $i < $iStrAsArray; ++$i)
			{
				$c = $ord_input[$i];
				if ($c < $initial_n)
				{
					$delta++;

					if ($delta == 0)
					{
						return $domain;
					}
				}

				if ($c == $initial_n)
				{
					$q = $delta;
					for ($k = 36;; $k += 36)
					{
						if ($k <= $bias)
						{
							$t = 1;
						}
						elseif ($k >= ($bias + 26))
						{
							$t = 26;
						}
						else
						{
							$t = $k - $bias;
						}

						if ($q < $t)
						{
							break;
						}

						$tmp_int = $t + (($q - $t) % (36 - $t));

						$aOutput[] = chr($tmp_int + 22 + 75 * ($tmp_int < 26));

						$q = ($q - $t) / (36 - $t);
					}

					$aOutput[] = chr($q + 22 + 75 * ($q < 26));

					$delta = $iPrev == $iBasicSymbols ? $delta / 700 : $delta >> 1;

					$delta += ($delta / ($iPrev + 1));

					$k2 = 0;
					while ($delta > 455)
					{
						$delta /= 35;
						$k2 += 36;
					}

					$bias = intval($k2 + (36 * $delta) / ($delta + 38));

					$delta = 0;
					$iPrev++;
				}
			}

			$delta++;
			$initial_n++;
		}

		return implode('', $aOutput);
	}

	/**
	 * for idnToAscii()
	 */
	static protected function _idnOrd($char, $index = 0, &$iBytes = NULL)
	{
		$len = strlen($char);

		$iBytes = 0;

		if ($index >= $len)
		{
			return FALSE;
		}

		$byte = ord($char[$index]);

		if ($byte <= 0x7F)
		{
			$iBytes = 1;
			return $byte;
		}
		elseif ($byte < 0xC2)
		{
			return FALSE;
		}
		elseif ($byte <= 0xDF && $index < $len - 1)
		{
			$iBytes = 2;
			return ($byte & 0x1F) << 6 | (ord($char[$index + 1]) & 0x3F);
		}
		elseif ($byte <= 0xEF && $index < $len - 2)
		{
			$iBytes = 3;
			return ($byte & 0x0F) << 12 | (ord($char[$index + 1]) & 0x3F) << 6 | (ord($char[$index + 2]) & 0x3F);
		}
		elseif ($byte <= 0xF4 && $index < $len - 3)
		{
			$iBytes = 4;
			return ($byte & 0x0F) << 18 | (ord($char[$index + 1]) & 0x3F) << 12 | (ord($char[$index + 2]) & 0x3F) << 6 | (ord($char[$index + 3]) & 0x3F);
		}

		return FALSE;
	}

	/**
	 * Sanitize phone numbers
	 * @return string
	 */
	static public function sanitizePhoneNumber($phone)
	{
		$phone = preg_replace('/^\+\s*[0-9]{1,4}\s+/', '', $phone);
		$phone = preg_replace('/^8\s{1,}\(/', '', $phone);
		$phone = preg_replace('/[^0-9]/', '', $phone);
		$phone = substr($phone, -10);

		return $phone;
	}
}