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
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$sActionName = $this->_action . 'Action';
		return $oController->$sActionName();
	}
}