<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * MD5
 *
 * @package HostCMS
 * @subpackage Core\Hash
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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