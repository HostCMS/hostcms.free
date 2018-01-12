<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SMTP driver
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Mail_Smtp extends Core_Mail
{
	/**
	 * Send mail
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $message message
	 * @param string $additional_headers additional_headers
	 * @return self
	 */
	protected function _send($to, $subject, $message, $additional_headers)
	{
		$header = "Date: " . date("D, d M Y H:i:s O") . $this->_separator;
		$header .= "Subject: {$subject}{$this->_separator}";
		$header .= "To: {$to}{$this->_separator}";
		$header .= $additional_headers . $this->_separator . $this->_separator;

		$header .= $message . $this->_separator;
		$timeout = 5;

		$context = stream_context_create(Core_Array::get($this->_config, 'options', array()));

		$fp = /*function_exists('stream_socket_client')
			? */stream_socket_client($this->_config['host'] . ":" . $this->_config['port'], $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context)
			/*: fsockopen($this->_config['host'], $this->_config['port'], $errno, $errstr, $timeout)*/;

		if ($fp)
		{
			stream_set_timeout($fp, $timeout);

			// Может быть много 220-х, последний отделяется пробелом, а не минусом
			do {
				$server_response = $this->_serverFgets($fp);

				if (!$this->_serverParse($server_response, "220"))
				{
					fclose($fp);
					return FALSE;
				}
			}
			while(!feof($fp)
				//&& $this->_getResponseStatus($server_response) == "220"
				&& substr($server_response, 3, 1) != ' '
			);

			fputs($fp, "EHLO " . Core_Array::get($_SERVER, 'SERVER_NAME') . "\r\n");

			// Может быть много 250-х, последний отделяется пробелом, а не минусом
			do {
				$server_response = $this->_serverFgets($fp);
				
				if (!$this->_serverParse($server_response, "250"))
				{
					fclose($fp);
					return FALSE;
				}
			}
			while(!feof($fp)
				//&& $this->_getResponseStatus($server_response) == "250"
				&& substr($server_response, 3, 1) != ' '
			);

			fputs($fp, "AUTH LOGIN\r\n");
			$server_response = $this->_serverFgets($fp); // Получен выше в цикле
			if (!$this->_serverParse($server_response, "334"))
			{
				fclose($fp);
				return FALSE;
			}

			fputs($fp, base64_encode($this->_config['username']) . "\r\n");
			$server_response = $this->_serverFgets($fp);
			if (!$this->_serverParse($server_response, "334"))
			{
				fclose($fp);
				return FALSE;
			}

			fputs($fp, base64_encode($this->_config['password']) . "\r\n");
			$server_response = $this->_serverFgets($fp);
			if (!$this->_serverParse($server_response, "235"))
			{
				fclose($fp);
				return FALSE;
			}

			// MAIL FROM and user name may be different
			$smtpFrom = Core_Array::get($this->_config, 'from', $this->_config['username']);

			fputs($fp, "MAIL FROM: <{$smtpFrom}>\r\n");
			$server_response = $this->_serverFgets($fp);
			if (!$this->_serverParse($server_response, "250")) {
				fclose($fp);
				return FALSE;
			}

			$aRecipients = explode(',', $to);
			foreach ($aRecipients as $sTo)
			{
				fputs($fp, "RCPT TO: {$sTo}\r\n");
				$server_response = $this->_serverFgets($fp);
				if (!$this->_serverParse($server_response, "250"))
				{
					fclose($fp);
					return FALSE;
				}
			}

			fputs($fp, "DATA\r\n");
			$server_response = $this->_serverFgets($fp);
			if (!$this->_serverParse($server_response, "354"))
			{
				fclose($fp);
				return FALSE;
			}

			fputs($fp, $header."\r\n.\r\n");
			$server_response = $this->_serverFgets($fp);
			if (!$this->_serverParse($server_response, "250"))
			{
				fclose($fp);
				return FALSE;
			}

			fputs($fp, "QUIT\r\n");
			fclose($fp);

			$this->_status = TRUE;
		}
		else
		{
			$this->_status = FALSE;
		}

		return $this;
	}

	/**
	 * fgets 256 bytes
	 * @param pointer $socket
	 * @return mixed
	 */
	protected function _serverFgets($socket)
	{
		return fgets($socket, 256);
	}

	/**
	 * Get status of response
	 * @param string $server_response
	 * @return string
	 */
	protected function _getResponseStatus($server_response)
	{
		return substr($server_response, 0, 3);
	}

	/**
	 * Parse server answer
	 * @param string $server_response
	 * @param string $response response
	 * @return string
	 */
	protected function _serverParse($server_response, $response)
	{
		$result = $this->_getResponseStatus($server_response) == $response;

		if (!$result)
		{
			//throw new Core_Exception('SMTP error: "%error"', array('%error' => $server_response));
			Core_Log::instance()->clear()
				->notify(FALSE) // avoid recursion
				->status(Core_Log::$ERROR)
				->write(sprintf('SMTP error: "%s"', $server_response));
		}

		return $result;
	}
}