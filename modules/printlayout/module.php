<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout Module.
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Module extends Core_Module
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
	public $date = '2022-08-05';

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
				'ico' => 'fa fa-file-word-o',
				'name' => Core::_('Printlayout.menu'),
				'href' => "/admin/printlayout/index.php",
				'onclick' => "$.adminLoad({path: '/admin/printlayout/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}