<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Entry_Subcount_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Chartaccount_Entry_Subcount_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'chartaccount_entry_subcount';

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
		'chartaccount_entry' => array(),
	);
}