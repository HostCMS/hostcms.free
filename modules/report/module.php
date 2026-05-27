<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Report
 *
 * @package HostCMS
 * @subpackage Report
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Report_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var string
	 */
	public $date = '2026-05-12';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'report';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		Core_Skin::instance()
			->addCss('/modules/report/assets/report.css');
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 3,
				'ico' => 'fa-solid fa-line-chart',
				'name' => Core::_('Report.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/report/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/report/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}