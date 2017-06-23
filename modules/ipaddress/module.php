<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP Address Module.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Module extends Core_Module
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
	public $date = '2017-06-14';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'ipaddress';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 3,
				'ico' => 'fa fa-link',
				'name' => Core::_('ipaddress.menu'),
				'href' => "/admin/ipaddress/index.php",
				'onclick' => "$.adminLoad({path: '/admin/ipaddress/index.php'}); return false"
			)
		);
	}
}