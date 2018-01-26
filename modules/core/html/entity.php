<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HTML entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Html_Entity extends Core_Servant_Properties
{
	/**
	 * Use common attributes
	 * @var boolean
	 */
	protected $_useAttrCommon = TRUE;

	/**
	 * Use common events
	 * @var boolean
	 */
	protected $_useAttrEvent = TRUE;

	/**
	 * Common attributes
	 * @var array
	 */
	static protected $_attrCommon = array(
		'accesskey',
		'class',
		'dir',
		'id',
		'lang',
		'style',
		'tabindex',
		'title'
	);

	/**
	 * Common events
	 * @var array
	 */
	static protected $_attrEvent = array(
		'onblur',
		'onchange',
		'onclick',
		'ondblclick',
		'onfocus',
		'onkeydown',
		'onkeypress',
		'onkeyup',
		'onload',
		'onmousedown',
		'onmousemove',
		'onmouseout',
		'onmouseover',
		'onmouseup',
		'onreset',
		'onselect',
		'onsubmit',
		'onunload'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Универсальные атрибуты
		// http://htmlbook.ru/html/attr/common
		if ($this->_useAttrCommon)
		{
			$this->_allowedProperties += array_combine(self::$_attrCommon, self::$_attrCommon);
		}

		// Универсальные события
		// http://htmlbook.ru/html/attr/event
		if ($this->_useAttrEvent)
		{
			$this->_allowedProperties += array_combine(self::$_attrEvent, self::$_attrEvent);
		}

		if (count($this->_skipProperies) > 0)
		{
			// Combine
			$this->_skipProperies = array_combine($this->_skipProperies, $this->_skipProperies);

			// Исключаемые свойства добавляем в список разрешенных объекта
			$this->_allowedProperties += $this->_skipProperies;
		}

		parent::__construct();
	}

	/**
	 * Get allowed object properties
	 * @return array
	 */
	public function getAllowedProperties()
	{
		return $this->_allowedProperties;
	}

	/**
	 * List of children elements
	 * @var array
	 */
	protected $_children = array();

	/**
	 * Clear children elements list
	 * @return self
	 */
	public function clear()
	{
		$this->_children = array();
		return $this;
	}

	/**
	 * Add new entity
	 * @param Admin_Form_Entity $oCore_Html_Entity new entity
	 * @return Core_Html_Entity
	 */
	public function add($oCore_Html_Entity)
	{
		$this->_children[] = $oCore_Html_Entity;
		return $this;
	}

	protected function _find($haystack, $object)
	{
		$aChildren = $haystack->getChildren();

		$key = array_search($object, $aChildren, $strict = TRUE);

		if ($key !== FALSE)
		{
			return array($key, $haystack);
		}
		else
		{
			foreach ($aChildren as $tmpKey => $tmpObject)
			{
				$result = $this->_find($tmpObject, $object);

				if ($result)
				{
					return $result;
				}
			}
		}

		return FALSE;
	}

	protected function _addBefore($key, $oCore_Html_Entity)
	{
		array_splice($this->_children, $key, 0, array($oCore_Html_Entity));
		return $this;
	}

	/**
	 * Add new entity before $oAdmin_Form_Entity_Before
	 * @param Admin_Form_Entity $oCore_Html_Entity new entity
	 * @param Admin_Form_Entity $oCore_Html_Entity_Before entity before which to add the new entity
	 * @return Core_Html_Entity
	 */
	public function addBefore($oCore_Html_Entity, $oCore_Html_Entity_Before)
	{
		// Find key for 'before' object
		//$key = array_search($oCore_Html_Entity_Before, $this->_children, $strict = TRUE);

		$result = $this->_find($this, $oCore_Html_Entity_Before);

		if ($result !== FALSE)
		{
			list($key, $haystack) = $result;
			/*array_splice($this->_children, $key, 0, array($oCore_Html_Entity));
			return $this;*/

			$haystack->_addBefore($key, $oCore_Html_Entity);
			return $this;
		}

		throw new Core_Exception(
			"addBefore(): before adding object '%name' does not exist.",
			array('%name' => $oCore_Html_Entity_Before->name)
		);
	}

	protected function _addAfter($key, $oCore_Html_Entity)
	{
		array_splice($this->_children, $key + 1, 0, array($oCore_Html_Entity));
		return $this;
	}

	/**
	 * Add new entity after $oAdmin_Form_Entity_After
	 * @param Admin_Form_Entity $oCore_Html_Entity new entity
	 * @param Admin_Form_Entity $oCore_Html_Entity_After entity after which to add the new entity
	 * @return Core_Html_Entity
	 */
	public function addAfter($oCore_Html_Entity, $oCore_Html_Entity_After)
	{
		// Find key for 'after' object
		//$key = array_search($oCore_Html_Entity_After, $this->_children, $strict = TRUE);

		$result = $this->_find($this, $oCore_Html_Entity_After);

		if ($result !== FALSE)
		{
			list($key, $haystack) = $result;

			//array_splice($this->_children, $key + 1, 0, array($oCore_Html_Entity));
			//return $this;

			$haystack->_addAfter($key, $oCore_Html_Entity);
			return $this;
		}

		throw new Core_Exception(
			"addAfter(): after adding object '%name' does not exist.",
			array('%name' => $oCore_Html_Entity_After->name)
		);
	}

	protected function _deleteObject($oSource_Object, $Core_Html_Entity)
	{
		$haystack = $oSource_Object->getChildren();

		foreach ($haystack as $key => $object)
		{
			if ($object == $Core_Html_Entity)
			{
				$oSource_Object->deleteChild($key);

				return TRUE;
			}
			// Ищем в потомках
			else
			{
				if ($this->_deleteObject($object, $Core_Html_Entity))
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Delete child element
	 * @param Core_Html_Entity $oCore_Html_Entity element
	 * @return self
	 */
	public function delete(Core_Html_Entity $oCore_Html_Entity)
	{
		$result = $this->_deleteObject($this, $oCore_Html_Entity);

		/*foreach ($this->_children as $key => $object)
		{
			if ($oCore_Html_Entity == $object)
			{
				unset($this->_children[$key]);

				// Reset keys
				$this->_children = array_values($this->_children);

				return $this;
			}
		}*/

		if (!$result)
		{
			throw new Core_Exception("delete(): deleting object does not exist.");
		}

		return $this;
	}

	/**
	 * Get allowed properties
	 * @return array
	 */
	public function getAttrsString()
	{
		$aAttr = array();
		foreach ($this->_allowedProperties as $key => $value)
		{
			if (!is_null($this->$key) && !isset($this->_skipProperies[$key]))
			{
				$aAttr[] = "{$key}=\"" . htmlspecialchars($this->$key) . "\"";
			}
		}

		return $aAttr;
	}

	/**
	 * Get entity's children
	 * @return int
	 */
	public function getChildren()
	{
		return $this->_children;
	}

	public function deleteChild($key)
	{
		if (isset($this->_children[$key]))
		{
			unset($this->_children[$key]);

			// Reset keys
			$this->_children = array_values($this->_children);
		}

		return $this;
	}

	/**
	 * Get count of children entities
	 * @return int
	 */
	public function getCountChildren()
	{
		return count($this->_children);
	}

	/**
	 * Executes the business logic.
	 * @return self
	 */
	public function execute()
	{
		return $this->executeChildren();
	}

	/**
	 * Execute all children
	 * @return self
	 */
	public function executeChildren()
	{
		foreach ($this->_children as $oCore_Html_Entity)
		{
			$oCore_Html_Entity->execute();
		}

		return $this;
	}
}