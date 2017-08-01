<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * LOCK TABLES Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/lock-tables.html
 *
 * <code>
 * $oCore_QueryBuilder_Lock = Core_QueryBuilder::lock('tableName1', 'READ')
 * 	->execute();
 * </code>
 * <code>
 * // Lock multiple tables
 * $oCore_QueryBuilder_Lock = Core_QueryBuilder::lock('tableName1', 'READ')
 * 	->table('tableName2', 'WRITE')
 * 	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Lock extends Core_QueryBuilder_Statement
{
	/**
	 * List of tables
	 * @var array
	 */
	protected $_table = array();

	/**
	 * DataBase Query Type
	 * 8 - LOCK
	 */
	protected $_queryType = 8;

	/**
	 * Constructor.
	 * 
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Lock = Core_QueryBuilder::lock('tableName1', 'READ')
	 * 	->execute();
	 * </code>
	 *
	 * @see table()
	 */
	public function __construct(array $args = array())
	{
		// Set table name
		count($args) > 0 && call_user_func_array(array($this, 'table'), $args);

		return parent::__construct($args);
	}

	/**
	 * Add table name
	 *
	 * @param string $tableName table name
	 * @param string $type lock type
	 * <code>
	 * $oCore_QueryBuilder_Lock = Core_QueryBuilder::lock()
	 * 	->table('tableName1', 'READ');
	 * </code>
	 * @return Core_QueryBuilder_Lock
	 */
	public function table($tableName, $type)
	{
		$this->_table[$tableName] = $type;
		return $this;
	}

	/**
	 * Build table lock list
	 *
	 * @param array $tables list of tables
	 * @return string The SQL query
	 */
	protected function _buildLock(array $tables)
	{
		$sql = array();

		foreach ($tables as $tableName => $type)
		{
			$tableName = $this->_dataBase->quoteColumnName($tableName);
			
			$type = strtoupper($type);
			
			if (!in_array($type, array('READ', 'READ LOCAL', 'WRITE', 'LOW_PRIORITY WRITE')))
			{
				throw new Core_Exception("Argument should be READ, READ LOCAL, WRITE or LOW_PRIORITY WRITE.");
			}

			$sql[] = $tableName . ' ' . $type;
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
		return 'LOCK TABLES ' . $this->_buildLock($this->_table);
	}
	
	/**
	 * UNLOCK TABLES
	 * 
	 * @return Core_DataBase
	 */
	public function unlock()
	{
		$oDataBase = $this->_dataBase
			->setQueryType($this->_queryType)
			->query('UNLOCK TABLES');

		return $oDataBase;
	}
}