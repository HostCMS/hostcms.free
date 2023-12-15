<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property Module.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2023-07-17';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'property';

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'recursive_properties' => array(
			'type' => 'checkbox',
			'default' => TRUE
		),
		'add_list_items' => array(
			'type' => 'checkbox',
			'default' => TRUE
		)
	);
}