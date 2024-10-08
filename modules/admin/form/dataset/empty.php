<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Empty Dataset
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Dataset_Empty extends Admin_Form_Dataset
{
	/**
	 * Get items count
	 * @return int
	 */
	public function getCount()
	{
		return 0;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return new stdClass();
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		return $this->_objects;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		return NULL;
	}
}