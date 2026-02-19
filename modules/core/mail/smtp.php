<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SMTP driver
 *
 * @package HostCMS
 * @subpackage Core\Mail
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Mail_Smtp extends Core_Mail
{
	/**
	 * Stream
	 * @var mixed
	 */
	protected $_fp = NULL;

	/**
	 * Send mail
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $message message
	 * @return self
	 */
	protected function _send($to, $subject, $message)
	{
		$header = "Date: " . date("D, d M Y H:i:s O") . $this->_separator;
		$header .= "Subject: {$subject}{$this->_separator}";
		$header .= "To: {$to}{$this->_separator}"; // 000298317

		// Change 'From' header
		if (isset($this->_config['from']) && isset($this->_headers['From']))
		{
			unset($this->_headers['From']);

			$sFrom = !is_null($this->_senderName)
				? '=?UTF-8?B?' . base64_encode($this->_senderName) . "?= <{$this->_config['from']}>"
				: $this->_config['from'];

			$this->header('From', $sFrom);
		}

		$header .= $this->_headersToString() . $this->_separator . $this->_separator;

		$header .= $message . $this->_separator;

		$context = stream_context_create(Core_Array::get($this->_config, 'options', array()));

		$this->_fp = /*function_exists('stream_socket_client')
			? */stream_socket_client($this->_config['host'] . ":" . $this->_config['port'], $errno, $errstr, $this->_config['timeout'], STREAM_CLIENT_CONNECT, $context)
			/*: fsockopen($this->_config['host'], $this->_config['port'], $errno, $errstr, $this->_config['timeout'])*/;

		if ($this->_fp)
		{
			stream_set_timeout($this->_fp, $this->_config['timeout']);

			// Может быть много 220-х, последний отделяется пробелом, а не минусом
			do {
				$server_response = $this->_serverFgets();

				if (!$this->_serverParse($server_response, "220"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}
			}
			while (!feof($this->_fp)
				//&& $this->_getResponseStatus($server_response) == "220"
				&& substr($server_response, 3, 1) != ' '
			);

			$this->_serverFputs("EHLO " . $this->_getServerName() . "\r\n");

			// Может быть много 250-х, последний отделяется пробелом, а не минусом
			do {
				$server_response = $this->_serverFgets();

				if (!$this->_serverParse($server_response, "250"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}
			}
			while (!feof($this->_fp)
				//&& $this->_getResponseStatus($server_response) == "250"
				&& substr($server_response, 3, 1) != ' '
			);

			// TLS
			if (isset($this->_config['tls']) && $this->_config['tls'])
			{
				$this->_serverFputs("STARTTLS\r\n");

				$server_response = $this->_serverFgets();
				if (!$this->_serverParse($server_response, "220"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}

				// http://php.net/manual/ru/function.stream-socket-enable-crypto.php#119122
				$crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

				if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT'))
				{
					$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
					$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
				}

				$xbCrypto = stream_socket_enable_crypto($this->_fp, TRUE, $crypto_method);

				// Resend EHLO after TLS
				$this->_serverFputs("EHLO " . $this->_getServerName() . "\r\n");

				// Может быть много 250-х, последний отделяется пробелом, а не минусом
				do {
					$server_response = $this->_serverFgets();

					if (!$this->_serverParse($server_response, "250"))
					{
						fclose($this->_fp);
						$this->log();
						$this->_status = FALSE;
						return $this;
					}
				}
				while (!feof($this->_fp)
					//&& $this->_getResponseStatus($server_response) == "250"
					&& substr($server_response, 3, 1) != ' '
				);
			}

			// AUTH
			if (isset($this->_config['username']) && isset($this->_config['password']))
			{
				$this->_serverFputs("AUTH LOGIN\r\n");
				$server_response = $this->_serverFgets();
				if (!$this->_serverParse($server_response, "334"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}

				$this->_serverFputs(base64_encode($this->_config['username']) . "\r\n");
				$server_response = $this->_serverFgets();
				if (!$this->_serverParse($server_response, "334"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}

				$this->_serverFputs(base64_encode($this->_config['password']) . "\r\n");
				$server_response = $this->_serverFgets();
				if (!$this->_serverParse($server_response, "235"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}
			}

			// MAIL FROM and user name may be different
			$smtpFrom = Core_Array::get($this->_config, 'from', Core_Array::get($this->_config, 'username', 'undefined'));

			$this->_serverFputs("MAIL FROM: <{$smtpFrom}>\r\n");
			$server_response = $this->_serverFgets();
			if (!$this->_serverParse($server_response, "250"))
			{
				fclose($this->_fp);
				$this->log();
				$this->_status = FALSE;
				return $this;
			}

			$aRecipients = explode(',', $to);
			foreach ($aRecipients as $sTo)
			{
				preg_match('/<(.*?)>/', $sTo, $match);
				
				$sTo = isset($match[1]) ? $match[1] : '';
				
				$this->_serverFputs("RCPT TO: <{$sTo}>\r\n");
				$server_response = $this->_serverFgets();
				if (!$this->_serverParse($server_response, "250"))
				{
					fclose($this->_fp);
					$this->log();
					$this->_status = FALSE;
					return $this;
				}
			}

			$this->_serverFputs("DATA\r\n");
			$server_response = $this->_serverFgets();
			if (!$this->_serverParse($server_response, "354"))
			{
				fclose($this->_fp);
				$this->log();
				$this->_status = FALSE;
				return $this;
			}

			$this->_serverFputs($header . "\r\n.\r\n");
			$server_response = $this->_serverFgets();
			if (!$this->_serverParse($server_response, "250"))
			{
				fclose($this->_fp);
				$this->log();
				$this->_status = FALSE;
				return $this;
			}

			$this->_serverFputs("QUIT\r\n");
			fclose($this->_fp);

			$this->log();

			$this->_status = TRUE;
		}
		else
		{
			$this->_status = FALSE;
		}

		return $this;
	}

	/**
	 * Server fputs
	 * @param string $str
	 * @return int|FALSE
	 */
	protected function _serverFputs($str)
	{
		$this->_config['log']
			&& $this->_log .= date('Y-m-d H:i:s') . " CLIENT: " . $str;

		return fputs($this->_fp, $str);
	}

	/**
	 * fgets 256 bytes
	 * @return false|string
     */
	protected function _serverFgets()
	{
		$return = fgets($this->_fp, 256);

		$this->_config['log']
			&& $this->_log .= date('Y-m-d H:i:s') . " SERVER: " . $return;

		return $return;
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
	 * @return bool
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
				->write(sprintf('SMTP error: "%s", expect "%s"', $server_response, $response));
		}

		return $result;
	}

	/**
	 * Get Server Name
	 * @return string
	 */
	protected function _getServerName()
	{
		return Core_Array::get($_SERVER, 'SERVER_NAME', gethostname());
	}
}