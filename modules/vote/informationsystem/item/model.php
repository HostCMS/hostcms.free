<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Vote_Informationsystem_Item_Model
 *
 * @package HostCMS
 * @subpackage Vote
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Vote_Informationsystem_Item_Model extends Core_Entity
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
		'informationsystem_item' => array(),
		'vote' => array()
	);
}