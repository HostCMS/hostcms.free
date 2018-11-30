<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark Module.
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Benchmark_Module extends Core_Module
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
	public $date = '2018-11-29';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'benchmark';

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
				'ico' => 'fa fa-dashboard',
				'name' => Core::_('benchmark.menu'),
				'href' => "/admin/benchmark/index.php",
				'onclick' => "$.adminLoad({path: '/admin/benchmark/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}