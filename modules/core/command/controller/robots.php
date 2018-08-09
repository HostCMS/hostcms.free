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
class Core_Command_Controller_Robots extends Core_Command_Controller
{
	/**
	 * Robots
	 */
	protected $_robots = NULL;

	/**
	 * Get robots.txt content
	 * @return string|NULL
	 */
	public function getRobots()
	{
		return $this->_robots;
	}

	/**
	 * Set robots.txt content
	 * @param string
	 * @return self
	 */
	public function setRobots($robots)
	{
		$this->_robots = $robots;
		return $this;
	}

	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Robots.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Robots.onAfterShowAction
	 */
	public function showAction()
	{
		$oSite = Core_Entity::factory('Site')->getByAlias(Core::$url['host']);

		!is_null($oSite)
			&& $this->setRobots($oSite->robots);

		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();
		
		Core_Page::instance()
			->response($oCore_Response);
		
		$oCore_Response->header('X-Powered-By', 'HostCMS');

		$content = $this->getRobots();

		if (!is_null($content))
		{
			$oCore_Response
				->status(200)
				->header('Content-Type', "text/plain; charset={$oSite->coding}")
				->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
				->body($content);
		}
		else
		{
			$oCore_Response->status(404);
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}