<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeweb driver
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Mail_Timeweb extends Core_Mail_Sendmail
{
	/**
	 * Send mail
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $message message
	 * @return self
	 */
	protected function _send($to, $subject, $message)
	{
		$this->_additionalParams = '-f ' . $this->_from;
		
		return parent::_send($to, $subject, $message);
	}
}