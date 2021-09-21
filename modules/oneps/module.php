<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * 1PS Module.
 *
 * @package HostCMS
 * @subpackage 1PS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Oneps_Module extends Core_Module
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
	public $date = '2021-08-23';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'oneps';
	
	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 1,
				'ico' => 'fa fa-bell-o',
				'name' => Core::_('oneps.menu'),
				'href' => "/admin/oneps/index.php",
				'onclick' => "$.adminLoad({path: '/admin/oneps/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}