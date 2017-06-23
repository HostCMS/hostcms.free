<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Rating_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Rating_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'seo_rating';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'seo_site' => array()
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}