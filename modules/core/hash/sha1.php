<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sha1
 *
 * @package HostCMS
 * @subpackage Core\Hash
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Hash_Sha1 extends Core_Hash
{
	/**
	 * Calculate hash
	 * @param string $value
	 * @return string
	 */
	public function hash($value)
	{
		$sha1 = sha1($value . $this->_salt);

		for($i = 0; $i < 5; $i++)
		{
			$sha1 .= substr($sha1, hexdec(substr($sha1, $i, 1)), 1);
		}
		return sha1($sha1);
	}
}