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
class Sql_Table_View_Field
{
	protected $_columns = array();

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if (isset($this->_columns[$property]))
		{
			return $this->_columns[$property];
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
		$this->_columns[$property] = $value;
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
		if (isset($this->_columns[$methodName]) && count($arguments) == 0)
		{
			return $this->_columns[$methodName];
		}
	}

	/**
	 * Get caption of the field
	 * @return string|NULL
	 */
	public function getCaption($admin_language_id)
	{
		return $this->name;
	}
}