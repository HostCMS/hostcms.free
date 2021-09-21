<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Notification_Subscriber_Model
 *
 * @package HostCMS
 * @subpackage Notification
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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