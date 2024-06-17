<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Password generator
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Password
{
	/**
	 * Генерация пароля
	 *
	 * @param int $len длина пароля (1-49), по умолчанию 8
	 * @param string $prefix префикс пароля, только латинские символы (до 10 символов, входит в длину пароля $len)
	 * @param int $fuzzy cмазанность (0-10), по умолчанию 3
	 * @return string
	 */
	static public function get($len = 10, $prefix = '', $fuzzy = 3)
	{
		$len %= 50;
		$fuzzy %= 11;

		$aRangeAlphabet = array(
			'a' => 'ntrsldicmzp', 'b' => 'euloayribsj',
			'c' => 'oheaktirulc', 'd' => 'eiorasydlun',
			'e' => 'nrdsaltevcm', 'f' => 'ioreafltuyc',
			'g' => 'aeohrilunsg', 'h' => 'eiaotruykms',
			'i' => 'ntscmledorg', 'j' => 'ueoairhjklm',
			'k' => 'eiyonashlus', 'l' => 'eoiyaldsfut',
			'm' => 'eaoipsuybmn', 'n' => 'goeditscayl',
			'o' => 'fnrzmwtovls', 'p' => 'earolipuths',
			'q' => 'uuuuaecdfok', 'r' => 'eoiastydgnm',
			's' => 'eothisakpuc', 't' => 'hoeiarzsuly',
			'u' => 'trsnlpgecim', 'v' => 'eiaosnykrlu',
			'w' => 'aiheonrsldw', 'x' => 'ptciaeuohnq',
			'y' => 'oesitabpmwc', 'z' => 'eaiozlryhmt',
			'0' => 'qazwsxedcrf', '1' => 'edcrfvtgbyh',
			'2' => 'tgbyhnujmik', '3' => 'mnbgrikdfgr',
			'4' => 'fvbcertgfdv', '5' => 'poilkmnghty',
			'6' => 'qwerfdbcvgd', '7' => 'vbntrfdcvgd',
			'8' => 'ghnbjytfgrt', '9' => 'jhtnbertyqw'
		);

		$aRange = range('a', 'z');

		$return = strlen($prefix)
			? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($prefix, 0, $len - 1)))
			: $aRange[rand(0, count($aRange) - 1)];
		
		while (strlen($return) < $len)
		{
			$tmpFuzzy = $fuzzy;
			while (
				substr_count($return, substr($return, strlen($return) - 1, 1) .
				($k = substr($aRangeAlphabet[substr($return, strlen($return) - 1, 1)], rand(0, $tmpFuzzy % 11), 1)))
			)

			if (++$tmpFuzzy > 10)
			{
				break;
			}

			$return .= $k;
		}

		return $return;
	}
}