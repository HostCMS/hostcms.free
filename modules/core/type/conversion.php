<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Type conversion helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Type_Conversion
{
	/**
	 * Convert $var to string value
	 * Rus: Приведение аргумента к строковому типу
	 *
	 * @param mixed $var
	 * @return string
	 */
	static public function toStr(&$var)
	{
		if (is_array($var) || is_object($var))
		{
			return '';
		}

		return strval($var);
	}

	/**
	 * Convert $var to integer value
	 * Rus: Приведение аргумента к целочисленному типу
	 *
	 * @param mixed $var
	 * @return int
	 */
	static public function toInt(&$var)
	{
		if (is_int($var))
		{
			return $var;
		}
		
		$return = intval($var);
		if (strval($return) != $var)
		{
			return 0;
		}

		return $return;
	}

	/**
	 * Convert $var to float value
	 * Rus: Приведение аргумента к вещественному типу
	 *
	 * @param mixed $var
	 * @return float
	 */
	static public function toFloat(&$var)
	{
		return floatval($var);
	}

	/**
	 * Convert $var to float value
	 * Rus: Приведение аргумента к логическому типу
	 *
	 * @param mixed $var
	 * @return bool
	 */
	static public function toBool(&$var)
	{
		if (is_bool($var))
		{
			return $var;
		}
		return $var == 1;
	}

	/**
	 * Convert $var to array
	 * Rus: Приведение аргумента к массиву
	 *
	 * @param mixed $var
	 * @return array
	 */
	static public function toArray(&$var)
	{
		return is_array($var) ? $var : array();
	}
}