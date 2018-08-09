<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * DROP Database Abstraction Layer (DBAL)
 *
 * http://dev.mysql.com/doc/refman/5.5/en/drop-table.html
 *
 * <code>
 * // Drop multiple tables
 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop('TableName1')
 * 	->table('TableName2')
 * 	->execute();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_QueryBuilder_Drop extends Core_QueryBuilder_Statement
{
	/**
	 * List of tables
	 * @var array
	 */
	protected $_table = array();

	/**
	 * DataBase Query Type
	 * 6 - DROP
	 */
	protected $_queryType = 6;

	/** 
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop('TableName1')
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
	 * @param string $tableName table name
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop()->table('TableName1');
	 * </code>
	 */
	public function table($tableName)
	{
		$this->_table[] = $tableName;
		return $this;
	}

	/**
	 * Use temporary
	 * @var boolean
	 */
	protected $_temporary = FALSE;
	
	/**
	 * Set TEMPORARY
	 * @param boolean $temporary TEMPORARY mode
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop('TableName1')->temporary();
	 * </code>
	 */
	public function temporary($temporary = TRUE)
	{
		$this->_temporary = $temporary;
		return $this;
	}
	
	/**
	 * Use IF EXISTS
	 * @var boolean
	 */
	protected $_ifExists = FALSE;
	
	/**
	 * Set IF EXISTS
	 * @param boolean $ifExists IF EXISTS mode
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop('TableName1')->ifExists();
	 * </code>
	 */
	public function ifExists($ifExists = TRUE)
	{
		$this->_ifExists = $ifExists;
		return $this;
	}
	
	/**
	 * Set RESTRICT
	 * @param boolean $restrict RESTRICT mode
	 * @return self
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop('TableName1')->restrict();
	 * </code>
	 */
	public function restrict($restrict = TRUE)
	{
		$this->_restrict = $restrict;
		return $this;
	}
	
	/**
	 * Set CASCADE
	 * @param boolean $cascade CASCADE mode
	 * @return self
	 * <code>
	 * $oCore_QueryBuilder_Drop = Core_QueryBuilder::drop('TableName1')->cascade();
	 * </code>
	 */
	public function cascade($cascade = TRUE)
	{
		$this->_cascade = $cascade;
		return $this;
	}
	
	/**
	 * Build table drop list
	 * 
	 * @param array $tables tables list
	 * @return string The SQL query
	 */
	protected function _buildDrop(array $tables)
	{
		$sql = array();

		foreach ($tables as $tableName)
		{
			$sql[] = $this->_dataBase->quoteColumnName($tableName);
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
		$query = array('DROP');
		
		if ($this->_temporary)
		{
			$query[] = 'TEMPORARY';
		}
	
		$query[] = 'TABLE';
	
		if ($this->_ifExists)
		{
			$query[] = 'IF EXISTS';
		}
	
		$query[] = $this->_buildDrop($this->_table);
	
		if ($this->_restrict)
		{
			$query[] = 'RESTRICT';
		}
		elseif ($this->_cascade)
		{
			$query[] = 'CASCADE';
		}
	
		return implode(" ", $query);
	}
}