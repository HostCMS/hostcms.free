<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Site_Closed extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Site_Closed.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Site_Closed.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		if ($oSite->closed)
		{
			return Core_Router::factory(
				Core_Entity::factory('Structure', $oSite->closed)->getPath()
			)
			->execute()->status(503);
		}

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response
			->status(503)
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS');

		$title = Core::_('Core.site_disabled_by_administrator', Core::$url['host']);

		ob_start();
		$oSkin = Core_Skin::instance()
			->title($title)
			->setMode('authorization')
			->header();

		Core_Html_Entity::factory('Div')
			->class('indexMessage')
			->add(Core_Html_Entity::factory('H1')->value($title))
			->add(Core_Html_Entity::factory('P')->value(Core::_('Core.site_activation_instruction')
			))
			->execute();

		$oSkin->footer();

		$oCore_Response->body(ob_get_clean());

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}