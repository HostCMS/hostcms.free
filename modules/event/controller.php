<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller
{
	static public function getDateTime($datetime)
	{
		if ($datetime == '0000-00-00 00:00:00')
		{
			return '∞';
		}

		$timestamp = Core_Date::sql2timestamp($datetime);

		return trim(self::getDate($datetime) . ' ' . date('H:i', $timestamp));
	}

	static public function getDate($datetime)
	{
		$timestamp = Core_Date::sql2timestamp($datetime);

		$day = date('j', $timestamp) != date('j')
			? date('j', $timestamp)
			: '';

		$month = strlen($day)
			? Core::_('Event.month_' . date('m', $timestamp), $day)
			: '';

		$year = date('Y', $timestamp) != date('Y')
			? ' ' . date('Y', $timestamp)
			: '';

		return $month . $year;
	}
}