<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Market Module.
 *
 * @package HostCMS
 * @subpackage Market
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Market_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'market';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 150,
				'block' => 3,
				'ico' => 'fa fa-cogs',
				'name' => Core::_('Market.menu'),
				'href' => "/admin/market/index.php",
				'onclick' => "$.adminLoad({path: '/admin/market/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}