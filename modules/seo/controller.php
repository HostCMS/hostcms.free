<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract Seo.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Seo_Controller{
	/**
	 * Token
	 * @var string
	 */
	protected $_token = NULL;

	/**
	 * Site object
	 * @var Site_Model
	 */
	protected $_oSite = NULL;

	/**
	 * Seo site object
	 * @var Site_Model
	 */
	protected $_oSeo_Site = NULL;
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name)
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$driver = self::_getDriverName($name);
			self::$instance[$name] = new $driver();
		}

		return self::$instance[$name];
	}

	/**
	 * Execute
	 * @return boolean
	 */
	abstract public function execute();

	/**
	 * Get token url
	 * @return string
	 */
	abstract public function getTokenUrl();

	/**
	 * Get rating name
	 * @return string
	 */
	abstract public function getRatingName();

	/**
	 * Get popular queries
	 * @return array
	 */
	abstract public function getSitePopularQueries($host_id);

	/**
	 * Get popular pages
	 * @return array
	 */
	abstract public function getSitePopularPages($host_id);

	/**
	 * Set token
	 * @param string $token token
	 * @return self
	 */
	public function setToken($token)
	{
		$this->_token = $token;
		return $this;
	}

	/**
	 * Set site
	 * @param string $oSite Site Model
	 * @return self
	 */
	public function setSite(Site_Model $oSite)
	{
		$this->_oSite = $oSite;
		return $this;
	}

	/**
	 * Set seo site
	 * @param string $oSite Site Model
	 * @return self
	 */
	public function setSeoSite(Seo_Site_Model $oSeo_Site)
	{
		$this->_oSeo_Site = $oSeo_Site;

		// Set token
		$this->setToken($this->_oSeo_Site->token);

		// Set site
		$this->setSite($this->_oSeo_Site->Site);

		return $this;
	}}