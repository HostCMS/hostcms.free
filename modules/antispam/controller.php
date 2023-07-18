<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam controller
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		//'text'
	);

	/**
	 * Text
	 * @var array
	 */
	protected $_text = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->ip = Core::getClientIp();
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

	/**
	 * GeoIP controller
	 * @var Antispam_GeoIP_Controller
	 */
	protected $_antispam_GeoIP_Controller = NULL;

	/**
	 * GeoIP
	 * @var object|NULL
	 */
	protected $_geoip = NULL;

	/**
	 * Get country code
	 * @param string $ip
	 * @return string
	 */
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

	/**
	 * Close GeoIP
	 * @return self
	 */
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
	 * Set text
	 * @param string $text text
	 * @return self
	 */
	public function text($text)
	{
		$this->_text = array('message' => $text);
		return $this;
	}

	/**
	 * Add text
	 * @param string $text text
	 * @param string|NULL $fieldName field name, default NULL
	 * @return self
	 */
	public function addText($text, $fieldName = NULL)
	{
		$text = strval($text);

		if ($text != '')
		{
			!is_null($fieldName)
				? $this->_text[$fieldName] = htmlspecialchars($text)
				: $this->_text[] = htmlspecialchars($text);
		}

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
	 * @hostcms-event Antispam_Controller.onBeforeExecute
	 * @hostcms-event Antispam_Controller.onAfterExecute
	 */
	public function execute()
	{
		$result = TRUE;

		Core_Event::notify('Antispam_Controller.onBeforeExecute', $this, array($this->_text));

		$eventResult = Core_Event::getLastReturn();
		!is_null($eventResult) && $result = $eventResult;

		if ($this->log)
		{
			$oAntispam_Log = Core_Entity::factory('Antispam_Log');
			$oAntispam_Log->ip = $this->ip;
			$oAntispam_Log->user_agent = $this->userAgent;
		}

		// Not SPAM
		if ($result)
		{
			$countryCode = $this->getCountryCode($this->ip);
			$oAntispam_Country = strlen($countryCode)
				? Core_Entity::factory('Antispam_Country')->getByCode($countryCode)
				: NULL;

			if (!is_null($oAntispam_Country))
			{
				$this->log
					&& $oAntispam_Log->antispam_country_id = $oAntispam_Country->id;

				if (!$oAntispam_Country->allow)
				{
					$result = FALSE;
				}
			}
		}

		// Not SPAM
		if ($result)
		{
			$text = implode(' ', $this->_text);

			$aAntispam_Stopwords = $this->getAntispamStopword();
			foreach ($aAntispam_Stopwords as $oAntispam_Stopword)
			{
				if ($oAntispam_Stopword->value != '' && strpos($text, $oAntispam_Stopword->value) !== FALSE)
				{
					$result = FALSE;
					break;
				}
			}
		}

		Core_Event::notify('Antispam_Controller.onAfterExecute', $this, array($this->_text));

		$eventResult = Core_Event::getLastReturn();
		!is_null($eventResult) && $result = $eventResult;

		if ($this->log)
		{
			$oAntispam_Log->result = intval($result); // 0 - SPAM, 1 - Regular Request
			$oAntispam_Log->save();
		}

		return $result;
	}
}