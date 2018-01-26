<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib Module.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lib_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-01-26';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'lib';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 90,
				'block' => 0,
				'ico' => 'fa fa-briefcase',
				'name' => Core::_('lib.menu'),
				'href' => "/admin/lib/index.php",
				'onclick' => "$.adminLoad({path: '/admin/lib/index.php'}); return false"
			)
		);
	}
}