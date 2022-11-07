<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Handler_Default
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Handler_Default extends Calendar_Caldav_Controller
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

	/**
	 * Send request
	 * @param string $method
	 * @param array $aHeaders
	 * @param string $rawData
	 * @param string $url
	 * @return Core_Http
	 */
	protected function _sendRequest($method, array $aHeaders = array(), $rawData = NULL, $url = NULL)
	{
		if (is_null($url))
		{
			$url = $this->_url;
		}

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method($method)
			->userAgent('CalDAV Client')
			->url($url)
			->config($this->_getConnectionConfig($method));

		foreach ($aHeaders as $name => $value)
		{
			$Core_Http->additionalHeader($name, $value);
		}

		if (!is_null($rawData))
		{
			$Core_Http->rawData($rawData);
		}

		$Core_Http->execute();

		return $Core_Http;
	}

	/**
	 * CalDAV connect
	 * @return string
	 */
	public function connect()
	{
		$this->_errno = $this->_error = NULL;

		$sResponse = NULL;

		try {
			$Core_Http = $this->_sendRequest('GET', array('Content-Type' => 'text/plain'));

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

					$this->_client = TRUE;
				break;
			}

			return $sResponse;
		}
		catch (Exception $e) {}
	}

	/**
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
  <sync-token />
 </prop>
</propfind>
EOD;

		$Core_Http = $this->_sendRequest('PROPFIND', array('Content-Type' => 'text/xml', 'Depth' => 1), $data);

		$data = $Core_Http->getDecompressedBody();

		if (strlen($data))
		{
			$oXml = simplexml_load_string($data);
			// $oXml->registerXPathNamespace('D', 'DAV:');

			if (is_object($oXml))
			{
				$namespaces = $oXml->getNameSpaces();
				$curentNamespace = key($namespaces);

				foreach ($oXml->xpath("{$curentNamespace}:response") as $oResponse)
				{
					$oProps = $oResponse->xpath("{$curentNamespace}:propstat/{$curentNamespace}:prop");
					$responceChildren = (array)$oResponse->children($namespaces[$curentNamespace]);

					foreach ($oProps as $oProp)
					{
						$explodeHref = explode('/', strval($responceChildren['href']));

						if (isset($explodeHref[3]))
						{
							// Now we don’t have the URL hard-coded
							$propChildren = (array)$oProp->children($namespaces[$curentNamespace]);

							$return = $this->_getCalendarDetails($responceChildren, $oProp, $propChildren);

							!is_null($return)
								&& $aReturn[strval($explodeHref[3])] = $return;
						}
					}
				}
			}
		}

		// var_dump($aReturn);

		return $aReturn;
	}

	/**
	 * Get calendar details
	 * @param object $responceChildren
	 * @param object $oProp
	 * @param object $propChildren
	 * @return array|NULL
	 */
	protected function _getCalendarDetails($responceChildren, $oProp, $propChildren)
	{
		$return = NULL;

		if (isset($propChildren['displayname']) && strlen($propChildren['displayname']))
		{
			$explodeHref = explode('/', strval($responceChildren['href']));

			if (isset($explodeHref[3]))
			{
				$oProp->registerXPathNamespace('ANS', 'http://apple.com/ns/ical/');
				$oProp->registerXPathNamespace('CS', 'http://calendarserver.org/ns/');

				$aCalendarColor = (array)$oProp->xpath("ANS:calendar-color");
				$aGetctag = (array)$oProp->xpath("CS:getctag");

				$return = array(
					'name' => strval($propChildren['displayname']),
					'href' => strval($responceChildren['href']),
					'ctag' => isset($aGetctag[0]) ? strval($aGetctag[0]) : '',
					'color' => isset($aCalendarColor[0]) ? strval($aCalendarColor[0]) : '',
					'sync_token' => isset($propChildren['sync-token']) ? strval($propChildren['sync-token']) : NULL
				);
			}
		}

		return $return;
	}

	/**
	 * Return list of calendars
	 * @return array
	 */
	public function getObjects()
	{
		if (is_null($this->_client))
		{
			throw new Core_Exception('No connection. Try connect().');
		}

		$aReturn = array();

		$start = Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp(date('Y-m-d', strtotime("-6 month"))));
		$end = Core_Date::gmdate("Ymd\THis\Z", Core_Date::datetime2timestamp(date('Y-m-d', strtotime("+6 month"))));

		$data = <<<EOD
<C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
	<D:prop>
		<C:calendar-data content-type="text/calendar" version="2.0"/>
		<D:getetag/>
	</D:prop>
	<C:filter>
		<C:comp-filter name="VCALENDAR">
			<C:comp-filter name="VEVENT">
				<C:time-range start="{$start}" end="{$end}"/>
			</C:comp-filter>
		</C:comp-filter>
	</C:filter>
</C:calendar-query>
EOD;

		$Core_Http = $this->_sendRequest('REPORT', array('Content-Type' => 'application/xml; charset=utf-8', 'Depth' => 1), $data);

		$data = $Core_Http->getDecompressedBody();

		// echo "<pre>";
		// var_dump($data);
		// echo "</pre>";

		if (strlen($data))
		{
			$oXml = simplexml_load_string($data);

			if (is_object($oXml))
			{
				$namespaces = $oXml->getNameSpaces();
				$curentNamespace = key($namespaces);

				foreach ($oXml->xpath("{$curentNamespace}:response") as $oResponse)
				{
					$oProps = $oResponse->xpath("{$curentNamespace}:propstat/{$curentNamespace}:prop");
					$responceChildren = (array)$oResponse->children($namespaces[$curentNamespace]);

					foreach ($oProps as $oProp)
					{
						// Now we don’t have the URL hard-coded
						$propChildren = (array)$oProp->children($namespaces[$curentNamespace]);

						$oProp->registerXPathNamespace('CD', 'urn:ietf:params:xml:ns:caldav');
						$aCalendarData = (array)$oProp->xpath("CD:calendar-data");

						if (isset($aCalendarData[0]))
						{
							$explodeHref = explode('/', strval($responceChildren['href']));

							if (isset($explodeHref[3]))
							{
								$sCalendarData = strval($aCalendarData[0]);

								//$sCalendarData = str_replace(array("\r\n", "\n\r", "\r"), "\n", $sCalendarData);

								$aExplodedData = explode("\n", $sCalendarData);

								$aTmp = array();

								$currentIndex = 0;

								foreach ($aExplodedData as $row)
								{
									/*
									https://www.rfc-editor.org/rfc/rfc5545#section-3.1
									Lines of text SHOULD NOT be longer than 75 octets, excluding the line
									break.  Long content lines SHOULD be split into a multiple line
									representations using a line "folding" technique.  That is, a long
									line can be split between any two characters by inserting a CRLF
									immediately followed by a single linear white-space character (i.e.,
									SPACE or HTAB).  Any sequence of CRLF followed immediately by a
									single linear white-space character is ignored (i.e., removed) when
									processing the content type.

									For example, the line:

										DESCRIPTION:This is a long description that exists on a long line.

									Can be represented as:

										DESCRIPTION:This is a lo
										ng description
										that exists on a long line.
									*/
									if (substr($row, 0, 1) === ' ')
									{
										$aTmp[$currentIndex] .= substr($row, 1);
									}
									else
									{
										$aTmp[++$currentIndex] = $row;
									}
								}

								$aEvent = array();

								$bVEVENT = FALSE;

								foreach ($aTmp as $row)
								{
									$row == 'BEGIN:VEVENT' && $bVEVENT = TRUE;
									$row == 'END:VEVENT' && $bVEVENT = FALSE;

									if ($bVEVENT)
									{
										// Delete all ;OPTION="VALUE"
										$row = preg_replace('/;[A-Z\-]*=(:?"[^"]+"|[^:;]+)/i', '', $row);

										list($name, $value) = explode(':', $row, 2);

										/*
										https://www.rfc-editor.org/rfc/rfc5545#section-3.3.11
										ESCAPED-CHAR = ("\\" / "\;" / "\," / "\N" / "\n")
										; \\ encodes \, \N or \n encodes newline
										; \; encodes ;, \, encodes ,
										*/
										$value = str_replace(array('\\\\', '\;', '\,', '\n', '\N'), array('\\', ';', ',', "\n", "\n"), $value);

										$aEvent[$name] = $value;
									}
								}

								if (isset($aEvent['UID']) && strlen($aEvent['UID']))
								{
									$allDay = FALSE;
									$start_datetime = $end_datetime = NULL;
									if (isset($aEvent['DTSTART']))
									{
										$start_datetime = date('Y-m-d H:i:s', strtotime($aEvent['DTSTART']));
									}

									if (isset($aEvent['DTEND']))
									{
										$iEnd = strtotime($aEvent['DTEND']);

										if (!is_null($start_datetime)
											&& strlen($aEvent['DTSTART']) == 8 && strlen($aEvent['DTEND']) == 8
											&& $aEvent['DTEND'] - $aEvent['DTSTART'] == 1
										)
										{
											$allDay = TRUE;

											$iEnd--; // delete one second
										}

										$end_datetime = date('Y-m-d H:i:s', $iEnd);
									}

									$aReturn[] = array(
										'href' => strval($responceChildren['href']),
										'getetag' => isset($propChildren['getetag']) ? strval($propChildren['getetag']) : '-',
										'summary' => isset($aEvent['SUMMARY']) ? strval($aEvent['SUMMARY']) : '',
										'start_datetime' => !is_null($start_datetime) ? $start_datetime : Core_Date::timestamp2sql(time()),
										'end_datetime' => !is_null($end_datetime) ? $end_datetime : Core_Date::timestamp2sql(time()),
										'created' => isset($aEvent['CREATED']) ? strval(date('Y-m-d H:i:s', strtotime($aEvent['CREATED']))) : Core_Date::timestamp2sql(time()),
										'modified' => isset($aEvent['LAST-MODIFIED']) ? strval(date('Y-m-d H:i:s', strtotime($aEvent['LAST-MODIFIED']))) : NULL,
										'uid' => $aEvent['UID'],
										'location' => isset($aEvent['LOCATION']) ? strval($aEvent['LOCATION']) : '',
										'description' => isset($aEvent['DESCRIPTION']) ? strval($aEvent['DESCRIPTION']) : '',
										'allDay' => $allDay
									);


								}
							}
						}
					}
				}
			}
		}

		// var_dump($aReturn);

		return $aReturn;
	}

	/**
	 * Set current calendar url
	 * @return self
	 */
	public function setCalendar(array $aCalendar)
	{
		if (is_null($this->_client))
		{
			throw new Core_Exception('No connection. Try connect().');
		}

		if (isset($aCalendar['href']))
		{
			$this->_calendar_url = $this->_host . $aCalendar['href'];
		}

		return $this;
	}

	/**
	 * Creates or update a calendar resource on the CalDAV-Server (event, todo, etc.)
	 * @param $cal iCalendar-data of the resource
	 * @return self
	 */
	public function save($sCalendar)
	{
		if (is_null($this->_client))
		{
			throw new Core_Exception('No connection. Try connect().');
		}

		if (is_null($this->_calendar_url))
		{
			throw new Core_Exception('No calendar selected.');
		}

		// Parse $sCalendar for UID
		if (preg_match('#^UID:(.*?)\r?\n?$#m', $sCalendar, $matches))
		{
			$sUid = $matches[1];
		}
		else
		{
			throw new Core_Exception('Can\'t find UID in iCalendar-data');
		}

		$iCAL = $this->_calendar_url . $sUid . '.ics';

		$aEvent = $this->getEvent($iCAL);

		if (isset($aEvent['etag']) && strlen($aEvent['etag']))
		{
			$this->update($aEvent, $iCAL, $sCalendar);
		}
		else
		{
			$this->create($aEvent, $iCAL, $sCalendar);
		}

		return $this;
	}

	/**
	 * Create event
	 * @return string
	 */
	public function create($aEvent, $sUrl, $sCalendar)
	{
		if ($aEvent['status_code'] != 200)
		{
			$Core_Http = $this->_sendRequest('PUT', array('Content-Type' => 'text/calendar; encoding="utf-8"'), $sCalendar, $sUrl);

			$aHeaders = $Core_Http->parseHeaders();

			return Core_Array::get($aHeaders, 'Etag');
		}
	}

	/**
	 * Update event
	 * @return string
	 */
	public function update($aEvent, $sUrl, $sCalendar)
	{
		$Core_Http = $this->_sendRequest('PUT', array('Content-Type' => 'text/calendar; encoding="utf-8"'), $sCalendar, $sUrl);

		$aHeaders = $Core_Http->parseHeaders();

		return Core_Array::get($aHeaders, 'Etag');
	}

	/**
	 * Get calendar event
	 * @return array
	 */
	public function getEvent($sHref)
	{
		$Core_Http = $this->_sendRequest('GET', array(), NULL, $sHref);

		$aHeaders = $Core_Http->parseHeaders();

		$sStatus = Core_Array::get($aHeaders, 'status');

		$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

		$aReturn = array('status_code' => $iStatusCode);

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
				$aReturn['etag'] = Core_Array::get($aHeaders, 'Etag');
			break;
		}

		return $aReturn;
	}

	/**
	 * Delete event
	 * @return string
	 */
	public function delete($sUrl)
	{
		$Core_Http = $this->_sendRequest('DELETE', array('Content-Type' => 'text/calendar; charset=utf-8'), NULL, $sUrl);

		$aHeaders = $Core_Http->parseHeaders();

		return Core_Array::get($aHeaders, 'status');
	}

	/**
	 * applyObjectProperty
	 * @param Calendar_Caldav_User_Controller_Edit $oCalendar_Caldav_User_Controller_Edit
	 */
	public function applyObjectProperty($oCalendar_Caldav_User_Controller_Edit){}

	/**
	 * Show second window
	 * @param Calendar_Caldav_User_Controller_Edit $oCalendar_Caldav_User_Controller_Edit
	 */
	public function showSecondWindow($oCalendar_Caldav_User_Controller_Edit){}
}