<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core benchmark controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Command_Controller_Benchmark extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Benchmark.onBeforeShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);
		
		$result = 'Error';

		if (defined('BENCHMARK_ENABLE') && BENCHMARK_ENABLE && Core::moduleIsActive('benchmark'))
		{
			Core_Entity::factory('Benchmark_Url')
				->structure_id(intval(Core_Array::getPost('structure_id', 0)))
				->waiting_time(intval(Core_Array::getPost('waiting_time', 0)))
				->load_page_time(intval(Core_Array::getPost('load_page_time', 0)))
				->dns_lookup(intval(Core_Array::getPost('dns_lookup', 0)))
				->connect_server(intval(Core_Array::getPost('connect_server', 0)))
				->save();

			$result = 'OK';

			// Clear old data
			if (rand(0, 999) == 0)
			{
				Benchmark_Controller::instance()->deleteOldUrlBenchmarks();
			}
		}

		$oCore_Response
			->status(200)
			->header('Pragma', 'no-cache')
			->header('Cache-Control', 'private, no-cache')
			->header('Vary', 'Accept')
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS')
			->body(json_encode($result));

		if (strpos(Core_Array::get($_SERVER, 'HTTP_ACCEPT', ''), 'application/json') !== FALSE)
		{
			$oCore_Response->header('Content-type', 'application/json; charset=utf-8');
		}
		else
		{
			$oCore_Response
				->header('X-Content-Type-Options', 'nosniff')
				->header('Content-type', 'text/plain; charset=utf-8');
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}