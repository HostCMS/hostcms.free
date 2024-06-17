<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Workday_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class User_Workday_Break_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user_workday' => array()
	);
	
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}