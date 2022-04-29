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
class Sql_Table_View_Entity
{
	public function __isset($property)
	{
		$this->getTableColumns();
		return isset($this->_columns[$property]);
	}

	/**
	 * Verify that the contents of a variable can be called as a function
	 * @param string $methodName method name
	 */
	public function isCallable($methodName)
	{
		$name = substr($methodName, 0, -7);
		return Core_Str::endsWith($methodName, 'Backend') && is_null($this->$name);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if (isset($this->_columns[$property]))
		{
			return '';
		}
	}

	/**
	 * Triggered when invoking inaccessible methods in an object context
	 * @param string $name method name
	 * @param array $arguments arguments
	 * @return mixed
	 * @hostcms-event modelname.onCall
	 */
	public function __call($methodName, $arguments)
	{
		// Будет вызываться только при NULL, в остальных случаях стандартный вывод
		if (Core_Str::endsWith($methodName, 'Backend'))
		{
			$name = substr($methodName, 0, -7);

			return is_null($this->$name)
				? 'NULL'
				: htmlspecialchars($this->$name);
		}
	}

	/**
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		$pr = $this->_primaryKeyName;
		return $this->$pr;
	}

	protected $_primaryKeyName = NULL;

	public function getPrimaryKeyName()
	{
		if (is_null($this->_primaryKeyName))
		{
			$this->_primaryKeyName = 'id';

			$aFileds = $this->getTableColumns();
			foreach ($aFileds as $key => $aRow)
			{
				// Set temporary key and order field for the Admin_Form
				if ($aRow['key'] == 'PRI')
				{
					$this->_primaryKeyName = $aRow['name'];
					break;
				}
			}
		}

		return $this->_primaryKeyName;
	}

	/**
	 * Select query builder
	 * @var Core_QueryBuilder_Select
	 */
	protected $_queryBuilder = NULL;

	/**
	 * Get query builder for select
	 * @return Core_QueryBuilder_Select
	 */
	public function queryBuilder()
	{
		if (is_null($this->_queryBuilder))
		{
			$this->_queryBuilder = Core_QueryBuilder::select();
		}

		return $this->_queryBuilder;
	}

	protected $_tableName = NULL;

	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
		return $this;
	}

	public function getTableName()
	{
		return $this->_tableName;
	}

	public function getModelName()
	{
		return 'sql_table_view';
	}

	protected $_columns = NULL;

	public function getTableColumns()
	{
		if (is_null($this->_columns))
		{
			$this->_columns = Core_DataBase::instance()->getColumns($this->_tableName);
		}

		return $this->_columns;
	}

	public function delete()
	{
		$primaryKeyName = $this->getPrimaryKeyName();

		Core_QueryBuilder::delete($this->_tableName)
			->where($primaryKeyName, '=', $this->$primaryKeyName)
			->execute();

		return $this;
	}
}