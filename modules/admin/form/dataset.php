<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Typical Admin Form Dataset.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Admin_Form_Dataset
{
	/**
	 * Limit
	 * @var int
	 */
	protected $_limit = NULL;

	/**
	 * Offset
	 * @var int
	 */
	protected $_offset = 0;

	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Load status
	 * @var boolean
	 */
	protected $_loaded = FALSE;

	/**
	 * List of changed fields
	 * @var array
	 */
	protected $_changedFields = array();
	
	/**
	 * List of changed actions
	 * @var array
	 */
	protected $_changedActions = array();

	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Array of external fields from additional tables
	 * @var array
	 */
	protected $_externalFields = array();

	/**
	 * Array of conditions
	 * array (
	 * 	array('where' => array('a', '=', 'b')),
	 * 	array('where' => array('c', '=', 'd'))
	 * )
	 */
	protected $_conditions = array();

	/**
	 * Array of orders
	 */
	protected $_orders = array();

	/**
	 * Dataset objects list
	 * @var NULL|array
	 */
	protected $_objects = NULL;

	/**
	 * Get count of finded objects
	 * @return int
	 */
	abstract public function getCount();

	/**
	 * Load objects
	 * @return array
	 */
	abstract public function load();

	/**
	 * Get typical entity
	 * @return object
	 */
	abstract public function getEntity();

	/**
	 * Get objects
	 * @return array
	 */
	public function getObjects()
	{
		return $this->_objects;
	}

	/**
	 * Set controller
	 * @param Admin_Form_Controller $controller
	 * @return self
	 */
	public function controller(Admin_Form_Controller $controller)
	{
		$this->_Admin_Form_Controller = $controller;
		return $this;
	}

	/**
	 * Set limit
	 * @param int $limit limit
	 * @return self
	 */
	public function limit($limit)
	{
		$this->_limit = intval($limit);
		return $this;
	}

	/**
	 * Set offset
	 * @param int $offset offset
	 * @return self
	 */
	public function offset($offset)
	{
		$this->_offset = intval($offset);
		return $this;
	}

	/**
	 * Set load status
	 * @param boolean $loaded status
	 * @return self
	 */
	public function loaded($loaded)
	{
		$this->_loaded = $loaded;
		return $this;
	}

	/**
	 * Set items count
	 * @param int $count
	 * @return self
	 */
	public function setCount($count)
	{
		$this->_count = $count;
		return $this;
	}

	/**
	 * Add condition
	 * @param array $condition condition
	 * @return self
	 */
	public function addCondition($condition)
	{
		$this->_conditions[] = $condition;
		return $this;
	}

	/**
	 * Add orderBy
	 * @param string $column column
	 * @param string $direction sorting direction
	 * @param boolean $binary binary option
	 * @return self
	 */
	public function orderBy($column, $direction = 'ASC', $binary = FALSE)
	{
		$this->_orders[] = array($column, $direction, $binary);
		return $this;
	}

	/**
	 * Change the data for the field Admin_Form_Field
	 * @param string $name field name
	 * @param string $property field's property name
	 * @param string $value field's property value
	 * @return self
	 */
	public function changeField($name, $property, $value)
	{
		$this->_changedFields[$name][$property] = $value;
		return $this;
	}

	/**
	 * Get changed field
	 * @param string $name field name
	 * @return string
	 */
	public function getFieldChanges($name)
	{
		return isset($this->_changedFields[$name])
			? $this->_changedFields[$name]
			: NULL;
	}

	/**
	 * Change the data for the action Admin_Form_Action
	 * @param string $name action name
	 * @param string $property action's property name
	 * @param string $value action's property value
	 * @return self
	 */
	public function changeAction($name, $property, $value)
	{
		$this->_changedActions[$name][$property] = $value;
		return $this;
	}

	/**
	 * Get changed field
	 * @param string $name field name
	 * @return string
	 */
	public function getActionChanges($name)
	{
		return isset($this->_changedActions[$name])
			? $this->_changedActions[$name]
			: NULL;
	}

	/**
	 * Add external field name
	 * @param string $fieldName name of the field
	 * @return self
	 */
	public function addExternalField($fieldName)
	{
		$this->_externalFields[] = $fieldName;
		return $this;
	}

	/**
	 * Check if external field exists
	 * @param string $fieldName name of the field
	 * @return boolean
	 */
	public function issetExternalField($fieldName)
	{
		return in_array($fieldName, $this->_externalFields);
	}

	/**
	 * User-defined comparison functions.
	 * @param mixed $m
	 * @param mixed $n
	 * @return int
	 */
	static public function _sortAsc($m, $n)
	{
		$sortField = $m->getSortField();

		$first = $m->$sortField;
		$second = $n->$sortField;

		if ($sortField == 'datetime')
		{
			$first = Core_Date::sql2timestamp($first);
			$second = Core_Date::sql2timestamp($second);
		}

		if ($first == $second)
		{
			return 0;
		}

		return $first < $second ? -1 : 1;
	}

	/**
	 * User-defined comparison functions.
	 * @param mixed $m
	 * @param mixed $n
	 * @return int
	 */
	static public function _sortDesc($m, $n)
	{
		$sortField = $m->getSortField();

		$first = $m->$sortField;
		$second = $n->$sortField;

		if ($sortField == 'datetime')
		{
			$first = Core_Date::sql2timestamp($first);
			$second = Core_Date::sql2timestamp($second);
		}

		if ($first == $second)
		{
			return 0;
		}

		return $first > $second ? -1 : 1;
	}
}