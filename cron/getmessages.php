<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/getmessages.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/getmessages.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 6\cron
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

@set_time_limit(90000);

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

/**
 * Установите идентификатор сайта
 */
define('CURRENT_SITE', 1);

$oSite = Core_Entity::factory('Site', CURRENT_SITE);

Core::initConstants($oSite);

// Получаем список всех служб технической поддержки сайта
$aHelpdesks = $oSite->Helpdesks->findAll();

// Счетчик добавленных тикетов
$counter = 0;

foreach ($aHelpdesks as $oHelpdesk)
{
	$aHelpdesk_Accounts = $oHelpdesk->Helpdesk_Accounts->getAllByActive(1);

	foreach ($aHelpdesk_Accounts as $oHelpdesk_Account)
	{
		try
		{
			$count = $oHelpdesk_Account->receive();
		}
		catch (Exception $e)
		{
			$count = 0;
			echo "\n", $e->getMessage();
		}

		$counter += $count;

		printf("\nAccount '%s', received %d messages.", $oHelpdesk_Account->email, $count);
	}

	// Закрываем старые тикеты
	$oHelpdesk->closeOldTickets();
}

printf("\nTotal %d messages.", $counter);
