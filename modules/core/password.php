<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Password generator
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Password
{
	/**
	 * Генерация пароля
	 *
	 * @param int $length длина пароля (1-49), по умолчанию 8
	 * @param string $prefix префикс пароля, только латинские символы (до 10 символов, входит в длину пароля $len)
	 * @param boolean $addDashes разбитие пароля на части через дефис
	 * @param string $availableSets сложность - l - нижний регистр, u - верхний регистр, d - цифры, s - спец.символы. По умолчанию - luds
	 * @return string
	 */
	static public function get($length = 9, $prefix = '', $addDashes = FALSE, $availableSets = 'luds')
	{
		$aSets = array();

		strpos($availableSets, 'l') !== FALSE && $aSets[] = 'abcdefghjkmnpqrstuvwxyz';
		strpos($availableSets, 'u') !== FALSE && $aSets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		strpos($availableSets, 'd') !== FALSE && $aSets[] = '23456789';
		strpos($availableSets, 's') !== FALSE && $aSets[] = '!@#$%&*?';

		$all = $password = '';

		$bAddPrefix = $prefix !== '' && strlen($prefix) < $length;
		$bAddPrefix && $length -= strlen($prefix);

		foreach ($aSets as $set)
		{
			$password .= $set[array_rand(str_split($set))];
			$all .= $set;
		}

		$all = str_split($all);
		for ($i = 0; $i < $length - count($aSets); $i++)
		{
			$password .= $all[array_rand($all)];
		}

		$password = str_shuffle($password);

		if ($addDashes)
		{
			$sTmp = $password;
			$password = '';

			$iDashLen = floor(sqrt($length));
			while (strlen($sTmp) > $iDashLen)
			{
				$password .= substr($sTmp, 0, $iDashLen) . '-';
				$sTmp = substr($sTmp, $iDashLen);
			}

			$password .= $sTmp;
		}

		$bAddPrefix && $password = $prefix . $password;

		return $password;
	}
}