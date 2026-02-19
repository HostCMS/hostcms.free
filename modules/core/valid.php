<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Valid helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	 * Checks if $ip is valid IPv4
	 * @param string $ip IP
	 * @return boolean
	 */
	static public function ipv4($ip)
	{
		return is_string($ip)
			? filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== FALSE
			: FALSE;
	}
	
	/**
	 * Checks if $ip is valid IPv6
	 * @param string $ip IP
	 * @return boolean
	 */
	static public function ipv6($ip)
	{
		return is_string($ip)
			? filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE
			: FALSE;
	}

	/**
	 * Checks if $ip is local IPv4
	 * @param string $ip IP
	 * @return boolean
	 */
	static public function localIpv4($ip)
	{
		return version_compare($ip, "192.168.0.0", ">") && version_compare($ip, "192.168.255.255", "<")
		|| version_compare($ip, "172.16.0.0", ">") && version_compare($ip, "172.31.255.255", "<")
		|| version_compare($ip, "10.0.0.0", ">") && version_compare($ip, "10.255.255.255", "<");
	}

    /**
     * Checks if $url is valid URL
     * @param string $url
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