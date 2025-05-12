<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Query builder. SELECT Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.7/en/select.html
 *
 * Subqueries
 * http://dev.mysql.com/doc/refman/5.7/en/subqueries.html
 *
 * The Subquery as Scalar Operand
 * http://dev.mysql.com/doc/refman/5.7/en/scalar-subqueries.html
 * <code>
 * // SELECT (SELECT MAX(`column2`) FROM `t2`) FROM `t1`
 * $oCore_QueryBuilder_Select2 = Core_QueryBuilder::select('MAX(column2)')->from('t2');
 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select($oCore_QueryBuilder_Select2)->from('t1');
 * </code>
 *
 * Comparisons Using Subqueries
 * http://dev.mysql.com/doc/refman/5.7/en/comparisons-using-subqueries.html
 *
 * <code>
 * // SELECT * FROM `t1` WHERE `column1` = (SELECT MAX(`column2`) FROM `t2`)
 * $oCore_QueryBuilder_Select2 = Core_QueryBuilder::select('MAX(column2)')->from('t2');
 *
 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select()->from('t1')
 * 	->where('column1', '=', $oCore_QueryBuilder_Select2);
 * </code>
 * @see Core_QueryBuilder_Selection
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_QueryBuilder_Select extends Core_QueryBuilder_Selection
{
	/**
	 * DISTINCT
	 * @var bool
	 */
	protected $_distinct = FALSE;

	/**
	 * HIGH_PRIORITY
	 * @var bool
	 */
	protected $_highPriority = FALSE;

	/**
	 * STRAIGHT_JOIN
	 * @var bool
	 */
	protected $_straightJoin = FALSE;

	/**
	 * SQL_CALC_FOUND_ROWS
	 * @var bool
	 */
	protected $_sqlCalcFoundRows = FALSE;

	/**
	 * SELECT
	 * @var array
	 */
	protected $_select = array();

	/**
	 * FROM
	 * @var array
	 */
	protected $_from = array();

	/**
	 * GROUP BY
	 * @var array
	 */
	protected $_groupBy = array();

	/**
	 * HAVING
	 * @var array
	 */
	protected $_having = array();

	/**
	 * Array of Core_QueryBuilder_Select's
	 * @var array
	 */
	protected $_union = array();

	/**
	 * UNION ORDER BY
	 * @var array
	 */
	protected $_unionOrderBy = array();

	/**
	 * UNION LIMIT
	 * @var mixed
	 */
	protected $_unionLimit = NULL;

	/**
	 * UNION OFFSET
	 * @var mixed
	 */
	protected $_unionOffset = NULL;

	/**
	 * DataBase Query Type
	 * 0 - SELECT
	 */
	protected $_queryType = 0;

	/**
	 * Constructor.
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select('id', array('tableName.name', 'aliasName'));
	 * </code>
	 *
	 * @param array $args list of arguments
	 * @see columns()
	 */
	public function __construct(array $args = array())
	{
		// Add columns for select
		call_user_func_array(array($this, 'columns'), $args);

		return parent::__construct($args);
	}

	/**
	 * Add columns for select
	 *
	 * <code>
	 * // SELECT `id`, `name`
	 * $Core_QueryBuilder_Select->columns('id', 'name');
	 *
	 * // SELECT `id`, `name` AS `aliasName`
	 * $Core_QueryBuilder_Select->columns('id', array('name', 'aliasName'));
	 *
	 * // SELECT `id`, `tablename`.`name` AS `aliasName`
	 * $Core_QueryBuilder_Select->columns('id', array('tablename.name', 'aliasName'));
	 * </code>
	 * @return self
	 */
	public function columns()
	{
		$args = func_get_args();
		$this->_select = array_merge($this->_select, $args);

		return $this;
	}

	/**
	 * Alias for columns()
	 *
	 * @see columns()
	 */
	public function select()
	{
		$args = func_get_args();
		return call_user_func_array(array($this, 'columns'), $args);
	}

	/**
	 * Get SELECT
	 * @return array
	 */
	public function getSelect()
	{
		return $this->_select;
	}

	/**
	 * Clear SELECT list
	 * @return self
	 */
	public function clearSelect()
	{
		$this->_select = array();
		return $this;
	}

	/**
	 * Set HIGH_PRIORITY
	 *
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select()->highPriority();
	 * </code>
	 * @param boolean $value
	 * @return self
	 */
	public function highPriority($value = TRUE)
	{
		$this->_highPriority = $value;
		return $this;
	}

	/**
	 * Check if HIGH_PRIORITY was set
	 *
	 * @return boolean
	 */
	public function isHighPriority()
	{
		return $this->_highPriority == TRUE;
	}

	/**
	 * Set STRAIGHT_JOIN
	 *
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select()->straightJoin();
	 * </code>
	 * @param boolean $value
	 * @return self
	 */
	public function straightJoin($value = TRUE)
	{
		$this->_straightJoin = $value;
		return $this;
	}

	/**
	 * Check if STRAIGHT_JOIN was set
	 *
	 * @return boolean
	 */
	public function isStraightJoin()
	{
		return $this->_straightJoin == TRUE;
	}

	/**
	 * Set SQL_CALC_FOUND_ROWS
	 *
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select()->sqlCalcFoundRows();
	 * </code>
	 * @param boolean $value
	 * @return self
	 */
	public function sqlCalcFoundRows($value = TRUE)
	{
		$this->_sqlCalcFoundRows = $value;
		return $this;
	}

	/**
	 * Set DISTINCT and add columns for select
	 *
	 * @see columns()
	 * @return self
	 */
	public function selectDistinct()
	{
		$args = func_get_args();
		$this->distinct()->columns($args);

		return $this;
	}

	/**
	 * Set DISTINCT
	 *
	 * <code>
	 * $oCore_QueryBuilder_Select = Core_QueryBuilder::select()->distinct();
	 * </code>
	 * @param boolean $distinct DISTINCT option
	 * @return self
	 */
	public function distinct($distinct = TRUE)
	{
		$this->_distinct = $distinct;
		return $this;
	}

	/**
	 * Set FROM
	 *
	 * <code>
	 * // FROM `tableName`
	 * $oCore_QueryBuilder_Select = $Core_QueryBuilder_Select->from('tableName');
	 *
	 * // FROM `tableName` AS `tableNameAlias`
	 * $oCore_QueryBuilder_Select = $Core_QueryBuilder_Select->from(array('tableName', 'tableNameAlias'));
	 * </code>
	 * @return self
	*/
	public function from()
	{
		$args = func_get_args();
		$this->_from = array_merge($this->_from, $args);
		return $this;
	}

	/**
	 * Get FROM
	 * @return array
	 */
	public function getFrom()
	{
		return $this->_from;
	}

	/**
	 * Clear FROM
	 * @return self
	 */
	public function clearFrom()
	{
		$this->_from = array();
		return $this;
	}

	/**
	 * Open bracket in HAVING
	 * @return self
	 */
	public function havingOpen()
	{
		$this->_having[] = array(
			$this->_operator => array('(')
		);

		// Set operator as EMPTY string
		$this->_operator = '';
		return $this;
	}

	/**
	 * Close bracket in HAVING
	 * @return self
	 */
	public function havingClose()
	{
		// Set operator as EMPTY string
		$this->_operator = '';

		$this->_having[] = array(
			$this->_operator => array(')')
		);

		// Set operator as default
		$this->setDefaultOperator();

		return $this;
	}

	/**
	 * Add HAVING
	 *
	 * <code>
	 * // HAVING `a1` > '2'
	 * $Core_QueryBuilder_Select->having('a1', '>', '2');
	 *
	 * // HAVING `a4` IN (17, 19, NULL, 'NULL')
	 * $Core_QueryBuilder_Select->having('a4', 'IN', array(17,19, NULL, 'NULL'));
	 *
	 * // HAVING `a7` BETWEEN 1 AND 10
	 * $Core_QueryBuilder_Select->having('a7', 'BETWEEN', array(1, 10));
	 * </code>
	 * @param string $column column
	 * @param string $expression expression
	 * @param string $value value
	 * @return self
	 */
	public function having($column, $expression, $value)
	{
		$this->_having[] = array(
			$this->_operator => array($column, $expression, $value)
		);

		// Set operator as default
		$this->setDefaultOperator();

		return $this;
	}

	/**
	 * Delete last HAVING condition
	 * @return self
	 */
	public function deleteLastHaving()
	{
		array_pop($this->_having);
		return $this;
	}

	/**
	 * Delete first HAVING condition
	 * @return self
	 */
	public function deleteFirstHaving()
	{
		array_shift($this->_having);
		return $this;
	}

	/**
	 * Add OR and HAVING, e.g. HAVING ... OR $column $expression $value
	 *
	 * <code>
	 * // HAVING `a1` > 2 OR `a2` < 7
	 * $Core_QueryBuilder_Select->having('a1', '>', 2)->orHaving('a2', '<', 7);
	 * </code>
	 * @param string $column column
	 * @param string $expression expression
	 * @param string $value value
	 * @return self
	 */
	public function orHaving($column, $expression, $value)
	{
		return $this
			->setOr()
			->having($column, $expression, $value);
	}

	/**
	 * Add raw expression into HAVING.
	 * ATTENTION! Danger, you should escape the query yourself!
	 *
	 * <code>
	 * // HAVING `a1` > 2
	 * $Core_QueryBuilder_Select->havingRaw("`a1` > 2");
	 * </code>
	 * @param string $expr expression
	 * @return self
	 */
	public function havingRaw($expr)
	{
		return $this->having(Core_QueryBuilder::raw($expr));
	}

	/**
	 * Clear HAVING list
	 * @return self
	 */
	public function clearHaving()
	{
		$this->_having = array();
		return $this;
	}

	/**
	 * Set GROUP BY
	 *
	 * <code>
	 * // GROUP BY `field`, COUNT(`id`)
	 * $Core_QueryBuilder_Select->groupBy('field1')->groupBy('COUNT(id)');
	 * </code>
	 * @param string $column column
	 * @return self
	 */
	public function groupBy()
	{
		$args = func_get_args();
		$this->_groupBy = array_merge($this->_groupBy, $args);

		return $this;
	}

	/**
	 * GROUP BY
	 * @param string $column column
	 * @return self
	 * @see groupBy()
	 */
	public function group($column)
	{
		return $this->groupBy($column);
	}

	/**
	 * Get GROUP BY
	 * @return array
	 */
	public function getGroupBy()
	{
		return $this->_groupBy;
	}

	/**
	 * Clear GROUP BY list
	 * @return self
	 */
	public function clearGroupBy()
	{
		$this->_groupBy = array();
		return $this;
	}

	/**
	 * UNION is used to combine the result from multiple SELECT statements into a single result set.
	 *
	 * <code>
	 * // (SELECT `id2`, `name2` FROM `tablename2`)
	 * // UNION
	 * // (SELECT `id`, `name` FROM `tablename` LIMIT 10 OFFSET 0)
	 * $select1 = Core_QueryBuilder::select('id', 'name')->from('tablename')
	 * 	->limit(0, 10);
	 *
	 * $select2 = Core_QueryBuilder::select('id2', 'name2')->from('tablename2')
	 * 	->union($select1);
	 * </code>
	 * @param Core_QueryBuilder_Select $object
	 * @return self
	 */
	public function union(Core_QueryBuilder_Select $object)
	{
		$this->_union[] = array('', $object);
		return $this;
	}

	/**
	 * UNION ALL is used to combine the result from multiple SELECT statements into a single result set.
	 *
	 * <code>
	 * // (SELECT `id2`, `name2` FROM `tablename2`)
	 * // UNION ALL
	 * // (SELECT `id`, `name` FROM `tablename` LIMIT 10 OFFSET 0)
	 * $select1 = Core_QueryBuilder::select('id', 'name')->from('tablename')
	 * 	->limit(0, 10);
	 *
	 * $select2 = Core_QueryBuilder::select('id2', 'name2')->from('tablename2')
	 * 	->unionAll($select1);
	 * </code>
	 * @param Core_QueryBuilder_Select $object
	 * @return self
	 */
	public function unionAll(Core_QueryBuilder_Select $object)
	{
		$this->_union[] = array('ALL', $object);
		return $this;
	}

	/**
	 * ORDER BY for UNION
	 *
	 * http://dev.mysql.com/doc/refman/5.7/en/union.html
	 * @param string $column column
	 * @param string $direction sorting direction
	 * @param boolean $binary binary option
	 */
	public function unionOrderBy($column, $direction = 'ASC', $binary = FALSE)
	{
		$direction = strtoupper($direction);
		if (in_array($direction, array('ASC', 'DESC', 'RAND()')))
		{
			$this->_unionOrderBy[] = array($column, $direction, $binary);
		}
		else
		{
			throw new Core_Exception("The direction '%direction' doesn't allow",
				array('%direction' => $direction)
			);
		}

		return $this;
	}

	/**
	 * LIMIT for UNION
	 * @param string $arg1 offset
	 * @param string $arg2 count
	 */
	public function unionLimit($arg1, $arg2 = NULL)
	{
		if (!is_null($arg2))
		{
			$this->_unionLimit = intval($arg2);
			return $this->unionOffset($arg1);
		}

		$this->_unionLimit = intval($arg1);

		return $this;
	}

	/**
	 * Set UNION offset
	 *
	 * @param int $offset offset
	 * @return selfion
	 */
	public function unionOffset($offset)
	{
		$this->_unionOffset = intval($offset);
		return $this;
	}

	/**
	 * Offset compatibility with PostgreSQL, default TRUE
	 * @var boolean
	 */
	protected $_offsetPostgreSQLSyntax = TRUE;

	/**
	 * Offset compatibility with PostgreSQL, default TRUE
	 * $param boolean $compatible
	 */
	public function offsetPostgreSQLSyntax($compatible)
	{
		$this->_offsetPostgreSQLSyntax = $compatible;
		return $this;
	}

	/**
	 * Get FOUND_ROWS()
	 *
	 * <code>
	 * $iCount = Core_QueryBuilder::select()->getFoundRows();
	 * </code>
	 * @return int
	 */
	public function getFoundRows()
	{
		$oDataBase = $this->clear()
			->columns(array('FOUND_ROWS()', 'count'))
			->execute();

		$row = $oDataBase->asAssoc()->current(FALSE);

		$oDataBase->free();

		return $row['count'];
	}

	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		$query = array();
		
		if (!empty($this->_comment))
		{
			$query[] = $this->_buildComment($this->_comment);
		}
		
		if (!empty($this->_with))
		{
			$query[] = $this->_buildWith($this->_with);
		}
		
		$sql = 'SELECT ';

		if ($this->_distinct)
		{
			$sql .= 'DISTINCT ';
		}

		if ($this->_highPriority)
		{
			$sql .= 'HIGH_PRIORITY ';
		}

		if ($this->_straightJoin)
		{
			$sql .= 'STRAIGHT_JOIN ';
		}

		if ($this->_sqlCalcFoundRows)
		{
			$sql .= 'SQL_CALC_FOUND_ROWS ';
		}

		$sql .= !empty($this->_select)
			? implode(', ', $this->_quoteColumns($this->_select))
			: '*';
		
		$query[] = $sql;

		if (!empty($this->_from))
		{
			$query[] = 'FROM ' . implode(', ', $this->_quoteTables($this->_from));
		}

		if (!empty($this->_join))
		{
			$query[] = $this->_buildJoin($this->_join);
		}

		if (!empty($this->_where))
		{
			$query[] = 'WHERE ' . $this->_buildExpression($this->_where);
		}

		if (!empty($this->_groupBy))
		{
			$query[] = 'GROUP BY ' . implode(', ', $this->_quoteColumns($this->_groupBy));
		}

		if (!empty($this->_having))
		{
			$query[] = 'HAVING ' . $this->_buildExpression($this->_having);
		}

		if (!empty($this->_orderBy))
		{
			$query[] = $this->_buildOrderBy($this->_orderBy);
		}

		if (!is_null($this->_limit))
		{
			$sLImit = 'LIMIT ';
			!$this->_offsetPostgreSQLSyntax && !is_null($this->_offset) && $sLImit .= $this->_offset . ', ';
			$query[] = $sLImit . $this->_limit;
		}

		if ($this->_offsetPostgreSQLSyntax && !is_null($this->_offset))
		{
			$query[] = 'OFFSET ' . $this->_offset;
		}

		$sql = implode(" \n", $query);

		// Unions
		if (!empty($this->_union))
		{
			$aUnion = array('(', $sql);

			foreach ($this->_union as $aTmpUnion)
			{
				list($unionType, $oUnion) = $aTmpUnion;

				$aUnion[] = ") \nUNION {$unionType}\n(";
				$aUnion[] = $oUnion->build();
			}

			$aUnion[] = ')';

			if (!empty($this->_unionOrderBy))
			{
				$aUnion[] = ' ' . $this->_buildOrderBy($this->_unionOrderBy);
			}

			if (!is_null($this->_unionLimit))
			{
				$aUnion[] = ' LIMIT ' . $this->_unionLimit;
			}

			if (!is_null($this->_unionOffset))
			{
				$aUnion[] = ' OFFSET ' . $this->_unionOffset;
			}

			$sql = implode('', $aUnion);
		}

		return $sql;
	}

	/**
	 * Retrieve a small chunk and feeds each one into $callback for processing. It stops looping when $callback returns FALSE
	 * @param int $count chunk size
	 * @param callable $callback
	 * @param boolean $bCache use cache
	 * @return boolean
	 */
	public function chunk($count, $callback, $bCache = TRUE)
	{
		$offset = $step = 0;

		do {
			$sql = $this
				->limit($count)
				->offset($offset)
				->build();

			// Set type of query
			$oCore_DataBase = $this->_dataBase
				->setQueryType($this->_queryType)
				->query($sql);

			$aRows = $oCore_DataBase->result($bCache);

			if ($callback($aRows, $step++) === FALSE)
			{
				return FALSE;
			}

			$offset += $count;
		} while (count($aRows) == $count);

		return TRUE;
	}

	/**
	 * Clear
	 * @return self
	 */
	public function clear()
	{
		$this->_distinct = $this->_unbuffered = $this->_highPriority = $this->_straightJoin = $this->_sqlCalcFoundRows = FALSE;

		$this->_unionLimit = $this->_unionOffset = NULL;

		$this->_select = $this->_from
			= $this->_join = $this->_groupBy
			= $this->_having = $this->_union = array();

		return parent::clear();
	}
}