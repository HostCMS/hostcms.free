<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site Module.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Module extends Core_Module
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
	public $date = '2020-01-31';

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
				'ico' => 'fa fa-globe',
				'name' => Core::_('Site.menu'),
				'href' => "/admin/site/index.php",
				'onclick' => "$.adminLoad({path: '/admin/site/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}