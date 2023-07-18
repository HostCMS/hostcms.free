<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP addresses.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Cache name
	 * @var string
	 */
	protected $_cacheName = 'ipaddresses';

	/**
	 * Check is IP blocked in Frontend
	 * @param mixed $ip array of IPs or IP
	 * @return boolean
	 */
	public function isBlocked($aIp)
	{
		!is_array($aIp) && $aIp = array($aIp);

		$bCache = Core::moduleIsActive('cache');

		if ($bCache)
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$aIpaddresses = $oCore_Cache->get('deny_access', $this->_cacheName);
		}
		else
		{
			$aIpaddresses = NULL;
		}

		$bNeedsUpdate = !is_array($aIpaddresses);

		if ($bNeedsUpdate)
		{
			//$aIpaddresses = Core_Entity::factory('Ipaddress')->getAllBydeny_access(1, FALSE);
			$aIpaddresses = Core_QueryBuilder::select('ip')
				->from('ipaddresses')
				->where('deny_access', '=', 1)
				->where('deleted', '=', 0)
				->execute()->asAssoc()->result();
		}

		$bBlocked = FALSE;
		foreach ($aIp as $ip)
		{
			foreach ($aIpaddresses as $aIpaddress)
			{
				$bBlocked = $this->ipCheck($ip, $aIpaddress['ip']);

				if ($bBlocked)
				{
					break 2;
				}
			}
		}

		$bCache && $bNeedsUpdate
			&& $oCore_Cache->set('deny_access', $aIpaddresses, $this->_cacheName);

		return $bBlocked;
	}

	/**
	 * Check is IP blocked in Backend
	 * @param mixed $aIp array of IPs or IP
	 * @return boolean
	 */
	public function isBackendBlocked($aIp)
	{
		!is_array($aIp) && $aIp = array($aIp);

		$bBlocked = FALSE;

		$aIpaddresses = Core_Entity::factory('Ipaddress')->getAllBydeny_backend(1, FALSE);

		foreach ($aIp as $ip)
		{
			foreach ($aIpaddresses as $oIpaddress)
			{
				$bBlocked = $this->ipCheck($ip, $oIpaddress->ip);

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
		// No subnet, check direct
		if (strpos($cidr, '/') === FALSE)
		{
			return $ip == $cidr;
		}
		
		// IPv4 with subnet
		if (strpos($cidr, ':') === FALSE)
		{
			list($sNet, $iMask) = explode('/', $cidr);

			if (!is_numeric($iMask) || $iMask > 32)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('Ipaddress: Wrong mask: ' . $cidr);

				return FALSE;
			}

			$iIpMask = ~((1 << (32 - $iMask)) - 1);

			return (ip2long($ip) & $iIpMask) == ip2long($sNet);
		}
		
		// IPv6 with subnet
		return FALSE;
	}

	/**
	 * Clear ipaddresses cache
	 * @return self
	 */
	public function clearCache()
	{
		// Clear cache
		if (Core::moduleIsActive('cache'))
		{
			$cacheName = 'ipaddresses';
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$oCore_Cache->delete('deny_access', $cacheName);
		}

		return $this;
	}
}