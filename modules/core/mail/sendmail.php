<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sendmail driver
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Mail_Sendmail extends Core_Mail
{
	/**
	 * The additional_params parameter can be used to pass additional flags as command line options to the program configured
	 * to be used when sending mail, as defined by the sendmail_path configuration setting.
	 * For example, this can be used to set the envelope sender address when using sendmail with the -f sendmail option.
	 * @var string
	 */
	protected $_additionalParams = '';

	/**
	 * Send mail
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $message message
	 * @return self
	 */
	protected function _send($to, $subject, $message)
	{
		$headers = $this->_headersToString();

		$signedHeaders = '';

		// DKIM
		if (isset($this->_config['dkim']) && is_array($this->_config['dkim']))
		{
			$headersToSign = $headers;

			$aDkimConfig = $this->_config['dkim'];

			if (!empty($to) || !empty($subject))
			{
				$headersToSign .= mb_substr($headersToSign, -2) == "\r\n"
					? ''
					: "\r\n";

				if (!empty($to))
				{
					$headersToSign .= 'To: ' . $to . "\r\n";
				}
				if (!empty($subject))
				{
					$headersToSign .= 'Subject: ' . $subject . "\r\n";
				}
			}

			// Get the canonicalized version of the $headersToSign
			$dkimRelaxedCanonicalizeHeader = $this->_dkimRelaxedCanonicalizeHeader($headersToSign);

			if (!empty($dkimRelaxedCanonicalizeHeader))
			{
				$signedHeaders = $this->_getDkimSignature($dkimRelaxedCanonicalizeHeader, $message);
			}
			else
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('Core_Mail_Sendmail::_send. No Headers to Sign!');
			}
		}

		$this->_status = @mail($to, $subject, $message, $signedHeaders . $headers, $this->_additionalParams);

		return $this;
	}
}