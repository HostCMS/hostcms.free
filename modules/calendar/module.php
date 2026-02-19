<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Module
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Calendar_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'calendar';

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'entityLimit' => array(
			'type' => 'int',
			'default' => 5
		)
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		Core_Skin::instance()->addJs('/modules/calendar/assets/calendar.js');

		if (Core_Auth::logged())
		{
			Core_Router::add('calendar-callback.php', '/calendar-callback.php')
				->controller('Calendar_Caldav_Command_Controller');
		}
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 180,
				'block' => 3,
				'ico' => 'fa fa-calendar',
				'name' => Core::_('Calendar.model_name'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/calendar/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/calendar/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}