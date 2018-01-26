<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Russian inflection.
 *
 * @package HostCMS
 * @subpackage Core\Inflection
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
				case 'й':
					$singular = 'я';
					$plural = 'ев';
				break;
				case 'а':
					$singular = 'ы';
					$plural = '';
				break;
				case 'я':
					$singular = 'и';
					$plural = 'й';
				break;
				case 'о':
					$singular = 'а';
					$plural = '';
				break;
				case 'е':
					$singular = 'я';
					$plural = 'й';
				break;
				case 'ь':
					$singular = 'я';
					$plural = 'ей';
				break;
				case 'с':
				case 'д':
					$singular = 'а';
					$plural = 'ов';
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
	 * Number to str
	 * @param float $float
	 */
	protected function _num2str($float)
	{
		$float = floatval($float);

		$ten = array(
			0 => array('ноль', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
			1 => array('ноль', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')
		);

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

		$tens = array('', '', 'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
		$hundreds = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');

		$AUnits = array(
			array('копейка' ,'копейки' ,'копеек', 1),
			array('рубль'   ,'рубля'   ,'рублей', 0),
			array('тысяча'  ,'тысячи'  ,'тысяч' , 1),
			array('миллион' ,'миллиона','миллионов', 0),
			array('миллиард','милиарда','миллиардов', 0),
		);

		// 3 => 000000000003.00
		list($iInteger, $fractional) = explode('.', sprintf("%015.2f", $float));

		$out = array();

		if (intval($iInteger))
		{
			$aSplit = str_split($iInteger, 3);
			foreach ($aSplit as $uk => $value)
			{
				if (!intval($value))
				{
					continue;
				}

				$uk = count($AUnits) - $uk - 1;

				$gender = $AUnits[$uk][3];
				list($iHundreds, $iTens, $i3) = array_map('intval', str_split($value, 1));
//var_dump($i3);
				$out[] = $hundreds[$iHundreds];

				$out[] = $iTens > 1
					? $tens[$iTens] . ' ' . $ten[$gender][$i3] # 20-99
					: ($iTens > 0 ? $a20[$i3] : $ten[$gender][$i3]); # 10-19 | 1-9

				$uk > 1 && $out[] = $this->_morph($value, $AUnits[$uk]);
			}
		}
		else
		{
			$out[] = $ten[0][0];
		}

		$out[] = $this->_morph(intval($iInteger), $AUnits[1]);
		$out[] = $fractional . ' ' . $this->_morph(intval($fractional), $AUnits[0]);

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