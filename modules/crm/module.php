<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Module.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Module extends Core_Module
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
	public $date = '2021-12-03';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'crm';
}