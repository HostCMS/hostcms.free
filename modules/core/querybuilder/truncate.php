<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * TRUNCATE Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/truncate-table.html
 *
 * <code>
 * $oCore_QueryBuilder_Truncate = Core_QueryBuilder::truncate('TableName')
 * 	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Truncate extends Core_QueryBuilder_Statement
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = NULL;

	/**
	 * DataBase Query Type
	 * 7 - TRUNCATE
	 */
	protected $_queryType = 7;

	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Truncate = Core_QueryBuilder::truncate('TableName')
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
	 * $oCore_QueryBuilder_Truncate = Core_QueryBuilder::truncate()->table('TableName');
	 * </code>
	 * @param string $tableName table name
	 * @return Core_QueryBuilder_Truncate
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
		return 'TRUNCATE TABLE ' . $this->_dataBase->quoteTableName($this->_table);
	}
}