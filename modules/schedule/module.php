<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule Module.
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2017-12-25';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'schedule';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

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
	}
}