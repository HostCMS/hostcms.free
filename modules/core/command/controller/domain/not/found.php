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
class Core_Command_Controller_Domain_Not_Found extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Domain_Not_Found.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Domain_Not_Found.onAfterShowAction
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

		$domain = htmlspecialchars(Core::$url['host']);

		$title = "Необходимо добавить домен {$domain} в список поддерживаемых системой управления сайтом HostCMS!";

		ob_start();
		$oSkin = Core_Skin::instance()
			->title($title)
			->setMode('authorization')
			->header();

		Core::factory('Core_Html_Entity_Div')
			->class('indexMessage')
			->add(Core::factory('Core_Html_Entity_H1')->value($title))
			->add(Core::factory('Core_Html_Entity_P')->value(
				'Домен <b>'. $domain.'</b> необходимо добавить в список поддерживаемых системой управления сайтом <b>HostCMS</b>!'
			))
			->add(Core::factory('Core_Html_Entity_P')->value(
				'Для добавления домена перейдите в <b><a href="/admin/site/index.php" target="_blank">"Раздел администрирования" → "Система" → "Сайты"</a></b>.'
			))
			->add(Core::factory('Core_Html_Entity_P')->value(
				'Выберите пиктограмму <b>"Домены"</b> для требуемого сайта. На открывшейся странице нажмите на ссылку <b>"Домен"</b> → <b>"Добавить"</b>.'
			))
			->execute();

		$oSkin->footer();

		$oCore_Response->body(ob_get_clean());

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}