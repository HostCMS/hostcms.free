<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Valid helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Valid
{
	/**
	 * Checks if $email is valid E-mail address
	 * @param string $email E-mail
	 * @return int
	 */
	static public function email($email)
	{
		return preg_match("/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/Du", $email) > 0;
	}

	/**
	 * Checks if $ip is valid IP-address
	 * @param string $ip IP
	 * @return int
	 */
	static public function ip($ip)
	{
		return preg_match('/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/u', $ip);
	}
}