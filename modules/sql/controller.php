<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Выполнение SQL-запроса
	 *
	 * @param string $sql SQL-запрос
	 * <code>
	 * <?php
	 * $site_id = intval(CURRENT_SITE);
	 *
	 * $sql = "SELECT * FROM `mytable`;
	 *
	 * -- Далее можно указывать следующие запросы
	 * ";
	 *
	 * Sql_Controller::instance()->execute($sql);
	 * ?>
	 * </code>
	 * @param string $sql SQL
	 * @return mixed количество выполненных запросов или NULL
	 */
	public function execute($sql)
	{
		// BOM
		$sql = str_replace(chr(0xEF) . chr(0xBB) . chr(0xBF), '', $sql);
		$sql = trim($sql);

		if (strlen($sql))
		{
			$aSql = explode("\n", str_replace("\r", '', $sql));

			$count_query = 0;

			$i = 0;

			$oCore_DataBase = Core_DataBase::instance();

			$aConfig = $oCore_DataBase->getConfig();

			while ($i < count($aSql))
			{
				$sql = trim($aSql[$i]);

				if (strlen($sql))
				{
					// Собираем запрос разделенный на несколько строк
					while (mb_substr($sql, -1) != ';' && mb_substr($sql, 0, 1) != '#'
					&& mb_substr($sql, 0, 2) != "--"
					&& $i+1 < count($aSql))
					{
						$i++;
						$sql .= "\n" . $aSql[$i];
					}

					$sql = trim($sql);

					// Если не комментарии
					if (mb_substr($sql, 0, 1) != '#' && mb_substr($sql, 0, 2) != '--' && $sql != '')
					{
						try
						{
							if (isset($aConfig['storageEngine']) && $aConfig['storageEngine'] != 'MyISAM')
							{
								$sql = str_replace(' ENGINE=MyISAM', ' ENGINE=' . $aConfig['storageEngine'], $sql);
							}

							$oCore_DataBase->setQueryType(1)->query($sql);
							$count_query++;
						}
						catch (Exception $e)
						{
							Core_Message::show($e->getMessage(), 'error');
						}
					}
				}

				$i++;
			}

			return $count_query;
		}

		return NULL;
	}
}