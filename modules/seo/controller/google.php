<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Google_Controller.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Controller_Google extends Seo_Controller
{
	/**
	 * Set token
	 * @param string $token token
	 * @return self
	 */
	public function setToken($token)
	{
		$oGoogleToken = json_decode($token);

		if (is_object($oGoogleToken))
		{
			// Проверяем токен на актуальность
			if (isset($oGoogleToken->expires_in)
				&& time() - $oGoogleToken->time >= $oGoogleToken->expires_in
			)
			{
				// Токен нужно обновить
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('POST')
					->url("https://www.hostcms.ru/seo-google-callback.php")
					->data('grant_type', 'refresh_token')
					->data('refresh_token', $oGoogleToken->refresh_token)
					->execute();

				$oNewGoogleToken = json_decode($Core_Http->getDecompressedBody());

				$oNewGoogleToken->refresh_token = $oGoogleToken->refresh_token;
				$oNewGoogleToken->time = time();

				$this->_oSeo_Site->token = json_encode($oNewGoogleToken);
				$this->_oSeo_Site->save();

				$this->_token = $oNewGoogleToken->access_token;
			}
			elseif (isset($oGoogleToken->access_token))
			{
				$this->_token = $oGoogleToken->access_token;
			}
		}

		return $this->_token;
	}

	/**
	 * Execute
	 * @return boolean
	 */
	public function execute()
	{
		$host_id = $this->getHostId();

		if (is_null($host_id))
		{
			$host_id = $this->addCurrentSite();

			if (!$host_id)
			{
				return FALSE;
			}

			$this->verification($host_id);
		}
	}

	/**
	 * Get token url
	 * @return string
	 */
	public function getTokenUrl()
	{
		$scopes = urlencode("https://www.googleapis.com/auth/webmasters https://www.googleapis.com/auth/webmasters.readonly https://www.googleapis.com/auth/siteverification https://www.googleapis.com/auth/siteverification.verify_only");

		return "https://accounts.google.com/o/oauth2/auth?client_id=570995708792-6l1afmd6b1bm67e2qq4qh3uno543i9oo.apps.googleusercontent.com&response_type=code&approval_prompt=force&access_type=offline&scope={$scopes}&redirect_uri=https://www.hostcms.ru/seo-google-callback.php";
	}

	/**
	 * Get host id
	 * @return id|NULL
	 */
	public function getHostId()
	{
		$aHostList = $this->getSiteList();

		if (isset($aHostList['siteEntry']))
		{
			$oSiteAlias = $this->_oSite->getCurrentAlias();
			if ($oSiteAlias)
			{
				foreach ($aHostList['siteEntry'] as $aHost)
				{
					if ($aHost['permissionLevel'] == 'siteOwner')
					{
						// Доменные ресурсы в Search Console, теперь два варианта, где спользуется доменный ресурс и предыдущий вариант
						if (strpos($aHost['siteUrl'], 'sc-domain:' . $oSiteAlias->name) === 0
							|| strpos($aHost['siteUrl'], '//' . $oSiteAlias->name) !== FALSE)
						{
							return $aHost['siteUrl'];
						}
					}
				}
			}
		}

		return NULL;
	}

	/**
	 * Get user sites list
	 * @return array
	 */
	public function getSiteList()
	{
		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url("https://www.googleapis.com/webmasters/v3/sites?fields=siteEntry")
			->additionalHeader("Authorization", "Bearer {$this->_token}")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error']))
		{
			throw new Core_Exception("getSiteList(), Server response %code: %message", array('%code' => $aAnswer['error']['code'], '%message' => $aAnswer['error']['message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Add current site
	 * @return id|NULL
	 */
	public function addCurrentSite()
	{
		// "host_url": "http://example.com"

		$oIndexPage = $this->_oSite->Structures->getByPath('/');

		if (!is_null($oIndexPage))
		{
			$oSiteAlias = $this->_oSite->getCurrentAlias();
			if ($oSiteAlias)
			{
				$host_url = $oIndexPage->https ? 'https://' : 'http://' . $oSiteAlias->name . '/';

				$hostID = $this->addSite($host_url);
				return $hostID;
			}
		}

		return NULL;
	}

	/**
	 * Add site into Google Search
	 * @param string $host_url domain url
	 * @return string|NULL
	 */
	public function addSite($host_url)
	{
		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('PUT')
			->url("https://www.googleapis.com/webmasters/v3/sites/" . urlencode(strval($host_url)))
			->additionalHeader("Authorization", "Bearer {$this->_token}")
			->additionalHeader("Content-Length", 0)
			->execute();

		$aHeaders = $Core_Http->parseHeaders();

		if ($Core_Http->parseHttpStatusCode($aHeaders['status']) == 204)
		{
			return $host_url;
		}

		return NULL;
	}

	/**
	 * Verification
	 * @param int $host_url site url
	 * @return TRUE|NULL
	 */
	public function verification($host_url)
	{
		$sData = json_encode(
			array(
				"site" => array(
					"identifier" => $host_url,
					"type" => "SITE"
				),
				"id" => urlencode(strval($host_url)),
				"verificationMethod" => "FILE"
			)
		);

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('POST')
			->url("https://www.googleapis.com/siteVerification/v1/token?fields=token,method")
			->additionalHeader("Authorization", "Bearer {$this->_token}")
			->additionalHeader("Content-Type", "application/json")
			->rawData($sData)
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error']))
		{
			throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error']['code'], '%message' => $aAnswer['error']['message']), 0, FALSE);
		}

		if (isset($aAnswer['token']) && isset($aAnswer['method']) && $aAnswer['method'] == 'FILE')
		{
			$sContent = "google-site-verification: {$aAnswer['token']}";

			// Create verification file
			Core_File::write(CMS_FOLDER . $aAnswer['token'], $sContent);

			$sNewData = json_encode(
				array(
					"site" => array(
						"identifier" => $host_url,
						"type" => "SITE"
					)
				)
			);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('POST')
				->url("https://www.googleapis.com/siteVerification/v1/webResource?verificationMethod=FILE")
				->additionalHeader("Authorization", "Bearer {$this->_token}")
				->additionalHeader("Content-Type", "application/json")
				->rawData($sNewData)
				->execute();

			$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

			if (isset($aAnswer['error']))
			{
				throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error']['code'], '%message' => $aAnswer['error']['message']), 0, FALSE);
			}

			if (isset($aAnswer['id']))
			{
				return TRUE;
			}
		}

		return NULL;
	}

	/**
	 * Query data with filters and parameters.
	 * @param string $host_url domain url
	 * @param string $dimensions dimensions to group results
	 * @param string $start_date start date of the requested date range
	 * @param string $end_date end date of the requested date range
	 * @param int $row_limit the maximum number of rows to return
	 * @return array
	 */
	public function setQuery($host_url, $dimensions, $start_date, $end_date, $row_limit = 1000)
	{
		$sUrl = "https://www.googleapis.com/webmasters/v3/sites/" . urlencode(strval($host_url)) . "/searchAnalytics/query?fields=rows";

		$sData = json_encode(
			array(
				"startDate" => $start_date,
				"endDate" => $end_date,
				"rowLimit" => intval($row_limit),
				"dimensions" => array($dimensions)
			)
		);

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('POST')
			->url($sUrl)
			->additionalHeader("Authorization", "Bearer {$this->_token}")
			->additionalHeader("Content-Type", "application/json")
			->rawData($sData)
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error']))
		{
			throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error']['code'], '%message' => $aAnswer['error']['message']), 0, FALSE);
		}

		return isset($aAnswer['rows'])
			? $aAnswer['rows']
			: array();
	}

	/**
	 * Get icon
	 * @return string
	 */
	public function getIcon()
	{
		return "<i class='fa fa-google google'></i>";
	}

	/**
	 * Get color
	 * @return string
	 */
	public function getColor()
	{
		return "#4285f4";
	}

	/**
	 * Get rating name
	 * @return string
	 */
	public function getRatingName(){}

	/**
	 * Get site popular queries
	 * @param string $host_id domain url
	 * @return array
	 */
	public function getSitePopularQueries($host_id)
	{
		$aReturn = array();

		$limit = 500;

		$aQueries = $this->setQuery($host_id, 'query' , Core_Date::timestamp2sqldate(strtotime('-8 days')), Core_Date::timestamp2sqldate(time()), $limit);

		foreach ($aQueries as $aTmp)
		{
			$query_text = array_shift($aTmp);

			$aReturn[$query_text[0]] = array(
				'clicks' => Core_Array::get($aTmp, 'clicks', 0),
				'shows' => Core_Array::get($aTmp, 'impressions', 0)
			);
		}

		return $aReturn;
	}

	/**
	 * Get site popular pages
	 * @param string $host_id domain url
	 * @return array
	 */
	public function getSitePopularPages($host_id)
	{
		$aReturn = array();

		$limit = 500;

		$aQueries = $this->setQuery($host_id, 'page' , Core_Date::timestamp2sqldate(strtotime('-1 week')), Core_Date::timestamp2sqldate(time()), $limit);

		foreach ($aQueries as $aTmp)
		{
			$query_text = array_shift($aTmp);

			$aReturn[$query_text[0]] = array(
				'clicks' => Core_Array::get($aTmp, 'clicks', 0),
				'shows' => Core_Array::get($aTmp, 'impressions', 0)
			);
		}

		return $aReturn;
	}
}