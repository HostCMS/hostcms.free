<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin View.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Admin_View extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'pageTitle',
		'module',
		'message',
		'content'
	);

	/**
	 * Children
	 * @var array
	 */
	protected $_children = array();

	/**
	 * Set children
	 * @param array $children
	 * @return self
	 */
	public function children(array $children)
	{
		$this->_children = $children;
		return $this;
	}

	/**
	 * Create new admin view
	 * @return object
	 */
	static public function create($className = NULL)
	{
		is_null($className)
			&& $className = self::getClassName(__CLASS__);

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist", array('%className' => $className));
		}

		return new $className();
	}

	/**
	 * Get class name depends on skin
	 * @return string
	 */
	static public function getClassName($className)
	{
		return 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . $className;
	}

	/**
	 * Add message for Back-end form
	 * @param string $message message
	 * @return self
	 */
	public function addMessage($message)
	{
		$this->message .= $message;
		return $this;
	}

	/**
	 * Add entity
	 * @param Admin_Form_Entity $oAdmin_Form_Entity
	 * @return self
	 */
	public function addChild(Admin_Form_Entity $oAdmin_Form_Entity)
	{
		$this->_children[] = $oAdmin_Form_Entity;
		return $this;
	}

	/**
	 * Abstract show
	 */
	abstract public function show();
}