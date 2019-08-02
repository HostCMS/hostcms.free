<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		if ($oSite)
		{
			$ico_path = $oSite->getIcoFilePath();
			$png_path = $oSite->getPngFilePath();

			$oCore_Response
				->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
				->header('X-Powered-By', 'HostCMS');

			if (is_readable($ico_path))
			{
				$oCore_Response
					->status(200)
					->header('Content-Type', 'image/x-icon')
					->body(Core_File::read($ico_path));
			}
			elseif (is_readable($png_path))
			{
				$oCore_Response
					->status(200)
					->header('Content-Type', 'image/png')
					->body(Core_File::read($png_path));
			}
			else
			{
				$oCore_Response
					->status(404);
			}
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