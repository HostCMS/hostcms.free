<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Useragent_Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Useragent_Controller
{
	/**
	 * Get user agent conditions
	 * @return array
	 */
	static public function getConditions()
	{
		return array(
			Core::_('Ipaddress_Useragent.condition0'),
			Core::_('Ipaddress_Useragent.condition1'),
			Core::_('Ipaddress_Useragent.condition2'),
			Core::_('Ipaddress_Useragent.condition3'),
			Core::_('Ipaddress_Useragent.condition4')
		);
	}

	/**
	 * Check user agent is blocked
	 * @return boolean
	 */
	static public function isBlocked()
	{
		$bReturn = FALSE;

		$userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');

		$aIpaddress_Useragents = Core_Entity::factory('Ipaddress_Useragent')->getAllByActive(1, FALSE);

		foreach ($aIpaddress_Useragents as $oIpaddress_Useragent)
		{
			switch ($oIpaddress_Useragent->condition)
			{
				case 0:
					$bReturn = $userAgent == $oIpaddress_Useragent->useragent;
				break;
				case 1:
					$bReturn = $userAgent != $oIpaddress_Useragent->useragent;
				break;
				case 2:
					$bReturn = mb_strpos($userAgent, $oIpaddress_Useragent->useragent) !== FALSE;
				break;
				case 3:
					$bReturn = mb_strpos($userAgent, $oIpaddress_Useragent->useragent) === 0;
				break;
				case 4:
					$bReturn = mb_strpos($userAgent, $oIpaddress_Useragent->useragent) === (mb_strlen($userAgent) - mb_strlen($oIpaddress_Useragent->useragent));
				break;
			}

			if ($bReturn)
			{
				// фильтр сработал
				break;
			}
		}

		return $bReturn;
	}
}