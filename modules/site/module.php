<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site Module.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Site_Module extends Core_Module_Abstract
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
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/site/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/site/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}