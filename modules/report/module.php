<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Report
 *
 * @package HostCMS
 * @subpackage Report
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
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
	 * @var date
	 */
	public $date = '2025-04-04';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'report';

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
				'ico' => 'fa fa-line-chart',
				'name' => Core::_('Report.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/report/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/report/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}