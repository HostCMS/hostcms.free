<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Routes
 *
 * <code>
 * $oCore_Router_Route = new Core_Router_Route('/news/({path})(page-{page}/)(tag/{tag}/)');
 * $oCore_Router_Route->setUri('/news/page-17/')
 * 	->execute()
 * 	->compress()
 * 	->sendHeaders()
 * 	->showBody();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Router
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Router_Route
{
	/**
	 * Controller name
	 * @var string
	 */
	protected $_controller = 'Core_Command_Controller_Default';

	/**
	 * Set controller name
	 * @param string $controllerName
	 * @return Core_Router_Route
	 */
	public function controller($controllerName)
	{
		$this->_controller = $controllerName;
		return $this;
	}

	/**
	 * Action name
	 * @var string
	 */
	protected $_action = 'show';

	/**
	 * Set action name
	 * @param string $actionName
	 * @return Core_Router_Route
	 */
	public function action($actionName)
	{
		$this->_action = $actionName;
		return $this;
	}

	/**
	 * URI pattern
	 * @var string
	 */
	protected $_uriPattern = NULL;

	/**
	 * Preg pattern
	 * @var string
	 */
	protected $_pregPattern = NULL;

	/**
	 * Constructor.
	 * @param string $uriPattern URI pattern. Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
	 * @param array $expressions list of expressions
	 */
	public function __construct($uriPattern = NULL, array $expressions = array())
	{
		// skip first '/'
		//$this->_uriPattern = ltrim($uriPattern, '/'); // 6.7.7
		$this->_uriPattern = $uriPattern;

		// If an opening parenthesis is followed by "?:", the subpattern does not do any capturing, and is not counted when computing the number of any subsequent capturing subpatterns.
		// Subpattern will be indexed in the matches array by its normal numeric position and also by name
		$expression = str_replace(
			array('\{', '\}', '\(', '\)', '(?:)'), // (?:) - any question => (?:[^\)]*)
			//array("(?'", "'.*?)", '(?:', ')?', '(?:[^\)]*)'),
			array("(?'", "'.*?)", '(?:', ')?', '(?:.*?)'),
			preg_quote($this->_uriPattern, '/')
		);

		foreach ($expressions as $name => $regex)
		{
			$name = preg_quote($name, '/');
			//$regex = preg_quote($regex, '/');
			$expression = str_replace("(?'{$name}'.*?)", "(?'{$name}'{$regex})", $expression);
		}

		// http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
		// D - dollar metacharacter in the pattern matches only at the end of the subject string
		// u - pattern strings are treated as UTF-8
		// s - a dot metacharacter in the pattern matches all characters, including newlines
		$this->_pregPattern = '/^' . $expression . '$/Dsu';
	}

	/**
	 * Apply pattern
	 * @param string $uri URI
	 * @return mixed array with data from URI or NULL
	 */
	public function applyPattern($uri)
	{
		// skip first '/'
		//$uri = ltrim($uri, '/'); // 6.7.7

		$result = preg_match($this->_pregPattern, $uri, $matches);

		return $result
			? $matches
			: FALSE;
	}

	/**
	 * Array of columns will be set for controller object
	 * @var array
	 */
	protected $_controllerColumns = array();

	/**
	 * Check URI
	 * @param string $uri URI
	 * @return bool
	 */
	public function check($uri)
	{
		$matches = $this->applyPattern($uri);

		if (!$matches)
		{
			return FALSE;
		}

		$this->_controllerColumns = array();
		foreach ($matches as $column => $value)
		{
			if (is_string($column))
			{
				$this->_controllerColumns[$column] = $value;

				// Change default action
				if ($column == 'action')
				{
					$this->action($value);
				}
			}
		}

		return TRUE;
	}

	/**
	 * URI
	 * @var string
	 */
	protected $_uri = NULL;

	/**
	 * Set URI
	 * @param string $uri URI
	 * @return Core_Router_Route
	 */
	public function setUri($uri)
	{
		$this->_uri = $uri;
		return $this;
	}

	/**
	 * Middleware stack
	 * @var array
	 */
	protected $_middlewares = array();

	/**
	 * Add middleware to the route
	 * @param string $middleware
	 * @param callable|NULL $callable
	 * @return self
	 */
	public function middleware($middleware, $callable = NULL)
	{
		if (!isset($this->_middlewares[$middleware]))
		{
			is_null($callable) && $callable = $middleware;

			$oMiddleware = is_callable($callable) ? $callable : new $callable();

			$this->_middlewares[$middleware] = $oMiddleware;
		}

		return $this;
	}
	
	/**
	 * Add middleware to the route
	 * @param string $middleware
	 * @param callable|NULL $callable
	 * @return self
	 * @see middleware()
	 */
	public function addMiddleware($middleware, $callable = NULL)
	{
		return $this->middleware($middleware, $callable);
	}
	
	/**
	 * Prepend middleware to the route
	 * @param string $middleware
	 * @param callable|NULL $callable
	 * @return self
	 */
	public function prependMiddleware($middleware, $callable = NULL)
	{
		if (!isset($this->_middlewares[$middleware]))
		{
			is_null($callable) && $callable = $middleware;

			$oMiddleware = is_callable($callable) ? $callable : new $callable();

			$this->_middlewares = array($middleware => $oMiddleware) + $this->_middlewares;
		}

		return $this;
	}

	/**
	 * Exclude middleware to the route
	 * @param string $middleware
	 * @return self
	 */
	public function withoutMiddleware($middleware)
	{
		if (isset($this->_middlewares[$middleware]))
		{
			unset($this->_middlewares[$middleware]);
		}

		return $this;
	}
	
	/**
	 * Exclude middleware to the route
	 * @param string $middleware
	 * @return self
	 * @see withoutMiddleware()
	 */
	public function removeMiddleware($middleware)
	{
		return $this->withoutMiddleware($middleware);
	}

	/**
	 * Set controller columns and execute command controller method '{action}Action()'.
	 * Default action name is showAction()
	 * @return mixed expect Core_Response
	 */
	public function execute()
	{
		$sControllerName = $this->_controller;

		$oController = new $sControllerName();
		$oController->setUri($this->_uri);

		foreach ($this->_controllerColumns as $column => $value)
		{
			$oController->$column = $value;
		}

		// Создаем замыкание для вызова контроллера
		$controllerHandler = function() use ($oController) {
			$sActionName = $this->_action . 'Action';
			return $oController->$sActionName();
		};

		/*$actionName = $this->_action . 'Action';
		return $oController->$actionName();*/
		
		// Обертываем контроллер в middleware
		$next = $this->_wrapMiddleware($oController, $controllerHandler);
		
		// Выполняем middleware и контроллер
		return $next();
	}
	
	/**
	 * Wrap middleware around the controller handler
	 * @param object $oController
	 * @param callable $next
	 * @return callable
	 */
	protected function _wrapMiddleware($oController, $next)
	{
		foreach (array_reverse($this->_middlewares) as $middleware)
		{
			$next = function() use ($middleware, $oController, $next) {
				if ($middleware instanceof Core_Middleware)
				{
					// Если middleware — это объект класса Core_Middleware, вызываем его метод handle
					return $middleware->handle($oController, $next);
				}
				elseif (is_callable($middleware))
				{
					// Если middleware — это callable-функция, вызываем её
					return call_user_func($middleware, $oController, $next);
				}
				else
				{
					throw new Core_Exception("Core_Router_Route: Middleware must be callable or extend Core_Middleware.");
				}
			};
		}
		
		return $next;
	}
}