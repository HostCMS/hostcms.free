<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP addresses.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check is IP blocked in Frontend
	 * @param mixed $ip array of IPs or IP
	 * @return boolean
	 */
	public function isBlocked($ip)
	{
		!is_array($ip) && $ip = array($ip);

		$bBlocked = FALSE;

		$aIpaddresses = Core_Entity::factory('Ipaddress')->getAllBydeny_access(1, FALSE);

		foreach ($ip as $sIp)
		{
			foreach ($aIpaddresses as $oIpaddress)
			{
				$bBlocked = strpos($oIpaddress->ip, '/') === FALSE
					? $sIp == $oIpaddress->ip
					: $this->ipCheck($sIp, $oIpaddress->ip);

				if ($bBlocked)
				{
					break 2;
				}
			}
		}

		return $bBlocked;
	}

	/**
	 * Check is IP blocked in Backend
	 * @param mixed $ip array of IPs or IP
	 * @return boolean
	 */
	public function isBackendBlocked($ip)
	{
		!is_array($ip) && $ip = array($ip);

		$bBlocked = FALSE;

		$aIpaddresses = Core_Entity::factory('Ipaddress')->getAllBydeny_backend(1, FALSE);

		foreach ($ip as $sIp)
		{
			foreach ($aIpaddresses as $oIpaddress)
			{
				$bBlocked = strpos($oIpaddress->ip, '/') === FALSE
					? $sIp == $oIpaddress->ip
					: $this->ipCheck($sIp, $oIpaddress->ip);

				if ($bBlocked)
				{
					break 2;
				}
			}
		}

		return $bBlocked;
	}
	
	/**
	 * Check IP in CIDR
	 * @param string $ip IP
	 * @param strin $cidr CIDR (Classless Inter-Domain Routing)
	 * @return boolean
	 */
	public function ipCheck($ip, $cidr)
	{
		list($sNet, $iMask) = explode('/', $cidr);
		$iIpMask = ~((1 << (32 - $iMask)) - 1);

		return (ip2long($ip) & $iIpMask) == ip2long($sNet);
	}
}