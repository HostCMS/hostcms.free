<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core Module
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Module extends Core_Module_Abstract
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
	public $date = '2024-07-09';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'core';
}