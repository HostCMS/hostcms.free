<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * RENAME Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/rename-table.html
 *
 * <code>
 * $oCore_QueryBuilder_Rename = Core_QueryBuilder::rename('oldTableName1', 'newTableName1')
 * 	->execute();
 * </code>
 * <code>
 * // Rename multiple tables
 * $oCore_QueryBuilder_Rename = Core_QueryBuilder::rename('oldTableName1', 'newTableName1')
 * 	->table('oldTableName2', 'newTableName2')
 * 	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Rename extends Core_QueryBuilder_Statement
{
	/**
	 * List of tables
	 * @var array
	 */
	protected $_table = array();

	/**
	 * DataBase Query Type
	 * 4 - RENAME
	 */
	protected $_queryType = 4;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Rename = Core_QueryBuilder::rename('oldTableName1', 'newTableName1')
	 * 	->execute();
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
	 * Add table name
	 *
	 * @param string $oldTableName old table name
	 * @param string $newTableName new table name
	 * <code>
	 * $oCore_QueryBuilder_Rename = Core_QueryBuilder::rename()
	 * 	->table('oldTableName2', 'newTableName2');
	 * </code>
	 * @return Core_QueryBuilder_Rename
	 */
	public function table($oldTableName, $newTableName)
	{
		$this->_table[$oldTableName] = $newTableName;
		return $this;
	}

	/**
	 * Build table rename list
	 *
	 * @param array $tables list of tables
	 * @return string The SQL query
	 */
	protected function _buildRename(array $tables)
	{
		$sql = array();

		foreach ($tables as $oldTableName => $newTableName)
		{
			$oldTableName = $this->_dataBase->quoteColumnName($oldTableName);
			$newTableName = $this->_dataBase->quoteColumnName($newTableName);

			$sql[] = $oldTableName . ' TO ' . $newTableName;
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
		return 'RENAME TABLE ' . $this->_buildRename($this->_table);
	}
}