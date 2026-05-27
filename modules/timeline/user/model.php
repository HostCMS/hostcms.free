<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_User_Model
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_User_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'timeline_user';

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
		'timeline' => array(),
		'user' => array()
	);
}