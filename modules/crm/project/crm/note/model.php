<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Crm_Note_Model
 *
 * @package HostCMS
 * @subpackage Crm_Project
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Crm_Project_Crm_Note_Model extends Core_Entity
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
		'crm_project' => array(),
		'crm_note' => array()
	);
}