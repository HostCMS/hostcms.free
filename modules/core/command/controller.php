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
class Core_Command_Controller
{
	/**
	 * URI
	 * @var string|null
	 */
	protected $_uri = NULL;

	/**
	 * Set URI
	 * @param string $uri URI
	 * @return Core_Command_Controller
	 */
	public function setUri($uri)
	{
		$this->_uri = $uri;
		return $this;
	}
	
	/**
	 * Get URI
	 * @param string $uri URI
	 * @return Core_Command_Controller
	 */
	public function getUri()
	{
		return $this->_uri;
	}
}