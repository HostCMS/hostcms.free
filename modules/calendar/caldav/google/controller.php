<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Google_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_Google_Controller extends Calendar_Caldav_Default_Controller
{
	/**
	 * Redirect uri
	 */
	protected $_redirectUri = NULL;

	protected $_token = NULL;

	/**
	 Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$oSite_Alias = $oSite->getCurrentAlias();

		$this->_redirectUri = !is_null($oSite_Alias)
			? (Core::httpsUses() ? 'https' : 'http') . '://' . $oSite_Alias->name . '/calendar-callback.php'
			: NULL;

		//$this->_redirectUri = 'https://demo2.hostcms.ru/calendar-callback.php';

		return $this;
	}

	public function showSecondWindow($oCalendar_Caldav_User_Controller_Edit)
	{
		$oMainTab = $oCalendar_Caldav_User_Controller_Edit->getTab('main');

		$object = $oCalendar_Caldav_User_Controller_Edit->getObject();

		$oMainTab->clear();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$sRedirectUri = urlencode($this->_redirectUri);
		$sClientId = strval($object->username);

		$sText = Core::_('Calendar_Caldav.google', $sClientId, $sRedirectUri);

		$sTextWell = "<div class=\"col-xs-12\"><div class=\"well bordered-left bordered-darkorange margin-top-10\">
			{$sText}</div></div>";

		$oMainRow1->add(Admin_Form_Entity::factory('Code')->html($sTextWell));

		$oDataField = Admin_Form_Entity::factory('Textarea')
			->name('data')
			->value($object->data)
			->divAttr(array('class' => 'form-group col-xs-12'));

		$oMainRow2->add($oDataField);
	}

	public function applyObjectProperty($oCalendar_Caldav_User_Controller_Edit)
	{
		$object = $oCalendar_Caldav_User_Controller_Edit->getObject();

		// save
		if (Core_Array::getGet('additionalSettings') == 1)
		{
			$data = Core_Array::getPost('data');

			$aParams = array(
				'code' => $data,
				'redirect_uri' => $this->_redirectUri,
				'client_id' => $this->_username,
				'scope' => '',
				'client_secret' => $this->_password,
				'grant_type' => 'authorization_code'
			);

			$sJson = $this->getAccessToken($aParams);
			$object->data = $sJson;
			$object->save();
		}
		// show
		else
		{
			$oCalendar_Caldav_User_Controller_Edit->addContent('<script>$.modalLoad({path: \'/admin/calendar/caldav/user/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $object->id . ']=1&calendar_caldav_id=' . $object->calendar_caldav_id. '&additionalSettings=1\', windowId: \'id_content\'});</script>');
		}
	}

	/**
	 * Get OAuth url
	 * @return string
	 */
	public function getOauthCodeUrl()
	{
		if (isset($this->_username) && !strlen($this->_username))
		{
			throw new Core_Exception("Invalid OAuth client_id. Check config.");
		}

		$sRedirectUri = urlencode($this->_redirectUri);
		$sClientId = strval($this->_username);

		// Full, permissive scope to access
		return "https://accounts.google.com/o/oauth2/auth?response_type=code&client_id={$sClientId}&approval_prompt=force&access_type=offline&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar&redirect_uri={$sRedirectUri}";
	}

	/**
	 * Get access token
	 * @param array $aParams params array
	 * @return string
	 */
	public function getAccessToken($aParams = array())
	{
		/*if (!count($aParams))
		{
			$aParams = array(
				'code' => $this->_data,
				'redirect_uri' => $this->_redirectUri,
				'client_id' => $this->_username,
				'scope' => '',
				'client_secret' => $this->_password,
				'grant_type' => 'authorization_code'
			);
		}*/

		if (!strlen($this->_username))
		{
			$this->_token = FALSE;
			throw new Core_Exception("Invalid OAuth client_id");
		}

		if (!strlen($this->_password))
		{
			$this->_token = FALSE;
			throw new Core_Exception("Invalid OAuth secret");
		}

		/*if (!strlen($this->_data))
		{
			$this->_token = FALSE;
			throw new Core_Exception("Invalid OAuth code");
		}*/

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('POST')
			->url("https://accounts.google.com/o/oauth2/token");

		foreach ($aParams as $key => $value)
		{
			$Core_Http->data($key, $value);
		}

		$Core_Http->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error']))
		{
			$this->_token = FALSE;
			throw new Core_Exception("Server response: {$aAnswer['error']}");
		}

		$aAnswer['time'] = time();

		$sAnswer = json_encode($aAnswer);

		//$this->_data = $sAnswer;

		return $sAnswer;
	}

	/**
	 * Get token
	 * @return string
	 */
	public function getToken()
	{
		if (is_null($this->_token))
		{
			$this->_token = json_decode($this->_data);

			if (is_object($this->_token))
			{
				// Проверяем токен на актуальность
				if (time() - $this->_token->time >= $this->_token->expires_in)
				{
					// Токен нужно обновить
					$sRefreshToken = $this->_token->refresh_token;
					$this->_token = json_decode(
						$this->getAccessToken(
							array(
								'client_id' => $this->_username,
								'client_secret' => $this->_password,
								'refresh_token' => $sRefreshToken,
								'grant_type' => 'refresh_token'
							)
						)
					);
					$this->_token->refresh_token = $sRefreshToken;
					$this->_token->time = time();
					$this->_data = json_encode($this->_token);
				}
			}
			/*else
			{
				$sAccessToken = $this->getAccessToken();
			}*/
		}

		return $this->_token;
	}

	/*
	 * CalDAV connect
	 * @return string
	 */
	public function connect()
	{
		$this->_errno = $this->_error = NULL;

		$AccessToken = $this->getToken();

		$sResponse = NULL;

		if (isset($AccessToken->access_token))
		{
			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('OPTIONS')
				->userAgent('CalDAV Client')
				->url($this->_url)
				->additionalHeader('Content-Type', 'text/plain')
				->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
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
		}
		else
		{
			$this->_errno = 0;
			$this->_error = 'access_token does not exist.';
		}

		return $sResponse;
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

		$AccessToken = $this->getToken();

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
			->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
			->rawData($data)
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
					$namespaces = $oResponse->getNameSpaces(TRUE);
					$responceChildren = (array)$oResponse->children($namespaces['D']);

					$oProps = $oResponse->xpath('D:propstat/D:prop');

					foreach ($oProps as $oProp)
					{
						// Use that namespace
						$namespaces = $oProp->getNameSpaces(TRUE);

						// Now we don’t have the URL hard-coded
						$propChildren = (array)$oProp->children($namespaces['D']);

						// $aProp = $this->xml2array($oProp);

						if (isset($propChildren['displayname'])
							&& strlen($propChildren['displayname'])
						)
						{
							$explodeHref = explode('/', strval($responceChildren['href']));

							if (isset($explodeHref[3]))
							{
								$calId = strval($explodeHref[3]);

								$aReturn[$calId] = array(
									'name' => strval($propChildren['displayname']),
									'href' => "/caldav/v2/{$calId}/events/",
									'ctag' => '',
									'color' => ''
								);
							}
						}
					}
				}
			}
		}

		return $aReturn;
	}

	/*
	 * Create event
	 * @return string
	 */
	public function create($aEvent, $sUrl, $sCalendar)
	{
		$AccessToken = $this->getToken();

		if ($aEvent['status_code'] != 200)
		{
			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('PUT')
				->userAgent('CalDAV Client')
				->url($sUrl)
				->additionalHeader('Content-Type', 'text/calendar; encoding="utf-8"')
				->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
				->rawData($sCalendar)
				->execute();

			$aHeaders = $Core_Http->parseHeaders();

			return Core_Array::get($aHeaders, 'Schedule-Tag');
		}
	}

	/*
	 * Update event
	 * @return string
	 */
	public function update($aEvent, $sUrl, $sCalendar)
	{
		$AccessToken = $this->getToken();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('PUT')
			->userAgent('CalDAV Client')
			->url($sUrl)
			->additionalHeader('Content-Type', 'text/calendar; encoding="utf-8"')
			->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
			->rawData($sCalendar)
			->execute();

		$aHeaders = $Core_Http->parseHeaders();

		return Core_Array::get($aHeaders, 'Schedule-Tag');
	}

	/*
	 * Get calendar event
	 * @return array
	 */
	public function getEvent($sHref)
	{
		$AccessToken = $this->getToken();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->userAgent('CalDAV Client')
			->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
			->url($sHref)
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
				$aReturn['etag'] = Core_Array::get($aHeaders, 'Schedule-Tag');
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
		$AccessToken = $this->getToken();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('DELETE')
			->userAgent('CalDAV Client')
			->url($sUrl)
			->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
			->execute();

		$aHeaders = $Core_Http->parseHeaders();

		return Core_Array::get($aHeaders, 'status');
	}
}