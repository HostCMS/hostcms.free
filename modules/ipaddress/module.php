<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP Address Module.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.9';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2020-08-04';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'ipaddress';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
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

		return parent::getMenu();
	}
}