<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP addresses.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
	 * Cache for getDenyAccessIpaddresses()
	 * @var array|NULL
	 */
	protected $_DenyAccessIpaddresses = NULL;

	/**
	 * Get Deny Access Ipaddresses
	 * @return array
	 */
	public function getDenyAccessIpaddresses()
	{
		if (is_null($this->_DenyAccessIpaddresses))
		{
			$bCache = Core::moduleIsActive('cache');

			if ($bCache)
			{
				$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
				$this->_DenyAccessIpaddresses = $oCore_Cache->get('deny_access', $this->_cacheName);
			}
			else
			{
				$this->_DenyAccessIpaddresses = NULL;
			}

			if (!is_array($this->_DenyAccessIpaddresses))
			{
				$aIPs = Core_QueryBuilder::select('id', 'ip')
					->from('ipaddresses')
					->where('deny_access', '=', 1)
					->where('deleted', '=', 0)
					->execute()->asAssoc()->result();

				$this->_DenyAccessIpaddresses = array();
				foreach ($aIPs as $aIP)
				{
					$this->_DenyAccessIpaddresses[$aIP['id']] = array_map('trim', explode(',', $aIP['ip']));
				}

				$bCache
					&& $oCore_Cache->set('deny_access', $this->_DenyAccessIpaddresses, $this->_cacheName);
			}
		}

		return $this->_DenyAccessIpaddresses;
	}

	/**
	 * Check is IP blocked in Frontend
	 * @param mixed $ip array of IPs or IP
	 * @param bool $incBanned Increase blocked counter
	 * @return boolean
	 */
	public function isBlocked($aIp, $incBanned = TRUE)
	{
		!is_array($aIp) && $aIp = array($aIp);

		$aIpaddresses = $this->getDenyAccessIpaddresses();

		$bBlocked = FALSE;
		foreach ($aIp as $checkIp)
		{
			foreach ($aIpaddresses as $ipId => $aIpaddress)
			{
				foreach ($aIpaddress as $sIpaddress)
				{
					$bBlocked = $this->ipCheck($checkIp, $sIpaddress);

					if ($bBlocked)
					{
						$incBanned && $this->incIpaddressBanned($ipId);
						break 3;
					}
				}
			}
		}

		return $bBlocked;
	}

	/**
	 * Check is IP blocked in Backend
	 * @param mixed $aIp array of IPs or IP
	 * @param bool $incBanned Increase blocked counter
	 * @return boolean
	 */
	public function isBackendBlocked($aIp, $incBanned = TRUE)
	{
		!is_array($aIp) && $aIp = array($aIp);

		$bBlocked = FALSE;

		$aIpaddresses = Core_Entity::factory('Ipaddress')->getAllBydeny_backend(1, FALSE);

		foreach ($aIp as $checkIp)
		{
			foreach ($aIpaddresses as $oIpaddress)
			{
				$aIPs = array_map('trim', explode(',', $oIpaddress->ip));

				foreach ($aIPs as $sIP)
				{
					$bBlocked = $this->ipCheck($checkIp, $sIP);

					if ($bBlocked)
					{
						$incBanned && $this->incIpaddressBanned($oIpaddress->id);
						break 3;
					}
				}
			}
		}

		return $bBlocked;
	}
	
	/**
	 * Update banned fo Ipaddress
	 * @param int $id
	 */
	public function incIpaddressBanned($id)
	{
		Core_DataBase::instance()
			->setQueryType(2)
			->query("UPDATE `ipaddresses` SET `banned` = `banned` + 1 WHERE `id` = {$id}");
	}


	/**
	 * Cache for getNoStatisticIpaddresses()
	 * @var array|NULL
	 */
	protected $_NoStatisticIpaddresses = NULL;

	/**
	 * Get No Statistic Ipaddresses
	 * @return array
	 */
	public function getNoStatisticIpaddresses()
	{
		if (is_null($this->_NoStatisticIpaddresses))
		{
			$bCache = Core::moduleIsActive('cache');

			if ($bCache)
			{
				$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
				$this->_NoStatisticIpaddresses = $oCore_Cache->get('no_statistic', $this->_cacheName);
			}
			else
			{
				$this->_NoStatisticIpaddresses = NULL;
			}

			if (!is_array($this->_NoStatisticIpaddresses))
			{
				$aIPs = Core_QueryBuilder::select('id', 'ip')
					->from('ipaddresses')
					->where('no_statistic', '=', 1)
					->where('deleted', '=', 0)
					->execute()->asAssoc()->result();

				$this->_NoStatisticIpaddresses = array();
				foreach ($aIPs as $aIP)
				{
					$this->_NoStatisticIpaddresses[$aIP['id']] = array_map('trim', explode(',', $aIP['ip']));
				}

				$bCache
					&& $oCore_Cache->set('no_statistic', $this->_NoStatisticIpaddresses, $this->_cacheName);
			}
		}

		return $this->_NoStatisticIpaddresses;
	}


	/**
	 * Check Ignore IP in statistics
	 * @param mixed $aIp array of IPs or IP
	 * @return boolean
	 */
	public function isNoStatistic($aIp)
	{
		!is_array($aIp) && $aIp = array($aIp);

		$aIpaddresses = $this->getNoStatisticIpaddresses();

		$bNoStatistic = FALSE;
		foreach ($aIp as $checkIp)
		{
			foreach ($aIpaddresses as $ipId => $aIpaddress)
			{
				foreach ($aIpaddress as $sIpaddress)
				{
					$bNoStatistic = $this->ipCheck($checkIp, $sIpaddress);

					if ($bNoStatistic)
					{
						break 3;
					}
				}
			}
		}

		return $bNoStatistic;
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
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$oCore_Cache->delete('deny_access', $this->_cacheName);
			$oCore_Cache->delete('no_statistic', $this->_cacheName);
		}

		$this->_DenyAccessIpaddresses = NULL;

		return $this;
	}

	/**
	 * Check IP in CIDR
	 * @param string $ip IP
	 * @param string $cidr CIDR (Classless Inter-Domain Routing)
	 * <code>
	 * // IPv4
	 * $oIp_Controller = new Ipaddress_Controller();
	 * var_dump($oIp_Controller->ipCheck('86.111.222.10', '86.111.222.0/24'));
	 *
	 * // IPv6
	 * $oIp_Controller = new Ipaddress_Controller();
	 * var_dump($oIp_Controller->ipCheck('02aa:5680:ffff:ffff:ffff:ffff:ffff:ffff', '2aa:5680::/32'));
	 * </code>
	 * @return boolean
	 */
	public function ipCheck($ip, $cidr)
	{
		// No subnet, check direct
		if (strpos($cidr, '/') === FALSE)
		{
			return $ip == $cidr;
		}

		// Request IP is IPv4
		$bIpv4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== FALSE;

		// IPv6 with subnet
		$bCidrv6 = strpos($cidr, ':') !== FALSE;

		// IPv4 and $cidr with subnet and not IPv6
		if ($bIpv4 && !$bCidrv6)
		{
			list($address, $netmask) = explode('/', $cidr);

			if (!is_numeric($netmask) || $netmask > 32)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('Ipaddress: Wrong mask: ' . $cidr);

				return FALSE;
			}

			$iIpMask = ~((1 << (32 - $netmask)) - 1);

			return (ip2long($ip) & $iIpMask) == ip2long($address);
		}

		// IPv6 and $cidr with subnet and IPv6
		if (!$bIpv4 && $bCidrv6)
		{
			list($address, $netmask) = explode('/', $cidr);

			if ($netmask === '0')
			{
				return (bool) unpack('n*', @inet_pton($address));
			}

			if ($netmask < 1 || $netmask > 128)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('Ipaddress: Not a valid IPv6 preflen: ' . $cidr);

				return FALSE;
			}

			$bytesAddr = unpack('n*', @inet_pton($address));
			$bytesTest = unpack('n*', @inet_pton($ip));

			if (!$bytesAddr || !$bytesTest)
			{
				return FALSE;
			}

			$ceil = ceil($netmask / 16);
			for ($i = 1; $i <= $ceil; $i++)
			{
				$left = $netmask - 16 * ($i - 1);
				$left = ($left <= 16) ? $left : 16;
				$mask = ~(0xFFFF >> $left) & 0xFFFF;
				if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask))
				{
					return FALSE;
				}
			}

			return TRUE;
		}

		return FALSE;
	}
}