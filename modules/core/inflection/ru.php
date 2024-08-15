<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Russian inflection.
 *
 * @package HostCMS
 * @subpackage Core\Inflection
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Inflection_Ru extends Core_Inflection
{
	/**
	 * Array of irregular form singular => plural
	 * @var array
	 */
	static public $pluralIrregular = array(
		'день' => 'дня,дней',
		'год' => 'года,лет',
	);

	/**
	 * Array of irregular form plural => singular
	 * @var array
	 */
	static public $singularIrregular = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		self::$singularIrregular = array_flip(self::$pluralIrregular);
	}

	/**
	 * Get plural form by singular
	 * @param string $word word
	 * @param int $count
	 * @return string
	 */
	protected function _getPlural($word, $count = NULL)
	{
		$last_digit = $count % 10;
		$last_two_digits = $count % 100;

		// Irregular words
		if (isset(self::$pluralIrregular[$word]))
		{
			$irregular = explode(',', self::$pluralIrregular[$word]);

			if ($last_digit == 1 && $last_two_digits != 11)
			{
				return $word;
			}
			elseif (isset($irregular[0]) &&
				($last_digit == 2 && $last_two_digits != 12
				|| $last_digit == 3 && $last_two_digits != 13
				|| $last_digit == 4 && $last_two_digits != 14))
			{
				return $irregular[0];
			}
			elseif (isset($irregular[1]))
			{
				return $irregular[1];
			}

			return $word;
		}

		if (strlen($word))
		{
			$lastChar = mb_substr($word, -1, 1);

			switch ($lastChar)
			{
				case 'й':
				case 'а':
				case 'я':
				case 'о':
				case 'е':
				case 'ь':
					$cutWord = mb_substr($word, 0, -1);
				break;
				default:
					$cutWord = $word;
			}

			switch ($lastChar)
			{
				case 'а':
					$singular = 'ы';
					$plural = '';
				break;
				case 'б':
				case 'в':
				case 'г':
				case 'д':
				case 'з':
				case 'к':
				case 'л':
				case 'м':
				case 'н':
				case 'п':
				case 'р':
				case 'с':
				case 'т':
				case 'ф':
				case 'х':
					$singular = 'а';
					$plural = 'ов';
				break;
				case 'е':
					$singular = 'я';
					$plural = 'й';
				break;
				case 'ж':
				case 'ч':
				case 'щ':
				case 'ш':
					$singular = 'а';
					$plural = 'ей';
				break;
				case 'й':
					$singular = 'я';
					$plural = 'ев';
				break;
				case 'о':
					$singular = 'а';
					$plural = '';
				break;
				case 'ь':
					$singular = 'я';
					$plural = 'ей';
				break;
				case 'я':
					$singular = 'и';
					$plural = 'й';
				break;
				default:
					$singular = '';
					$plural = '';
			}

			if ($last_digit == 1 && $last_two_digits != 11)
			{
				return $word;
			}
			elseif ($last_digit == 2 && $last_two_digits != 12
				|| $last_digit == 3 && $last_two_digits != 13
				|| $last_digit == 4 && $last_two_digits != 14)
			{
				return $cutWord . $singular;
			}
			else
			{
				return $cutWord . $plural;
			}
		}

		return $word;
	}

	/**
	 * Units
	 * @var array
	 */
	static protected $_aUnits = array(
		array(), //array('копейка', 'копейки', 'копеек', 1),
		array(), //array('рубль', 'рубля', 'рублей', 0),
		array('тысяча', 'тысячи', 'тысяч', 1),
		array('миллион', 'миллиона', 'миллионов', 0),
		array('миллиард', 'милиарда', 'миллиардов', 0),
		array('триллион', 'триллиона', 'триллионов', 0),
		array('квадриллион', 'квадриллиона', 'квадриллионов', 0),
		array('квинтиллион', 'квинтиллиона', 'квинтиллионов', 0),
		array('секстиллион', 'секстиллиона', 'секстиллионов', 0),
	);

	/**
	 * Currency in word
	 * @param float $float
	 * @param string $currencyCode
	 * @return string
	 */
	public function currencyInWords($float, $currencyCode)
	{
		switch ($currencyCode)
		{
			case 'USD':
				$aUnits = self::$_aUnits;
				$aUnits[0] = array('цент', 'цента', 'центов', 1);
				$aUnits[1] = array('доллар', 'доллара', 'долларов', 0);
				return self::numberInWords($float, $aUnits);
			break;
			case 'EUR':
				$aUnits = self::$_aUnits;
				$aUnits[0] = array('цент', 'цента', 'центов', 1);
				$aUnits[1] = array('евро', 'евро', 'евро', 0);
				return self::numberInWords($float, $aUnits);
			break;
			case 'RUB':
			case 'RUR':
			case 'BYN':
				$aUnits = self::$_aUnits;
				$aUnits[0] = array('копейка', 'копейки', 'копеек', 1);
				$aUnits[1] = array('рубль', 'рубля', 'рублей', 0);
				return self::numberInWords($float, $aUnits);
			break;
			case 'UAH':
				$aUnits = self::$_aUnits;
				$aUnits[0] = array('копейка', 'копейки', 'копеек', 1);
				$aUnits[1] = array('гривна', 'гривны', 'гривен', 0);
				return self::numberInWords($float, $aUnits);
			break;
			default:
				return $float . ' ' . $currencyCode;
		}
	}

	/**
	 * Number to str
	 * @param float $float
	 */
	public function numberInWords($float, $aUnits = NULL)
	{
		is_null($aUnits) && $aUnits = self::$_aUnits;

		$float = floatval($float);

		$ten = array(
			0 => array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
			1 => array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')
		);

		if ($float > 0 && $float < 1)
		{
			$ten[0][0] = $ten[1][0] = 'ноль';
		}

		$a20 = array(
			'десять',
			'одиннадцать',
			'двенадцать',
			'тринадцать',
			'четырнадцать',
			'пятнадцать',
			'шестнадцать',
			'семнадцать',
			'восемнадцать',
			'девятнадцать'
		);

		$tens = array('', '', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
		$hundreds = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');

		// Считаем количество необходимых знаков
		$iIntLen = ceil(strlen(sprintf("%.0f", $float)) / 3) * 3 + 3;

		// 3 => 003.00
		// 12345678987.66 => 012345678987.66
		list($iInteger, $fractional) = explode('.', sprintf("%0{$iIntLen}.2f", $float)); //15/18

		$out = array();

		if (intval($iInteger))
		{
			// Делим по 3
			$aSplit = str_split($iInteger, 3);

			// Переворачиваем, начинаем с тысяч
			$aSplit = array_reverse($aSplit);

			foreach ($aSplit as $uk => $value)
			{
				if (!intval($value))
				{
					continue;
				}

				$uk++; // в 0 - копейка, смещаем на 1

				$gender = isset($aUnits[$uk][3]) ? $aUnits[$uk][3] : 0;
				list($iHundreds, $iTens, $i3) = array_map('intval', str_split($value, 1));

				$uk > 1
					&& isset($aUnits[$uk]) && count($aUnits[$uk]) == 4
					&& array_unshift($out, $this->_morph($value, $aUnits[$uk]));

				array_unshift($out, $iTens > 1
					? $tens[$iTens] . ' ' . $ten[$gender][$i3] // 20-99
					: ($iTens > 0 ? $a20[$i3] : $ten[$gender][$i3]) // 10-19 | 1-9
				);

				array_unshift($out, $hundreds[$iHundreds]);
			}
		}
		else
		{
			$out[] = $ten[0][0];
		}

		isset($aUnits[1]) && count($aUnits[1]) == 4
			&& $out[] = $this->_morph(intval($iInteger), $aUnits[1]);

		if (intval($fractional))
		{
			isset($aUnits[0]) && count($aUnits[0]) == 4
				&& $fractional .= ' ' . $this->_morph(intval($fractional), $aUnits[0]);

			$out[] = $fractional;
		}

		return trim(preg_replace('/ {2,}/', ' ', implode(' ',$out)));
	}

	/**
	 * Get morph form
	 * @param int $n number
	 * @param array $aFroms array of morph form
	 * @return string
	 */
	protected function _morph($n, $aFroms)
	{
		$n = abs($n) % 100;
		if ($n > 10 && $n < 20)
		{
			return $aFroms[2];
		}

		$n = $n % 10;
		if ($n > 1 && $n < 5)
		{
			return $aFroms[1];
		}

		if ($n == 1)
		{
			return $aFroms[0];
		}

		return $aFroms[2];
	}
}