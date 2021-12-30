<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Report
 *
 * @package HostCMS
 * @subpackage Report
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Report_Module extends Core_Module{	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-12-29';
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
	}}