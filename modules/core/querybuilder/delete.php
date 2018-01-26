<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * DELETE Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/delete.html
 *
 * <code>
 * // DELETE FROM `tableName` WHERE `column1` = '17'
 * $delete = Core_QueryBuilder::delete('tableName')
 * 		->where('column1', '=', '17');
 * </code>
 *
 * <code>
 * // DELETE LOW_PRIORITY QUICK IGNORE FROM `tableName`
 * // WHERE `column1` = '17' AND `column2` != '19'
 * // ORDER BY `field1` DESC LIMIT 10
 * $delete = Core_QueryBuilder::delete('tableName')
 *		->lowPriority()
 *		->quick()
 *		->ignore()
 *		->where('column1', '=', '17')
 *		->where('column2', '!=', '19')
 *		->orderBy('field1', 'DESC')
 *		->limit(10)
 *		->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Delete extends Core_QueryBuilder_Selection
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = array();

	/**
	 * Columns
	 * @var array
	 */
	protected $_columns = array();

	/**
	 * Use LOW_PRIORITY
	 * @var mixed
	 */
	protected $_priority = NULL;

	/**
	 * Use QUICK
	 * @var mixed
	 */
	protected $_quick = NULL;

	/**
	 * Use IGNORE
	 * @var mixed
	 */
	protected $_ignore = NULL;

	/**
	 * DataBase Query Type
	 * 3 - DELETE
	 */
	protected $_queryType = 3;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete('tableName');
	 * </code>
	 *
	 * <code>
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete('tableName', array('tableName2', 'aliasTable2'));
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
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete('tableName')->lowPriority();
	 * </code>
	 * @return Core_QueryBuilder_Delete
	 */
	public function lowPriority()
	{
		$this->_priority = 'LOW_PRIORITY';
		return $this;
	}

	/**
	 * Set QUICK
	 *
	 * <code>
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete('tableName')->quick();
	 * </code>
	 * @return Core_QueryBuilder_Delete
	 */
	public function quick()
	{
		$this->_quick = TRUE;
		return $this;
	}

	/**
	 * Set IGNORE
	 *
	 * <code>
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete('tableName')->ignore();
	 * </code>
	 * @return Core_QueryBuilder_Delete
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
	 * // DELETE FROM `tableName`
	 * $oCore_QueryBuilder_Delete = Core_QueryBuilder::delete()->table('tableName');
	 * </code>
	 * @return Core_QueryBuilder_Delete
	 */
	public function table()
	{
		$args = func_get_args();
		$this->_tableName = array_merge($this->_tableName, $args);
		return $this;
	}

	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		$query = array('DELETE');

		if (!is_null($this->_priority))
		{
			$query[] = $this->_priority;
		}

		if (!is_null($this->_quick))
		{
			$query[] = 'QUICK';
		}

		if (!is_null($this->_ignore))
		{
			$query[] = 'IGNORE';
		}

		$aQuoteColumns = $this->quoteColumns($this->_tableName);

		// Delte from first table when using JOIN
		if (!empty($this->_join))
		{
			$query[] = $aQuoteColumns[0];
		}

		$query[] = 'FROM ' . implode(', ', $aQuoteColumns);

		if (!empty($this->_join))
		{
			$query[] = $this->_buildJoin($this->_join);
		}

		if (!empty($this->_where))
		{
			$query[] = 'WHERE ' . $this->_buildExpression($this->_where);
		}

		if (!empty($this->_orderBy))
		{
			$query[] = $this->_buildOrderBy($this->_orderBy);
		}

		if (!is_null($this->_limit))
		{
			$query[] = 'LIMIT ' . $this->_limit;
		}

		/*if (!is_null($this->_offset))
		{
			$query[] = 'OFFSET ' . $this->_offset;
		}*/

		$sql = implode(' ', $query);

		return $sql;
	}
}
