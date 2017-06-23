<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bit operations helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Bit
{
	/**
	 * Set bit value
	 * @param int $int source integer
	 * @param int $bitNumber number of the bit
	 * @param int $value value 
	 * @return int
	 */
	static public function setBit($int, $bitNumber, $value = 1)
	{
		$int = intval($int);
		$bitNumber = intval($bitNumber);
		$value = intval($value);

		$int = $value == 0
			// Сбрасываем бит для числа
			// число XOR 2^номер_бита, doesn't work when bit is 0
			//? $int ^ bcpow(2, $bitNumber)
			// ~(2^номер_бита) AND число
			? (~pow(2, $bitNumber)) & $int
			// Устанавливаем бит для числа
			// число OR 2^номер_бита
			//: $int | bcpow(2, $bitNumber);
			: $int | pow(2, $bitNumber);

		return $int;
	}

	/**
	 * Сбросить бит числа в 0
	 *
	 * @param int $int число
	 * @param int $bitNumber номер бита
	 */
	static public function resetBit($int, $bitNumber)
	{
		return self::setBit($int, $bitNumber, 0);
	}

	/**
	 * Получить бит номер $bitNumber числа $int
	 *
	 * @param int $int число
	 * @param int $bitNumber номер бита
	 */
	static public function getBit($int, $bitNumber)
	{
		$int = intval($int);
		$bitNumber = intval($bitNumber);

		// Определяем бит для числа
		// число AND 2^номер_бита
		$bit = ($int & pow(2, $bitNumber)) > 0 ? 1 : 0;

		return $bit;
	}
}
