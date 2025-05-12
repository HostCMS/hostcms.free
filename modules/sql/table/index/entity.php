<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_Index_Entity
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Sql_Table_Index_Entity extends Core_Empty_Entity
{
	//public $view = NULL;

	public $name = NULL;
	public $unique = NULL;
	public $packed = NULL;
	public $type = NULL;
	public $columns = NULL;

	/**
	 * Fields
	 * @var array
	 */
	protected $_tableColums = array(
		'name' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		)
	);

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

	public function getModelName()
	{
		return 'sql_table_index';
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		if (isset($this->_tableColums[$property]))
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
		if (isset($this->_tableColums[$property]))
		{
			return '';
		}
	}

	/**
	 * Table name
	 * @var mixed
	 */
	protected $_tableName = NULL;

	/**
	 * Set table name
	 * @param string $tableName
	 * @return self
	 */
	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
		return $this;
	}

	/**
	 * Get table name
	 * @return string
	 */
	public function getTableName()
	{
		return $this->_tableName;
	}

	/**
	 * Delete
	 * @return self
	 */
	public function delete()
	{
		$table_name = $this->getTableName();

		$oDataBase = Core_DataBase::instance();

		$query = 'ALTER TABLE ' . $oDataBase->quoteTableName($table_name) . ' DROP INDEX ' . $oDataBase->quoteColumnName($this->name);

		$oDataBase->query($query);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function uniqueBackend()
	{
		$this->unique && Core_Html_Entity::factory('Span')
			->value('<i class="fa fa-check-circle green"></i>')
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function columnBackend()
	{
		$return = '<table class="table table-borderless no-background"><tbody>';

		foreach ($this->columns as $row)
		{
			$return .= '<tr>
			<td class="semi-bold">' . htmlspecialchars($row['Column_name'])
				. ($row['Sub_part'] != '' ? ' (' . htmlspecialchars($row['Sub_part']) . ')' : '')
			. '</td>
			<td width="10%">' . htmlspecialchars((string) $row['Cardinality']) . '</td>
			<td width="30">' . ($row['Null'] == 'YES' ? '<i class="fa fa-circle-o azure" title="NULL"></i>' : '') . '</td>
			</tr>';
		}

		$return .= '</tbody></table>';

		return $return;
	}
}