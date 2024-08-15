<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * OPTIMIZE Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/optimize-table.html
 *
 * <code>
 * $oCore_QueryBuilder_Optimize = Core_QueryBuilder::optimize('TableName')
 * 	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_QueryBuilder_Optimize extends Core_QueryBuilder_Statement
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = NULL;

	/**
	 * DataBase Query Type
	 * 8 - OPTIMIZE
	 */
	protected $_queryType = 8;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Optimize = Core_QueryBuilder::optimize('TableName')
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
	 * Set table name
	 *
	 * <code>
	 * $oCore_QueryBuilder_Optimize = Core_QueryBuilder::optimize()->table('TableName');
	 * </code>
	 * @param string $tableName table name
	 * @return Core_QueryBuilder_Optimize
	 */
	public function table($tableName)
	{
		$this->_table = $tableName;
		return $this;
	}

	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		return 'OPTIMIZE TABLE ' . $this->_dataBase->quoteTableName($this->_table);
	}
}