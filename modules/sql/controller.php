<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @var Core_DataBase|NULL
	 */
	protected $_Core_DataBase = NULL;

	/**
	 * @var array|NULL
	 */
	protected $_config = NULL;

	public function __construct()
	{
		$this->_Core_DataBase = Core_DataBase::instance();

		$this->_config = $this->_Core_DataBase->getConfig();
	}

	/**
	 * Выполнение SQL-запроса
	 *
	 * @param string $sql SQL-запрос
	 * @return mixed количество выполненных запросов или NULL
	 * @see executeByString()
	 */
	public function execute($sql)
	{
		return $this->executeByString($sql);
	}

	/**
	 * Выполнение SQL-запроса из строки
	 *
	 * @param string $sql SQL-запрос
	 * @return mixed количество выполненных запросов
	 *
	 * <code>
	 * <?php
	 * $sql = "ALTER TABLE `table1` ADD `my_field` INT(11) NOT NULL DEFAULT '0' AFTER `id`;";
	 *
	 * Sql_Controller::instance()->executeByString($sql);
	 * ?>
	 * </code>
	 */
	public function executeByString($sql)
	{
		// вернуть в 7.0.1
		//$sql = Core_Str::removeBOM($sql);
		if (substr($sql, 0, 3) === "\xEF\xBB\xBF")
		{
			$sql = substr($sql, 3);
		}

		$sql = str_replace("\r", "\n", $sql);

		$iQuery = $position = 0;

		$len = strlen($sql);

		$query = '';

		do {
			//echo "<br>";
			$newLinePos = strpos($sql, "\n", $position);

			if ($newLinePos !== FALSE)
			{
				$line = substr($sql, $position, $newLinePos - $position);
				$position = $newLinePos + 1;
			}
			else
			{
				$line = substr($sql, $position);
				$position = $len;
			}

			$line = trim($line);

			// Не комментарий
			if ($line != '' && substr($line, 0, 1) != '#' && substr($line, 0, 2) != '--')
			{
				$query .= $line . "\n";

				// Собираем запрос разделенный на несколько строк
				if (substr($line, -1) == ';' || $position == $len)
				{
					$this->_executeQuery($query) && $iQuery++;

					$query = '';
				}
			}

		} while ($position < $len);

		return $iQuery;
	}

	/**
	 * Выполнение SQL-запроса из файла
	 *
	 * @param string $filePath путь к файлу
	 * @return mixed количество выполненных запросов или NULL
	 *
	 * <code>
	 * <?php
	 * Sql_Controller::instance()->executeByFile(CMS_FOLDER . 'sql/myfile.sql');
	 * ?>
	 * </code>
	 */
	public function executeByFile($filePath)
	{
		$iQuery = $i = 0;

		$query = '';

		if ($fd = fopen($filePath, "rb"))
		{
			$sql = $line = '';

			while (!feof($fd))
			{
				$sql .= fread($fd, 1000);

				$feof = feof($fd);

				if ($i == 0)
				{
					// BOM
					// вернуть в 7.0.1
					//$sql = Core_Str::removeBOM($sql);
					if (substr($sql, 0, 3) === "\xEF\xBB\xBF")
					{
						$sql = substr($sql, 3);
					}
				}

				$sql = str_replace("\r", "\n", $sql);

				do {
					$newLinePos = strpos($sql, "\n");

					if ($newLinePos !== FALSE || $feof)
					{
						if ($newLinePos !== FALSE)
						{
							$line = substr($sql, 0, $newLinePos - 1);

							$sql = substr($sql, $newLinePos + 1);
						}
						elseif ($feof)
						{
							$line = $sql;
							$sql = '';
						}

						$line = trim($line);

						// Не комментарий
						if ($line != '' && substr($line, 0, 1) != '#' && substr($line, 0, 2) != '--')
						{
							$query .= $line . "\n";

							// Собираем запрос разделенный на несколько строк
							if (substr($line, -1) == ';' || $feof)
							{
								$this->_executeQuery($query) && $iQuery++;

								$query = '';
							}
						}
					}
				} while ($newLinePos !== FALSE);

				$i++;
			}

			fclose($fd);
		}

		return $iQuery;
	}

	/**
	 * Выполнение SQL-запроса из строки с подменой ENGINE и CHARSET
	 *
	 * @param string $sql SQL-запрос
	 * @return boolean
	 */
	protected function _executeQuery($sql)
	{
		try
		{
			isset($this->_config['storageEngine']) && $this->_config['storageEngine'] != 'MyISAM'
				&& $sql = str_replace(' ENGINE=MyISAM', ' ENGINE=' . $this->_config['storageEngine'], $sql);

			isset($this->_config['charset']) && $this->_config['charset'] != 'utf8'
				&& $sql = str_replace(' CHARSET=utf8;', ' CHARSET=' . $this->_config['charset'] . ';', $sql);

			$this->_Core_DataBase->setQueryType(1)->query($sql);

			return TRUE;
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}

		return FALSE;
	}
}