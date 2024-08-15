<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
		$sql = Core_Str::removeBOM($sql);

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

		// Last line
		if ($query !== '')
		{
			$this->_executeQuery($query) && $iQuery++;
		}

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
				$sql .= str_replace("\r", "\n", fread($fd, 1000));

				$feof = feof($fd);

				if ($i == 0)
				{
					// BOM
					$sql = Core_Str::removeBOM($sql);
				}

				do {
					$newLinePos = strpos($sql, "\n");

					if ($newLinePos !== FALSE || $feof)
					{
						if ($newLinePos !== FALSE)
						{
							$line = substr($sql, 0, $newLinePos);
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
							if (substr($line, -1) == ';' || $feof && $sql == '')
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
				&& $sql = str_ireplace(' ENGINE=MyISAM', " ENGINE={$this->_config['storageEngine']}", $sql);

			isset($this->_config['charset']) && $this->_config['charset'] != 'utf8'
				&& $sql = str_ireplace(
					array(' CHARSET=utf8;', ' CHARACTER SET utf8 ', ' COLLATE utf8_'),
					array(" CHARSET={$this->_config['charset']};", " CHARACTER SET {$this->_config['charset']} ", " COLLATE {$this->_config['charset']}_"),
					$sql
				);

			$this->_Core_DataBase->setQueryType(1)->query($sql);

			return TRUE;
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}

		return FALSE;
	}

	/**
	 * Sanitize MySQL Identifiers
	 * @param string
	 * @return string
	 */
	static public function sanitizeIdentifiers($str)
	{
		// https://dev.mysql.com/doc/refman/8.0/en/identifiers.html
		/* Permitted characters in unquoted identifiers:
			ASCII: [0-9,a-z,A-Z$_] (basic Latin letters, digits 0-9, dollar, underscore)
			Extended: U+0080 .. U+FFFF
		Permitted characters in quoted identifiers include the full Unicode Basic Multilingual Plane (BMP), except U+0000:
			ASCII: U+0001 .. U+007F
			Extended: U+0080 .. U+FFFF */
		return !is_null($str) ? preg_replace('/[^A-Za-z0-9$_\x{0001}-\x{007F}\x{0080}-\x{FFFF}]/u', '', $str) : '';
	}

	/**
	 * Get fields icon
	 * @param string $href
	 * @param string $class
	 * @return Admin_Form_Entity
	 */
	static public function getTableViewIcon($tableName, $class = 'fa fa-table h5-edit-icon warning')
	{
		$href = '/admin/sql/table/view/index.php?table=' . $tableName;
		$onclick = "$.adminLoad({path: '/admin/sql/table/view/index.php',additionalParams: 'table={$tableName}', windowId: 'id_content'}); return false";

		return Admin_Form_Entity::factory('Code')
			->html('
				<script>
					$(\'h5.row-title\').append(
						$("<a>")
							.attr("href", "' . $href . '")
							.attr("title", "' . Core::_('Sql_Table_View.title', $tableName) . '")
							.attr("target", "_blank")
							.attr("onclick", "' . Core_Str::escapeJavascriptVariable($onclick) . '")
							.append(\'<i class="' . htmlspecialchars($class) . '"></i>\')
						);
				</script>
		');
	}

	/**
	 * Get fields icon
	 * @param string $href
	 * @param string $class
	 * @return Admin_Form_Entity
	 */
	static public function getFieldsIcon($tableName, $class = 'fa fa-th-list h5-edit-icon azure')
	{
		$href = '/admin/sql/table/field/index.php?table=' . $tableName;
		$onclick = "$.adminLoad({path: '/admin/sql/table/field/index.php',additionalParams: 'table={$tableName}', windowId: 'id_content'}); return false";

		return Admin_Form_Entity::factory('Code')
			->html('
				<script>
					$(\'h5.row-title\').append(
						$("<a>")
							.attr("href", "' . $href . '")
							.attr("title", "' . Core::_('Sql_Table_Field.title', $tableName) . '")
							.attr("target", "_blank")
							.attr("onclick", "' . Core_Str::escapeJavascriptVariable($onclick) . '")
							.append(\'<i class="' . htmlspecialchars($class) . '"></i>\')
						);
				</script>
		');
	}

	/**
	 * Get indexes icon
	 * @param string $href
	 * @param string $class
	 * @return Admin_Form_Entity
	 */
	static public function getIndexesIcon($tableName, $class = 'fas fa-key h5-edit-icon success')
	{
		$href = '/admin/sql/table/index/index.php?table=' . $tableName;
		$onclick = "$.adminLoad({path: '/admin/sql/table/index/index.php',additionalParams: 'table={$tableName}', windowId: 'id_content'}); return false";

		return Admin_Form_Entity::factory('Code')
			->html('
				<script>
					$(\'h5.row-title\').append(
						$("<a>")
							.attr("href", "' . $href . '")
							.attr("title", "' . Core::_('Sql_Table_Index.title', $tableName) . '")
							.attr("target", "_blank")
							.attr("onclick", "' . Core_Str::escapeJavascriptVariable($onclick) . '")
							.append(\'<i class="' . htmlspecialchars($class) . '"></i>\')
						);
				</script>
		');
	}
}