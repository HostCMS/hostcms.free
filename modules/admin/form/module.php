<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin Forms Module.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Admin_Form_Module extends Core_Module_Abstract
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
	public $date = '2025-04-04';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'admin_form';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 174,
				'block' => 3,
				'ico' => 'fa fa-table',
				'name' => Core::_('Admin_Form.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/admin_form/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/admin_form/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}