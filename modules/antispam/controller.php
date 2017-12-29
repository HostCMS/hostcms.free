<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam controller
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
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
	 * Execute business logic.
	 * @return boolean FALSE - SPAM, TRUE - regular request
	 */
	public function execute()
	{
		$oAntispam_GeoIP_Controller = new Antispam_GeoIP_Controller();
		$geoInit = $oAntispam_GeoIP_Controller->geoip_open(
			CMS_FOLDER . 'modules/antispam/geoip/GeoIP.dat', $oAntispam_GeoIP_Controller->GEOIP_STANDARD
		);

		if (!is_null($geoInit))
		{
			$countryCode = $oAntispam_GeoIP_Controller->geoip_country_code_by_addr($geoInit, $this->ip);
			$oAntispam_GeoIP_Controller->geoip_close($geoInit);
		}
		else
		{
			$countryCode = NULL;
		}

		$oAntispam_Log = Core_Entity::factory('Antispam_Log');
		$oAntispam_Log->ip = $this->ip;
		$oAntispam_Log->user_agent = $this->userAgent;

		$oAntispam_Country = strlen($countryCode)
			? Core_Entity::factory('Antispam_Country')->getByCode($countryCode)
			: NULL;

		if (!is_null($oAntispam_Country))
		{
			$oAntispam_Log->antispam_country_id = $oAntispam_Country->id;

			if (!$oAntispam_Country->allow)
			{
				$oAntispam_Log->result = 0;
				$oAntispam_Log->save();

				return FALSE;
			}
		}

		$aAntispam_Stopwords = Core_Entity::factory('Antispam_Stopword')->findAll(FALSE);

		foreach ($aAntispam_Stopwords as $oAntispam_Stopword)
		{
			if (strpos($this->text, $oAntispam_Stopword->value) !== FALSE)
			{
				$oAntispam_Log->result = 0;
				$oAntispam_Log->save();

				return FALSE;
			}
		}

		$oAntispam_Log->result = 1;
		$oAntispam_Log->save();

		return TRUE;
	}
}