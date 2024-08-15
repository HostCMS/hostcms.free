<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * MD5
 *
 * @package HostCMS
 * @subpackage Core\Hash
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Hash_Md5 extends Core_Hash
{
	/**
	 * Calculate hash
	 * @param string $value
	 * @return string
	 */
	public function hash($value)
	{
		return md5($value . $this->_salt);
	}
}