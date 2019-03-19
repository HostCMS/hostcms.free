<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module Module.
 *
 * @package HostCMS
 * @subpackage Module
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Module_Module extends Core_Module
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
	public $date = '2019-03-12';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'module';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 220,
				'block' => 3,
				'ico' => 'fa fa-puzzle-piece',
				'name' => Core::_('Module.menu'),
				'href' => "/admin/module/index.php",
				'onclick' => "$.adminLoad({path: '/admin/module/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}