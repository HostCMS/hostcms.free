<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Http cUrl driver
 *
 * @package HostCMS
 * @subpackage Core\Http
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Http_Curl extends Core_Http
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
		if (!function_exists('curl_init'))
		{
			throw new Core_Exception("cURL has been disabled, please contact your system administrator!");
		}

		$curl = @curl_init();

		// Предотвращаем chunked-ответ
		//curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		// Force to use HTTP 1.1 instead of 2.0
		//curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

		curl_setopt($curl, CURLOPT_URL, "{$scheme}://{$host}:{$this->_port}{$path}{$query}");

		switch ($this->_method)
		{
			case 'GET':
				curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
				//curl_setopt($curl, CURLOPT_POST, FALSE);
			break;
			case 'PUT':
				curl_setopt($curl, CURLOPT_HTTPGET, FALSE);
				curl_setopt($curl, CURLOPT_POST, FALSE);
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			break;
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, TRUE);
				//curl_setopt($curl, CURLOPT_HTTPGET, FALSE);
			break;
			case 'HEAD':
				curl_setopt($curl, CURLOPT_NOBODY, TRUE);
			break;
			default:
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->_method);
		}

		if ($this->_method != 'GET')
		{
			if ($this->_rawData)
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_rawData);

				/*if ($this->_method == 'POST')
				{
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
				}*/

				!is_array($this->_rawData) && $this->additionalHeader('Content-Length', strlen($this->_rawData));
			}
			else
			{
				count($this->_data)
					&& curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->_data));
			}
		}

		foreach ($this->_config['options'] as $optionName => $optionValue)
		{
			curl_setopt($curl, $optionName, $optionValue);
		}

		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		// Can't set for FILE send
		//curl_setopt($curl, CURLOPT_NOBODY, FALSE); // Return body

		// Outgoing header
		//curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);

		curl_setopt($curl, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->_userAgent);
		curl_setopt($curl, CURLOPT_REFERER, $this->_referer);

		curl_setopt($curl, CURLOPT_VERBOSE, FALSE); // Minimize logs
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // No certificate
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // Return in string

		// Close connection
		//curl_setopt($curl, CURLOPT_FORBID_REUSE, TRUE);

		// TLS 1.2
		//curl_setopt($curl, CURLOPT_SSLVERSION, 6);

		!isset($this->_additionalHeaders['Content-Type'])
			&& !is_null($this->_contentType)
			&& $this->_additionalHeaders['Content-Type'] = $this->_contentType;

		// Additional headers
		if (count($this->_additionalHeaders))
		{
			$aTmp = array();
			foreach ($this->_additionalHeaders as $name => $value)
			{
				$aTmp[] = "{$name}: {$value}";
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER, $aTmp);
		}

		$bEmulateFollowlocation = FALSE;

		if (!isset($this->_config['options'][CURLOPT_FOLLOWLOCATION]))
		{
			if (ini_get('open_basedir') == ''
				&& ini_get('safe_mode') != 1
				&& strtolower(ini_get('safe_mode')) != 'off'
			)
			{
				// When CURLOPT_FOLLOWLOCATION and CURLOPT_HEADER are both true and redirects have happened then the header returned by curl_exec() will contain all the headers in the redirect chain in the order they were encountered.
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
			}
			else
			{
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
				$bEmulateFollowlocation = TRUE;
			}
		}

		// Get the target contents
		$datastr = @curl_exec($curl);

		// Sent headers
		//var_dump(curl_getinfo($curl, CURLINFO_HEADER_OUT));

		$this->_errno = curl_errno($curl);

		if ($bEmulateFollowlocation && !$this->_errno)
		{
			$iMaxRedirects = 5;

			do {
				$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				if ($code == 301 || $code == 302)
				{
					preg_match('/Location:\s*(.*?)\n/i', $datastr, $matches);
					$newurl = array_pop($matches);
					if (!is_null($newurl) && strlen(trim($newurl)))
					{
						$rch = curl_copy_handle($curl);
						@curl_close($curl);
						$curl = $rch;
						//curl_setopt($rch, CURLOPT_FORBID_REUSE, TRUE);
						curl_setopt($curl, CURLOPT_URL, trim($newurl));

						// follow location
						$datastr = @curl_exec($curl);

						// set new errno
						$this->_errno = curl_errno($curl);
					}
					else
					{
						$code = 0;
					}
				}
				else
				{
					$code = 0;
				}
			} while ($code && --$iMaxRedirects);
		}

		$this->_error = curl_error($curl);

		$iHeaderSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$this->_headers = substr($datastr, 0, $iHeaderSize);

		$this->_body = substr($datastr, $iHeaderSize);

		// Close PHP cURL handle
		@curl_close($curl);
		unset($curl); // PHP-8: curl_close no longer closes the resource

		//$aTmp = explode("\r\n\r\n", $datastr, 2);

		unset($datastr);

		/*$this->_headers = Core_Array::get($aTmp, 0);
		$this->_body = Core_Array::get($aTmp, 1);*/

		return $this;
	}
}