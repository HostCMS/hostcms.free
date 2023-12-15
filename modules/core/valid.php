<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Valid helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Valid
{
	/**
	 * Checks if $email is valid E-mail address
	 * @param string $email E-mail
	 * @return boolean
	 */
	static public function email($email)
	{
		return is_string($email)
			? preg_match("/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/Du", $email) > 0
			: FALSE;
	}

	/**
	 * Checks if $ip is valid IPv4 or IPv6
	 * @param string $ip IP
	 * @return boolean
	 */
	static public function ip($ip)
	{
		return is_string($ip)
			? filter_var($ip, FILTER_VALIDATE_IP) !== FALSE
			: FALSE;
	}

	/**
	 * Checks if $url is valid URL
	 * @param string $ip URL
	 * @return boolean
	 */
	static public function url($url)
	{
		return is_string($url)
			? preg_match("/^(?:https?|ftp):\/\/[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]$/i", $url) > 0
			: FALSE;
	}

	/**
	 * Checks if $host is valid host
	 * @param string $host host
	 * @return boolean
	 */
	static public function host($host)
	{
		return is_string($host)
			? preg_match("/^[-a-z0-9.]*(:[0-9]{1,5})?$/i", $host) > 0
			: FALSE;
	}
}