<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Date helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Date
{
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
	 * @return string дата-время в формате SQL
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
		return self::timestamp2sqldate(
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
	 * @param int $timestamp
	 * @return string дата в формате SQL
	 */
	static public function timestamp2sql($timestamp)
	{
		return date('Y-m-d H:i:s', $timestamp);
	}

	/**
	 * Преобразовывает дату из временной метки в формат даты SQL
	 *
	 * @param int $timestamp
	 * @return string дата в формате SQL
	 */
	static public function timestamp2sqldate($timestamp)
	{
		return date('Y-m-d', $timestamp);
	}

	/**
	 * Преобразовывает дату из временной метки в формат даты
	 *
	 * @param int $timestamp
	 * @return string дата в формате Core::$mainConfig['dateFormat']
	 */
	static public function timestamp2date($timestamp)
	{
		return date(Core::$mainConfig['dateFormat'], $timestamp);
	}

	/**
	 * Форматирует дату с использованием gmdate
	 *
	 * @param string $format
	 * @param int $timestamp
	 * @return string
	 */
	static public function gmdate($format, $timestamp)
	{
		return function_exists('gmdate')
			? gmdate($format, $timestamp)
			: date($format, $timestamp - (date('O') / 100) * 60 * 60);
	}

	/**
	 * Преобразовывает дату из временной метки в формат даты-время
	 *
	 * @param int $timestamp
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
			$sLastMessageTime = $time . ' ' . Core::_('Core.shortTitleSeconds');
		}
		// Минуты
		elseif ($time >= 60 && $time < 60 * 60)
		{
			$sLastMessageTime = floor($time / 60) . ' ' . Core::_('Core.shortTitleMinutes');
		}
		// Часы
		elseif ($time >= 60 * 60 && $time < 60 * 60 * 24)
		{
			$sLastMessageTime = floor($time / 60 / 60) . ' ' . Core::_('Core.shortTitleHours');
		}
		// Дни
		elseif ($time >= 60 * 60 * 24 && $time < 60 * 60 * 24 * 365)
		{
			$sLastMessageTime = floor($time / 60 / 60 / 24) . ' ' . Core::_('Core.shortTitleDays');
		}
		// Годы
		else
		{
			$sLastMessageTime = floor($time / 60 / 60 / 24 / 365) . ' ' . Core::_('Core.shortTitleYears');
		}

		return $sLastMessageTime;
	}

	/**
	 * Преобразовывает дату из временной метки в текстовый формат
	 *
	 * @param int $timestamp
	 * @return string
	 */
	static public function timestamp2string($timestamp, $withTime = TRUE)
	{
		list($year, $month, $day) = array_values(date_parse(Core_Date::timestamp2sql($timestamp)));

		$aMonth = array(
			1 => Core::_('Core.month1'),
			2 => Core::_('Core.month2'),
			3 => Core::_('Core.month3'),
			4 => Core::_('Core.month4'),
			5 => Core::_('Core.month5'),
			6 => Core::_('Core.month6'),
			7 => Core::_('Core.month7'),
			8 => Core::_('Core.month8'),
			9 => Core::_('Core.month9'),
			10 => Core::_('Core.month10'),
			11 => Core::_('Core.month11'),
			12 => Core::_('Core.month12')
		);

		$estimate_time = time() - $timestamp;

		$time = date('H:i', $timestamp);

		$sReturn = '';

		// Прошло дней
		//$estimate_days = floor($estimate_time / 86400);
		$dateZ = date('z', $timestamp);
		$currentZ = date('z');

		$dateY = date('Y', $timestamp);
		$currentY = date('Y');

		// Прошло часов
		$estimate_hours = floor($estimate_time / 3600);

		if ($estimate_hours > 0 && $estimate_hours < 3)
		{
			// Прошло минут
			$estimate_minutes = floor($estimate_time / 60);

			if ($estimate_minutes == 0)
			{
				$sReturn = Core::_('Core.now');
			}
			else
			{
				$hour_prefix = $estimate_hours > 0
					? $estimate_hours . ' ' . Core_Str::declensionNumber($estimate_hours, Core::_('Core.hour_nominative'), Core::_('Core.hour_genitive_singular'), Core::_('Core.hour_genitive_plural')) . ' '
					: '';

				$minutes_ago = $estimate_minutes - 60 * $estimate_hours;

				$minute_prefix = $minutes_ago > 0
					? $minutes_ago . ' ' . Core_Str::declensionNumber($minutes_ago, Core::_('Core.minute_nominative'), Core::_('Core.minute_genitive_singular'), Core::_('Core.minute_genitive_plural'))
					: '';

				$sReturn = Core::_('Core.ago', $hour_prefix, $minute_prefix);
			}
		}
		else
		{
			if ($dateY == $currentY && $dateZ == $currentZ)
			{
				$sReturn = Core::_('Core.today');
			}
			elseif ($dateY == $currentY && $dateZ == $currentZ - 1)
			{
				$sReturn = Core::_('Core.yesterday');
			}
			elseif ($dateY == $currentY && $dateZ == $currentZ + 1)
			{
				$sReturn = Core::_('Core.tomorrow');
			}
			elseif ($dateY == $currentY)
			{
				$sReturn = Core::_('Core.estimate_date', $day, $aMonth[$month]);
			}
			else
			{
				$sReturn = Core::_('Core.estimate_date_year', $day, $aMonth[$month], $year);
			}

			$withTime && $sReturn .= Core::_('Core.time_postfix', $time);
		}

		return $sReturn;
	}
	
	static public function getDuration($duration)
	{
		$duration < 0 && $duration = 0;

		$aReturn = array(
			'value' => $duration,
			'type' => 0
		);

		if ($duration == 0)
		{
			return $aReturn;
		}

		// Days
		if ($duration % 1440 == 0)
		{
			$aReturn['value'] = $duration / 1440;
			$aReturn['type'] = 2;
		}
		// Hours
		elseif ($duration % 1440 > 0 && $duration % 60 == 0)
		{
			$aReturn['value'] = $duration / 60;
			$aReturn['type'] = 1;
		}
		// Minutes
		elseif ($duration % 60 > 0)
		{
			$aReturn['value'] = $duration;
			$aReturn['type'] = 0;
		}

		return $aReturn;
	}

	static public function convertDuration($duration, $type)
	{
		$duration < 0 && $duration = 0;

		switch ($type)
		{
			case 0:
				return $duration;
			break;
			case 1:
				return $duration * 60;
			break;
			case 2:
				return $duration * 60 * 24;
			break;
		}
	}	
}