<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/yandexmarket.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/recount.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 7\cron
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023, https://www.hostcms.ru
 */

@set_time_limit(9000);
ini_set("memory_limit", "512M");

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$aShops = Core_Entity::factory('Shop')->findAll(FALSE);

foreach ($aShops as $oShop)
{
	if ($oShop->Shop_Items->getCount()
		&& $oShop->Site->active && $oShop->Site->getCurrentAlias()
	)
	{
		$sTmpFilename = "~yandexmarket{$oShop->id}.xml";
		$sFilename = "yandexmarket{$oShop->id}.xml";

		$oCore_Out_File = new Core_Out_File();
		$oCore_Out_File->filePath(CMS_FOLDER . $sTmpFilename);

		$Shop_Controller_YandexMarket = new Shop_Controller_YandexMarket($oShop);
		$Shop_Controller_YandexMarket->stdOut($oCore_Out_File);
		$Shop_Controller_YandexMarket->model('ADV');
		$Shop_Controller_YandexMarket->priceMode('item');
		$Shop_Controller_YandexMarket->itemsProperties(TRUE);
		$Shop_Controller_YandexMarket->show();

		Core_File::rename(CMS_FOLDER . $sTmpFilename, CMS_FOLDER . $sFilename);

		echo "\nFile {$sFilename} OK";
	}
}