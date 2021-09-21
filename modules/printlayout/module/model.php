<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Module_Model
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Module_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'printlayout_module';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'printlayout' => array(),
		'module' => array()
	);
	
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
		'printlayout_modules.type' => 'ASC',
	);
}