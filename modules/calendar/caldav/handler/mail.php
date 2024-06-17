<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Handler_Mail
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Calendar_Caldav_Handler_Mail extends Calendar_Caldav_Handler_Default
{
	/**
	 * Get Connection Config options
	 * @return array
	 */
	protected function _getConnectionConfig($method)
	{
		return array(
			'options' => array(
				CURLOPT_USERPWD => $this->_username . ':' . $this->_password
			)
		);
	}
}