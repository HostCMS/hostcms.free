<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark.
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Benchmark_Controller_Check extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$oBenchmark = Core_Entity::factory('Benchmark');

		$oBenchmark_Controller = Benchmark_Controller::instance();

		$oBenchmark
			->site_id(CURRENT_SITE)
			->mysql_write($oBenchmark_Controller->writeTable())
			->mysql_read($oBenchmark_Controller->readTable())
			->mysql_update($oBenchmark_Controller->changeTable())
			->filesystem($oBenchmark_Controller->fileSystemTest())
			->cpu_math($oBenchmark_Controller->cpuMathTest())
			->cpu_string($oBenchmark_Controller->cpuStringTest())
			->network($oBenchmark_Controller->networkDownloadTest())
			->mail($oBenchmark_Controller->mailTest())
			->save();

		return FALSE;
	}
}