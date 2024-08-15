<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Calendar_Caldav_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Calendar_Caldav_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'event' => array(),
		'calendar_caldav' => array()
	);
}