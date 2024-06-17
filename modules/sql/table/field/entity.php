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
class Sql_Table_Field_Entity extends Core_Empty_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'sql_table_field';

	//public $view = NULL;

	protected $_values = array();

	/**
	 * Fields
	 * @var array
	 */
	protected $_tableColums = array(
		'Field' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'Type' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'Collation' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'Null' => array(
			'datatype' => 'tinyint unsigned',
			'type' => 'int',
			'max_length' => NULL,
			'default' => NULL,
			'null' => FALSE,
			'unsigned' => TRUE,
			'min' => 0,
			'max' => 255
		),
		'Key' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'Default' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => '',
			'null' => FALSE
		),
		'Extra' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'Privileges' => array(
			'datatype' => 'string',
			'type' => 'string',
			'max_length' => 255,
			'default' => NULL,
			'null' => FALSE
		),
		'Comment' => array(
			'datatype' => 'text',
			'type' => 'string',
			'max_length' => 65535,
			'default' => '',
			'null' => FALSE
		));

	/**
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		return $this->Field;
	}

	/**
	 * Get primary key value
	 * @return mixed
	 */
	public function getPrimaryKeyName()
	{
		return 'Field';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function FieldBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->Key != '')
		{
			switch ($this->Key)
			{
				case 'PRI':
					Core_Html_Entity::factory('I')
						->class('fas fa-key azure')
						->title('PRIMARY KEY')
						->execute();
				break;
				case 'UNI':
					Core_Html_Entity::factory('I')
						->class('fas fa-key darkorange')
						->title('UNIQUE KEY')
						->execute();
				break;
				case 'MUL':
					Core_Html_Entity::factory('I')
						->class('fas fa-key warning')
						->title('MULTIPLE KEY')
						->execute();
				break;
			}
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function DefaultBackend()
	{
		return $this->Null == 'YES' && is_null($this->Default)
			? 'NULL'
			: htmlspecialchars((string) $this->Default);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function NullBackend()
	{
		$this->Null == 'YES' && Core_Html_Entity::factory('Span')
			->value('<i class="fa fa-check-circle green" title="NULL"></i>')
			->execute();
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		if (isset($this->_values[$property]))
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
		if (isset($this->_values[$property]))
		{
			return $this->_values[$property];
		}
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 * @ignore
	 */
	public function __set($property, $value)
	{
		$this->_values[$property] = $value;
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
		if (isset($this->_values[$methodName]) && count($arguments) == 0)
		{
			return $this->_values[$methodName];
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

		$query = 'ALTER TABLE ' . $oDataBase->quoteTableName($table_name) . ' DROP ' . $oDataBase->quoteColumnName($this->Field);

		$oDataBase->query($query);

		return $this;
	}
}