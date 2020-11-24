<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * INSERT Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/insert.html
 *
 * <code>
 * // Sample 1
 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('table_name')
 * 	->columns('column1', 'column2', 'column3')
 *	// Option 1
 * 	->values('value1', 'value2', 11)
 *	// Option 2
 * 	->values(array('value3', 'value4', 17))
 *	->execute();
 * </code>
 *
 * <code>
 * // Sample 2
 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('table_name', array('column1' => 'value1', 'column2' => 'value2'))
 *	->execute();
 * </code>
 *
 * <code>
 * // Sample 3
 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('table_name')
 * 	->columns('column1', 'column2', 'column3')
 * 	->select(
 *  	Core_QueryBuilder::select('*')->from('source_table')
 *  )
 *	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Insert extends Core_QueryBuilder_Replace
{
	/**
	 * Use IGNORE
	 * @var mixed
	 */
	protected $_ignore = FALSE;

	/**
	 * Select
	 * @var mixed
	 */
	protected $_select = NULL;

	/**
	 * Set HIGH_PRIORITY
	 *
	 * <code>
	 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('tableName')->highPriority();
	 * </code>
	 * @return self
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
	 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('tableName')->ignore();
	 * </code>
	 * @return self
	 */
	public function ignore()
	{
		$this->_ignore = TRUE;
		return $this;
	}

	/**
	 * Set SELECT
	 * @param Core_QueryBuilder_Select $oCore_QueryBuilder_Select
	 *
	 * <code>
	 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('tableName')->select($oCore_QueryBuilder_Select);
	 * </code>
	 * @return self
	 */
	public function select(Core_QueryBuilder_Select $oCore_QueryBuilder_Select)
	{
		$this->_select = $oCore_QueryBuilder_Select;
		return $this;
	}

	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		$query = array('INSERT');

		!is_null($this->_priority) && $query[] = $this->_priority;

		$this->_ignore && $query[] = 'IGNORE';

		$query[] = 'INTO ' . $this->_dataBase->quoteTableName($this->_into);

		$query[] = "\n(" . implode(', ', $this->_quoteColumns($this->_columns)) . ')';

		if (is_null($this->_select))
		{
			$query[] = "\nVALUES ";

			$aValues = array();
			foreach ($this->_values as $aValue)
			{
				$aValues[] = '(' . implode(', ', $this->_quoteValues($aValue)) . ')';
			}

			$query[] = implode(",\n", $aValues);
		}
		else
		{
			$query[] = "\n" . $this->_select->build();
		}

		$sql = implode(' ', $query);

		return $sql;
	}
}