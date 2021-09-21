<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Driver_Libreoffice
 * yum install libreoffice-core libreoffice-headless libreoffice-writer
 *
 * @package HostCMS 6
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Driver_Libreoffice extends Printlayout_Driver_Controller
{
	protected $_extension = 'pdf';

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		$tmpPath = CMS_FOLDER . TMP_DIR;

		// Пользователь apache не имеет $HOME, поэтому libreoffice не может работать, если $HOME не определен
		@shell_exec('export HOME=' . $tmpPath . ' && libreoffice --headless --writer -convert-to pdf --outdir ' . $tmpPath . ' ' . $this->_sourceDocx);

		Core_File::delete($this->_sourceDocx);

		return $this;
	}

	/**
	 * Get file
	 * @return string
	 */
	public function getFile()
	{
		return $this->_sourceDocx . '.' . $this->_extension;
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return TRUE;
	}
}