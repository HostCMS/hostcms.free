<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_Entity
{
	//public $view = NULL;

	protected $_fields = array(
		'name' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'engine' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'collation' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'auto_increment' => array(
			'datatype' => 'int',
			'type' => 'int'
		),
		// 'version', 'row_format', 'table_rows', 'avg_row_legth', 'data_length', 'index_length', 'fragmented'

	);

	/**
	 * Get tableColumns
	 * @return array
	 */
	public function getTableColumns()
	{
		// return array_combine($this->_fields, $this->_fields);
		return $this->_fields;
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
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKeyName()
	{
		return 'name';
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
		if (isset($this->_fields[$property]))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if (isset($this->_fields[$property]))
		{
			return '';
		}
	}

	/**
	 * Delete
	 * @return self
	 */
	public function delete()
	{
		$oDataBase = Core_DataBase::instance();

		$query = 'DROP TABLE ' . $oDataBase->quoteTableName($this->name);

		$oDataBase->query($query);

		return $this;
	}
}