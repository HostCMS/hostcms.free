<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shortcode.
 *
 * @package HostCMS
 * @subpackage Shortcode
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shortcode_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'shortcode';

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
				'ico' => 'fa-regular fa-file-code',
				'name' => Core::_('Shortcode.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/shortcode/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/shortcode/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}