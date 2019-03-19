<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * GUID helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Guid
{
	/**
	 * Generate GUID
	 * @return string
	 */
	static public function get()
	{
		$sGuid = strtoupper(md5(uniqid(rand(), TRUE)));
		$separator = chr(45);

		return substr($sGuid, 0, 8) . $separator .
			substr($sGuid, 8, 4) . $separator .
			substr($sGuid, 12, 4) . $separator .
			substr($sGuid, 16, 4) . $separator .
			substr($sGuid, 20, 12);
	}
}
