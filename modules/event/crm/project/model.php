<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Crm_Project_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Event_Crm_Project_Model extends Core_Entity
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
		'crm_project' => array()
	);
}