<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam controller
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'log',
		'ip',
		'userAgent',
		'text'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->ip = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
		$this->userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT');

		$this->log = TRUE;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->closeGeoIp();
	}

	protected $_antispam_GeoIP_Controller = NULL;
	protected $_geoip = NULL;

	public function getCountryCode($ip)
	{
		if (is_null($this->_antispam_GeoIP_Controller))
		{
			$this->_antispam_GeoIP_Controller = new Antispam_GeoIP_Controller();
		}

		if (is_null($this->_geoip))
		{
			$this->_geoip = $this->_antispam_GeoIP_Controller->geoip_open(
				CMS_FOLDER . 'modules/antispam/geoip/GeoIP.dat', $this->_antispam_GeoIP_Controller->GEOIP_STANDARD
			);
		}

		$countryCode = !is_null($this->_geoip)
			? $this->_antispam_GeoIP_Controller->geoip_country_code_by_addr($this->_geoip, $ip)
			: NULL;

		return $countryCode;
	}

	public function closeGeoIp()
	{
		if (!is_null($this->_geoip))
		{
			$this->_antispam_GeoIP_Controller->geoip_close($this->_geoip);
			$this->_geoip = NULL;
		}

		return $this;
	}

	/**
	 * Add text
	 * @param string $text text
	 * @return self
	 */
	public function addText($text)
	{
		$this->text .= ' ' . htmlspecialchars(strval($text));
		return $this;
	}

	/**
	 * Antispam_Stopwords Cache
	 * @var NULL|array
	 */
	protected $_aAntispam_Stopwords = NULL;

	/**
	 * Get Antispam_Stopwords
	 * @return array
	 */
	public function getAntispamStopword()
	{
		if (is_null($this->_aAntispam_Stopwords))
		{
			$this->_aAntispam_Stopwords = Core_Entity::factory('Antispam_Stopword')->findAll(FALSE);
		}

		return $this->_aAntispam_Stopwords;
	}

	/**
	 * Execute business logic.
	 * @return boolean FALSE - SPAM, TRUE - regular request
	 */
	public function execute()
	{
		$countryCode = $this->getCountryCode($this->ip);

		if ($this->log)
		{
			$oAntispam_Log = Core_Entity::factory('Antispam_Log');
			$oAntispam_Log->ip = $this->ip;
			$oAntispam_Log->user_agent = $this->userAgent;
		}

		$oAntispam_Country = strlen($countryCode)
			? Core_Entity::factory('Antispam_Country')->getByCode($countryCode)
			: NULL;

		if (!is_null($oAntispam_Country))
		{
			$this->log
				&& $oAntispam_Log->antispam_country_id = $oAntispam_Country->id;

			if (!$oAntispam_Country->allow)
			{
				if ($this->log)
				{
					$oAntispam_Log->result = 0;
					$oAntispam_Log->save();
				}

				return FALSE;
			}
		}

		$aAntispam_Stopwords = $this->getAntispamStopword();
		foreach ($aAntispam_Stopwords as $oAntispam_Stopword)
		{
			if ($oAntispam_Stopword->value != '' && strpos($this->text, $oAntispam_Stopword->value) !== FALSE)
			{
				if ($this->log)
				{
					$oAntispam_Log->result = 0;
					$oAntispam_Log->save();
				}

				return FALSE;
			}
		}

		if ($this->log)
		{
			$oAntispam_Log->result = 1;
			$oAntispam_Log->save();
		}

		return TRUE;
	}
}