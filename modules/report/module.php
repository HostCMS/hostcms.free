<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Report
 *
 * @package HostCMS
 * @subpackage Report
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Report_Module extends Core_Module_Abstract
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
	public $date = '2024-07-09';

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
				'href' => "/admin/report/index.php",
				'onclick' => "$.adminLoad({path: '/admin/report/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}