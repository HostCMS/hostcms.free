<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sendmail driver
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Mail_Sendmail extends Core_Mail
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
		$this->_status = @mail($to, $subject, $message, $this->_headersToString());
		return $this;
	}
}