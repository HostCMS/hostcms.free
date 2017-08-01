<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Consist of WHERE, ORDER BY, and LIMIT
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_QueryBuilder_Selection extends Core_QueryBuilder_Statement
{
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
	 * @return Core_QueryBuilder_Selection
	 */
	public function asObject($className = NULL)
	{
		$this->_dataBase->asObject($className);
		return $this;
	}

	/**
	 * Set result type as an array
	 * @return Core_QueryBuilder_Selection
	 */
	public function asAssoc()
	{
		$this->_dataBase->asAssoc();
		return $this;
	}

	/**
	 * Set operator as a default operator
	 * @return Core_QueryBuilder_Selection
	 */
	public function setDefaultOperator()
	{
		// Set operator as default
		$this->_operator = $this->_defaultOperator;
		return $this;
	}

	/**
	 * Set operator as AND
	 * @return Core_QueryBuilder_Selection
	 */
	public function setAnd()
	{
		$this->_operator = 'AND';
		return $this;
	}

	/**
	 * Set operator as OR
	 * @return Core_QueryBuilder_Selection
	 */
	public function setOr()
	{
		$this->_operator = 'OR';
		return $this;
	}

	/**
	 * Open bracket in WHERE
	 * @return Core_QueryBuilder_Selection
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
	 * @return Core_QueryBuilder_Selection
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
	 * http://dev.mysql.com/doc/refman/5.5/en/join.html
	 * @param string $type join type
	 * @param string $table table name
	 * @param string $column column name
	 * @param string $expression expression
	 * @param string $value value
	 * @param string $additionalConditions additional conditions
	 * @return Core_QueryBuilder_Select
	 */
	protected function _join($type, $table, $column = NULL, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		$this->_join[] = array($type, $table, $column, $expression, $value, $additionalConditions);

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
	 * @param string $expression expression
	 * @param string $value value
	 * @param string $additionalConditions additional conditions
	 * @return Core_QueryBuilder_Select
	 */
	public function join($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('INNER JOIN', $table, $column, $expression, $value, $additionalConditions);
	}

	/**
	 * <code>
	 * // LEFT OUTER JOIN `join1` USING (`join_field2`)
	 * $Core_QueryBuilder_Select->leftJoin('join1', 'join_field2');
	 *
	 * // LEFT OUTER JOIN `jointable`.`join1` ON `join_field` = `join_field2`
	 * $Core_QueryBuilder_Select->leftJoin('jointable.join1', 'join_field', '=', 'join_field2');
	 * </code>
	 * @param string $table table name
	 * @param string $column column name
	 * @param string $expression expression
	 * @param string $value value
	 * @param string $additionalConditions additional conditions
	 * @return Core_QueryBuilder_Select
	 */
	public function leftJoin($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('LEFT OUTER JOIN', $table, $column, $expression, $value, $additionalConditions);
	}

	/**
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
	 * @return Core_QueryBuilder_Select
	 */
	public function rightJoin($table, $column, $expression = NULL, $value = NULL, $additionalConditions = NULL)
	{
		return $this->_join('RIGHT OUTER JOIN', $table, $column, $expression, $value, $additionalConditions);
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
	 * @return Core_QueryBuilder_Select
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
	 * @return Core_QueryBuilder_Select
	 */
	public function naturalJoin($table)
	{
		return $this->_join('NATURAL JOIN', $table);
	}

	/**
	 * Add WHERE
	 *
	 * <code>
	 * // WHERE `a1` > '2'
	 * $Core_QueryBuilder_Select->where('a1', '>', '2');
	 *
	 * // WHERE `f5` IS TRUE
	 * $Core_QueryBuilder_Select->where('f5', 'IS', 'TRUE');
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
	 * @return Core_QueryBuilder_Selection
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
				list($operator, $aWhere) = each($aCondition);

				// Skip first expression
				if ($key)
				{
					$sql .= ' ' . $operator;
				}

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
			: $this->_dataBase->quoteColumnName($column);

		$expression = strtoupper($expression);
		if (is_null($value))
		{
			$expression = $expression !== 'IS' && $expression !== '='
				? 'IS NOT'
				: 'IS';
		}

		// http://dev.mysql.com/doc/refman/5.5/en/comparison-operators.html
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

				// Subquery Syntax
				// http://dev.mysql.com/doc/refman/5.5/en/subqueries.html
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
			case 'REGEXP':
			case 'NOT REGEXP':
			case 'RLIKE':
			case 'NOT RLIKE':
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
					$aValue = $this->_quoteValues($value);
					$value = $aValue[0] . ' AND ' . $aValue[1];
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
	 * Set order column and direction
	 *
	 * http://dev.mysql.com/doc/refman/5.5/en/sorting-rows.html
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
	 * @return Core_QueryBuilder_Selection
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
	 * Build ORDER BY expression
	 *
	 * @param array $aOrderBy
	 * @return string The SQL query
	 */
	protected function _buildOrderBy(array $aOrderBy)
	{
		$orderSql = array();

		if (!empty($aOrderBy))
		{
			foreach ($aOrderBy as $aOrder)
			{
				list($column, $direction, $binary) = $aOrder;

				$column = $direction == 'RAND()'
					? ''
					// Quote column name
					: $this->_dataBase->quoteColumnName($column);

				$orderSql[] = ($binary ? ' BINARY ' : '') . $column . ' ' . $direction;
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
	 * @param int $arg1 offset
	 * @param int $arg2 limit
	 * @return Core_QueryBuilder_Selection
	 */
	public function limit($arg1, $arg2 = NULL)
	{
		if (!is_null($arg2))
		{
			$this->_limit = intval($arg2);
			return $this->offset($arg1);
		}

		$this->_limit = intval($arg1);

		return $this;
	}

	/**
	 * Set offset
	 *
	 * <code>
	 * // OFFSET 10
	 * $Core_QueryBuilder_Select->offset(10);
	 * </code>
	 * @param int $offset offset
	 * @return Core_QueryBuilder_Selection
	 */
	public function offset($offset)
	{
		$this->_offset = intval($offset);
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
	 * @return Core_QueryBuilder_Select
	 */
	public function clearOrderBy()
	{
		$this->_orderBy = NULL;
		return $this;
	}

	/**
	 * Clear
	 *
	 * @return Core_QueryBuilder_Selection
	 */
	public function clear()
	{
		$this->_where = $this->_orderBy = array();
		$this->_operator = '';

		$this->_defaultOperator = 'AND';

		$this->_limit = $this->_offset = NULL;
		return $this;
	}
}