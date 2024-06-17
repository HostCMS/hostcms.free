<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * DataBase observers
 *
 * @package HostCMS
 * @subpackage Core\Database
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Database_Observer
{
	/**
	 * onBeforeConnect callback method
	 * @param object $object
	 * @param array $args array of arguments
	 */
	static public function onBeforeConnect($object, $args)
	{
		Core_Registry::instance()->set('Core_DataBase.onBeforeConnect', Core::getmicrotime());
	}

	/**
	 * onAfterConnect callback method
	 * @param object $object
	 * @param array $args array of arguments
	 */
	static public function onAfterConnect($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();
		$oCore_Registry->set('Core_DataBase.connectTime',
			$oCore_Registry->get('Core_DataBase.connectTime', 0)
				+ Core::getmicrotime()
				- $oCore_Registry->get('Core_DataBase.onBeforeConnect', 0)
		);
	}

	/**
	 * onBeforeSelectDb callback method
	 * @param object $object
	 * @param array $args array of arguments
	 */
	static public function onBeforeSelectDb($object, $args)
	{
		Core_Registry::instance()->set('Core_DataBase.onBeforeSelectDb', Core::getmicrotime());
	}

	/**
	 * onAfterSelectDb callback method
	 * @param object $object
	 * @param array $args array of arguments
	 */
	static public function onAfterSelectDb($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();
		$oCore_Registry->set('Core_DataBase.selectDbTime',
			$oCore_Registry->get('Core_DataBase.selectDbTime', 0)
				+ Core::getmicrotime()
				- $oCore_Registry->get('Core_DataBase.onBeforeSelectDb', 0)
		);
	}

	/**
	 * onBeforeQuery callback method
	 * @param object $object
	 * @param array $args array of arguments
	 */
	static public function onBeforeQuery($object, $args)
	{
		Core_Registry::instance()->set('Core_DataBase.onBeforeQuery', Core::getmicrotime());
	}

	/**
	 * onAfterQuery callback method
	 * @param object $object
	 * @param array $args array of arguments
	 */
	static public function onAfterQuery($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();

		$time = Core::getmicrotime() - $oCore_Registry->get('Core_DataBase.onBeforeQuery', 0);

		$oCore_Registry->set('Core_DataBase.queryTime',
			$oCore_Registry->get('Core_DataBase.queryTime', 0) + $time
		);

		/*if (FALSE)
		{
			if ($f_log = @fopen(CMS_FOLDER . 'sql.log', 'a'))
			{
				if (flock($f_log, LOCK_EX))
				{
					fwrite($f_log, date("d.m.Y H:i:s")."\t time=".sprintf('%.5f', $time)." \t Query: {$args[0]}\r\n");
					flock($f_log, LOCK_UN);
				}
				fclose($f_log);
			}
		}*/

		if (defined('ALLOW_SHOW_SQL') && ALLOW_SHOW_SQL && !defined('IS_ADMIN_PART'))
		{
			$oUser = Core_Auth::getCurrentUser();

			if ($oUser && $oUser->superuser)
			{
				$queryLog = $oCore_Registry->get('Core_DataBase.queryLog', array());
				$limit = defined('ALLOW_SHOW_SQL_LIMIT') ? ALLOW_SHOW_SQL_LIMIT : 2000;

				if (count($queryLog) < $limit)
				{
					$aLog = array(
						'query' => $args[0],
						'time' => $time,
						'trimquery' => trim(str_replace(array("\n", "\t"), '', $args[0]))
					);

					// Получаем данные о вызывающем
					if (function_exists('debug_backtrace'))
					{
						$aLog['debug_backtrace'] = debug_backtrace();
					}

					/*if ($object->getQueryType() === 0 && $object->getResult())
					{
						$sQueryUpper = strtoupper($args[0]);

						// Перед выполнением "SELECT FOUND_ROWS() ..." не должно быть лишнего запроса
						if (strpos($sQueryUpper, 'FOUND_ROWS() ') === FALSE
							&& strpos($sQueryUpper, 'EXPLAIN ') === FALSE
							&& strpos($sQueryUpper, '_LOCK') === FALSE
						)
						{
							$oExplainCore_DataBase = clone $object;
							$oExplainCore_DataBase->query("EXPLAIN {$args[0]}")->asAssoc();

							while ($row = $oExplainCore_DataBase->current())
							{
								$aLog['explain'][] = $row;
							}

							$oExplainCore_DataBase->free();
						}
					}*/

					$queryLog[] = $aLog;
					$oCore_Registry->set('Core_DataBase.queryLog', $queryLog);
				}
			}
		}

		$oCore_Registry->set('Core_DataBase.queryCount',
			$oCore_Registry->get('Core_DataBase.queryCount', 0) + 1
		);
	}
}
