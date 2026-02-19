<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam Module.
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Antispam_Module extends Core_Module_Abstract
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
	public $date = '2026-02-10';

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
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/antispam/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/antispam/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}