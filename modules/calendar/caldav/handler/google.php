<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Caldav_Handler_Google
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Calendar_Caldav_Handler_Google extends Calendar_Caldav_Handler_Default
{
	/**
	 * Redirect uri
	 */
	protected $_redirectUri = NULL;

	/**
	 * Token
	 * @var mixed
	 */
	protected $_token = NULL;

	/**
	 * Constructor
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

		$AccessToken = $this->getToken();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method($method)
			->userAgent('CalDAV Client')
			->url($url)
			->additionalHeader("Authorization", "Bearer {$AccessToken->access_token}")
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
	 * Show second window
	 * @param Calendar_Caldav_User_Controller_Edit $oCalendar_Caldav_User_Controller_Edit
	 */
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

	/**
	 * Show second window
	 * @param Calendar_Caldav_User_Controller_Edit $oCalendar_Caldav_User_Controller_Edit
	 */
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
			$oCalendar_Caldav_User_Controller_Edit->addContent('<script>$.modalLoad({path: hostcmsBackend + \'/calendar/caldav/user/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $object->id . ']=1&calendar_caldav_id=' . $object->calendar_caldav_id. '&additionalSettings=1\', windowId: \'id_content\'});</script>');
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
	 * @return object
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
		}

		return $this->_token;
	}

	/**
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
			$Core_Http = $this->_sendRequest('OPTIONS', array('Content-Type' => 'text/plain'));

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
				$calId = strval($explodeHref[3]);

				$return = array(
					'name' => strval($propChildren['displayname']),
					'href' => "/caldav/v2/{$calId}/events/",
					'ctag' => '',
					'color' => '',
					'sync_token' => ''
				);
			}
		}

		return $return;
	}

	/**
	 * Create event
	 * @return string
	 */
	public function create($aEvent, $sUrl, $sCalendar)
	{
		// $AccessToken = $this->getToken();

		if ($aEvent['status_code'] != 200)
		{
			$Core_Http = $this->_sendRequest('PUT', array('Content-Type' => 'text/calendar; encoding="utf-8"'), $sCalendar, $sUrl);

			$aHeaders = $Core_Http->parseHeaders();

			return Core_Array::get($aHeaders, 'Schedule-Tag');
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

		return Core_Array::get($aHeaders, 'Schedule-Tag');
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
				$aReturn['etag'] = Core_Array::get($aHeaders, 'Schedule-Tag');
			break;
		}

		return $aReturn;
	}
}