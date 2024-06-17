<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Directory_Email_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Company_Directory_Email_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_email' => array(),
		'company' => array()
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}