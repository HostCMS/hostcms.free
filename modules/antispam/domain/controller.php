<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam domain controller
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Domain_Controller extends Core_Servant_Properties
{
	/**
	 * Check e-mail
	 * @param string $email e-mail
	 * @return boolean TRUE - not SPAM, FALSE - SPAM
	 */
	static public function checkEmail($email)
	{
		$email = trim(strval($email));

		if (strlen($email))
		{
			$aTmp = explode('@', $email, 2);

			if (isset($aTmp[1]))
			{
				return self::check($aTmp[1]);
			}
		}

		return TRUE;
	}

	/**
	 * Check domain
	 * @param string $domain
	 * @return boolean TRUE - not SPAM, FALSE - SPAM
	 */
	static public function check($domain)
	{
		$oAntispam_Domain = Core_Entity::factory('Antispam_Domain')->getByDomain($domain);

		return is_null($oAntispam_Domain);
	}
}