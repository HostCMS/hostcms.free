<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Valid helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		return preg_match('/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/', $ip);
	}
	
	/**
	 * Checks if $url is valid URL
	 * @param string $ip URL
	 * @return int
	 */
	static public function url($url)
	{
		return preg_match("/^(?:https?|ftp):\/\/[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]$/i", $url);
	}
	
	/**
	 * Checks if $host is valid host
	 * @param string $host host
	 * @return int
	 */
	static public function host($host)
	{
		return preg_match("/^[-a-z0-9.]*(:[0-9]{1,5})?$/i", $host);
	}
}