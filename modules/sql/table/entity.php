<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_Entity
{
	//public $view = NULL;

	protected $_fields = array('name', 'engine', 'version', 'row_format', 'table_rows', 'avg_row_legth', 'data_length', 'index_length', 'fragmented', 'collation');

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
		return 'sql_table';
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
	 * Backend callback method
	 * @return string
	 */
	public function data_lengthBackend()
	{
		return Core_Str::getTextSize($this->data_length);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function index_lengthBackend()
	{
		if ($this->index_length)
		{
			return Core_Str::getTextSize($this->index_length);
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function fragmentedBackend()
	{
		if ($this->fragmented)
		{
			return Core_Str::getTextSize($this->fragmented);
		}
	}

	/**
	 * Truncate Table
	 */
	public function truncate()
	{
		Core_QueryBuilder::truncate($this->name)->execute();
		return $this;
	}

	/**
	 * Optimize Table
	 */
	public function optimize()
	{
		Core_QueryBuilder::optimize($this->name)->execute();
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