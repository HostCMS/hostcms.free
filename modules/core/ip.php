<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Ip
{
	/**
	 * Get IPv4 network, e.g. '11.22.0.0' for '11.22.5.6' and mask '255.255.0.0'
	 * @param string $ipv4 IPv4
	 * @param string $mask mask '255.255.255.0'
	 * @return string
	 */
	static public function ipv4Network($ipv4, $mask)
	{
		return long2ip(ip2long($ipv4) & ip2long($mask));
	}
	
	/**
	 * Get IPv4 broadcast, e.g. '11.22.255.255' for '11.22.5.6' and mask '255.255.0.0'
	 * @param string $ipv4 IPv4
	 * @param string $mask mask '255.255.255.0'
	 * @return string
	 */
	static public function ipv4Broadcast($ipv4, $mask)
	{
		return long2ip(ip2long($ipv4) | ~ip2long($mask));
	}
	
	/**
	 * Convert IPv4 or IPV6 into hexadecimal value
	 * @param string $ip IP
	 * @return string|NULL
	 */
	static public function ip2hex($ip)
	{
		return Core_Valid::ip($ip)
			? bin2hex(inet_pton($ip))
			: NULL;
	}

	/**
	 * Convert hexadecimal value into IPv4 or IPV6
	 * @param string $hex source value
	 * @return string
	 */
	static public function hex2ip($hex)
	{
		$hexLen = strlen($hex);
		
		return $hexLen == 8 || $hexLen == 32
			? inet_ntop(hex2bin($hex))
			: '';
	}
}