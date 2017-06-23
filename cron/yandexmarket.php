<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/yandexmarket.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/recount.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 6\cron
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

@set_time_limit(9000);
ini_set("memory_limit", "512M");

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$aShops = Core_Entity::factory('Shop')->findAll(FALSE);

foreach ($aShops as $oShop)
{
	if ($oShop->Shop_Items->getCount())
	{
		$sFilename = "yandexmarket{$oShop->id}.xml";
		$oCore_Out_File = new Core_Out_File();
		$oCore_Out_File->filePath(CMS_FOLDER . $sFilename);

		$Shop_Controller_YandexMarket = new Shop_Controller_YandexMarket($oShop);
		$Shop_Controller_YandexMarket->stdOut($oCore_Out_File);
		$Shop_Controller_YandexMarket->itemsProperties(TRUE);
		$Shop_Controller_YandexMarket->show();

		echo "\nFile {$sFilename} OK";
	}
}