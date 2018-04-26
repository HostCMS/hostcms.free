<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin Forms Module.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.8';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-04-24';

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