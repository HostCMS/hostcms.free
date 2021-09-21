<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Default_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Default_Controller extends Calendar_Caldav_Controller
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
	public function findCalendars(){}

	/*
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

	/*
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
			throw new Exception('No calendar selected.');
		}

		// Parse $sCalendar for UID
		if (preg_match('#^UID:(.*?)\r?\n?$#m', $sCalendar, $matches))
		{
			$sUid = $matches[1];
		}
		else
		{
			throw new Exception('Can\'t find UID in iCalendar-data');
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

	/*
	 * Create event
	 * @return string
	 */
	public function create($aEvent, $sUrl, $sCalendar)
	{
		if ($aEvent['status_code'] != 200)
		{
			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('PUT')
				->userAgent('CalDAV Client')
				->url($sUrl)
				->additionalHeader('Content-Type', 'text/calendar; encoding="utf-8"')
				->rawData($sCalendar)
				->config(
					array(
						'options' => array(
							CURLOPT_USERPWD => $this->_username . ':' . $this->_password,
						)
					)
				)
				->execute();

			$aHeaders = $Core_Http->parseHeaders();

			return Core_Array::get($aHeaders, 'Etag');
		}
	}

	/*
	 * Update event
	 * @return string
	 */
	public function update($aEvent, $sUrl, $sCalendar)
	{
		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('PUT')
			->userAgent('CalDAV Client')
			->url($sUrl)
			->additionalHeader('Content-Type', 'text/calendar; encoding="utf-8"')
			->rawData($sCalendar)
			->config(
				array(
					'options' => array(
						CURLOPT_USERPWD => $this->_username . ':' . $this->_password,
					)
				)
			)
			->execute();

		$aHeaders = $Core_Http->parseHeaders();

		return Core_Array::get($aHeaders, 'Etag');
	}

	/*
	 * Get calendar event
	 * @return array
	 */
	public function getEvent($sHref)
	{
		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->userAgent('CalDAV Client')
			->url($sHref)
			->config(
				array(
					'options' => array(
						CURLOPT_USERPWD => $this->_username . ':' . $this->_password,
					)
				)
			)
			->execute();

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

	/*
	 * Delete event
	 * @return string
	 */
	public function delete($sUrl)
	{
		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('DELETE')
			->userAgent('CalDAV Client')
			->url($sUrl)
			->config(
				array(
					'options' => array(
						CURLOPT_USERPWD => $this->_username . ':' . $this->_password,
					)
				)
			)
			->execute();

		$aHeaders = $Core_Http->parseHeaders();

		return Core_Array::get($aHeaders, 'status');
	}
	
	public function applyObjectProperty($oCalendar_Caldav_User_Controller_Edit){}
	
	public function showSecondWindow($oCalendar_Caldav_User_Controller_Edit){}
}