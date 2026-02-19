<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout Module.
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Printlayout_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'printlayout';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 60,
				'block' => 1,
				'ico' => 'fa-regular fa-file-word',
				'name' => Core::_('Printlayout.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/printlayout/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/printlayout/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}