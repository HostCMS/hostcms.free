<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Tab extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'caption',
		'active'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->active = TRUE;
	}

	/**
	 * Check if there field with same name is
	 * @param string $fieldName name
	 * @return boolean
	 */
	public function issetField($fieldName)
	{
		foreach ($this->_children as $object)
		{
			if (isset($object->name) && $object->name == $fieldName)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Get field by name
	 * @param string $fieldName name
	 * @return object
	 */
	public function getField($fieldName)
	{
		foreach ($this->_children as $object)
		{
			if (isset($object->name) && $object->name == $fieldName)
			{
				return $object;
			}
		}

		throw new Core_Exception("Field %fieldName does not exist.", array('%fieldName' => $fieldName));
	}

	/**
	 * Get tab fields
	 * @return array
	 */
	public function getFields()
	{
		return $this->_children;
	}

	/**
	 * Delete empty items from tab
	 * @return self
	 */
	public function deleteEmptyItems()
	{
		// Удаляем пустые div - row из показа
		$this->_children = array_filter($this->_children, array(__CLASS__, '_deleteEmptyItems'));

		return $this;
	}

	/**
	 * Check if $value is not instance of Skin_Default_Admin_Form_Entity_Div or $value has children
	 * @return boolean
	 */
	static protected function _deleteEmptyItems($value)
	{
		return !($value instanceof Skin_Default_Admin_Form_Entity_Div)
			|| $value->getCountChildren();
	}

	/**
	 * Execute all children
	 * @return self
	 */
	public function executeChildren()
	{
		return parent::executeChildren();
	}
}