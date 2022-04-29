<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Directory_Social_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Directory_Social_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_social' => array(),
		'company' => array()
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}