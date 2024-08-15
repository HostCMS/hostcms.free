<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Expression Database Abstraction Layer (DBAL)
 *
 * @package HostCMS
 * @subpackage Core\Querybuilder
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_QueryBuilder_Expression extends Core_QueryBuilder_Statement
{
	/**
	 * Expression
	 * @var string
	 */
	protected $_expression = NULL;
	
	/**
	 * Constructor.
	 * @param array $args list of arguments
	 * <code>
	 * $oCore_QueryBuilder_Expression = Core_QueryBuilder::expression('SEC_TO_TIME(SUM(TIME_TO_SEC(`time_col`)))');
	 * </code>
	 *
	 * @see table()
	 */
	public function __construct(array $args = array())
	{
		// Set table name
		call_user_func_array(array($this, 'expression'), $args);

		return parent::__construct($args);
	}

	/**
	 * Set expression
	 *
	 * @param string $expression
	 * <code>
	 * Core_QueryBuilder::expression()->expression('SEC_TO_TIME(SUM(TIME_TO_SEC(`time_col`)))');
	 * </code>
	 * @return Core_QueryBuilder_Expression
	 */
	public function expression($expression)
	{
		$this->_expression = $expression;
		return $this;
	}
	
	/**
	 * Build the SQL query
	 *
	 * @return string The SQL query
	 */
	public function build()
	{
		return $this->_expression;
	}
}