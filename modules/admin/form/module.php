<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin Forms Module.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Module extends Core_Module_Abstract
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
				'href' => "/admin/admin_form/index.php",
				'onclick' => "$.adminLoad({path: '/admin/admin_form/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}