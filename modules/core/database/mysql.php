<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * MySQL DataBase driver
 *
 * @package HostCMS
 * @subpackage Core\Database
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_DataBase_Mysql extends Core_DataBase
{
	/**
	 * Result of query
	 * @var resource|null
	 */
	protected $_result = NULL;

	/**
	 * Get MySQL connection
	 * @return resource
	 */
	public function getConnection()
	{
		$this->connect();
		return $this->_connection;
	}

	/**
	 * Connect to the database
	 * @return boolean
	 * @hostcms-event Core_DataBase.onBeforeConnect
	 * @hostcms-event Core_DataBase.onAfterConnect
	 */
	public function connect()
	{
		// Connection already exists
		if ($this->_connection)
		{
			return TRUE;
		}

		Core_Event::notify('Core_DataBase.onBeforeConnect', $this);

		/**
		 * Trying to open connection
		 * The new_link parameter makes mysql_connect() always open a new link,
		 * even if mysql_connect() was called before with the same parameters.
		 * @var boolean
		 */
		$this->_connection = @mysql_connect(
			$this->_config['host'], $this->_config['username'], $this->_config['password'], $this->_config['newlink']
		);

		if (!$this->_connection)
		{
			throw new Core_Exception('Database open connection error %errno: %error',
				array('%errno' => mysql_errno(), '%error' => mysql_error()),
				mysql_errno());
		}

		if (!is_null($this->_config['database']))
		{
			$this->selectDb($this->_config['database']);
		}

		if (!empty($this->_config['charset']))
		{
			// Sets the client character set
			$this->setCharset($this->_config['charset']);
		}

		Core_Event::notify('Core_DataBase.onAfterConnect', $this);

		return TRUE;
	}

	/**
	 * Select MySQL database
	 * @param string $databaseName The name of the database that is to be selected.
	 * @return self
	 * @hostcms-event Core_DataBase.onBeforeSelectDb
	 * @hostcms-event Core_DataBase.onAfterSelectDb
	 */
	public function selectDb($databaseName)
	{
		Core_Event::notify('Core_DataBase.onBeforeSelectDb', $this);

		if (!is_resource($this->_connection) || !mysql_select_db($databaseName, $this->_connection))
		{
			throw new Core_Exception('Database select error %errno: %error',
				array('%errno' => mysql_errno($this->_connection), '%error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}

		Core_Event::notify('Core_DataBase.onAfterSelectDb', $this);

		return $this;
	}

	/**
	 * Close MySQL connection
	 * @return self
	 */
	public function disconnect()
	{
		if (is_resource($this->_connection))
		{
			if (mysql_close($this->_connection))
			{
				$this->_connection = NULL;
			}
			else
			{
				throw new Core_Exception('Database closes connection error %errno: %error',
					array('%errno' => mysql_errno(), '%error' => mysql_error()),
					mysql_errno());
			}
		}

		return $this;
	}

	/**
	 * Get database version
	 * @return string|NULL
	 */
	public function getVersion()
	{
		return function_exists('mysql_get_server_info') ? mysql_get_server_info() : NULL;
	}

	/**
	 * Set the client character set
	 * @param string $charset A valid character set name.
	 * @return self
	 */
	public function setCharset($charset)
	{
		$this->connect();

		$result = function_exists('mysql_set_charset')
			// PHP 5 >= 5.2.3
			? mysql_set_charset($charset, $this->_connection)
			: mysql_query('SET NAMES ' . $this->quote($charset), $this->_connection);

		if (!$result)
		{
			throw new Core_Exception('Database sets charset error %errno: %error',
				array('%errno' => mysql_errno($this->_connection), '%error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}

		return $this;
	}

	/**
	 * Escapes special characters in a string for use in an SQL statement
	 * @param string $unescapedString The string that is to be escaped
	 * @return string Escaped string
	 */
	public function escape($unescapedString)
	{
		$unescapedString = strval($unescapedString);

		$this->connect();

		$escapedString = mysql_real_escape_string($unescapedString, $this->_connection);

		if ($escapedString === FALSE)
		{
			throw new Core_Exception('Database escapes charset error %errno: %error',
				array('%errno' => mysql_errno($this->_connection), '%error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}

		return "'" . $escapedString . "'";
	}

	/**
	 * Get extended column type information
	 * @param string $columnType Column type, e.g. 'TINYINT'
	 * @return array array(
	 * 	'type' =>
	 * 	'fixed' =>
	 * 	'binary' =>
	 * 	'unsigned' =>
	 * 	'zerofill' =>
	 * 	'min' =>
	 * 	'max' =>
	 * 	'max_length' =>
	 * );
	 */
	public function getColumnType($columnType)
	{
		// http://dev.mysql.com/doc/refman/5.5/en/data-type-overview.html

		$columnType = strtolower($columnType);

		$type = $fixed = $binary = $min = $max = NULL;
		$unsigned = strpos($columnType, 'unsigned') !== FALSE;
		$zerofill = strpos($columnType, 'zerofill') !== FALSE;

		list($switchType, $max_length) = $this->getColumnTypeAndLength($columnType);

		if ($unsigned)
		{
			$min = 0;
		}

		$switchType = str_replace(' zerofill', '', $switchType);

		$default_max_length = NULL;

		switch ($switchType)
		{
			case 'enum':
			case 'nvarchar':
			case 'national varchar':
			case 'datetime':
			case 'year':
				$type = 'string';
				break;

			case 'tinyint':
				$type = 'int';
				$min = -128;
				$max = 127;
				break;

			case 'tinyint unsigned':
				$type = 'int';
				$min = 0;
				$max = 255;
				break;

			case 'smallint':
				$type = 'int';
				$min = -32768;
				$max = 32767;
				break;

			case 'smallint unsigned':
				$type = 'int';
				$min = 0;
				$max = 65535;
				break;

			case 'mediumint':
				$type = 'int';
				$min = -8388608;
				$max = 8388607;
				break;

			case 'mediumint unsigned':
				$type = 'int';
				$min = 0;
				$max = 16777215;
				break;

			case 'int unsigned':
			case 'integer unsigned':
				$type = 'int';
				$min = 0;
				$max = 4294967295;
				break;

			case 'tinytext':
				$type = 'string';
				$default_max_length = 255;
				break;

			case 'text':
				$type = 'string';
				$default_max_length = 65535;
				break;

			case 'mediumtext':
				$type = 'string';
				$default_max_length = 16777215;
				break;

			case 'longtext':
				$type = 'string';
				$default_max_length = 4294967295;
				break;

			case 'tinyblob':
				$type = 'string';
				$binary = TRUE;
				$default_max_length = 255;
				break;

			case 'blob':
				$type = 'string';
				$binary = TRUE;
				$default_max_length = 65535;
				break;

			case 'mediumblob':
				$type = 'string';
				$binary = TRUE;
				$default_max_length = 16777215;
				break;

			case 'longblob':
				$type = 'string';
				$binary = TRUE;
				$default_max_length = 4294967295;
				break;
		}

		if ($type)
		{
			return array(
				'datatype' => $switchType,
				'type' => $type,
				'fixed' => $fixed,
				'binary' => $binary,
				'unsigned' => $unsigned,
				'zerofill' => $zerofill,
				'min' => $min,
				'max' => $max,
				'max_length' => is_null($max_length) ? $default_max_length : $max_length
			);
		}

		return parent::getColumnType($columnType);
	}

	/**
	 * Maximum number of objects
	 * Максимальное количество объектов в кэше
	 * @var integer
	 */
	static protected $_maxObjects = 256;

	/**
	 * Quoted column name cache
	 * @var array
	 */
	protected $_quoteColumnNameCache = array();

	/**
	 * Insert a quoted column name into cache
	 * @param string $columnName column name
	 * @param string $value quoted column name
	 * @return self
	 */
	protected function _addQuoteColumnNameCache($columnName, $value)
	{
		// Delete old items
		if (/*rand(0, self::$_maxObjects) == 0 && */count($this->_quoteColumnNameCache) > self::$_maxObjects)
		{
			$this->_quoteColumnNameCache = array_slice($this->_quoteColumnNameCache, floor(self::$_maxObjects / 4));
		}

		$this->_quoteColumnNameCache[$columnName] = $value;
		return $this;
	}

	/**
	 * Quote table name, e.g. `tableName` for 'tableName',
	 * `tableName` AS `tableNameAlias` for array('tableName', 'tableNameAlias')
	 * @param mixed $columnName string|array
	 * @return string
	 */
	public function quoteTableName($tableName)
	{
		if (is_array($tableName))
		{
			// array('columnName', 'columnNameAlias') => `columnName` AS `columnNameAlias`
			if (count($tableName) == 2)
			{
				list($tableName, $columnNameAlias) = $tableName;
				return $this->quoteTableName($tableName) . ' AS ' . $this->quoteTableName($columnNameAlias);
			}
			else
			{
				list($tableName) = $tableName;
				return $this->quoteTableName($tableName);
			}
		}
		// Core_QueryBuilder_Expression
		elseif (is_object($tableName))
		{
			// add brackets for subquery
			return get_class($tableName) == 'Core_QueryBuilder_Select'
				? '(' . $tableName->build() . ')'
				: $tableName->build();
		}

		return $this->_tableQuoteCharacter . str_replace($this->_tableQuoteCharacter, '\\' . $this->_tableQuoteCharacter, $tableName) . $this->_tableQuoteCharacter;
	}

	/**
	 * Quote column name, e.g. `columnName` for 'columnName',
	 * `columnName` AS `columnNameAlias` for array('columnName', 'columnNameAlias')
	 * @param mixed $columnName string or array
	 * @return string
	 */
	public function quoteColumnName($columnName)
	{
		if ($columnName === '*')
		{
			return $columnName;
		}

		if (is_array($columnName))
		{
			// array('columnName', 'columnNameAlias') => `columnName` AS `columnNameAlias`
			if (count($columnName) == 2)
			{
				list($columnName, $columnNameAlias) = $columnName;
				return $this->quoteColumnName($columnName) . ' AS ' . $this->quoteColumnName($columnNameAlias);
			}
			else
			{
				list($columnName) = $columnName;
				return $this->quoteColumnName($columnName);
			}
		}
		// Core_QueryBuilder_Expression
		elseif (is_object($columnName))
		{
			// add brackets for subquery
			return get_class($columnName) == 'Core_QueryBuilder_Select'
				? '(' . $columnName->build() . ')'
				: $columnName->build();
		}

		if (isset($this->_quoteColumnNameCache[$columnName]))
		{
			return $this->_quoteColumnNameCache[$columnName];
		}

		// До проверки на точку, т.к. аргумент функции может ее содержать!
		// SEC_TO_TIME(SUM(TIME_TO_SEC(time_col))) -> SEC_TO_TIME(SUM(TIME_TO_SEC(`time_col`)))
		if (strpos($columnName, '(') !== FALSE && strpos($columnName, ')') !== FALSE)
		{
			preg_match('/^([a-zA-Z_]*)\s*\((.+?)\)$/i', $columnName, $matches);

			if (count($matches) == 3)
			{
				$return = $matches[1] . '(' . $this->quoteColumnName($matches[2]) . ')';

				$this->_addQuoteColumnNameCache($columnName, $return);
				return $return;
			}

			// AS IS
			// ROUND(SUM(id)/SUM(id) * 100, 2)
			$this->_addQuoteColumnNameCache($columnName, $columnName);
			return $columnName;
		}

		// SELECT 17 AS INT
		if (is_int($columnName))
		{
			$this->_addQuoteColumnNameCache($columnName, $columnName);
			return $columnName;
		}

		// 'tableName.columnName' => `tableName`.`columnName`
		// columnName1 + columnName2 => `columnName1`+`columnName2`
		foreach ($this->_separator as $separator)
		{
			if (strpos($columnName, $separator) !== FALSE)
			{
				$aColumnName = explode($separator, $columnName);

				foreach ($aColumnName AS $key => $value)
				{
					$aColumnName[$key] = $this->quoteColumnName(trim($value));
				}

				$return = implode($separator, $aColumnName);
				$this->_addQuoteColumnNameCache($columnName, $return);
				return $return;
			}
		}

		$return = $this->_columnQuoteCharacter . str_replace($this->_columnQuoteCharacter, '\\' . $this->_columnQuoteCharacter, $columnName) . $this->_columnQuoteCharacter;

		$this->_addQuoteColumnNameCache($columnName, $return);
		return $return;
	}

	/**
	 * Set transaction isolation level
	 *
	 * @param string $IsolationLevel Isolation level:
	 * READ UNCOMMITTED, READ COMMITTED, REPEATABLE READ, SERIALIZABLE
	 *
	 * @return self
	 */
	public function setIsolationLevel($IsolationLevel)
	{
		$IsolationLevel = strtoupper(trim($IsolationLevel));

		if (!in_array($IsolationLevel, array('READ UNCOMMITTED', 'READ COMMITTED', 'REPEATABLE READ', 'SERIALIZABLE')))
		{
			return FALSE;
		}

		$this->connect();

		$result = mysql_query("SET TRANSACTION ISOLATION LEVEL {$IsolationLevel}", $this->_connection);

		if (!$result)
		{
			throw new Core_Exception('SET TRANSACTION ISOLATION LEVEL error %errno: %error',
				array('%errno' => mysql_errno($this->_connection), 	'%error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}

		return $this;
	}

	/**
	 * Start SQL-transaction
	 *
	 * @param string $IsolationLevel Transaction isolation level
	 * @return self
	 */
	public function begin($IsolationLevel = NULL)
	{
		$this->connect();

		if ($IsolationLevel)
		{
			// Sets transaction isolation level
			$this->setIsolationLevel($IsolationLevel);
		}

		mysql_query('BEGIN', $this->_connection);

		return $this;
	}

	/**
	 * Commit SQL-transaction
	 *
	 * @return self
	 */
	public function commit()
	{
		$this->connect();

		mysql_query('COMMIT', $this->_connection);

		return $this;
	}

	/**
	 * Rollback SQL-transaction
	 *
	 * @return self
	 */
	public function rollback()
	{
		$this->connect();

		mysql_query('ROLLBACK', $this->_connection);

		return $this;
	}

	/**
	 * Get list of tables in a database
	 *
	 * @param mixed $selectionCondition Selection condition
	 * @return array
	 */
	public function getTables($selectionCondition = NULL)
	{
		$this->connect();

		$query = is_null($selectionCondition)
			? 'SHOW TABLES'
			: 'SHOW TABLES LIKE ' . $this->quote($selectionCondition);

		$result = mysql_query($query, $this->_connection);

		$return = array();
		while ($row = mysql_fetch_row($result))
		{
			$return[] = $row[0];
		}

		return $return;
	}

	/**
	 * Send a MySQL query
	 * @param string $sql An SQL query. The query string should not end with a semicolon. Data inside the query should be properly escaped.
	 * @return self
	 * @hostcms-event Core_DataBase.onBeforeQuery
	 * @hostcms-event Core_DataBase.onAfterQuery
	 */
	public function query($sql)
	{
		$this->connect();

		Core_Event::notify('Core_DataBase.onBeforeQuery', $this, array($sql));

		$this->_lastQuery = $sql;

		/*
		Warning: to delete
		if ($f_log = @fopen(CMS_FOLDER . 'sql.log', 'a'))
		{
			if (flock($f_log, LOCK_EX))
			{
				fwrite($f_log, date("d.m.Y H:i:s")." Query: {$sql}\r\n");
				flock($f_log, LOCK_UN);
			}
			fclose($f_log);
		}*/

		$this->_result = $this->_unbuffered
			? mysql_unbuffered_query($sql, $this->_connection)
			: mysql_query($sql, $this->_connection);

		if (!$this->_result)
		{
			throw new Core_Exception('Query error %errno: %error. Query: %query',
				array(
					'%errno' => mysql_errno($this->_connection),
					'%error' => mysql_error($this->_connection),
					'%query' => $sql
				),
				mysql_errno($this->_connection), TRUE, Core_Log::$ERROR);
		}

		Core_Event::notify('Core_DataBase.onAfterQuery', $this, array($sql));

		return $this;
	}

	/**
	 * Get last query result rows as an array of associative arrays or as an array of objects
	 * @param boolean $bCache use cache
	 * @return array
	 * @see current()
	 */
	public function result($bCache = TRUE)
	{
		$return = array();

		// Может быть изменен при запросах внутри моделей, например find() в конструкторе
		$_asObject = $this->_asObject;
		$result = $this->getResult();

		if (is_resource($result))
		{
			if ($this->_asObject === FALSE)
			{
				while ($row = $this->_currentAssoc($result))
				{
					$return[] = $row;
					$this->_asObject = $_asObject;
				}
			}
			else
			{
				while ($row = $this->_currentObject($result, $bCache))
				{
					$return[] = $row;
					$this->_asObject = $_asObject;
				}
			}

			$this->_free($result);
		}

		return $return;
	}

	/**
	 * Get next row for last query result as an associative array or as an object
	 * @param boolean $bCache use cache
	 * @return mixed array or object
	 * @see asAssoc()
	 * @see asObject()
	 * @see result()
	 */
	public function current($bCache = TRUE)
	{
		return $this->_asObject === FALSE
			? $this->_currentAssoc()
			: $this->_currentObject(NULL, $bCache);
	}

	/**
	 * Get last query result as resource
	 *
	 * @return resource
	 */
	public function getResult()
	{
		return $this->_result;
	}

	/**
	 * Set result type as an array
	 * @return self
	 */
	public function asAssoc()
	{
		$this->_asObject = FALSE;
		return $this;
	}

	/**
	 * Set result type as an object with className
	 * @param mixed $className Object class name
	 * @return self
	 */
	public function asObject($className = NULL)
	{
		$this->_asObject = $className;
		return $this;
	}

	/**
	 * Get mysql_fetch_assoc() last result
	 * @param $result resource
	 * @return mixed
	 */
	protected function _currentAssoc($result = NULL)
	{
		return mysql_fetch_assoc(is_null($result) ? $this->_result : $result);
	}

	/**
	 * Get mysql_fetch_object() last result
	 * @param $result resource
	 * @param boolean $bCache use cache
	 * @return mixed
	 */
	protected function _currentObject($result = NULL, $bCache = TRUE)
	{
		$object_name = is_null($this->_asObject) ? 'stdClass' : $this->_asObject;
		$result = is_null($result) ? $this->_result : $result;

		$return = mysql_fetch_object($result, $object_name);

		if ($bCache && $return && $object_name != 'stdClass')
		{
			$Core_ObjectWatcher = Core_ObjectWatcher::instance();

			$object = $Core_ObjectWatcher->exists(get_class($return), $return->getPrimaryKey());

			if (!is_null($object))
			{
				return $object;
			}

			$Core_ObjectWatcher->add($return);
		}

		return $return;
	}

	/**
	 * Get number of rows in result
	 * @return integer|null number of rows or NULL
	 */
	public function getNumRows()
	{
		if ($this->_queryType === 0 && is_resource($this->_result))
		{
			return mysql_num_rows($this->_result);
		}

		return NULL;
	}

	/**
	 * Get the ID generated in the last query
	 * @return integer|null
	 */
	public function getInsertId()
	{
		if ($this->_queryType === 1 && $this->_connection)
		{
			return mysql_insert_id($this->_connection);
		}

		return NULL;
	}

	/**
	 * Get number of affected rows in previous MySQL operation
	 * @return integer|null number of affected rows or NULL
	 */
	public function getAffectedRows()
	{
		// Get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE query
		if ($this->_queryType > 0 && $this->_queryType < 4 && $this->_connection)
		{
			return mysql_affected_rows($this->_connection);
		}

		return NULL;
	}

	/**
	 * Returns the number of columns in the result set
	 * @return integer|null number of columns in the result set
	 */
	public function getColumnCount()
	{
		return is_resource($this->_result)
			? mysql_num_fields($this->_result)
			: NULL;
	}

	/**
	 * Free last result memory
	 * @return self
	 */
	public function free()
	{
		$this->_free($this->_result);
		$this->_result = NULL;

		return $this;
	}

	/**
	 * Free result memory
	 * @return self
	 */
	protected function _free($result)
	{
		is_resource($result) && mysql_free_result($result);
		$result = NULL;

		return $this;
	}
}