<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Favicon extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Favicon.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Favicon.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oSite = Core_Entity::factory('Site')->getByAlias(Core::$url['host']);

		if ($oSite && $oSite->favicon != '')
		{
			$faviconPath = $oSite->getFaviconPath();

			$oCore_Response
				->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
				->header('X-Powered-By', 'HostCMS');

			if (is_readable($faviconPath))
			{
				$oCore_Response
					->status(200)
					->header('Content-Type', Core_Mime::getFileMime($faviconPath))
					->body(Core_File::read($faviconPath));
			}
			else
			{
				$oCore_Response->status(404);
			}
		}
		else
		{
			$oCore_Response->status(404);
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}