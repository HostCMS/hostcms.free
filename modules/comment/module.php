<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment Module.
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Comment_Module extends Core_Module_Abstract
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
	public $date = '2024-06-06';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'comment';

	protected $_options = array(
		'gradeStep' => array(
			'type' => 'int',
			'default' => 1
		),
		'gradeLimit' => array(
			'type' => 'int',
			'default' => 5
		)
	);
}