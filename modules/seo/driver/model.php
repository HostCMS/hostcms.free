<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Driver_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Driver_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'seo_driver';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
	
	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'seo_drivers.name' => 'ASC',
	);
}