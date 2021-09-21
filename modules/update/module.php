<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update Module.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Module extends Core_Module
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
	public $date = '2021-05-25';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'update';
	
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
				'ico' => 'fa fa-refresh',
				'name' => Core::_('Update.menu'),
				'href' => "/admin/update/index.php",
				'onclick' => "$.adminLoad({path: '/admin/update/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}