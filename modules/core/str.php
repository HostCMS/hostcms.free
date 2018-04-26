<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * String helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		if (defined('YANDEX_TRANSLATE_KEY') && strlen(YANDEX_TRANSLATE_KEY))
		{
			$url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?' .
				'key=' . urlencode(YANDEX_TRANSLATE_KEY) .
				'&text=' . urlencode($string) .
				'&lang=en&format=plain';

			$Core_Http = Core_Http::instance()
				->url($url)
				->timeout(3)
				->execute();

			$data = trim($Core_Http->getBody());

			if (strlen($data))
			{
				$oData = json_decode($data);

				if (is_object($oData) && $oData->code == 200 && isset($oData->text[0]))
				{
					return $oData->text[0];
				}
			}
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
		$uml_search = array('À','Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'ĸ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'Ǆ', 'ǅ', 'ǆ', 'Ǉ', 'ǈ', 'ǉ', 'Ǌ', 'ǋ', 'ǌ', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'ǝ', 'Ǟ', 'ǟ', 'Ǡ', 'ǡ', 'Ǣ', 'ǣ', 'Ǥ', 'ǥ', 'Ǧ', 'ǧ', 'Ǩ', 'ǩ', 'Ǫ', 'ǫ', 'Ǭ', 'ǭ', 'Ǯ', 'ǯ', 'ǰ', 'Ǳ', 'ǲ', 'ǳ', 'Ǵ', 'ǵ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ȁ', 'ȁ', 'Ȃ', 'ȃ', 'Ȅ', 'ґ', 'є', 'і', 'ї', 'Ґ', 'Є', 'І', 'Ї', 'ô');

		$uml_replace = array('a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','d','n','o','o','o','o','o','o','u','u','u','u','y','ss','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','d','n','o','o','o','o','o','o','u','u','u','u','y','y','a','a','a','a','a','a','c','c','c','c','c','c','c','c','d','d','d','d','e','e','e','e','e','e','e','e','e','e','g','g','g','g','g','g','g','g','h','h','h','i','i','i','i','i','i','i','i','i','i','j','j','k','k','k','l','l','l','l','l','l','l','l','l','l','n','n','n','n','n','n','n','n','n','o','o','o','o','o','o','ce','ce','r','r','r','r','r','r','s','s','s','s','s','s','s','s','t','t','t','t','t','t','u','u','u','u','u','u','u','u','u','u','u','u','w','w','y','y','y','z','z','z','z','z','z','dz','dz','dz','lj','lj','kj','nj','nj','nj','a','a','i','i','o','o','u','u','u','u','u','u','u','u','u','u','e','a','a','a','a','ae','ae','g','g','g','g','k','k','o','o','o','o','z','z','z','dz','dz','dz','g','g','a','a','ae','ae','o','o','a','a','a','a','e','g', 'ye', 'i', 'yi', 'G', 'Ye', 'I', 'I', 'o');

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
		while (stristr($text, '  '))
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
	 * Convert BR to NL
	 * @param string $string source string
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
	 * Convert HEX color to RGB or RGBA
	 * @param string $hex HEX color, e.g. #B781AF or #FF0
	 * @param float|NULL opacity between 0 and 1, e.g. 0.85
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

		foreach ($rgb as $key => $iColor)
		{
			$k = $iColor + floor((255 - $iColor) * $opacity);
			$rgb[$key] = $k < 255 ? $k : 255;
		}

		$rgb = array_map('dechex', $rgb);

		return '#' . implode('', $rgb);
	}
}