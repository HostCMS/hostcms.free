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
class Sql_Table_Field_Entity
{
	//public $view = NULL;

	protected $_fields = array(
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
	 * Get tableColumns
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->_fields;
	}

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

	public function getModelName()
	{
		return 'sql_table_field';
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
					Core::factory('Core_Html_Entity_I')
						->class('fas fa-key azure')
						->title('PRIMARY KEY')
						->execute();
				break;
				case 'UNI':
					Core::factory('Core_Html_Entity_I')
						->class('fas fa-key darkorange')
						->title('UNIQUE KEY')
						->execute();
				break;
				case 'MUL':
					Core::factory('Core_Html_Entity_I')
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
		if ($this->Null == 'YES' && is_null($this->Default))
		{
			return 'NULL';
		}
		else
		{
			return htmlspecialchars($this->Default);
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function NullBackend()
	{
		$this->Null == 'YES' && Core::factory('Core_Html_Entity_Span')
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

	public function delete()
	{
		$table_name = $this->getTableName();

		$oDataBase = Core_DataBase::instance();

		$query = 'ALTER TABLE ' . $oDataBase->quoteTableName($table_name) . ' DROP ' . $oDataBase->quoteColumnName($this->Field);

		$oDataBase->query($query);

		return $this;
	}
}