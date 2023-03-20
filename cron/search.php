<?php

/**
 * Пример вызова:
 * /usr/bin/php /var/www/site.ru/httpdocs/cron/search.php
 * Пример вызова с передачей php.ini
 * /usr/bin/php --php-ini /etc/php.ini /var/www/site.ru/httpdocs/cron/search.php
 * Реальный путь на сервере к корневой директории сайта уточните в службе поддержки хостинга.
 *
 * @package HostCMS 7\cron
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
@set_time_limit(9000);
ini_set("memory_limit", "512M");

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
	echo "\nModule ", $oModule->path;

	$oModule->loadModule();

	if (!is_null($oModule->Core_Module))
	{
		if (method_exists($oModule->Core_Module, 'indexing'))
		{
			$offset
				= $_SESSION['search_block']
				= $_SESSION['previous_step']
				= $_SESSION['last_limit'] = 0;

			do {
				$previousSearchBlock = Core_Array::get($_SESSION, 'search_block');

				$mTmp = $oModule->Core_Module->indexing($offset, $step);

				if (isset($mTmp['pages']) && isset($mTmp['finished']))
				{
					// Проиндексированные страницы
					$aPages = $mTmp['pages'];

					// Модуль завершил индексацию
					$finished = $mTmp['finished'];

					// Проиндексировано последним блоком, может быть меньше количества $aPages, т.к. $aPages содержит результат нескольких блоков
					$indexed = $mTmp['indexed'];
				}
				else
				{
					$aPages = $mTmp;

					$indexed = $_SESSION['last_limit'] > 0
						? $_SESSION['last_limit']
						: $step;

					// Больше, т.к. некоторые модули могут возвращать больше проиндексированных элементов, чем запрошено, например, форумы
					$finished = empty($aPages) || count($aPages) < $step;
				}

				$count = $aPages ? count($aPages) : 0;

				echo "\n  ", $offset, ' -> ', $offset + $step, ', found: ', $count;

				$count && $Search_Controller->indexingSearchPages($aPages);

				if (!$finished)
				{
					// Если предыдущая индексация шла в несколько этапов, лимит сбрасывается для нового шага
					if (Core_Array::get($_SESSION, 'search_block') != $previousSearchBlock)
					{
						$offset = 0;
					}

					$offset += $indexed;
				}

				Core_ObjectWatcher::clear();
				Search_Stemmer::instance('ru')->clearCache();

				//echo "\nMemory: ", round(memory_get_usage() / 1048576);

				//$offset += $step;
			} while ($aPages && $count >= $step);
		}
	}
}

echo "\nOK";