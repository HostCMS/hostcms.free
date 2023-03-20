<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark Module.
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Benchmark_Module extends Core_Module
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
	public $date = '2023-03-01';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'benchmark';

	/**
	 * Module options
	 * @var array
	 */
	protected $_options = array(
		'database_table_name' => array(
			'type' => 'string',
			'default' => 'performance_test'
		),
		'database_write_query_count' => array(
			'type' => 'int',
			'default' => 10000
		),
		'database_read_query_count' => array(
			'type' => 'int',
			'default' => 10000
		),
		'database_change_query_count' => array(
			'type' => 'int',
			'default' => 10000
		),
		'sample_text' => array(
			'type' => 'string',
			'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
		),
		'files_count' => array(
			'type' => 'int',
			'default' => 1000
		),
		'math_count' => array(
			'type' => 'int',
			'default' => 100000
		),
		'string_count' => array(
			'type' => 'int',
			'default' => 1000
		),
		'benchmark_file_path' => array(
			'type' => 'string',
			'default' => 'http://www.hostcms.ru/download/benchmark/1mb'
		)
	);

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