<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Empty Entity
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Empty_Entity
{
	/**
	 * Columns
	 * @var mixed
	 */
	protected $_columns = NULL;

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
	protected $_tableColums = array();

	/**
	 * Set table columns
	 * @param array $tableColums columns
	 * @return self
	 */
	public function setTableColums($tableColums)
	{
		$this->_tableColums = $tableColums;
		return $this;
	}

	/**
	 * Get table colums
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->_tableColums;
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
}