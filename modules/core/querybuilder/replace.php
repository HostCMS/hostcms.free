<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * REPLACE Database Abstraction Layer (DBAL)
 *
 * https://dev.mysql.com/doc/refman/5.5/en/replace.html
 *
 * <code>
 * // Sample 1
 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')
 * 	->columns('column1', 'column2', 'column3')
 *	// Option 1
 * 	->values('value1', 'value2', 11)
 * 	// Option 2
 * 	->values(array('value3', 'value4', 17))
 *	->execute();
 * </code>
 *
 * <code>
 * // Sample 2
 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName', array('column1' => 'value1', 'column2' => 'value2'))
 *	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_QueryBuilder_Replace extends Core_QueryBuilder_Statement
{
	/**
	 * Table name
	 * @var mixed
	 */
	protected $_into = NULL;

	/**
	 * Columns
	 * @var array
	 */
	protected $_columns = array();

	/**
	 * Array of values
	 * @var array
	 */
	protected $_values = array();

	/**
	 * Use LOW_PRIORITY
	 * @var mixed
	 */
	protected $_priority = NULL;

	/**
	 * Use IGNORE
	 * @var mixed
	 */
	protected $_ignore = FALSE;

	/**
	 * DataBase Query Type
	 * 1 - INSERT
	 */
	protected $_queryType = 1;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName');
	 * </code>
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName', array('column1' => 'value1', 'column2' => 'value2'));
	 * </code>
	 * @see into()
	 */
	public function __construct(array $args = array())
	{
		// Set table name
		count($args) && call_user_func_array(array($this, 'into'), $args);

		// Set columns and values
		if (count($args) > 1 && is_array($args[1]))
		{
			$this->_columns = array_merge($this->_columns, array_keys($args[1]));
			$this->_values[] = array_values($args[1]);
		}

		return parent::__construct($args);
	}

	/**
	 * Set LOW_PRIORITY
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')->lowPriority();
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function lowPriority()
	{
		$this->_priority = 'LOW_PRIORITY';
		return $this;
	}

	/**
	 * Set DELAYED
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')->delayed();
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function delayed()
	{
		$this->_priority = 'DELAYED';
		return $this;
	}

	/**
	 * Set HIGH_PRIORITY
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')->highPriority();
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function highPriority()
	{
		$this->_priority = 'HIGH_PRIORITY';
		return $this;
	}

	/**
	 * Set IGNORE
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')->ignore();
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function ignore()
	{
		$this->_ignore = TRUE;
		return $this;
	}

	/**
	 * Set table name
	 * @param string $tableName table name
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace()->into('tableName');
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function into($tableName)
	{
		$this->_into = $tableName;
		return $this;
	}

	/**
	 * Add columns for INSERT
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')
	 * 		->columns('column1', 'column2', 'column3');
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function columns()
	{
		$args = func_get_args();
		$this->_columns = array_merge($this->_columns, $args);

		return $this;
	}

	/**
	 * Set values for INSERT
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')
	 * 	->columns('column1', 'column2', 'column3')
	 *	// Option 1
	 * 	->values('value1', 'value2', 11)
	 * 	// Option 2
	 * 	->values(array('value3', 'value4', 17))
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function values()
	{
		$args = func_get_args();

		$this->_values[] = count($args) == 1 && is_array($args[0])
			? $args[0]
			: $args;

		return $this;
	}

	/**
	 * Clear values
	 *
	 * <code>
	 * $oCore_QueryBuilder_Replace = Core_QueryBuilder::replace('tableName')
	 * 	->columns('column1', 'column2', 'column3')
	 * 	->values('value1', 'value2', 11);
	 * $oCore_QueryBuilder_Replace->execute();
	 * $oCore_QueryBuilder_Replace->clearValues();
	 * $oCore_QueryBuilder_Replace->values('value3', 'value4', 17)
	 * 	->execute();
	 * </code>
	 * @return Core_QueryBuilder_Replace
	 */
	public function clearValues()
	{
		$this->_values = array();
		return $this;
	}

	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		$query = array($this->_buildComment($this->_comment) . 'REPLACE');

		!is_null($this->_priority) && $query[] = $this->_priority;

		$query[] = 'INTO ' . $this->_dataBase->quoteTableName($this->_into);

		$query[] = "\n(" . implode(', ', $this->_quoteColumns($this->_columns)) . ')';

		$query[] = "\nVALUES ";

		$aValues = array();
		foreach ($this->_values as $aValue)
		{
			$aValues[] = '(' . implode(', ', $this->_quoteValues($aValue)) . ')';
		}

		$query[] = implode(",\n", $aValues);

		$sql = implode(' ', $query);

		return $sql;
	}
}