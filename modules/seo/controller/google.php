<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Google_Controller.
 *
 * @package HostCMS 6\Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$this->_token = $oGoogleToken->access_token;

			// Проверяем токен на актуальность
			if (time() - $oGoogleToken->time >= $oGoogleToken->expires_in)
			{
				// Токен нужно обновить
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('POST')
					->url("https://www.hostcms.ru/seo-google-callback.php")
					->data('grant_type', 'refresh_token')
					->data('refresh_token', $oGoogleToken->refresh_token)
					->execute();

				$oNewGoogleToken = json_decode($Core_Http->getBody());

				$oNewGoogleToken->refresh_token = $oGoogleToken->refresh_token;
				$oNewGoogleToken->time = time();

				$this->_oSeo_Site->token = json_encode($oNewGoogleToken);
				$this->_oSeo_Site->save();

				$this->_token = $oNewGoogleToken->access_token;
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
		return "https://accounts.google.com/o/oauth2/auth?client_id=570995708792-6l1afmd6b1bm67e2qq4qh3uno543i9oo.apps.googleusercontent.com&response_type=code&approval_prompt=force&access_type=offline&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fwebmasters&https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fwebmasters.readonly&https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fsiteverification&https%3A%2F%2Fwww.googleapis.com%2Fauth%siteverification.verify_only&redirect_uri=https://www.hostcms.ru/seo-google-callback.php";
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
						$pos = strpos($aHost['siteUrl'], '//' . $oSiteAlias->name);

						if ($pos !== FALSE)
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

		$aAnswer = json_decode($Core_Http->getBody(), TRUE);

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
	 * @return array
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
			->url("https://www.googleapis.com/siteVerification/v1/token?fields=token")
			->additionalHeader("Authorization", "Bearer {$this->_token}")
			->additionalHeader("Content-Type", "application/json")
			->rawData($sData)
			->execute();

			$aAnswer = json_decode($Core_Http->getBody(), TRUE);

			if (isset($aAnswer['error']))
			{
				throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error']['code'], '%message' => $aAnswer['error']['message']), 0, FALSE);
			}

			echo "<pre>";
			var_dump($aAnswer);
			echo "</pre>";

			// $sContent = "google-site-verification: google{$aAnswer['verification_uin']}";

			// Create verification file
			// Core_File::write(CMS_FOLDER . "google" . $aAnswer['verification_uin'] . ".html", $sContent);
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

		$aAnswer = json_decode($Core_Http->getBody(), TRUE);

		if (isset($aAnswer['error']))
		{
			throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error']['code'], '%message' => $aAnswer['error']['message']), 0, FALSE);
		}

		// echo "<pre>";
		// var_dump($aAnswer);
		// echo "</pre>";

		return $aAnswer;
	}

	public function getSitePopularQueries()
	{

	}
}