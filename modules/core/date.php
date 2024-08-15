<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Date helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
		$sDate = (string) $sDate;

		if (isset(Core::$mainConfig['reverseDateTimeFormat']))
		{
			$DateTime = DateTime::createFromFormat(Core::$mainConfig['reverseDateTimeFormat'], $sDate);
			return $DateTime->getTimestamp();
		}

		return strtotime($sDate);
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
		if (is_null($time) || $time < 0)
		{
			$return = '';
		}
		// Секунды
		elseif ($time >= 0 && $time < 60)
		{
			$return = $time . ' ' . Core::_('Core.shortTitleSeconds');
		}
		// Минуты
		elseif ($time >= 60 && $time < 60 * 60)
		{
			$return = floor($time / 60) . ' ' . Core::_('Core.shortTitleMinutes');
		}
		// Часы
		elseif ($time >= 60 * 60 && $time < 60 * 60 * 24)
		{
			$return = floor($time / 60 / 60) . ' ' . Core::_('Core.shortTitleHours');
		}
		// Дни
		elseif ($time >= 60 * 60 * 24 && $time < 60 * 60 * 24 * 365)
		{
			$return = floor($time / 60 / 60 / 24) . ' ' . Core::_('Core.shortTitleDays');
		}
		// Годы
		else
		{
			$return = floor($time / 60 / 60 / 24 / 365) . ' ' . Core::_('Core.shortTitleYears');
		}

		return $return;
	}

	/**
	 * Преобразовывает дату из SQL в текстовый формат
	 *
	 * @param strin $sql
	 * @return string
	 */
	static public function sql2string($sql, $withTime = TRUE)
	{
		return self::timestamp2string(self::sql2timestamp($sql), $withTime);
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
				// Для прошлого года время не указываем
				$withTime = FALSE;
			}

			$withTime && $sReturn .= Core::_('Core.time_postfix', $time);
		}

		return $sReturn;
	}

	/**
	 * Get duration
	 * @param int $duration
	 * @return array
	 */
	static public function getDuration($duration)
	{
		$duration = intval($duration);
		
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

	/**
	 * Convert duration
	 * @param int $duration
	 * @param int $type
	 * @return int
	 */
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

	/**
	 * Strftime
	 * @param string $format
	 * @param int|NULL $timestamp
	 * @return string|FALSE
	 */
	static function strftime($format, $timestamp = NULL)
	{
		if (is_null($format))
		{
			return FALSE;
		}

		if (PHP_VERSION_ID < 80100)
		{
			return strftime($format, $timestamp);
		}
		else
		{
			is_null($timestamp) && $timestamp = time();

			if (is_numeric($timestamp))
			{
				$timestamp = date_create('@' . $timestamp);

				$timestamp
					&& $timestamp->setTimezone(new DateTimezone(date_default_timezone_get()));
			}
			elseif (is_string($timestamp))
			{
				$timestamp = date_create($timestamp);
			}

			if (!($timestamp instanceof DateTimeInterface))
			{
				throw new InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
			}

			$locale = setlocale(LC_ALL, 0);
			// en_EN.utf => en_EN
			$locale = substr((string) $locale, 0, 5);

			// Fix for compatibility with PHP 8.1.25
			if (strpos($locale, '_') === FALSE)
			{
				$locale = 'en_US';
			}

			$intl_formats = array(
				'%a' => 'EEE',	// An abbreviated textual representation of the day	Sun through Sat
				'%A' => 'EEEE',	// A full textual representation of the day	Sunday through Saturday
				'%b' => 'MMM',	// Abbreviated month name, based on the locale	Jan through Dec
				'%B' => 'MMMM',	// Full month name, based on the locale	January through December
				'%h' => 'MMM'	// Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
			);

			$intl_formatter = function (DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
				$tz = $timestamp->getTimezone();
				$date_type = $time_type = IntlDateFormatter::FULL;
				$pattern = '';

				// %c = Preferred date and time stamp based on locale
				// Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
				if ($format == '%c') {
					$date_type = IntlDateFormatter::LONG;
					$time_type = IntlDateFormatter::SHORT;
				}
				// %x = Preferred date representation based on locale, without the time
				// Example: 02/05/09 for February 5, 2009
				elseif ($format == '%x') {
					$date_type = IntlDateFormatter::SHORT;
					$time_type = IntlDateFormatter::NONE;
				}
				// Localized time format
				elseif ($format == '%X') {
					$date_type = IntlDateFormatter::NONE;
					$time_type = IntlDateFormatter::MEDIUM;
				}
				else {
					$pattern = $intl_formats[$format];
				}

				$oIntlDateFormatter = new IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern);
				return $oIntlDateFormatter->format($timestamp);
			};

			// Same order as https://www.php.net/manual/en/function.strftime.php
			$translation_table = array(
				// Day
				'%a' => $intl_formatter,
				'%A' => $intl_formatter,
				'%d' => 'd',
				'%e' => function ($timestamp) {
					return sprintf('% 2u', $timestamp->format('j'));
				},
				'%j' => function ($timestamp) {
					// Day number in year, 001 to 366
					return sprintf('%03d', $timestamp->format('z')+1);
				},
				'%u' => 'N',
				'%w' => 'w',

				// Week
				'%U' => function ($timestamp) {
					// Number of weeks between date and first Sunday of year
					$day = new DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
					return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
				},
				'%V' => 'W',
				'%W' => function ($timestamp) {
					// Number of weeks between date and first Monday of year
					$day = new DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
					return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
				},

				// Month
				'%b' => $intl_formatter,
				'%B' => $intl_formatter,
				'%h' => $intl_formatter,
				'%m' => 'm',

				// Year
				'%C' => function ($timestamp) {
					// Century (-1): 19 for 20th century
					return floor($timestamp->format('Y') / 100);
				},
				'%g' => function ($timestamp) {
					return substr($timestamp->format('o'), -2);
				},
				'%G' => 'o',
				'%y' => 'y',
				'%Y' => 'Y',

				// Time
				'%H' => 'H',
				'%k' => function ($timestamp) {
					return sprintf('% 2u', $timestamp->format('G'));
				},
				'%I' => 'h',
				'%l' => function ($timestamp) {
					return sprintf('% 2u', $timestamp->format('g'));
				},
				'%M' => 'i',
				'%p' => 'A', // AM PM (this is reversed on purpose!)
				'%P' => 'a', // am pm
				'%r' => 'h:i:s A', // %I:%M:%S %p
				'%R' => 'H:i', // %H:%M
				'%S' => 's',
				'%T' => 'H:i:s', // %H:%M:%S
				'%X' => $intl_formatter, // Preferred time representation based on locale, without the date

				// Timezone
				'%z' => 'O',
				'%Z' => 'T',

				// Time and Date Stamps
				'%c' => $intl_formatter,
				'%D' => 'm/d/Y',
				'%F' => 'Y-m-d',
				'%s' => 'U',
				'%x' => $intl_formatter
			);

			$out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
				if ($match[1] == '%n') {
					return "\n";
				}
				elseif ($match[1] == '%t') {
					return "\t";
				}

				if (!isset($translation_table[$match[1]]))
				{
					throw new InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
				}

				$replace = $translation_table[$match[1]];

				if (is_string($replace)) {
					return $timestamp->format($replace);
				}
				else {
					return $replace($timestamp, $match[1]);
				}
			}, $format);

			$out = str_replace('%%', '%', $out);

			return $out;
		}
	}
}