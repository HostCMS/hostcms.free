<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Sql_Variable_Entity extends Core_Empty_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'sql_variable';

	protected $_fields = array('name', 'value');

	public $name = NULL;
	public $value = NULL;

	/**
	 * Get tableColumns
	 * @return array
	 */
	public function getTableColumns()
	{
		return array_combine($this->_fields, $this->_fields);
	}

	/**
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		return $this->name;
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		$lowerProperty = strtolower($property);

		return in_array($lowerProperty, $this->_fields);
	}
}