<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Constants Module.
 *
 * @package HostCMS
 * @subpackage Constant
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Constant_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'constant';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 250,
				'block' => 3,
				'ico' => 'fa fa-wrench',
				'name' => Core::_('constant.menu'),
				'href' => "/admin/constant/index.php",
				'onclick' => "$.adminLoad({path: '/admin/constant/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}