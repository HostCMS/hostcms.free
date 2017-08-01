<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Vote_Shop_Item_Model
 *
 * @package HostCMS
 * @subpackage Vote
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Vote_Shop_Item_Model extends Core_Entity
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
		'shop_item' => array(),
		'vote' => array()
	);
}