<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Std core out
 *
 * <code>
 * $oCore_Out_Std = new Core_Out_Std();
 * $oCore_Out_Std
 * 	->open()
 * 	->write('content 1')
 * 	->write('content 2')
 * 	->write('content 3')
 * 	->close();
 * </code>
 * @package HostCMS
 * @subpackage Core\Out
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Out_Std extends Core_Out
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array();

	/**
	 * Open stream
	 */
	public function open() {}

	/**
	 * Write into stream
	 * @param string $content content
	 * @return self
	 */
	public function write($content)
	{
		echo $content;
		return $this;
	}

	/**
	 * Close stream
	 */
	public function close() {}
}