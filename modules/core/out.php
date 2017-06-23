<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract core out, e.g. file or string
 *
 * @package HostCMS
 * @subpackage Core\Out
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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