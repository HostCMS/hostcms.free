<?php

/**
 * Отправка отчета о событиях сайта за предыдущий день
 *
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/sendeventlogs.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/sendeventlogs.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 7\cron
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

if (Core::moduleIsActive('eventlog'))
{
	$oModule = Core_Entity::factory('Module')->getByPath('eventlog');
	if (!is_null($oModule))
	{
		if ($oModule->Core_Module->sendReport(
			date('Y-m-d', strtotime('-1 day'))
		))
		{
			echo "\nReport sent successfully";
		}
	}
}
else
{
	throw new Core_Exception("Module 'eventlog' does not exist.");
}