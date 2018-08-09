<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * INSERT Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/insert.html
 *
 * <code>
 * // Sample 1
 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('tableName')
 * 	->columns('column1', 'column2', 'column3')
 * 	->values('value1', 'value2', 11)
 * 	->values('value3', 'value4', 17)
 * 	->values('value5', 'value6', 19)
 *	->execute();
 * </code>
 *
 * <code>
 * // Sample 2
 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('tableName', array('column1' => 'value1', 'column2' => 'value2'))
 *	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Insert extends Core_QueryBuilder_Replace
{
	/**
	 * Use IGNORE
	 * @var mixed
	 */
	protected $_ignore = FALSE;

	/**
	 * Set HIGH_PRIORITY
	 *
	 * <code>
	 * $oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('tableName')->highPriority();
	 * </code>
	 * @return Core_QueryBuilder_Insert
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
	 * @return Core_QueryBuilder_Insert
	 */
	public function ignore()
	{
		$this->_ignore = TRUE;
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

		$query[] = 'INTO ' . $this->_dataBase->quoteColumnName($this->_into);

		$query[] = "\n(" . implode(', ', $this->quoteColumns($this->_columns)) . ')';

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