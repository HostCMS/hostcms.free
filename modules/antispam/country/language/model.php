<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam_Country_Language_Model
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Antispam_Country_Language_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'antispam_country' => array(),
		'admin_language' => array(),
	);
	
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'antispam_country_language';
}