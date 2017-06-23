<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/search.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/search.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 6\cron
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

@set_time_limit(90000);

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$Search_Controller = Search_Controller::instance();
$Search_Controller->truncate();

// Цикл по модулям
$oModules = Core_Entity::factory('Module');
$oModules->queryBuilder()
	->where('modules.active', '=', 1)
	->where('modules.indexing', '=', 1);

$aModules = $oModules->findAll(FALSE);

Core_Session::start();

$step = 500;
foreach ($aModules as $oModule)
{
	$offset = 0;

	echo "\nModule ", $oModule->path;
	if (!is_null($oModule->Core_Module))
	{
		if (method_exists($oModule->Core_Module, 'indexing'))
		{
			$_SESSION['search_block'] = $_SESSION['previous_step'] = $_SESSION['last_limit'] = 0;

			do {
				$result = $oModule->Core_Module->indexing($offset, $step);
				$count = $result ? count($result) : 0;

				echo "\n  ", $offset, ' -> ', $offset + $step, ', found: ', $count;

				$count && $Search_Controller->indexingSearchPages($result);

				$offset += $step;
			} while ($result && $count >= $step);
		}
	}
}

echo "\nOK";