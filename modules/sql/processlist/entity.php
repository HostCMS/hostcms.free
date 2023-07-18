<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Processlist_Entity
{
	protected $_fields = array('id', 'user', 'host', 'db', 'command', 'time', 'state', 'info');

	public $id = NULL;
	public $user = NULL;
	public $host = NULL;
	public $db = NULL;
	public $command = NULL;
	public $time = NULL;
	public $state = NULL;
	public $info = NULL;

	/**
	 * Get tableColumns
	 * @return array
	 */
	public function getTableColumns()
	{
		return array_combine($this->_fields, $this->_fields);
	}

	public function getModelName()
	{
		return 'sql_processlist';
	}

	/**
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		return $this->id;
	}

	/**
	 * Kill Process
	 */
	public function kill()
	{
		Core_DataBase::instance()
			->setQueryType(99)
			->query('KILL ' . intval($this->id));

		return $this;
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