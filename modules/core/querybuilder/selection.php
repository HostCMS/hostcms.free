<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Consist of WHERE, ORDER BY, and LIMIT
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
abstract class Core_QueryBuilder_Selection extends Core_QueryBuilder_Statement
{
	/**
	 * WITH
	 * @var array
	 */
	protected $_with = array();
	
	/**
	 * WITH RECURCIVE option
	 * @var array
	 */
	protected $_withRecursive = FALSE;

	/**
	 * WHERE
	 * @var array
	 */
	protected $_where = array();

	/**
	 * Current operator
	 * @var string
	 */
	protected $_operator = '';

	/**
	 * Default operator between conditions
	 * @var string
	 */
	protected $_defaultOperator = 'AND';

	/**
	 * PARTITION
	 * @var array
	 */
	protected $_partition = array();

	/**
	 * Index Hints
	 * @var array
	 */
	protected $_indexHints = array();

	/**
	 * JOIN
	 * @var array
	 */
	protected $_join = array();

	/**
	 * ORDER BY
	 * @var array
	 */
	protected $_orderBy = array();

	/**
	 * LIMIT
	 * @var mixed
	 */
	protected $_limit = NULL;

	/**
	 * OFFSET
	 * @var mixed
	 */
	protected $_offset = NULL;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 */
	public function __construct(array $args = array())
	{
		// Set operator as default
		$this->setDefaultOperator();

		return parent::__construct($args);
	}

	/**
	 * Set result type as an object with className
	 * @param mixed $className Object class name
	 * @return self
	 */
	public function asObject($className = NULL)
	{
		$this->_dataBase->asObject($className);
		return $this;
	}

	/**
	 * Set result type as an array
	 * @return self
	 */
	public function asAssoc()
	{
		$this->_dataBase->asAssoc();
		return $this;
	}

	/**
	 * Add WITH (Common Table Expressions) and WITH RECURSIVE (Recursive Common Table Expressions)
	 *
	 * <code>
	 * $cte = Core_QueryBuilder::select(array(1, 'col1'), array(2, 'col2'))
	 * 	->union(
	 * 		Core_QueryBuilder::select(3, 4)
	 * 	);
	 *
	 * Core_QueryBuilder::select('col1', 'col2')
	 * 	//->with('cte', $cte)
	 * 	//->with('RECURSIVE', 'cte', $cte)
	 * 	->with('cte', 'col1', 'col2', $cte)
	 * 	->from('cte')
	 * 	->execute();
	 * </code>
	 * @return self
	 */
	public function with()
	{
		$args = func_get_args();

		if (count($args) >= 2)
		{
			$oQB = array_pop($args);

			if (!is_object($oQB))
			{
				throw new Core_Exception("with(cte_name, [col_name [, col_name] ...], subquery) expected subquery as an object, %type given",
						array('%type' => gettype($oQB))
					);
			}

			if (is_string($args[0]) && strtoupper($args[0]) == 'RECURSIVE')
			{
				array_shift($args);
				$this->_withRecursive = TRUE;
			}

			$this->_with[] = array(
				'cte_name' => array_shift($args),
				'subquery' => $oQB,
				'cols' => $args
			);
		}
		else
		{
			throw new Core_Exception("with(cte_name, [col_name [, col_name] ...], subquery) must has an options with 2 or more args, %count given",
					array('%count' => count($args))
				);
		}

		return $this;
	}

	/**
	 * Build WITH expression
	 *
	 * @param array $aWiths
	 * @return string The SQL query
	 */
	protected function _buildWith(array $aWiths)
	{
		$aTmp = array();
		foreach ($aWiths as $aWith)
		{
			$sql = $this->quoteTable($aWith['cte_name']);

			if (count($aWith['cols']))
			{
				$sql .= ' (' . implode(', ', $this->_quoteColumns($aWith['cols'])) . ')';
			}

			$sql .= ' AS (' . $aWith['subquery']->build() . ')';
			
			$aTmp[] = $sql;
		}

		return "WITH" . ($this->_withRecursive ? ' RECURSIVE' : '') . "\n" . implode(",\n", $aTmp);
	}

	/**
	 * Set operator as a default operator
	 * @return self
	 */
	public function setDefaultOperator()
	{
		// Set operator as default
		$this->_operator = $this->_defaultOperator;
		return $this;
	}

	/**
	 * Set operator as a default operator
	 * @param string $operator
	 * @return self
	 */
	public function setOperator($operator)
	{
		$this->_operator = $operator;
		return $this;
	}

	/**
	 * Set operator as AND
	 * @return self
	 */
	public function setAnd()
	{
		$this->_operator = 'AND';
		return $this;
	}

	/**
	 * Set operator as OR
	 * @return self
	 */
	public function setOr()
	{
		$this->_operator = 'OR';
		return $this;
	}

	/**
	 * Open bracket in WHERE
	 * @return self
	 */
	public function open()
	{
		$this->_where[] = array(
			$this->_operator => array('(')
		);

		// Set operator as EMPTY string
		$this->_operator = '';

		return $this;
	}

	/**
	 * Close bracket in WHERE
	 * @return self
	 */
	public function close()
	{
		// Set operator as EMPTY string
		$this->_operator = '';

		$this->_where[] = array(
			$this->_operator => array(')')
		);

		// Set operator as default
		$this->setDefaultOperator();

		return $this;
	}

	/**
	 * Set PARTITION
	 *
	 * <code>
	 * // FROM|JOIN `tableName` PARTITION (`p0`, `p1`)
	 * $oCore_QueryBuilder_Select = $Core_QueryBuilder_Select->from('tableName')->partition('tableName', array('p0', 'p1'));
	 * </code>
	 * @return Core_QueryBuilder_Select
	*/
	public function partition($tableName, array $partitions)
	{
		$this->_partition[$tableName] = $partitions;
		return $this;
	}


	/**
	 * Add Index Hints
	 * https://dev.mysql.com/doc/refman/5.7/en/index-hints.html
	 *
	 * <code>
	 * // USE INDEX (`col1_index`)
	 * $Core_QueryBuilder_Select->indexHint('table1', array('USE INDEX', 'col1_index'));
	 *
	 * // USE INDEX FOR JOIN (`col1_index`, `col2_index`)
	 * $Core_QueryBuilder_Select->indexHint('table1', array('USE INDEX', 'FOR JOIN', array('col1_index', 'col2_index')));
	 * </code>
	 * @param string $tableName table name
	 * @param array $options [$action, $index_list] or [$action, $for, $index_list]
	 * @return self
	*/
	public function indexHint($tableName, $options)
	{
		$this->_indexHints[$tableName][] = $options;

		return $this;
	}

	/**
	 * Build INDEX expression
	 *
	 * @param array $aTableIndexHints
	 * @return string The SQL query
	 */
	protected function _buildIndexHint(array $aTableIndexHints)
	{
		/*
		index_hint:
			USE {INDEX|KEY}
				[FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
			| {IGNORE|FORCE} {INDEX|KEY}
				[FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
		*/
		$sql = array();

		foreach ($aTableIndexHints as $aTableIndexHint)
		{
			$iCount = count($aTableIndexHint);

			if ($iCount == 2)
			{
				$for = NULL;
				$index_list = $aTableIndexHint[1];
			}
			elseif ($iCount == 3)
			{
				$for = $aTableIndexHint[1];
				$index_list = $aTableIndexHint[2];
			}
			else
			{
				throw new Core_Exception("indexHint(tableName, options) must has an options with 2 or 3 values, %count given",
					array('%count' => $iCount)
				);
			}

			$action = strtoupper($aTableIndexHint[0]);
			switch ($action)
			{
				case 'USE INDEX':
				case 'USE KEY':
				case 'FORCE INDEX':
				case 'FORCE KEY':
				case 'IGNORE INDEX':
				case 'IGNORE KEY':
					$sql[] = $action;
				break;
				default:
					throw new Core_Exception("indexHint(tableName, options) wrong action '%action'",
						array('%action' => $action)
					);
			}

			if (!is_null($for))
			{
				$for = strtoupper($for);
				switch ($for)
				{
					case 'FOR JOIN':
					case 'FOR ORDER BY':
					case 'FOR GROUP BY':
						$sql[] = $for;
					break;
					default:
						throw new Core_Exception("indexHint(tableName, options) wrong FOR '%for'",
							array('%for' => $for)
						);
				}
			}

			!is_array($index_list) && $index_list = array($index_list);

			$index_list = array_map(array($this->_dataBase, 'quoteColumnName'), $index_list);

			$sql[] = '(' . implode(', ', $index_list) . ')';
		}

		return implode(' ', $sql);
	}

	/**
	 * Quote columns
	 * @param array $array
	 * @return array
	 */
	public function quoteTable($tableName)
	{
		if (is_array($tableName) && count($tableName) == 2 && $this->_isObjectSelect($tableName[0]))
		{
			$value = '(' . $tableName[0]->build() . ') AS ' . $this->_dataBase->quoteTableName($tableName[1]);
		}
		elseif ($this->_isObjectSelect($tableName))
		{
			$value = '(' . $tableName->build() . ')';
		}
		else
		{
			$columnNameAlias = NULL;

			if (is_array($tableName))
			{
				// array('columnName', 'columnNameAlias') => `columnName` AS `columnNameAlias`
				if (count($tableName) == 2)
				{
					list($tableName, $columnNameAlias) = $tableName;
				}
				else
				{
					list($tableName) = $tableName;
				}
			}

			$value = $this->_dataBase->quoteTableName($tableName);

			if (is_string($tableName) && isset($this->_partition[$tableName]))
			{
				$value .= ' PARTITION (' . implode(', ', $this->_quoteColumns($this->_partition[$tableName])) . ')';
			}

			if (!is_null($columnNameAlias))
			{
				$value .= ' AS ' . $this->_dataBase->quoteTableName($columnNameAlias);
			}

			if (is_string($tableName) && isset($this->_indexHints[$tableName]))
			{
				$value .= ' ' . $this->_buildIndexHint($this->_indexHints[$tableName]);
			}
		}

		return $value;
	}

	/**
	 * Quote columns
	 * @param array $array
	 * @return array
	 */
	protected function _quoteTables(array $array)
	{
		foreach ($array as $key => $tableName)
		{
			$array[$key] = $this->quoteTable($tableName);
		}

		return array_unique($array);
	}

	/**
	 * http://dev.mysql.com/doc/refman/5.7/en/join.html
	 * @param string $type join type
	 * @param string $table table name
	 * @param string|NULL $column column name
	 * @param string|NULL $expression expression
	 * @param string|NULL $value value
	 * @param string|NULL $additionalConditions additional conditions
	 * @return self
	 */
	protected function _join($type, $table, $column = NULL, $expression = NULL, $value = NULL, $additionalConditions = NULL, $mode = NULL)
	{
		$mode == 'first'
			? array_unshift($this->_join, array($type, $table, $column, $expression, $value, $additionalConditions))
			: $this->_join[] = array($type, $table, $column, $expression, $value, $additionalConditions);

		return $this;
	}

	/**
	 * INNER JOIN
	 *
	 * <code>
	 * // INNER JOIN `join1` USING (`join_field`)
	 * $Core_QueryBuilder_Select->join('join1', 'join_field');
	 *
	 * // INNER JOIN `jointable`.`join1` ON `join_field` = `join_field2`
	 * $Core_QueryBuilder_Select->join('jointable.join1', 'join_field1', '=', 'join_field2');
	 *
	 * // INNER JOIN `jointable`.`join1` ON `join_field` = `join_field2` AND `A` = '123' AND `B` = 'xyz'
	 * $Core_QueryBuilder_Select->join('jointable.join1', 'join_field1', '=', 'join_field2', array(
	 *		array('AND' => array('A', '=', '123')),
	 *		array('AND' => array('B', '=', 'xyz'))
	 *	));
	 * </code>
	 * @param string $table table name
	 * @param string $column column name
	 * @param string|NULL $expression expression
	 * @param string|NULL $value value
	 * @param string|NULL $additionalConditions additional conditions
	 * @return self
	 */
	public function join($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('INNER JOIN', $table, $column, $expression, $value, $additionalConditions);
	}

	/**
	 * Add INNER JOIN first
	 *
	 * @param string $table table name
	 * @param string $column column name
	 * @param string|NULL $expression expression
	 * @param string|NULL $value value
	 * @param string|NULL $additionalConditions additional conditions
	 * @return self
	 */
	public function firstJoin($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('INNER JOIN', $table, $column, $expression, $value, $additionalConditions, 'first');
	}

	/**
	 * LEFT OUTER JOIN
	 *
	 * <code>
	 * // LEFT OUTER JOIN `join1` USING (`join_field2`)
	 * $Core_QueryBuilder_Select->leftJoin('join1', 'join_field2');
	 *
	 * // LEFT OUTER JOIN `jointable`.`join1` ON `join_field` = `join_field2`
	 * $Core_QueryBuilder_Select->leftJoin('jointable.join1', 'join_field', '=', 'join_field2');
	 * </code>
	 * @param string $table table name
	 * @param string $column column name
	 * @param string|NULL $expression expression
	 * @param string|NULL $value value
	 * @param string|NULL $additionalConditions additional conditions
	 * @return self
	 */
	public function leftJoin($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('LEFT OUTER JOIN', $table, $column, $expression, $value, $additionalConditions);
	}

	/**
	 * Add LEFT OUTER JOIN first
	 *
	 * @param string $table table name
	 * @param string $column column name
	 * @param string|NULL $expression expression
	 * @param string|NULL $value value
	 * @param string|NULL $additionalConditions additional conditions
	 * @return self
	 */
	public function firstLeftJoin($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('LEFT OUTER JOIN', $table, $column, $expression, $value, $additionalConditions, 'first');
	}

	/**
	 * RIGHT OUTER JOIN
	 *
	 * <code>
	 * // RIGHT OUTER JOIN `join1` USING (`join_field2`)
	 * $Core_QueryBuilder_Select->rightJoin('join1', 'join_field2');
	 *
	 * // RIGHT OUTER JOIN `jointable`.`join1` ON `join_field` = `join_field2`
	 * $Core_QueryBuilder_Select->rightJoin('jointable.join1', 'join_field', '=', 'join_field2');
	 * </code>
	 * @param string $table table name
	 * @param string $column column name
	 * @param string $expression expression
	 * @param string $value value
	 * @param string $additionalConditions additional conditions
	 * @return self
	 */
	public function rightJoin($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('RIGHT OUTER JOIN', $table, $column, $expression, $value, $additionalConditions);
	}

	/**
	 * Add RIGHT OUTER JOIN first
	 *
	 * @param string $table table name
	 * @param string $column column name
	 * @param string $expression expression
	 * @param string $value value
	 * @param string $additionalConditions additional conditions
	 * @return self
	 */
	public function rightJoinFirst($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('RIGHT OUTER JOIN', $table, $column, $expression, $value, $additionalConditions, 'first');
	}

	/**
	 * In MySQL, CROSS JOIN is a syntactic equivalent to INNER JOIN (they can replace each other).
	 * In standard SQL, they are not equivalent. INNER JOIN is used with an ON clause, CROSS JOIN is used otherwise.
	 *
	 * <code>
	 * // CROSS JOIN `join1`
	 * $Core_QueryBuilder_Select->crossJoin('join1');
	 * </code>
	 * @param string $table table name
	 * @return self
	 */
	public function crossJoin($table)
	{
		return $this->_join('CROSS JOIN', $table);
	}

	/**
	 *
	 * <code>
	 * // NATURAL JOIN `join1`
	 * $Core_QueryBuilder_Select->naturalJoin('join1');
	 * </code>
	 * @param string $table table name
	 * @return self
	 */
	public function naturalJoin($table)
	{
		return $this->_join('NATURAL JOIN', $table);
	}

	/**
	 * Build JOIN expression
	 *
	 * @param array $aJoins
	 * @return string The SQL query
	 */
	protected function _buildJoin(array $aJoins)
	{
		$sql = array();

		foreach ($aJoins as $aJoin)
		{
			list($type, $table, $column, $expression, $value, $additionalConditions) = $aJoin;

			$table = !is_object($table)
				? $this->quoteTable($table)
				: $table->build();

			if (!is_null($column))
			{
				if (is_null($expression) && is_null($value))
				{
					$condition = ' USING (' . $this->_dataBase->quoteColumnName($column) . ')';
				}
				else
				{
					if (is_null($value))
					{
						$expression = $expression !== 'IS' && $expression !== '='
							? 'IS NOT'
							: 'IS';

						// Escape value
						$value = $this->_dataBase->quote($value);
					}
					else
					{
						$value = $this->_dataBase->quoteColumnName($value);
					}

					$condition = ' ON ' . $this->_dataBase->quoteColumnName($column) . ' ' . $expression . ' ' . $value;

					if (is_array($additionalConditions) && count($additionalConditions) > 0)
					{
						reset($additionalConditions[0]);
						$key = key($additionalConditions[0]);

						// Warning: Добавить проверку $key на перечень доступных операций (AND, OR и т.д.)
						$condition .= ' ' . $key . ' ' . $this->_buildExpression($additionalConditions);
					}
				}
			}
			else
			{
				$condition = '';
			}

			$sql[] = $type . ' ' . $table . $condition;
		}

		return implode(" \n", $sql);
	}

	/**
	 * Add WHERE
	 *
	 * <code>
	 * // WHERE `a1` > '2'
	 * $Core_QueryBuilder_Select->where('a1', '>', '2');
	 *
	 * // WHERE `a4` IN (17, 19, NULL, 'NULL')
	 * $Core_QueryBuilder_Select->where('a4', 'IN', array(17, 19, NULL, 'NULL'));
	 *
	 * // WHERE `a7` BETWEEN 1 AND 10
	 * $Core_QueryBuilder_Select->where('a7', 'BETWEEN', array(1, 10));
	 * </code>
	 * @param string $column column
	 * @param string $expression expression
	 * @param string $value value
	 * @return self
	 */
	public function where($column, $expression = NULL, $value = NULL)
	{
		$this->_where[] = array(
			$this->_operator => array($column, $expression, $value)
		);

		// Set operator as default
		$this->setDefaultOperator();

		return $this;
	}

	/**
	 * Delete last WHERE condition
	 * @return self
	 */
	public function deleteLastWhere()
	{
		array_pop($this->_where);
		return $this;
	}

	/**
	 * Delete first WHERE condition
	 * @return self
	 */
	public function deleteFirstWhere()
	{
		array_shift($this->_where);
		return $this;
	}

	/**
	 * Add OR and WHERE, e.g. WHERE ... OR $column $expression $value
	 *
	 * <code>
	 * // WHERE `a1` > 2 OR `a2` < 7
	 * $Core_QueryBuilder_Select->where('a1', '>', 2)->orWhere('a2', '<', 7);
	 * </code>
	 * @param string $column column
	 * @param string $expression expression
	 * @param string $value value
	 * @return self
	 */
	public function orWhere($column, $expression = NULL, $value = NULL)
	{
		return $this
			->setOr()
			->where($column, $expression, $value);
	}

	/**
	 * Add raw expression into WHERE.
	 * ATTENTION! Danger, you should escape the query yourself!
	 *
	 * <code>
	 * // WHERE `a1` > 2
	 * $Core_QueryBuilder_Select->whereRaw("`a1` > 2");
	 * </code>
	 * @param string $expr expression
	 * @return self
	 */
	public function whereRaw($expr)
	{
		return $this->where(Core_QueryBuilder::raw($expr));
	}

	/**
	 * Verify two columns
	 *
	 * <code>
	 * // WHERE `a1` = `a2`
	 * $Core_QueryBuilder_Select->whereColumn('a1', '=', 'a2');
	 * </code>
	 * @param string $column1 first column
	 * @param string $expression expression
	 * @param string $column2 second column
	 * @return self
	 */
	public function whereColumn($column1, $expression, $column2)
	{
		return $this->where($column1, $expression, Core_QueryBuilder::raw($this->_dataBase->quoteColumnName($column2)));
	}

	/**
	 * Set OR and verify two columns
	 *
	 * <code>
	 * // WHERE ... OR `a1` = `a2`
	 * $Core_QueryBuilder_Select->orWhereColumn('a1', '=', 'a2');
	 * </code>
	 * @param string $column1 first column
	 * @param string $expression expression
	 * @param string $column2 second column
	 * @return self
	 */
	public function orWhereColumn($column1, $expression, $column2)
	{
		return $this
			->setOr()
			->whereColumn($column1, $expression, $column2);
	}

	/**
	 * Add WHERE $column BETWEEN x AND y
	 *
	 * <code>
	 * // WHERE `a7` BETWEEN 1 AND 10
	 * $Core_QueryBuilder_Select->whereBetween('a7', 1, 10);
	 * </code>
	 * @param string $column column
	 * @param string $from
	 * @param string $to
	 * @return self
	 */
	public function whereBetween($column, $from, $to)
	{
		return $this->where($column, 'BETWEEN', array($from, $to));
	}

	/**
	 * Add OR and WHERE $column BETWEEN x AND y
	 *
	 * <code>
	 * // WHERE ... OR `a7` BETWEEN 1 AND 10
	 * $Core_QueryBuilder_Select->orWhereBetween('a7', 1, 10);
	 * </code>
	 * @param string $column column
	 * @param string $from
	 * @param string $to
	 * @return self
	 */
	public function orWhereBetween($column, $from, $to)
	{
		return $this
			->setOr()
			->whereBetween($column, $from, $to);
	}

	/**
	 * Add WHERE $column NOT BETWEEN x AND y
	 *
	 * <code>
	 * // WHERE `a7` NOT BETWEEN 1 AND 10
	 * $Core_QueryBuilder_Select->whereNotBetween('a7', 1, 10);
	 * </code>
	 * @param string $column column
	 * @param string $from
	 * @param string $to
	 * @return self
	 */
	public function whereNotBetween($column, $from, $to)
	{
		return $this->where($column, 'NOT BETWEEN', array($from, $to));
	}

	/**
	 * Add OR and WHERE $column NOT BETWEEN x AND y
	 *
	 * <code>
	 * // WHERE ... OR `a7` NOT BETWEEN 1 AND 10
	 * $Core_QueryBuilder_Select->orWhereNotBetween('a7', 1, 10);
	 * </code>
	 * @param string $column column
	 * @param string $from
	 * @param string $to
	 * @return self
	 */
	public function orWhereNotBetween($column, $from, $to)
	{
		return $this
			->setOr()
			->whereNotBetween($column, $from, $to);
	}

	/**
	 * Add WHERE $column IN (x, y)
	 *
	 * <code>
	 * // WHERE `a7` IN (1, 2, 'aaa')
	 * $Core_QueryBuilder_Select->whereIn('a7', array(1, 2, 'aaa'));
	 * </code>
	 * @param string $column column
	 * @param string $value value
	 * @return self
	 */
	public function whereIn($column, array $value)
	{
		return $this->where($column, 'IN', $value);
	}

	/**
	 * Add OR and WHERE $column IN (x, y)
	 *
	 * <code>
	 * // WHERE ... OR `a7` IN (1, 2, 'aaa')
	 * $Core_QueryBuilder_Select->orWhereIn('a7', array(1, 2, 'aaa'));
	 * </code>
	 * @param string $column column
	 * @param string $value value
	 * @return self
	 */
	public function orWhereIn($column, array $value)
	{
		return $this
			->setOr()
			->whereIn($column, $value);
	}

	/**
	 * Add WHERE $column NOT IN (x, y)
	 *
	 * <code>
	 * // WHERE `a7` NOT IN (1, 2, 'aaa')
	 * $Core_QueryBuilder_Select->whereNotIn('a7', array(1, 2, 'aaa'));
	 * </code>
	 * @param string $column column
	 * @param string $value value
	 * @return self
	 */
	public function whereNotIn($column, array $value)
	{
		return $this->where($column, 'NOT IN', $value);
	}

	/**
	 * Add OR and WHERE $column NOT IN (x, y)
	 *
	 * <code>
	 * // WHERE ... OR `a7` NOT IN (1, 2, 'aaa')
	 * $Core_QueryBuilder_Select->orWhereNotIn('a7', array(1, 2, 'aaa'));
	 * </code>
	 * @param string $column column
	 * @param string $value value
	 * @return self
	 */
	public function orWhereNotIn($column, array $value)
	{
		return $this
			->setOr()
			->whereNotIn($column, $value);
	}

	/**
	 * Add WHERE $column IS NULL
	 *
	 * <code>
	 * // WHERE `a1` IS NULL
	 * $Core_QueryBuilder_Select->whereIsNull('a1');
	 * </code>
	 * @param string $column column
	 * @return self
	 */
	public function whereIsNull($column)
	{
		return $this->where($column, 'IS', NULL);
	}

	/**
	 * Add OR and WHERE $column IS NULL
	 *
	 * <code>
	 * // WHERE `a1` IS NULL OR `a2` IS NULL
	 * $Core_QueryBuilder_Select->whereIsNull('a1')->orWhereIsNull('a2');
	 * </code>
	 * @param string $column column
	 * @return self
	 */
	public function orWhereIsNull($column)
	{
		return $this
			->setOr()
			->whereIsNull($column);
	}

	/**
	 * Add WHERE $column IS NOT NULL
	 *
	 * <code>
	 * // WHERE `a2` IS NOT NULL
	 * $Core_QueryBuilder_Select->whereIsNotNull('a2');
	 * </code>
	 * @param string $column column
	 * @return self
	 */
	public function whereIsNotNull($column)
	{
		return $this->where($column, 'IS NOT', NULL);
	}

	/**
	 * Add OR and WHERE $column IS NOT NULL
	 *
	 * <code>
	 * // WHERE `a1` IS NOT NULL
	 * $Core_QueryBuilder_Select->orWhereIsNotNull('a1');
	 * </code>
	 * @param string $column column
	 * @return self
	 */
	public function orWhereIsNotNull($column)
	{
		return $this
			->setOr()
			->whereIsNotNull($column);
	}

	/**
	 * Build WHERE or HAVING expression
	 * @param array $aConditions list of conditions
	 * @return string The SQL query
	 * @see _buildWhere()
	 */
	protected function _buildExpression(array $aConditions)
	{
		$sql = '';

		foreach ($aConditions as $key => $aCondition)
		{
			if (is_array($aCondition))
			{
				foreach ($aCondition as $operator => $aWhere)
				{
					// Skip first expression
					if ($key)
					{
						$sql .= ' ' . $operator;
					}

					if (!is_null($aWhere))
					{
						if (count($aWhere) == 3 && !is_null($aWhere[1]))
						{
							list($column, $expression, $value) = $aWhere;
							$sql .= ' ' . $this->_buildWhere($column, $expression, $value);
						}
						else
						{
							list($column) = $aWhere;
							$sql .= ' ' . (is_object($column) ? $column->build() : $column);
						}
					}
				}
			}
		}

		return $sql;
	}

	/**
	 * Build expression for one condition
	 * @param string $column column
	 * @param string $expression expression
	 * @param string $value value
	 * @return string The SQL query
	 * @see _buildExpression()
	 */
	protected function _buildWhere($column, $expression, $value)
	{
		// Quote column name
		$column = is_object($column)
			? ($this->_isObjectSelect($column)
				? '(' . $column->build() . ')'
				: $column->build()
			)
			: (is_null($column) // EXISTS, NOT EXISTS
				? ''
				: $this->_dataBase->quoteColumnName($column)
			);

		$expression = strtoupper($expression);
		if (is_null($value))
		{
			$expression = $expression !== 'IS' && $expression !== '='
				? 'IS NOT'
				: 'IS';
		}

		// http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html
		switch ($expression)
		{
			case '<=>': // NULL-safe equal to operator
			case '=': // Equal operator
			case '>=': // Greater than or equal operator
			case '>': // Greater than operator
			case '<=': // Less than or equal operator
			case '<': // Less than operator
			case 'LIKE': // Simple pattern matching
			case 'NOT LIKE': // Negation of simple pattern matching
			case '!=': // Not equal operator
			case '<>': // Not equal operator
			case 'EXISTS':
			case 'NOT EXISTS':
				// Subquery Syntax
				// http://dev.mysql.com/doc/refman/5.7/en/subqueries.html
				if ($this->_isObjectSelect($value))
				{
					$value = '(' . $value->build() . ')';
				}
				else
				{
					// Expression
					$value = is_object($value)
						? $value->build()
						: $this->_dataBase->quote($value);
				}
			break;
			case 'REGEXP': // Whether string matches regular expression
			case 'NOT REGEXP': // Negation of REGEXP
			case 'RLIKE': // Whether string matches regular expression
			case 'NOT RLIKE': // Negation of RLIKE
				$value = $this->_dataBase->quote($value);
			break;
			case 'IN': // Check whether a value is within a set of values
			case 'NOT IN': // Check whether a value is not within a set of values
			case 'COALESCE': // Return the first non-NULL argument
			case 'GREATEST': // Return the largest argument
			case 'INTERVAL': // Return the index of the argument that is less than the first argument
			case 'LEAST': // Return the smallest argument
			case 'STRCMP': // Compare two strings
				$value = $this->_isObjectSelect($value)
					? '(' . $value->build() . ')'
					: '(' . implode(', ', $this->_quoteValues($value)) . ')';
			break;
			case 'BETWEEN': // BETWEEN ... AND ... 	Check whether a value is within a range of values
			case 'NOT BETWEEN': // NOT BETWEEN ... AND ... 	Check whether a value is not within a range of values
				if (is_array($value) && count($value) == 2)
				{
					// Escape values
					$value = (is_object($value[0]) ? $value[0]->build() : $this->_dataBase->quote($value[0]))
						. ' AND '
						. (is_object($value[1]) ? $value[1]->build() : $this->_dataBase->quote($value[1]));
				}
				else
				{
					throw new Core_Exception("An expression '%expression' must has array with 2 values",
						array('%expression' => $expression)
					);
				}
				break;
			case 'IS': // Test a value against a boolean
			case 'IS NOT': // Test a value against a boolean
				// Skip if value is NULL
				if (is_null($value))
				{
					// Escape value
					$value = $this->_dataBase->quote($value);
				}
				else
				{
					$value = strtoupper($value);

					if (!in_array($value, array('TRUE', 'FALSE', 'UNKNOWN')))
					{
						throw new Core_Exception("Argument should be TRUE, FALSE, or UNKNOWN.");
					}
				}
				break;
			case 'ISNULL': // Test whether the argument is NULL
			default:
				throw new Core_Exception("The comparison operator '%expression' doesn't allow",
					array('%expression' => $expression)
				);
		}

		return $column . ' ' . $expression . ' ' . $value;
	}

	/**
	 * Add order column and direction
	 *
	 * http://dev.mysql.com/doc/refman/5.7/en/sorting-rows.html
	 *
	 * <code>
	 * // ORDER BY `field1` ASC
	 * $Core_QueryBuilder_Select->orderBy('field1');
	 *
	 * // ORDER BY `field1` DESC
	 * $Core_QueryBuilder_Select->orderBy('field1', 'DESC');
	 *
	 * // ORDER BY BINARY `field1` DESC
	 * $Core_QueryBuilder_Select->orderBy('field1', 'DESC', TRUE);
	 *
	 * // ORDER BY `field2` RAND()
	 * $Core_QueryBuilder_Select->orderBy('field1', 'RAND()');
	 * </code>
	 * @param string $column column
	 * @param string $direction sorting direction
	 * @param boolean $binary binary option
	 * @return self
	 */
	public function orderBy($column, $direction = 'ASC', $binary = FALSE)
	{
		$direction = strtoupper($direction);
		if (in_array($direction, array('ASC', 'DESC', 'RAND()')))
		{
			$this->_orderBy[] = array($column, $direction, $binary);
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
	 * Order options
	 * @param string $column column
	 * @param string $direction sorting direction
	 * @param boolean $binary binary option
	 * @see orderBy()
	 */
	public function order($column, $direction = 'ASC', $binary = FALSE)
	{
		return $this->orderBy($column, $direction, $binary);
	}

	/**
	 * Add raw expression into ORDER BY.
	 * ATTENTION! Danger, you should escape the query yourself!
	 *
	 * <code>
	 * // ORDER BY `field1` + `field2` ASC
	 * $Core_QueryBuilder_Select->orderByRaw('`field1` + `field2` ASC');
	 * </code>
	 * @param string $expr expression
	 * @return self
	 */
	public function orderByRaw($expr)
	{
		$this->_orderBy[] = array($expr);

		return $this;
	}

	/**
	 * Build ORDER BY expression
	 *
	 * @param array $aOrderBy
	 * @return string|NULL The SQL query
	 */
	protected function _buildOrderBy(array $aOrderBy)
	{
		$orderSql = array();

		if (!empty($aOrderBy))
		{
			foreach ($aOrderBy as $aOrder)
			{
				if (count($aOrder) == 3)
				{
					list($column, $direction, $binary) = $aOrder;

					$column = $direction == 'RAND()'
						? ''
						// Quote column name
						: $this->_dataBase->quoteColumnName($column);

					$orderSql[] = ($binary ? ' BINARY ' : '') . $column . ' ' . $direction;
				}
				else
				{
					list($column) = $aOrder;
					$orderSql[] = (is_object($column) ? $column->build() : $column);
				}
			}

			return 'ORDER BY ' . implode(', ', $orderSql);
		}
	}

	/**
	 * Set limit and offset
	 *
	 * <code>
	 * // LIMIT 10
	 * $Core_QueryBuilder_Select->limit(10);
	 *
	 * // LIMIT 10 OFFSET 0
	 * $Core_QueryBuilder_Select->limit(0, 10);
	 * </code>
	 * @param int $arg1
	 * @param int $arg2
	 * @return self
	 */
	public function limit($arg1, $arg2 = NULL)
	{
		if (!is_null($arg2))
		{
			$this->_limit = intval($arg2);
			return $this->offset($arg1);
		}

		$this->_limit = is_null($arg1)
			? NULL
			: intval($arg1);

		return $this;
	}

	/**
	 * Get limit
	 *
	 * <code>
	 * $limit = $Core_QueryBuilder_Select->getLimit();
	 * </code>
	 * @return int|NULL
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	/**
	 * Clear LIMIT
	 * @return self
	 */
	public function clearLimit()
	{
		$this->_limit = NULL;
		return $this;
	}

	/**
	 * Set offset
	 *
	 * <code>
	 * // OFFSET 10
	 * $Core_QueryBuilder_Select->offset(10);
	 * </code>
	 * @param int|NULL $offset offset
	 * @return self
	 */
	public function offset($offset)
	{
		$this->_offset = is_null($offset)
			? NULL
			: intval($offset);

		return $this;
	}

	/**
	 * Get offset
	 *
	 * <code>
	 * $offset = $Core_QueryBuilder_Select->getOffset();
	 * </code>
	 * @return int|NULL
	 */
	public function getOffset()
	{
		return $this->_offset;
	}

	/**
	 * Clear OFFSET
	 * @return self
	 */
	public function clearOffset()
	{
		$this->_offset = NULL;
		return $this;
	}

	/**
	 * Get ORDER BY
	 * @return array
	 */
	public function getOrderBy()
	{
		return $this->_orderBy;
	}

	/**
	 * Clear ORDER BY
	 * @return self
	 */
	public function clearOrderBy()
	{
		$this->_orderBy = NULL;
		return $this;
	}

	/**
	 * Get WHERE
	 * @return array
	 */
	public function getWhere()
	{
		return $this->_where;
	}

	/**
	 * Clear WHERE list
	 * @return self
	 */
	public function clearWhere()
	{
		$this->_where = array();
		return $this;
	}

	/**
	 * Clear Index Hints
	 * @return self
	 */
	public function clearIndexHints()
	{
		$this->_indexHints = array();
		return $this;
	}

	/**
	 * Get JOIN
	 * @return array
	 */
	public function getJoin()
	{
		return $this->_join;
	}

	/**
	 * Clear JOIN
	 * @return self
	 */
	public function clearJoin()
	{
		$this->_join = array();
		return $this;
	}

	/**
	 * Clear
	 *
	 * @return self
	 */
	public function clear()
	{
		$this->_where = $this->_orderBy = $this->_join
			 = $this->_partition = $this->_indexHints = array();

		$this->_operator = '';

		$this->_defaultOperator = 'AND';

		$this->_limit = $this->_offset = NULL;
		return $this;
	}
}