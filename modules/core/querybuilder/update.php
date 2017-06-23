<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * UPDATE Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/update.html
 *
 * <code>
 * // UPDATE `tableName` SET `column1` = 'value', `column2` = 'value2'
 * // WHERE `column` != '5' AND `a4` IN (17, 19, NULL) ORDER BY `column2` ASC LIMIT 10
 * $update = Core_QueryBuilder::update('tableName')
 * 	->columns(array('column1' => 'value', 'column2' => 'value2'))
 * 	->where('column', '!=', '5')
 * 	->where('a4', 'IN', array(17,19, NULL))
 * 	->orderBy('column2')
 * 	->limit(10)
 * 	->execute();
 * </code>
 *
 * <code>
 * // UPDATE `tableName` SET `column1` = 'value', `column2` = 'value2' WHERE `column` = '5'
 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName')
 * 	 ->set('column1', 'value')
 * 	 ->set('column2', 'value2')
 * 	->where('column', '=', '5')
 * 	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Update extends Core_QueryBuilder_Selection
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = array();

	/**
	 * List of columns
	 * @var array
	 */
	protected $_columns = array();

	/**
	 * LOW_PRIORITY
	 * @var mixed
	 */
	protected $_priority = NULL;

	/**
	 * IGNORE
	 * @var mixed
	 */
	protected $_ignore = NULL;

	/**
	 * DataBase Query Type
	 * 2 - UPDATE
	 */
	protected $_queryType = 2;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName');
	 * </code>
	 *
	 * <code>
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName', array('tableName2', 'aliasTable2'));
	 * </code>
	 *
	 * @see table()
	 */
	public function __construct(array $args = array())
	{
		// Set table name
		call_user_func_array(array($this, 'table'), $args);

		return parent::__construct($args);
	}

	/**
	 * Set LOW_PRIORITY
	 *
	 * <code>
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName')->lowPriority();
	 * </code>
	 * @return Core_QueryBuilder_Update
	 */
	public function lowPriority()
	{
		$this->_priority = 'LOW_PRIORITY';
		return $this;
	}

	/**
	 * Set IGNORE
	 *
	 * <code>
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName')->ignore();
	 * </code>
	 * @return Core_QueryBuilder_Update
	 */
	public function ignore()
	{
		$this->_ignore = TRUE;
		return $this;
	}

	/**
	 * Add table name
	 *
	 * <code>
	 * // UPDATE `tableName`
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update()->table('tableName');
	 * </code>
	 * @return Core_QueryBuilder_Update
	 */
	public function table()
	{
		$args = func_get_args();
		$this->_tableName = array_merge($this->_tableName, $args);
		return $this;
	}

	/**
	 * Add multiple columns for UPDATE
	 *
	 * <code>
	 * // UPDATE `tableName` SET `column` = 'value', `column2` = 'value3'
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName')
	 * 	->columns(array('column' => 'value', 'column2' => 'value3'));
	 * </code>
	 * @param array $args array of columns
	 * @return Core_QueryBuilder_Update
	 */
	public function columns(array $args)
	{
		$this->_columns = array_merge($this->_columns, $args);
		return $this;
	}

	/**
	 * Add column for UPDATE
	 *
	 * <code>
	 * // UPDATE `tableName` SET `column` = 'value'
	 * $oCore_QueryBuilder_Update = Core_QueryBuilder::update('tableName')
	 * 	->set('column', 'value');
	 * </code>
	 * @param string $column column name
	 * @param string $value column value
	 * @return Core_QueryBuilder_Update
	 */
	public function set($column, $value)
	{
		$this->_columns[$column] = $value;
		return $this;
	}

	/**
	 * Build SET expression
	 * @param array $columns columns list
	 * @return string The SQL query
	 */
	protected function _buildSet(array $columns)
	{
		$sql = array();

		foreach ($columns as $columnName => $value)
		{
			$value = is_object($value)
				? ($this->_isObjectSelect($value)
					? '(' . $value->build() . ')'
					: $value->build()
				)
				: $this->_dataBase->quote($value);

			$sql[] = $this->_dataBase->quoteColumnName($columnName) . ' = ' . $value;
		}

		return implode(', ', $sql);
	}

	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		$sql = 'UPDATE';

		if (!is_null($this->_priority))
		{
			$sql .= ' ' . $this->_priority;
		}

		if (!is_null($this->_ignore))
		{
			$sql .= ' IGNORE';
		}

		$sql .= ' ' . implode(', ', $this->quoteColumns($this->_tableName));
		
		if (!empty($this->_join))
		{
			$sql .= ' ' . $this->_buildJoin($this->_join);
		}
		
		$sql .= ' SET ' . $this->_buildSet($this->_columns);

		if (!empty($this->_where))
		{
			$sql .= ' WHERE ' . $this->_buildExpression($this->_where);
		}

		if (!empty($this->_orderBy))
		{
			$sql .= ' ' . $this->_buildOrderBy($this->_orderBy);
		}

		if (!is_null($this->_limit))
		{
			$sql .= ' LIMIT ' . $this->_limit;
		}

		/*if (!is_null($this->_offset))
		{
			$sql .= ' OFFSET ' . $this->_offset;
		}*/

		return $sql;
	}

	/**
	 * Clear
	 * @return Core_QueryBuilder_Update
	 */
	public function clear()
	{
		$this->_tableName = $this->_columns = array();

		$this->_priority = $this->_ignore = NULL;

		return parent::clear();
	}
}