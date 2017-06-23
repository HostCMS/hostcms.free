<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin View.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ÎÎÎ "Õîñòìýéê" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Admin_View extends Core_Servant_Properties
{
	protected $_allowedProperties = array(
		'pageTitle',
		'module',
		'message',
		'content',
		'pageSelector',
	);

	protected $_children = array();
	
	public function children(array $children)
	{
		$this->_children = $children;
		return $this;
	}

	/**
	 * Create new admin view
	 * @return object
	 */
	static public function create()
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__;

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
					array('%className' => $className));
		}

		return new $className();
	}

	/**
	 * Add message for administration center form
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
	
	abstract public function show();
}