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
class Core_Command_Controller_Access_Forbidden extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 */
	public function showAction()
	{
		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response
			->status(403)
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS');

		$title = Core::_('Core.title_no_access_to_page');

		ob_start();
		$oSkin = Core_Skin::instance()
			->title($title)
			->setMode('authorization')
			->header();

		Core::factory('Core_Html_Entity_Div')
			->class('indexMessage')
			->add(Core::factory('Core_Html_Entity_H1')->value($title))
			->add(Core::factory('Core_Html_Entity_P')->value(
				'<p>' . Core::_('Core.message_more_info') . '</p>'
			))
			->execute();

		$oSkin->footer();

		$oCore_Response->body(ob_get_clean());

		return $oCore_Response;
	}
}