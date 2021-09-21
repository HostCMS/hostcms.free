<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract Printlayout.
 *
 * @package HostCMS 6
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Printlayout_Driver_Controller
{
	protected $_extension = NULL;

	protected $_title = NULL;

	public function getExtension()
	{
		return $this->_extension;
	}

	protected $_sourceDocx = NULL;

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return 'Printlayout_Driver_' . ucfirst($driver);
	}

	/**
	 * Create and return an object of printlayout
	 * @param string $driveName
	 */
	static public function factory($driverName)
	{
		$driver = self::_getDriverName($driverName);
		return new $driver();
	}

	/**
	 * Get file
	 * @return string
	 */
	public function setFile($sourceDocx)
	{
		$this->_sourceDocx = $sourceDocx;
		return $this;
	}

	/**
	 * Set title
	 * @return string
	 */
	public function setTitle($title)
	{
		$this->_title = $title;
		return $this;
	}

	/**
	 * Execute
	 * @return self
	 */
	abstract public function execute();

	/**
	 * Get file
	 * @return string
	 */
	abstract public function getFile();

	/**
	 * Check available
	 * @return boolean
	 */
	abstract public function available();
}