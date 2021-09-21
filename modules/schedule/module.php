<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule Module.
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Module extends Core_Module
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
	public $date = '2021-08-23';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'schedule';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 100,
				'block' => 0,
				'ico' => 'fa fa-calendar-check-o',
				'name' => Core::_('Schedule.menu'),
				'href' => "/admin/schedule/index.php",
				'onclick' => "$.adminLoad({path: '/admin/schedule/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}