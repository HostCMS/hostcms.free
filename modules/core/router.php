<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Routers
 *
 * <code>
 * // Add robots.txt route
 * Core_Router::add('robots.txt', '/robots.txt')
 * 	->controller('Core_Command_Controller_Robots');
 *
 * // Add news route
 * Core_Router::add('news', '/news/({path})(page-{page}/)(tag/{tag}/)')
 * 	->controller('Core_Command_Controller_News');
 * </code>
 * 
 * <code>
 * // Resolve route for URI $uri
 * Core_Router::factory(Core::$url['path'])
 * 	->execute()
 * 	->compress()
 * 	->sendHeaders()
 * 	->showBody();
 * </code>
 * 
 * @package HostCMS
 * @subpackage Core\Router
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Router
{
	/** 
	 * List of routes
	 * @var array
	 */
	static protected $_routes = array();

	/**
	 * Add route Core_Router_Route with name $routeName for URI with routing rules $uri
	 * @param $routeName Name of route
	 * @param $uri URI pattern
	 * @param array $expressions list of expressions
	 * @return Core_Router_Route
	 */
	static public function add($routeName, $uri = NULL, $expressions = array())
	{
		return self::$_routes[$routeName] = new Core_Router_Route($uri, $expressions);
	}

	/**
	 * Resolve route for URI $uri
	 * @param string $uri URI
	 * @return Core_Router_Route
	 */
	static public function factory($uri)
	{
		foreach (self::$_routes as $routeName => $oCore_Router_Route)
		{
			if ($oCore_Router_Route->check($uri))
			{
				return $oCore_Router_Route->setUri($uri);
			}
		}

		$oCore_Response = new Core_Response();
		$oCore_Response
			->status(503)
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS')
			->sendHeaders();
		
		throw new Core_Exception("Unroutable URI '%uri'.", array('%uri' => $uri), NULL, FALSE);
	}
}