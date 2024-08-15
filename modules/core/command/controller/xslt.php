<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Command_Controller_Xslt extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Template_Not_Found.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Template_Not_Found.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response
			->status(503)
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS');

		$title = Core::_('Core.hosting_mismatch_system_requirements');

		ob_start();
		$oSkin = Core_Skin::instance()
			->title($title)
			->setMode('authorization')
			->header();

		Core_Html_Entity::factory('Div')
			->class('indexMessage')
			->add(
				Core_Html_Entity::factory('Div')
					->add(Core_Html_Entity::factory('H1')->value($title))
					->add(Core_Html_Entity::factory('P')->value(Core::_('Core.requires_php5')))
					->add(Core_Html_Entity::factory('P')->value(Core::_('Core.list_tested_hosting')))
			)
			->execute();

		$oSkin->footer();

		$oCore_Response->body(ob_get_clean());

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}