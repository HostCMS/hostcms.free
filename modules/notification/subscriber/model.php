<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Notification_Subscriber_Model
 *
 * @package HostCMS
 * @subpackage Notification
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Notification_Subscriber_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
	
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);
}