<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Controller_Yandex
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Seo_Controller_Yandex extends Seo_Controller
{
	/**
	 * Yandex.Webmaster user id
	 * @var int
	 */
	protected $_user_id = NULL;

	/**
	 * Yandex.Webmaster API url
	 * @var string
	 */
	protected $_baseUrl = 'https://api.webmaster.yandex.net/v4/';

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

		// Get external links
		$aExternalLinksHistory = $this->getExternalLinksHistory($host_id);

		if (isset($aExternalLinksHistory['indicators']['LINKS_TOTAL_COUNT']))
		{
			foreach($aExternalLinksHistory['indicators']['LINKS_TOTAL_COUNT'] as $aTmp)
			{
				$date = Core_Date::timestamp2sqldate(strtotime(Core_Array::get($aTmp, 'date')));

				$oSeo_Link = $this->_oSeo_Site->Seo_Links->getByDate($date, FALSE);

				if (is_null($oSeo_Link))
				{
					$oSeo_Link = Core_Entity::factory('Seo_Link');
					$oSeo_Link
						->seo_site_id($this->_oSeo_Site->id)
						->date($date)
						->value(intval(Core_Array::get($aTmp, 'value')))
						->save();
				}
			}
		}

		// Get sqi
		$aSqiHistory = $this->getSqiHistory($host_id);

		if (isset($aSqiHistory['points']))
		{
			foreach($aSqiHistory['points'] as $aTmp)
			{
				$date = Core_Date::timestamp2sqldate(strtotime(Core_Array::get($aTmp, 'date')));

				$oSeo_Rating = $this->_oSeo_Site->Seo_Ratings->getByDate($date, FALSE);

				if (is_null($oSeo_Rating))
				{
					$oSeo_Rating = Core_Entity::factory('Seo_Rating');
					$oSeo_Rating
						->seo_site_id($this->_oSeo_Site->id)
						->date($date)
						->value(intval(Core_Array::get($aTmp, 'value')))
						->save();
				}
			}
		}

		// Get indexed pages
		$aIndicators = array(
			'SEARCHABLE',
			'DOWNLOADED',
			'DOWNLOADED_2XX',
			'DOWNLOADED_3XX',
			'DOWNLOADED_4XX',
			'DOWNLOADED_5XX',
			'FAILED_TO_DOWNLOAD',
			'EXCLUDED',
		);

		$aIndexingHistory = $this->getIndexingHistory($host_id, $aIndicators);

		$aValues = array();

		foreach ($aIndicators as $sIndicator)
		{
			if (isset($aIndexingHistory['indicators'][$sIndicator]))
			{
				foreach($aIndexingHistory['indicators'][$sIndicator] as $aTmp)
				{
					$date = Core_Date::timestamp2sqldate(strtotime(Core_Array::get($aTmp, 'date')));
					$aValues[$date][$sIndicator] = intval(Core_Array::get($aTmp, 'value'));
				}
			}
		}

		foreach ($aValues as $date => $aValue)
		{
			$oSeo_Indexed = $this->_oSeo_Site->Seo_Indexeds->getByDate($date, FALSE);

			if (is_null($oSeo_Indexed))
			{
				$oSeo_Indexed = Core_Entity::factory('Seo_Indexed');
				$oSeo_Indexed
					->seo_site_id($this->_oSeo_Site->id)
					->date($date);

				foreach ($aValue as $indicator => $value)
				{
					$indicator = strtolower($indicator);
					$oSeo_Indexed->$indicator = $value;
				}

				$oSeo_Indexed->save();
			}
		}
	}

	/**
	 * Get token url
	 * @return string
	 */
	public function getTokenUrl()
	{
		return "https://oauth.yandex.ru/authorize?response_type=code&client_id=8aa761c2df0a4683875b7e96ce301b72";
	}

	/**
	 * Get user id
	 * @return int
     */
	public function getUserId()
	{
		if (is_null($this->_user_id))
		{
			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('GET')
				->url($this->_baseUrl . 'user/')
				->additionalHeader("Authorization", "OAuth {$this->_token}")
				->additionalHeader("Accept", "application/json")
				->execute();

			$sAnswer = $Core_Http->getDecompressedBody();

			if ($sAnswer === FALSE)
			{
				throw new Core_Exception("getUserId(), Server response: false. Error: %error", array('%error' => $Core_Http->getError()), $Core_Http->getErrno(), FALSE);
			}

			$aAnswer = json_decode($sAnswer, TRUE);

			if (isset($aAnswer['error_code']))
			{
				throw new Core_Exception("getUserId(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
			}

			$this->_user_id = intval($aAnswer['user_id']);
		}

		return $this->_user_id;
	}

	/**
	 * Get user sites list
	 * @return array
	 */
	public function getSiteList()
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Content-type", "application/json")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getSiteList(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Get host id
	 * @return id|NULL
	 */
	public function getHostId()
	{
		$aHostList = $this->getSiteList();

		if (isset($aHostList['hosts']))
		{
			$oSiteAlias = $this->_oSite->getCurrentAlias();
			if ($oSiteAlias)
			{
				foreach ($aHostList['hosts'] as $aHost)
				{
					$pos = strpos($aHost['ascii_host_url'], '//' . $oSiteAlias->name);

					if ($pos !== FALSE)
					{
						return $aHost['host_id'];
					}
				}
			}
		}

		return NULL;
	}

	/**
	 * Add current site
	 * @return string|NULL
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
				$host_url = ($oIndexPage->https ? 'https://' : 'http://') . $oSiteAlias->name;

				$hostID = $this->addSite($host_url);
				return $hostID;
			}
		}

		return NULL;
	}

	/**
	 * Add site into Yandex.Webmaster
	 * @param string $host_url domain url
	 * @return string
	 */
	public function addSite($host_url)
	{
		// Get user id
		$this->getUserId();

		$sData = json_encode(
			array(
				"host_url" => $host_url
			)
		);

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('POST')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->additionalHeader("Content-type", "application/json")
			->rawData($sData)
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("Yandex:addSite(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer['host_id'];
	}

	/**
	 * Delete site from Yandex.Webmaster
	 * @param string $host_id host_id
	 * @return TRUE|FALSE
	 */
	public function deleteSite($host_id)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('DELETE')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("deleteSite(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return TRUE;
	}

	/**
	 * Get site information
	 * @param int $host_id Yandex.Webmaster site id
	 * @return self
	 */
	public function getSiteInfo($host_id)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getSiteInfo(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Get site statistics
	 * @param int $host_id Yandex.Webmaster site id
	 * @return array
	 */
	public function getSiteStatistic($host_id)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/summary/")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		var_dump($aAnswer);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getSiteStatistic(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Verification
	 * @param int $host_id Yandex.Webmaster site id
	 */
	public function verification($host_id)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/verification/")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		if (isset($aAnswer['verification_uin']) && $aAnswer['verification_state'] != 'VERIFIED')
		{
			$sContent = '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<body>Verification: ' . $aAnswer['verification_uin'] . '</body>
</html>';

			// Create verification file
			Core_File::write(CMS_FOLDER . "yandex_" . $aAnswer['verification_uin'] . ".html", $sContent);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('POST')
				->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/verification/?verification_type=HTML_FILE")
				->additionalHeader("Authorization", "OAuth {$this->_token}")
				->additionalHeader("Accept", "application/json")
				->execute();

			$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

			if (isset($aAnswer['error_code']))
			{
				throw new Core_Exception("verification(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
			}
		}
	}

	/**
	 * Get site external links
	 * @param int $host_id Yandex.Webmaster site id
	 * @param int $offset offset
	 * @param int $limit limit
	 * @return array
	 */
	public function getSiteExternalLinks($host_id, $offset = 0, $limit = 100)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/links/external/samples/?offset={$offset}&limit={$limit}")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getSiteExternalLinks(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Get site popular queries
	 * @param int $host_id Yandex.Webmaster site id
	 * @param string $order_by queries order indicator
	 * @return array
	 */
	public function getQueries($host_id, $order_by = 'TOTAL_CLICKS')
	{
		// Get user id
		$this->getUserId();

		$sUrl = $this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/search-queries/popular/?order_by={$order_by}&query_indicator=TOTAL_SHOWS&query_indicator=TOTAL_CLICKS&query_indicator=AVG_CLICK_POSITION&query_indicator=AVG_SHOW_POSITION";

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($sUrl)
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getQueries(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return isset($aAnswer['queries'])
			? $aAnswer['queries']
			: array();
	}

	/**
	 * Get site indexing history by robots
	 * @param int $host_id Yandex.Webmaster site id
	 * @param array $indexing_indicators indicator list
	 * @return array
	 */
	public function getIndexingHistory($host_id, $indexing_indicators = array('SEARCHABLE'))
	{
		// Get user id
		$this->getUserId();

		$sUrl = $this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/indexing/history/";

		$i = 0;

		foreach ($indexing_indicators as $indexing_indicator)
		{
			$prefix = $i == 0 ? '?' : '&';

			$sUrl .= $prefix . "indexing_indicator={$indexing_indicator}";

			$i++;
		}

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($sUrl)
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getIndexingHistory(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Get site sqi history for last year
	 * @param int $host_id Yandex.Webmaster site id
	 * @return array
	 */
	public function getSqiHistory($host_id)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/sqi-history/")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getSqiHistory(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Get site external links history
	 * @param int $host_id Yandex.Webmaster site id
	 * @return array
	 */
	public function getExternalLinksHistory($host_id)
	{
		// Get user id
		$this->getUserId();

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('GET')
			->url($this->_baseUrl . "user/{$this->_user_id}/hosts/{$host_id}/links/external/history/?indicator=LINKS_TOTAL_COUNT")
			->additionalHeader("Authorization", "OAuth {$this->_token}")
			->additionalHeader("Accept", "application/json")
			->execute();

		$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

		if (isset($aAnswer['error_code']))
		{
			throw new Core_Exception("getExternalLinksHistory(), Server response %code: %message", array('%code' => $aAnswer['error_code'], '%message' => $aAnswer['error_message']), 0, FALSE);
		}

		return $aAnswer;
	}

	/**
	 * Get icon
	 * @return string
	 */
	public function getIcon()
	{
		return "<span class='badge-yandex'>Я</span>";
	}

	/**
	 * Get color
	 * @return string
	 */
	public function getColor()
	{
		return "#ff0000";
	}

	/**
	 * Get rating name
	 * @return string
	 */
	public function getRatingName()
	{
		return "тИЦ";
	}

    /**
     * Get site popular queries
     * @param int $host_id Yandex.Webmaster site id
     * @return array
     * @throws Core_Exception
     */
	public function getSitePopularQueries($host_id)
	{
		$limit = 100;

		$aQueries = $this->getQueries($host_id);

		$aReturn = array();

		foreach ($aQueries as $aTmp)
		{
			$aReturn[$aTmp['query_text']] = array(
				'clicks' => $aTmp['indicators']['TOTAL_CLICKS'],
				'shows' => $aTmp['indicators']['TOTAL_SHOWS']
			);
		}

		return array_slice($aReturn, 0, $limit);
	}

	/**
	 * Get site popular pages
	 * @param string $host_url domain url
	 * @return array
	 */
	public function getSitePopularPages($host_url)
	{
		return array();
	}
}