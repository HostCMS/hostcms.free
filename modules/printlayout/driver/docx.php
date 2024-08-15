<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Driver_Docx
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Printlayout_Driver_Docx extends Printlayout_Driver_Controller
{
	protected $_extension = 'docx';

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		// Nothing to do...
		return $this;
	}

	/**
	 * Get file
	 * @return string
	 */
	public function getFile()
	{
		return $this->_sourceDocx;
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return TRUE;
	}
}