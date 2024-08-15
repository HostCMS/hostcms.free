<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Driver_Libreoffice
 * yum install libreoffice-core libreoffice-headless libreoffice-writer
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Printlayout_Driver_Libreoffice extends Printlayout_Driver_Controller
{
	/**
	 * Extension
	 * @var string|NULL
	 */
	protected $_extension = 'pdf';

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		$tmpPath = CMS_FOLDER . TMP_DIR;

		$sh = '';

		// Пользователь apache не имеет $HOME, поэтому libreoffice не может работать, если $HOME не определен
		if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN')
		{
			$sh .= 'export HOME=' . $tmpPath . ' && ';
		}

		$aConfig = Core_Config::instance()->get('printlayout_config', array());

		$libreofficePath = isset($aConfig['libreoffice']['path'])
			? strval($aConfig['libreoffice']['path'])
			: '';

		if (strlen($libreofficePath))
		{
			$sh .= $libreofficePath . ' --headless --writer --invisible --convert-to pdf --outdir ' . $tmpPath . ' ' . $this->_sourceDocx;

			@shell_exec($sh);

			Core_File::delete($this->_sourceDocx);
		}

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