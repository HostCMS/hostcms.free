<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Http socket driver
 *
 * @package HostCMS
 * @subpackage Core\Http
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Http_Socket extends Core_Http
{
	/**
	 * Send request
	 * @param string $host host
	 * @param string $path path
	 * @param string $query query
	 * @return self
	 */
	protected function _execute($host, $path, $query, $scheme = 'http')
	{
		// для 443 порта в fsockopen перед хостом нужно добавлять ssl://
		$socketHost = $scheme == 'https'
			? 'ssl://' . $host
			: $host;

		if (!function_exists('fsockopen'))
		{
			throw new Core_Exception("Fsockopen has been disabled, please contact your system administrator!");
		}

		$fp = @fsockopen($socketHost, $this->_port, $errno, $errstr, $this->_timeout);

		if (!$fp)
		{
			$this->_errno = $errno;
			$this->_error = $errstr;

			throw new Core_Exception("Fsockopen failed '%errstr' (%errno)",
				array('%errstr' => $errstr, '%errno' => $errno), $errno
			);
		}

		$out = "{$this->_method} {$path}{$query} HTTP/1.1\r\n";
		$out .= "Content-Type: {$this->_contentType}\r\n";
		$out .= "Referer: {$this->_referer}\r\n";
		$out .= "User-Agent: {$this->_userAgent}\r\n";

		// Additional headers
		foreach ($this->_additionalHeaders as $name => $value)
		{
			$out .= "{$name}: {$value}\r\n";
		}

		$out .= "Host: {$host}\r\n";

		$bIsPost = $this->_method != 'GET' && ($this->_rawData || count($this->_data) > 0);

		if ($bIsPost)
		{
			if ($this->_rawData)
			{
				$sPost = $this->_rawData;
			}
			else
			{
				$aData = array();
				foreach ($this->_data as $key => $value)
				{
					$aData[] = urlencode($key) . '=' . urlencode($value);
				}

				$sPost = implode('&', $aData);
			}

			$out .= "Content-length: " . strlen($sPost) . "\r\n";
		}

		$out .= "Connection: Close\r\n\r\n";

		if ($bIsPost)
		{
			$out .= $sPost . "\r\n\r\n";
		}

		fwrite($fp, $out);

		if (function_exists('stream_set_timeout'))
		{
			stream_set_timeout($fp, $this->_timeout);
		}

		$this->_headers = $this->_body = NULL;

		$this->_body = '';

		while (!feof($fp))
		{
			$line = fgets($fp, 65536);

			if ($line === FALSE)
			{
				break;
			}

			$this->_body .= $line;

			$socketStatus = stream_get_meta_data($fp);
			if ($socketStatus['timed_out'])
			{
				throw new Core_Exception("HostCMS: Timed Out, socket closed by the server!");
			}

			// Explode header until data is short
			if (is_null($this->_headers) && strlen($this->_body) > 2048)
			{
				$this->_explode();
			}
		}

		fclose($fp);

		is_null($this->_headers) && $this->_explode();

		return $this;
	}

	/**
	 * Explode Headers and Body
	 * @return self
	 */
	protected function _explode()
	{
		$pos = strpos($this->_body, "\r\n\r\n");
		if ($pos !== FALSE)
		{
			$this->_headers = substr($this->_body, 0, $pos);
			$this->_body = substr($this->_body, $pos + 4);
		}

		return $this;
	}

	/**
	 * Get decompressed body
	 * @return string
	 */
	public function getDecompressedBody()
	{
		$aHeaders = array_change_key_case($this->parseHeaders(), CASE_LOWER);

		if (isset($aHeaders['transfer-encoding']))
		{
			$encoding = is_array($aHeaders['transfer-encoding'])
				? end($aHeaders['transfer-encoding'])
				: $aHeaders['transfer-encoding'];

			switch ($encoding)
			{
				// Transfer-Encoding: chunked
				case 'chunked':
					$rawBody = $this->_body;
					$this->_body = '';

					do
					{
						$nextCRLF = strpos($rawBody, "\r\n");
						$blockLen = hexdec(substr($rawBody, 0, $nextCRLF));
						$this->_body .= substr($rawBody, $nextCRLF + 2, $blockLen);
						$rawBody = substr($rawBody, $nextCRLF + 4 + $blockLen); // <HEX-len><CRLF><content><CRLF>
					} while ($blockLen);

				break;
				default:
					throw new Core_Exception('Core_Http_Socket unsupported transfer encoding "%name"', array('%name' => $encoding));
			}
		}

		return parent::getDecompressedBody();
	}
}