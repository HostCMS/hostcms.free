<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Yandex_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Yandex_Controller extends Calendar_Caldav_Default_Controller
{
	/*
	 * CalDAV connect
	 * @return string
	 */
	public function connect()
	{
		$this->_errno = $this->_error = NULL;

		$sResponse = NULL;

		try {
			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('GET')
				->userAgent('CalDAV Client')
				->url($this->_url)
				->additionalHeader('Content-Type', 'text/plain')
				->config(
					array(
						'options' => array(
							CURLOPT_USERPWD => $this->_username . ':' . $this->_password
						)
					)
				)
				->execute();

			$aHeaders = $Core_Http->parseHeaders();

			$sStatus = Core_Array::get($aHeaders, 'status');

			$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

			switch ($iStatusCode)
			{
				case '':
					$this->_errno = 0;
					$this->_error = 'Can\'t reach server';
				break;
				default:
					$this->_errno = $iStatusCode;
					$this->_error = $sStatus;
				break;
				case 200:
					$sResponse = $Core_Http->getDecompressedBody();

					// $this->_list_events = explode("\n", $sResponse);

					$this->_client = TRUE;
				break;
			}

			return $sResponse;
		}
		catch (Exception $e) {}
	}

	/*
	 * Return list of calendars
	 * @return array
	 */
	public function findCalendars()
	{
		if (is_null($this->_client))
		{
			throw new Core_Exception('No connection. Try connect().');
		}

		$aReturn = array();

		$data = <<<EOD
<?xml version="1.0" encoding="utf-8" ?>
<propfind xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav" xmlns:C1="http://calendarserver.org/ns/" xmlns:A="http://apple.com/ns/ical/">
 <prop>
  <resourcetype/>
  <displayname/>
  <C1:getctag/>
  <A:calendar-color/>
   <A:calendar-order/>
 </prop>
</propfind>
EOD;

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('PROPFIND')
			->userAgent('CalDAV Client')
			->url($this->_url)
			->additionalHeader('Content-Type', 'text/xml')
			->additionalHeader('Depth', 1)
			->rawData($data)
			->config(
				array(
					'options' => array(
						CURLOPT_USERPWD => $this->_username . ':' . $this->_password,
					)
				)
			)
			->execute();

		$data = $Core_Http->getDecompressedBody();

		if (strlen($data))
		{
			$oXml = simplexml_load_string($data);
			// $oXml->registerXPathNamespace('D', 'DAV:');

			if (is_object($oXml))
			{
				foreach ($oXml->xpath('D:response') as $oResponse)
				{
					$oProps = $oResponse->xpath('D:propstat/D:prop');

					foreach ($oProps as $oProp)
					{
						// Use that namespace
						$namespaces = $oProp->getNameSpaces(TRUE);

						// Now we don’t have the URL hard-coded
						$propChildren = (array)$oProp->children($namespaces['D']);

						$aProp = $this->xml2array($oProp);

						if (isset($propChildren['displayname'])
							&& strlen($propChildren['displayname'])
							&& isset($aProp['getctag'])
							&& strlen($aProp['getctag'])
						)
						{
							$explodeHref = explode('/', strval($oResponse->href));

							if (isset($explodeHref[3]))
							{
								$aReturn[strval($explodeHref[3])] = array(
									'name' => strval($propChildren['displayname']),
									'href' => strval($oResponse->href),
									'ctag' => strval($aProp['getctag']),
									'color' => strval($aProp['calendar-color'])
								);
							}
						}
					}
				}
			}
		}

		return $aReturn;
	}
}