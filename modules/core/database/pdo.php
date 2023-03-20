<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * PDO DataBase driver
 *
 * @package HostCMS
 * @subpackage Core\Database
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_DataBase_Pdo extends Core_DataBase
{
	/**
	 * Result of query
	 * @var resource|null
	 */
	protected $_result = NULL;

	/**
	 * Get connection
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

		$this->_config += array(
			'driverName' => 'mysql',
			'attr' => array(
				PDO::ATTR_PERSISTENT => FALSE,
				// Moved to ->setAttribute() after connectiom
				//PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

				// Setting the connection character set to UTF-8 prior to PHP 5.3.6
				//PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->quote($this->_config['charset'])
			)
		);

		// Trying to open connection. Prior to PHP 5.3.6 charset option is ignored
		try
		{
			$aHost = explode(':', $this->_config['host']);

			$dsn = "{$this->_config['driverName']}:host={$aHost[0]}";

			isset($aHost[1])
				&& $dsn .= ";port={$aHost[1]}";

			!is_null($this->_config['database'])
				&& $dsn .= ";dbname={$this->_config['database']}";

			$dsn .= ";charset={$this->_config['charset']}";

			$this->_connection = new PDO(
				$dsn,
				$this->_config['username'],
				$this->_config['password'],
				$this->_config['attr']
			);

			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			throw new Core_Exception('Database open connection error %errno: %error',
				array('%errno' => $e->getCode(), '%error' => $e->getMessage()));
		}

		// 5.3.27 doesn't work without setCharset()
		if (!empty($this->_config['charset']) /*&& version_compare(PHP_VERSION, '5.3.6', '<')*/)
		{
			// Sets the client character set
			$this->setCharset($this->_config['charset']);
		}

		Core_Event::notify('Core_DataBase.onAfterConnect', $this);

		return TRUE;
	}

	/**
	 * Close connection
	 * @return self
	 */
	public function disconnect()
	{
		$this->_connection = NULL;
		return $this;
	}

	/**
	 * Get database version
	 * @return string|NULL
	 */
	public function getVersion()
	{
		return $this->_connection->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Set the client character set
	 * @param string $charset A valid character set name.
	 * @return self
	 */
	public function setCharset($charset)
	{
		$this->connect();

		$this->_connection->exec('SET NAMES ' . $this->quote($charset));

		return $this;
	}

	/**
	 * Escapes special characters in a string for use in an SQL statement
	 * @param string $unescapedString The string that is to be escaped
	 * @return string Escaped string
	 */
	public function escape($unescapedString)
	{
		$this->connect();

		$unescapedString = addcslashes(strval($unescapedString), "\000\032");

		return $this->_connection->quote($unescapedString);
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

			case 'int':
			case 'integer':
				$type = 'int';
				$min = -2147483648;
				$max = 2147483647;
				break;

			case 'int unsigned':
			case 'integer unsigned':
				$type = 'int';
				$min = 0;
				$max = 4294967295;
				break;

			case 'char':
				$type = 'string';
				$default_max_length = 255;
				break;
			case 'binary':
				$type = 'string';
				$default_max_length = 255;
				$binary = TRUE;
				break;

			case 'varchar':
				$type = 'string';
				$default_max_length = 65535;
				break;
			case 'varbinary':
				$type = 'string';
				$default_max_length = 65535;
				$binary = TRUE;
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

		return $type
			? array(
				'datatype' => $switchType,
				'type' => $type,
				'fixed' => $fixed,
				'binary' => $binary,
				'unsigned' => $unsigned,
				'zerofill' => $zerofill,
				'min' => $min,
				'max' => $max,
				'defined_max_length' => $max_length,
				'max_length' => is_null($max_length) ? $default_max_length : $max_length
			)
			: parent::getColumnType($columnType);
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
	 * @param mixed $columnName string|array
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
			// add brackets for subquery // see _isObjectSelect inside QB
			return /*get_class($columnName) == 'Core_QueryBuilder_Select'
				? '(' . $columnName->build() . ')'
				: */$columnName->build();
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

				foreach ($aColumnName as $key => $value)
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

		$this->_connection->exec("SET TRANSACTION ISOLATION LEVEL {$IsolationLevel}");

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

		$this->_connection->beginTransaction();

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

		$this->_connection->commit();

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

		$this->_connection->rollBack();

		return $this;
	}

	/**
	 * Get list of tables in the database
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

		$result = $this->_connection->query($query);

		$return = array();
		while ($row = $result->fetch(PDO::FETCH_NUM))
		{
			$return[] = $row[0];
		}

		$this->_free($result);

		return $return;
	}

	/**
	 * Get tables schema in the database
	 *
	 * @param mixed $selectionCondition Selection condition
	 * @return array
	 */
	public function getTablesSchema($selectionCondition = NULL)
	{
		$this->connect();

		// MAX_DATA_LENGTH as max_data_length, DATA_FREE, AUTO_INCREMENT, CREATE_TIME, UPDATE_TIME, CHECK_TIME, CHECKSUM, CREATE_OPTIONS, TABLE_COMMENT

		$query = 'SELECT TABLE_NAME as name, ENGINE as engine, AUTO_INCREMENT as auto_increment, VERSION as version, ROW_FORMAT as row_format, TABLE_ROWS as table_rows, AVG_ROW_LENGTH as avg_row_legth, DATA_LENGTH as data_length, INDEX_LENGTH as index_length, DATA_FREE as fragmented, TABLE_COLLATION as collation
		FROM `INFORMATION_SCHEMA`.`TABLES`
		WHERE `table_schema` = ' . $this->quote($this->_config['database']);

		!is_null($selectionCondition)
			&& $query .= ' AND `TABLE_NAME` LIKE ' . $this->quote($selectionCondition);

		$query .= ' ORDER BY `name` ASC';

		// echo htmlspecialchars($query);

		$result = $this->_connection->query($query);

		// free in the _fetch
		return $this->_fetch($result, FALSE);
	}

	/**
	 * Get the process list indicates the operations currently being performed
	 *
	 * @return array
	 */
	public function getProcesslist()
	{
		$this->connect();

		$query = 'SELECT * FROM `INFORMATION_SCHEMA`.`PROCESSLIST` WHERE `state` != "" AND `db` = ' . $this->quote($this->_config['database']);

		$result = $this->_connection->query($query);

		$return = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC))
		{
			$return[] = $row;
		}

		$this->_free($result);

		return $return;
	}

	/**
	 * Query without fetching and buffering the result rows
	 * @param bool $unbuffered
	 * @return self
	 */
	public function unbuffered($unbuffered)
	{
		parent::unbuffered($unbuffered);

		$this->_connection
			->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$this->_unbuffered);

		return $this;
	}

	/**
	 * Send a query
	 * @param string $sql An SQL query. The query string should not end with a semicolon. Data inside the query should be properly escaped.
	 * @return self
	 * @hostcms-event Core_DataBase.onBeforeQuery
	 * @hostcms-event Core_DataBase.onAfterQuery
	 */
	public function query($sql)
	{
		$this->connect();

		Core_Event::notify('Core_DataBase.onBeforeQuery', $this, array($sql));
//if (!is_null($this->_result)) { echo '+'; var_dump($this->_lastQuery);}
		$this->_lastQuery = $sql;

		try
		{
			// Free result memory if exists previous unbuffered query
			//$this->_free($this->_result);

			$this->_result = $this->_connection->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Core_Exception('Query error %errno: %error. Query: %query',
				array(
					'%errno' => $e->getCode(),
					'%error' => $e->getMessage(),
					'%query' => $sql
				),
				$e->getCode(), TRUE, Core_Log::$ERROR);
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
		// free in the _fetch
		return $this->_fetch($this->getResult(), $bCache);
	}

	/**
	 * Fetch query result
	 * @param $result resource
	 * @param boolean $bCache use cache
	 * @return array
	 */
	protected function _fetch($result, $bCache = TRUE)
	{
		$return = array();

		if ($result)
		{
			// Может быть изменен при запросах внутри моделей, например find() в конструкторе
			$_asObject = $this->_asObject;

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
	 * Get last result item as array
	 * @param $result resource
	 * @return mixed
	 */
	protected function _currentAssoc($result = NULL)
	{
		is_null($result)
			&& $result = $this->_result;

		$result->setFetchMode(PDO::FETCH_ASSOC);

		return $result->fetch();
	}

	/**
	 * Get last result as object
	 * @param $result resource
	 * @param boolean $bCache use cache
	 * @return mixed
	 */
	protected function _currentObject($result = NULL, $bCache = TRUE)
	{
		$objectName = is_null($this->_asObject) ? 'stdClass' : $this->_asObject;
		$result = is_null($result) ? $this->_result : $result;

		$result->setFetchMode(PDO::FETCH_CLASS/*|PDO::FETCH_PROPS_LATE*/, $objectName);
		$return = $result->fetch();

		if ($bCache && $return && $return instanceof Core_ORM)
		{
			$Core_ObjectWatcher = Core_ObjectWatcher::instance();

			$object = $Core_ObjectWatcher->exists(get_class($return), $return->getPrimaryKey());

			if (!is_null($object))
			{
				// move dataXXX values
				foreach ($return->getDataValues() as $key => $value)
				{
					$object->$key = $value;
				}

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
	/*public function getNumRows()
	{
		throw new Core_Exception('getNumRows() does not allow');
	}*/

	/**
	 * Get the ID generated in the last query
	 * @return integer|null
	 */
	public function getInsertId()
	{
		if ($this->_queryType === 1 && $this->_connection)
		{
			return $this->_connection->lastInsertId();
		}

		return NULL;
	}

	/**
	 * Get number of affected rows in previous operation
	 * @return integer|null number of affected rows or NULL
	 */
	public function getAffectedRows()
	{
		// Get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE query
		if ($this->_queryType > 0 && $this->_queryType < 4 && $this->_result)
		{
			return $this->_result->rowCount();
		}

		return NULL;
	}

	/**
	 * Returns the number of columns in the result set
	 * @return integer|null number of columns in the result set
	 */
	public function getColumnCount()
	{
		return $this->_result
			? $this->_result->columnCount()
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
		$result && $result->closeCursor();
		$result = NULL;

		return $this;
	}
}