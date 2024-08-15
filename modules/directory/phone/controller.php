<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Phone_Controller
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Phone_Controller
{
	/**
	 * Cache
	 * @var mixed
	 */
	static protected $_cache = NULL;

	/**
	 * Get formats
	 * @return array
	 */
	static protected function _getFormats()
	{
		if (is_null(self::$_cache))
		{
			self::$_cache = Core_Entity::factory('Directory_Phone_Format')->getAllByActive(1, FALSE);
		}

		return self::$_cache;
	}

	/**
	 * Format phone number
	 * @param string $phone
	 * @return string
	 */
	static public function format($phone)
	{
		$aDirectory_Phone_Formats = self::_getFormats();
		foreach ($aDirectory_Phone_Formats as $oDirectory_Phone_Format)
		{
			$tmp = $oDirectory_Phone_Format->applyPattern($phone);
			if ($tmp != $phone)
			{
				$phone = $tmp;
				break;
			}
		}

		return $phone;
	}
}