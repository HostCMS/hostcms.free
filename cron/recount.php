<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/recount.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/recount.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 7\cron
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

@set_time_limit(9000);
ini_set("memory_limit", "512M");

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$aShops = Core_Entity::factory('Shop')->findAll(FALSE);

foreach ($aShops as $oShop)
{
	$oShop->recount();
}

echo "\nOK";