<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties for item group.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Property_Controller_Tab extends Property_Controller_Tab
{
	/**
	 * Create and return an object of Property_Controller_Tab for current skin
	 * @return object
	 */
	static public function factory(Admin_Form_Controller $Admin_Form_Controller)
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__;

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $className));
		}

		return new $className($Admin_Form_Controller);
	}
}