<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Math helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Math
{
	static public function encodeHexBase36($hex)
	{
		// Проверяем, что строка содержит только hex-символы
		if (!preg_match('/^[0-9a-fA-F]+$/', $hex))
		{
			return false;
		}

		// Символы для цифр в 36-ричной системе
		$digits = '0123456789abcdefghijklmnopqrstuvwxyz';
		$result = '';

		// Приводим hex к нижнему регистру для удобства
		$hex = strtolower($hex);

		// Пока число не стало нулевым
		while ($hex !== '0')
		{
			$remainder = 0;
			$newHex = '';

			// Проходимся по каждой цифре hex (деление "в столбик")
			for ($i = 0; $i < strlen($hex); $i++)
			{
				$currentDigit = $hex[$i];
				$currentValue = ord($currentDigit) - ord('0');
				if ($currentValue > 9)
				{
					$currentValue = ord($currentDigit) - ord('a') + 10;
				}

				// Добавляем остаток от предыдущего шага
				$currentValue += $remainder * 16;
				$remainder = $currentValue % 36;
				$newValue = intval($currentValue / 36);

				// Пропускаем ведущие нули
				if ($newValue !== 0 || !empty($newHex))
				{
					$newHex .= $digits[$newValue];
				}
			}

			// Если после деления получился 0, но остаток есть
			if (empty($newHex) && $remainder !== 0)
			{
				$newHex = '0';
			}

			// Записываем остаток в результат
			$result = $digits[$remainder] . $result;
			$hex = $newHex;
		}

		return $result === '' ? '0' : $result;
	}
}