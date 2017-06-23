<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract DataBase
 *
 * @package HostCMS
 * @subpackage Core\Database
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_DataBase
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Driver's configuration
	 */
	protected $_config = NULL;

	/**
	 * Cache for driver
	 */
	//protected $_cache = NULL;

	/**
	 * Driver's connection
	 */
	protected $_connection = NULL;

	/**
	 * Last executed query
	 */
	protected $_lastQuery = NULL;

	/**
	 * Type of query:
	 * 0 - SELECT
	 * 1 - INSERT
	 * 2 - UPDATE
	 * 3 - DELETE
	 * 4 - RENAME
	 * 5 - ALTER
	 * 6 - DROP
	 * 7 - TRUNCATE
	 * 8 - LOCK
	 * 9 - SHOW
	 * 99 - OTHER
	 */
	protected $_queryType = NULL;

	/**
	 * Get result as object
	 */
	protected $_asObject = FALSE;

	/**
	 * Array of allowed separators, e.g. table.field => `table`.`field`, a + b => `a` + `b`
	 */
	protected $_separator = array('.', ' + ', ' - ', ' / ', ' * ');

	/**
	 * Connect to the database
	 * @return boolean
	 */
	abstract public function connect();

	/**
	 * Close connection
	 * @return self
	 */
	abstract public function disconnect();

	/**
	 * Get database version
	 * @return string|NULL
	 */
	abstract public function getVersion();

	/**
	 * Set the client character set
	 * @param string $charset A valid character set name.
	 * @return self
	 */
	abstract public function setCharset($charset);

	/**
	 * Escapes special characters in a string for use in an SQL statement
	 * @param string $unescapedString The string that is to be escaped
	 * @return string Escaped string
	 */
	abstract public function escape($unescapedString);

	/**
	 * Get list of tables in a database
	 *
	 * @param mixed $selectionCondition Selection condition
	 * @return array
	 */
	abstract public function getTables($selectionCondition = NULL);

	/**
	 * Get list of columns in a table
	 *
	 * @param string $tableName Table name
	 * @param mixed $selectionCondition Selection condition
	 * @return array
	 */
	abstract public function getColumns($tableName, $selectionCondition = NULL);

	/**
	 * Quote column name, e.g. `columnName` for 'columnName',
	 * `columnName` AS `columnNameAlias` for array('columnName', 'columnNameAlias')
	 * @param mixed $columnName string or array
	 * @return string
	 */
	abstract public function quoteColumnName($columnName);

	/**
	 * Start SQL-transaction
	 *
	 * @param string $IsolationLevel Transaction isolation level
	 * @return self
	 */
	abstract public function begin($IsolationLevel = NULL);

	/**
	 * Commit SQL-transaction
	 *
	 * @return self
	 */
	abstract public function commit();

	/**
	 * Rollback SQL-transaction
	 *
	 * @return self
	 */
	abstract public function rollback();

	/**
	 * Send a MySQL query
	 * @param string $sql An SQL query. The query string should not end with a semicolon. Data inside the query should be properly escaped.
	 * @return self
	 */
	abstract public function query($sql);

	/**
	 * Get last query result rows as an array of associative arrays or as an array of objects
	 * @param boolean $bCache use cache
	 * @return array
	 * @see current()
	 */
	abstract public function result($bCache = TRUE);

	/**
	 * Get last query result as resource
	 *
	 * @return resource
	 */
	abstract public function getResult();

	/**
	 * Set result type as an array
	 * @return self
	 */
	abstract public function asAssoc();

	/**
	 * Set result type as an object with className
	 * @param mixed $className Object class name
	 * @return self
	 */
	abstract public function asObject($className = NULL);

	/**
	 * Get next row for last query result as an associative array or as an object
	 * @param boolean $bCache use cache
	 * @return mixed array or object
	 * @see asAssoc()
	 * @see asObject()
	 * @see result()
	 */
	abstract public function current($bCache = TRUE);

	/**
	 * Get the ID generated in the last query
	 * @return integer|null
	 */
	abstract public function getInsertId();

	/**
	 * Get number of affected rows in previous MySQL operation
	 * @return integer|null number of affected rows or NULL
	 */
	abstract public function getAffectedRows();

	/**
	 * Returns the number of columns in the result set 
	 * @return integer|null number of columns in the result set
	 */
	abstract public function getColumnCount();
	
	/**
	 * Free last result memory
	 * @return self
	 */
	abstract public function free();

	/**
	 * Colors array
	 */
	static protected $_colors = array(
		'orange' , 'blue' , 'green' , 'brown'
	);

	/**
	 * Words array
	 */
	static protected $_words = array(
		array('AND', 'IS', '&&', 'LOG', 'NOT','NOW', 'MIN', '!', '\|\|', 'OR', 'OCT', 'TAN',
			'STD', 'SHA', 'ORD', 'XOR', 'ASC', 'DESC'),
		array('SELECT', 'UPDATE', 'INSERT', 'DELETE', 'USING', 'LIMIT', 'OFFSET', 'SET' ),
		array('DATE', 'INTO', 'FROM', 'THEN', 'WHEN', 'WHERE', 'LEFT JOIN', 'LEFT OUTER JOIN', 'RIGHT JOIN', 'RIGHT OUTER JOIN', 'INNER JOIN', 'JOIN', 'ELSE', 'ORDER BY', 'GROUP BY', 'HAVING', 'ON'),
		array('ABS', 'ACOS', 'ADDDATE', 'ADDTIME', 'AES_DECRYPT', 'AES_ENCRYPT', '&&', 'ASCII', 'ASIN', 'ATAN2', 'ATAN',
			'AVG', 'BETWEEN', 'BIN', 'BINARY', 'BIT_AND', 'BIT_LENGTH', 'BIT_OR', 'BIT_XOR', 'CASE',
			'CAST', 'CEIL', 'CEILING', 'CHAR_LENGTH', 'CHAR', 'CHARACTER_LENGTH', 'CHARSET', 'COALESCE',
			'COERCIBILITY', 'COLLATION', 'COMPRESS', 'CONCAT_WS', 'CONCAT', 'CONNECTION_ID', 'CONV',
			'CONVERT_TZ', 'Convert', 'COS', 'COT', 'COUNT', 'COUNT', 'COUNT(DISTINCT)', 'DISTINCT', 'CRC32',
			'CURDATE', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURTIME',
			'DATABASE', 'DATE_ADD', 'DATE_FORMAT', 'DATE_SUB', 'DATEDIFF', 'DAY ', 'DAYNAME', 'DAYOFMONTH',
			'DAYOFWEEK', 'DAYOFYEAR', 'DECODE', 'DEFAULT', 'DEGREES', 'DES_DECRYPT', 'DES_ENCRYPT', 'DIV',
			'ELT', 'ENCODE', 'ENCRYPT', '<=>', 'EXP()', 'EXPORT_SET', 'EXTRACT', 'FIELD', 'FIND_IN_SET',
			'FLOOR', 'FORMAT', 'SQL_CALC_FOUND_ROWS ', 'FOUND_ROWS', 'STRAIGHT_JOIN', 'FROM_DAYS', 'FROM_UNIXTIME', 'GET_FORMAT', 'GET_LOCK',
			'GREATEST', 'GROUP_CONCAT', 'HEX ', 'HOUR', ' IF ', 'IFNULL', ' IN ', 'INET_ATON', 'INET_NTOA',
			'INSTR', 'IS_FREE_LOCK', 'IS NOT NULL', 'IS NOT', 'IS NULL', 'IS_USED_LOCK', 'ISNULL',
			'LAST_DAY', 'LAST_INSERT_ID', 'LCASE', 'LEAST', '<<', 'LEFT', 'LENGTH', 'LIKE', 'LN',
			'LOAD_FILE', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCATE', 'LOG10', 'LOG2', 'LOWER', 'LPAD',
			'LTRIM', 'MAKE_SET', 'MAKEDATE', 'MAKETIME', 'MASTER_POS_WAIT', 'MATCH', 'MAX', 'MD5', 'MICROSECOND',
			'MID', 'MINUTE', 'MOD', '%', 'MONTH', 'MONTHNAME', 'NOT BETWEEN', '!=', 'NOT IN', 'NOT LIKE',
			'NOT REGEXP', 'NULLIF', 'OCTET_LENGTH', 'OLD_PASSWORD', 'ORD', 'PASSWORD', 'PERIOD_ADD', 'PERIOD_DIFF',
			'PI', '\+', 'POSITION', 'POW', 'POWER', 'PROCEDURE ANALYSE', 'QUARTER', 'QUOTE', 'RADIANS',
			'RAND', 'REGEXP', 'RELEASE_LOCK', 'REPEAT', 'REPLACE', 'REVERSE', '>>', 'RIGHT', 'RLIKE',
			'ROUND', 'ROW_COUN', 'RPAD', 'RTRIM', 'SCHEMA', 'SEC_TO_TIME', 'SECOND', 'SESSION_USER',
			'SHA1', 'SIGN', 'SLEEP', 'SOUNDEX', 'SOUNDS LIKE', 'SPACE', 'SQRT', 'STDDEV_POP', 'STDDEV_SAMP',
			'STDDEV', 'STR_TO_DATE', 'SUBDATE', 'SUBSTR', 'SUBSTRING_INDEX', 'SUBSTRING', 'SUBTIME',
			'SUM', 'SYSDATE', 'SYSTEM_USER', 'TIME_FORMAT', 'TIME_TO_SEC', 'TIME', 'TIMEDIFF', '\*',
			'TIMESTAMP', 'TIMESTAMPADD', 'TIMESTAMPDIFF', 'TO_DAYS', 'TRIM', 'TRUNCATE', 'UCASE', 'UNCOMPRESS',
			'UNCOMPRESSED_LENGTH', 'UNHEX', 'UNIX_TIMESTAMP', 'UPPER', 'USER', 'UTC_DATE', 'UTC_TIME',
			'UTC_TIMESTAMP', 'UUID', 'VALUES', 'VAR_POP', 'VAR_SAMP', 'VARIANCE', 'VERSION', 'WEEK',
			'WEEKDAY', 'WEEKOFYEAR', 'YEAR', 'YEARWEE')
		);

	/**
	 * Highlight SQL
	 * @param string $sql SQL
	 * @return string SQL
	 */
	public function highlightSql($sql)
	{
		foreach(self::$_colors as $key => $color)
		{
			foreach(self::$_words[$key] as $word)
			{
				//$sql = str_ireplace(" {$word} " , ' <span style="color: ' . $color . '">' . $word . '</span> ', $sql);
				$sql = preg_replace("/(\s?)({$word})(\s)/iu", "\\1<span style=\"color: {$color}\">\\2</span>\\3", $sql);
			}
		}
		return $sql;
	}

	/**
	 * Set type of query
	 *
	 * @param int $queryType
	 * @return self
	 */
	public function setQueryType($queryType)
	{
		$this->_queryType = $queryType;
		return $this;
	}

	/**
	 * Get type of query
	 *
	 * @return integer
	 */
	public function getQueryType()
	{
		return $this->_queryType;
	}

	/**
	 * Constructor.
	 * @param array $config config
	 */
	public function __construct(array $config)
	{
		// Set config
		$this->setConfig($config);
	}

	/**
	 * Set database config
	 * @param array $config config
	 * @return self
	 */
	public function setConfig(array $config)
	{
		$this->_config = $config + array(
			'host' => 'localhost',
			'username' => '',
			'password' => '',
			'database' => NULL,
			'charset' => 'UTF8',
			'storageEngine' => 'MyISAM',
			'newlink' => FALSE,
			'attr' => array(),
			//'cache' => 'memory'
		);

		return $this;
	}

	/**
	 * Get current config array
	 * @return array
	 */
	public function getConfig()
	{
		return array('username' => NULL, 'password' => NULL) + $this->_config;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$aConfig = Core::$config->get('core_database');

			if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
			{
				throw new Core_Exception('Database configuration doesn\'t defined');
			}

			$driver = self::_getDriverName($aConfig[$name]['driver']);
			self::$instance[$name] = new $driver($aConfig[$name]);
		}

		return self::$instance[$name];
	}

	/**
	 * Convert and escape value for SQL query.
	 * @param mixed $value
	 * @return string
	 */
	public function quote($value)
	{
		if (is_string($value))
		{
			// UNIX_TIMESTAMP(NOW()) -> UNIX_TIMESTAMP(NOW())
			/*if (strpos($value, '(') !== FALSE && strpos($value, ')') !== FALSE)
			{
				preg_match('/^([a-zA-Z_]*)\s*\((.*)\)$/i', $value, $matches);

				if (count($matches) == 3)
				{
					return $matches[1] . '(' . $this->quote($matches[2]) . ')';
				}
			}*/

			// escape and add quote just for string value
			return $this->escape($value);
		}
		// bool true -> 1
		elseif ($value === TRUE)
		{
			return "'1'";
		}
		// bool false -> 0
		elseif ($value === FALSE)
		{
			return "'0'";
		}
		// NULL -> 'NULL'
		elseif (is_null($value))
		{
			return 'NULL';
		}
		// integer value
		elseif (is_int($value))
		{
			return intval($value);
		}
		// float value
		elseif (is_float($value))
		{
			// F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
			// Available since PHP 4.3.10 and PHP 5.0.3.
			return sprintf("%F", $value);
		}
		elseif (is_array($value))
		{
			foreach ($value as $mKey => $mValue)
			{
				$value[$mKey] = $this->quote($mValue);
			}

			return '(' . implode(',', $value) . ')';
		}

		throw new Core_Exception("Wrong argument type '%type' (expected string) for quote()", array(
				'%type' => gettype($value)
			));
	}

	/**
	 * Create table $tableName dump
	 * @param string $tableName
	 * @param Core_Out $stdOut
	 * return Core_DataBase
	 */
	public function dump($tableName, Core_Out $stdOut)
	{
		$sQuoted = $this->quote($tableName);
		$sColumnName = $this->quoteColumnName($tableName);

		$stdOut->write(
			"\r\n\r\n" .
			"-- \r\n" .
			"-- Structure for table " . $sQuoted . " \r\n" .
			"-- \r\n\r\n" .
			"DROP TABLE IF EXISTS " . $sColumnName . ";\r\n"
		);
		
		$aCreate = $this->query("SHOW CREATE TABLE {$sColumnName}")->asAssoc()->current();
		
		$stdOut->write(
			"{$aCreate['Create Table']};\r\n\r\n" .
			"-- \r\n" .
			"-- Data for table {$sQuoted} \r\n" .
			"-- \r\n\r\n"
		);

		$oCore_QueryBuilderSelect = Core_QueryBuilder::select()
			->from($tableName)
			->execute()
			->asAssoc();

		$sInsertInto = "INSERT INTO {$sColumnName} VALUES ";
		$search = array("'", "\\", "\x00", "\x0a", "\x0d", "\x1a");
		$replace = array("''", "\\\\", '\0', '\n', '\r', '\Z');

		$i = 0;
		$content = '';
		while ($row = $oCore_QueryBuilderSelect->current())
		{
			if ($i == 0)
			{
				$content .= "LOCK TABLES {$sColumnName} WRITE; \r\n" .
					$sInsertInto;
			}
			else
			{
				$i % 100 == 0
					? $content .= ";\r\n" . $sInsertInto
					: $content .= ",\r\n";
			}

			$values = array();
			foreach ($row as $sRowName => $rowValue)
			{
				if (is_null($rowValue))
				{
					$values[] = 'NULL';
				}
				// is_numeric нельзя использовать, т.к. 4e998516 истина
				elseif (is_int($rowValue))
				{
					$values[] = $rowValue;
				}
				else
				{
					$values[] = "'" . str_replace($search, $replace, $rowValue) . "'";
				}
			}

			$content .= '(' . implode(',', $values) . ")";

			if ($i % 100)
			{
				$stdOut->write($content);
				$content = '';
			}
			
			$i++;
		}
		
		if ($i > 0)
		{
			$stdOut->write($content . ";\r\n");
			$stdOut->write("UNLOCK TABLES;");
		}

		return $this;
	}

	/**
	 * Split column type into type and length
	 *
	 * @param string $columnType column type
	 * @return array 0 => type, 1 => length
	 */
	public function getColumnTypeAndLength($columnType)
	{
		$type = $columnType;
		$length = NULL;

		$fistBracketPos = strpos($columnType, '(');

		if ($fistBracketPos > 0)
		{
			$lastBracketPos = strpos($columnType, ')');
			$type = substr($columnType, 0, $fistBracketPos) . substr($columnType, $lastBracketPos + 1);
			$length = substr($columnType, $fistBracketPos + 1, $lastBracketPos - $fistBracketPos - 1);
		}

		return array($type, $length);
	}

	/**
	 * Get database colimn type
	 * @param string $columnType
	 * @return array
	 */
	public function getColumnType($columnType)
	{
		// http://www.faqs.org/docs/ppbook/x2632.htm
		$columnType = strtolower($columnType);

		$type = NULL;
		$fixed = NULL;
		$binary = NULL;
		$unsigned = (strpos($columnType, 'unsigned') !== FALSE);
		$zerofill = (strpos($columnType, 'zerofill') !== FALSE);
		$min = $max = NULL;

		list($switch_type, $max_length) = $this->getColumnTypeAndLength($columnType);

		if ($unsigned)
		{
			$min = 0;
		}

		$switch_type = str_replace(' zerofill', '', $switch_type);

		switch ($switch_type)
		{
			case 'boolean':
			case 'bool':
				$type = 'bool';
			break;
			case 'int':
			case 'integer':
			case 'int4':
				$type = 'int';
				$min = -2147483648;
				$max = 2147483647;
			break;
			case 'smallint':
			case 'int2':
				$type = 'int';
				$min = -32768;
				$max = 32767;
			break;
			case 'smallint unsigned':
				$type = 'int';
				$min = 0;
				$max = 65535;
			break;
			case 'bigint':
				$type = 'int';
				$min = -9223372036854775808;
				$max = 9223372036854775807;
			break;
			case 'bigint unsigned':
				$type = 'int';
				$min = 0;
				$max = 18446744073709551615;
			break;
			case 'real':
			case 'real unsigned':
			case 'float':
			case 'float unsigned':
			case 'float4':
			case 'double':
			case 'float8':
			case 'double precision':
			case 'double unsigned':
			case 'double precision unsigned':
				$type = 'float';
			break;
			case 'numeric':
			case 'numeric unsigned':
			case 'decimal':
			case 'decimal unsigned':
				$type = 'float';
				$fixed = TRUE;
			break;
			case 'bit':
			case 'char':
			case 'character':
			case 'nchar':
			case 'national char':
			case 'national character':
				$type = 'string';
				$fixed = TRUE;
			break;
			case 'varchar':
			case 'bit varying':
			case 'char varying':
			case 'nchar varying':
			case 'character varying':
			case 'national char varying':
			case 'national character varying':
				$type = 'string';
			break;
			case 'date':
			case 'time':
			case 'time with time zone':
			case 'time without time zone':
			case 'timestamp':
			case 'timestamp with time zone':
			case 'timestamp without time zone':
			case 'interval':
				$type = 'string';
			break;
			case 'blob':
			case 'varbinary':
			case 'binary large object':
			case 'binary varying':
				$type = 'string';
				$binary = TRUE;
			break;
			case 'clob':
			case 'char large object':
			case 'nclob':
			case 'nchar large object':
			case 'character large object':
			case 'national character large object':
				$type = 'string';
			break;
			case 'binary':
				$type = 'string';
				$binary = TRUE;
				$fixed = TRUE;
			break;
		}

		return array(
			'datatype' => $switch_type,
			'type' => $type,
			'fixed' => $fixed,
			'binary' => $binary,
			'unsigned' => $unsigned,
			'zerofill' => $zerofill,
			'min' => $min,
			'max' => $max,
			'max_length' => $max_length
		);
	}

	/**
	 * Get last query
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->_lastQuery;
	}
}