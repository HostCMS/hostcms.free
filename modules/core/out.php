<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract core out, e.g. file or string
 *
 * @package HostCMS
 * @subpackage Core\Out
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
abstract class Core_Out extends Core_Servant_Properties
{
	/**
	 * Open out
	 */
	abstract public function open();
	
	/**
	 * Write in out
	 * @param string $content
	 */
	abstract public function write($content);
	
	/**
	 * Close out
	 */
	abstract public function close();
}