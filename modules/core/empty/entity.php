<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Empty Entity
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Empty_Entity
{
	/**
	 * data-values, e.g. dataMyValue
	 * @var array
	 */
	protected $_dataValues = array();

	/**
	 * Constructor
	 */
	public function __construct() { }

	/**
	 * Get dataValues
	 * @return array
	 */
	public function getDataValues()
	{
		return $this->_dataValues;
	}

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = NULL;

	/**
	 * Get model name, e.g. 'book' for 'Book_Model'
	 * @return string
	 */
	public function getModelName()
	{
		return $this->_modelName;
	}

	/**
	 * Load columns list
	 * @return self
	 */
	protected function _loadColumns()
	{
		return $this;
	}

	/**
	 * Table columns
	 * @var array
	 */
	protected $_tableColumns = array();

	/**
	 * Set table columns
	 * @param array $tableColums columns
	 * @return self
	 */
	public function setTableColums($tableColums)
	{
		$this->_tableColumns = $tableColums;
		return $this;
	}

	/**
	 * Get table colums
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->_tableColumns;
	}

	/**
	 * Get primary key name
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return 'id';
	}

	/**
	 * Get Related Site
	 * @return NULL
	 */
	public function getRelatedSite()
	{
		return NULL;
	}
	
	/**
	 * Convert object to string
	 * @return string
	 * @ignore
	 */
	public function __toString()
	{
		$return = array();

		if (!empty($this->_tableColumns))
		{
			// Model columns
			$return[] = implode(",\n", array_keys($this->_tableColumns));

			// 'dataXXX' values
			foreach ($this->_dataValues as $key => $value)
			{
				$return[] = htmlspecialchars((string) $key) . '=' . htmlspecialchars((string) $value);
			}

			return "Core_Empty_Entity, model: '" . strval($this->_modelName) . "',\nfields: " . implode(",\n", $return);
		}

		return '';
	}

}