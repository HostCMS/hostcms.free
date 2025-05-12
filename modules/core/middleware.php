<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core_Middleware
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
abstract class Core_Middleware
{
	/**
	 * Handle
	 * @param Core_Command_Controller $oController
	 * @param callable $next
	 * @return callable
	 */
	abstract public function handle(Core_Command_Controller $oController, callable $next);
}