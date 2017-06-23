<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Date helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Date
{
	/**
	 * SQL date format
	 * @var string
	 */
	static protected $_sqlFormat = 'Y-m-d H:i:s';

	/**
	 * Преобразовывает дату из формата даты во временную метку
	 *
	 * @param string $sDate дата в формате SQL
	 * @return int временную метку
	 */
	static public function date2timestamp($sDate)
	{
		return self::datetime2timestamp($sDate);
	}

	/**
	 * Преобразовывает дату из формата даты-времени во временную метку.
	 * При установленном формате обратного преобразования в Core::::$mainConfig['reverseDateTimeFormat'] будет использоваться он.
	 *
	 * @param string $sDate дата в формате SQL
	 * @return int временную метку
	 */
	static public function datetime2timestamp($sDate)
	{
		if (isset(Core::$mainConfig['reverseDateTimeFormat']))
		{
			$DateTime = DateTime::createFromFormat(Core::$mainConfig['reverseDateTimeFormat'], $sDate);
			return $DateTime->getTimestamp();
		}
		else
		{
			return strtotime($sDate);
		}
	}

	/**
	 * Преобразовывает дату из формата даты-время SQL во временную метку
	 *
	 * @param string $sDate дата в формате SQL
	 * @return int временную метку
	 */
	static public function sql2timestamp($sDate)
	{
		return self::datetime2timestamp($sDate);
	}

	/**
	 * Преобразовывает дату из формата даты-время SQL в формат даты-время
	 *
	 * @param string $sDate дата в формате SQL
	 * @return string дата-время в формате Core::$mainConfig['dateTimeFormat']
	 */
	static public function sql2datetime($sDate)
	{
		return self::timestamp2datetime(
			self::sql2timestamp($sDate)
		);
	}

	/**
	 * Преобразовывает дату из формата даты-время в SQL
	 *
	 * @param string $sDate дата-время в формате Core::$mainConfig['dateTimeFormat']
	 * @return string дата в формате SQL
	 */
	static public function datetime2sql($sDate)
	{
		return self::timestamp2sql(
			self::datetime2timestamp($sDate)
		);
	}

	/**
	 * Преобразовывает дату из формата даты в SQL
	 *
	 * @param string $sDate дата в формате Core::$mainConfig['dateFormat']
	 * @return string дата в формате SQL
	 */
	static public function date2sql($sDate)
	{
		return self::timestamp2sql(
			self::date2timestamp($sDate)
		);
	}

	/**
	 * Преобразовывает дату из формата даты-время SQL в формат даты
	 *
	 * @param string $sDate дата в формате SQL
	 * @return string дата в формате Core::$mainConfig['dateFormat']
	 */
	static public function sql2date($sDate)
	{
		return self::timestamp2date(
			self::sql2timestamp($sDate)
		);
	}

	/**
	 * Преобразовывает дату из временной метки в формат даты-время SQL
	 *
	 * @param string $timestamp
	 * @return string дата в формате SQL
	 */
	static public function timestamp2sql($timestamp)
	{
		return date(self::$_sqlFormat, $timestamp);
	}

	/**
	 * Преобразовывает дату из временной метки в формат даты
	 *
	 * @param string $timestamp
	 * @return string дата в формате Core::$mainConfig['dateFormat']
	 */
	static public function timestamp2date($timestamp)
	{
		return date(Core::$mainConfig['dateFormat'], $timestamp);
	}

	/**
	 * Преобразовывает дату из временной метки в формат даты-время
	 *
	 * @param string $timestamp
	 * @return string дата-время в формате Core::$mainConfig['dateTimeFormat']
	 */
	static public function timestamp2datetime($timestamp)
	{
		return date(Core::$mainConfig['dateTimeFormat'], $timestamp);
	}

	/**
	 * Convert seconds to string (seconds, minutes, hours, days or years). If NULL return empty string
	 * @param int $time
	 * @return string
	 */
	static public function time2string($time)
	{
		if (is_null($time))
		{
			$sLastMessageTime = '';
		}
		// Секунды
		elseif ($time >= 0 && $time < 60)
		{
			$sLastMessageTime = $time . ' с.';
		}
		// Минуты
		elseif ($time >= 60 && $time < 60 * 60)
		{
			$sLastMessageTime = floor($time / 60) . ' м.';
		}
		// Часы
		elseif ($time >= 60 * 60 && $time < 60 * 60 * 24)
		{
			$sLastMessageTime = floor($time / 60 / 60) . ' ч.';
		}
		// Дни
		elseif ($time >= 60 * 60 * 24 && $time < 60 * 60 * 24 * 365)
		{
			$sLastMessageTime = floor($time / 60 / 60/ 24) . ' д.';
		}
		// Годы
		else
		{
			$sLastMessageTime = floor($time / 60 / 60 / 24 / 365) . ' г.';
		}

		return $sLastMessageTime;
	}
}