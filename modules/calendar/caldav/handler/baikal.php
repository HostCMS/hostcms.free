<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Handler_Baikal
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Handler_Baikal extends Calendar_Caldav_Handler_Default
{
	/**
	 * Get Connection Config options
	 * @return array
	 */
	protected function _getConnectionConfig($method)
	{
		$aArr = array('PUT', 'DELETE');

		return array(
			'options' => array(
				CURLOPT_USERPWD => $this->_username . ':' . $this->_password,
				CURLOPT_HTTPAUTH => in_array($method, $aArr) ? CURLAUTH_DIGEST : CURLAUTH_ANY
			)
		);
	}
}