<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Mail_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Mail_Controller extends Calendar_Caldav_Default_Controller
{
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

		// var_dump($data);

		if (strlen($data))
		{
			$oXml = simplexml_load_string($data);
			// $oXml->registerXPathNamespace('D', 'DAV:');

			if (is_object($oXml))
			{
				foreach ($oXml->xpath('ns0:response') as $oResponse)
				{
					$namespaces = $oResponse->getNameSpaces(TRUE);
					$responceChildren = (array)$oResponse->children($namespaces['ns0']);

					$oProps = $oResponse->xpath('ns0:propstat/ns0:prop');

					foreach ($oProps as $oProp)
					{
						// Use that namespace
						$namespaces = $oProp->getNameSpaces(TRUE);

						// Now we don’t have the URL hard-coded
						$propChildren = (array)$oProp->children($namespaces['ns0']);
						$propChildrenCS = isset($namespaces['CS']) ? (array)$oProp->children($namespaces['CS']) : array();
						$propChildrenICAL = isset($namespaces['CS']) ? (array)$oProp->children($namespaces['ICAL']) : array();

						if (isset($propChildren['displayname'])
							&& strlen($propChildren['displayname'])
							&& isset($propChildrenCS['getctag'])
							&& strlen($propChildrenCS['getctag'])
						)
						{
							$explodeHref = explode('/', strval($responceChildren['href']));

							if (isset($explodeHref[3]))
							{
								$aReturn[strval($explodeHref[3])] = array(
									'name' => strval($propChildren['displayname']),
									'href' => strval($responceChildren['href']),
									'ctag' => strval($propChildrenCS['getctag']),
									'color' => strval($propChildrenICAL['calendar-color'])
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