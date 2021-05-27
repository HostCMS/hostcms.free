<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP addresses.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	public function isBlocked($ip)
	{
		!is_array($ip) && $ip = array($ip);

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
		foreach ($ip as $key => $sIp)
		{
			foreach ($aIpaddresses as $aIpaddress)
			{
				$bBlocked = strpos($aIpaddress['ip'], '/') === FALSE
					? $sIp == $aIpaddress['ip']
					: $this->ipCheck($sIp, $aIpaddress['ip']);

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

		if (!is_numeric($iMask))
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write('Ipaddress: Wrong mask: ' . $cidr);

			return FALSE;
		}

		$iIpMask = ~((1 << (32 - $iMask)) - 1);

		return (ip2long($ip) & $iIpMask) == ip2long($sNet);
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