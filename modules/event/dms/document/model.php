<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Dms_Document_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Dms_Document_Model extends Core_Entity
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
		'dms_document' => array()
	);
}