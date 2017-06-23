<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/getposition.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/getposition.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 6\cron
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

@set_time_limit(90000);

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$oSeo_Controller = Seo_Controller::instance();

// Запрашиваем позиции для запросов каждого сайта и характеристики
$aSites = Core_Entity::factory('Site')->getAllByActive(1);
foreach ($aSites as $oSite)
{
	$oSeo = Core_Entity::factory('Seo');
	$oSite->add($oSeo);

	$oSeo_Controller->requestSiteCharacteristics($oSeo);
	$oSeo_Controller->requestSitePositions($oSite);

	echo "Site '{$oSite->name}' characteristics were requested\n";
}

echo "OK\n";