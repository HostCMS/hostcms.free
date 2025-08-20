<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Module.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2025-08-19';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'crm';
}