<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property Module.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var string
	 */
	public $date = '2026-05-12';

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