<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site Module.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Site_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'site';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 140,
				'block' => 3,
				'ico' => 'fa-solid fa-globe',
				'name' => Core::_('Site.menu'),
				'href' => "/admin/site/index.php",
				'onclick' => "$.adminLoad({path: '/admin/site/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}