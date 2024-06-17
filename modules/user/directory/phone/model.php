<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Directory_Phone_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class User_Directory_Phone_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_phone' => array(),
		'user' => array()
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}