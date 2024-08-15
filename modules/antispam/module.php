<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam Module.
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Antispam_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'antispam';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 200,
				'block' => 3,
				'ico' => 'fa fa-ban',
				'name' => Core::_('Antispam.menu'),
				'href' => "/admin/antispam/index.php",
				'onclick' => "$.adminLoad({path: '/admin/antispam/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}