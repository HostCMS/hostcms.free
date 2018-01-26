<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Sitemap extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Sitemap.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Sitemap.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);
		
		$oSite = Core_Entity::factory('Site')->getByAlias(Core::$url['host']);

		Core_Session::close();

		if ($oSite)
		{
			Core::$url['path'] = '/sitemap/';

			$oCore_Response
				->status(200);

			header('Content-Type: text/xml');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
			header('X-Powered-By: ' . Core::xPoweredBy());

			Core_Router::factory(Core::$url['path'])
				->execute() // consist exit()
				/*->header('Content-Type', 'text/xml')
				->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
				->header('X-Powered-By', Core::xPoweredBy())
				->compress()*/
				->sendHeaders()
				->showBody();

				die();
		}
		else
		{
			$oCore_Response
				->status(404);
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}