<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * File core out
 *
 * <code>
 * $oCore_Out_File = new Core_Out_File();
 * $oCore_Out_File
 * 	->filePath(CMS_FOLDER . 'file.txt')
 * 	->open()
 * 	->write('content 1')
 * 	->write('content 2')
 * 	->write('content 3')
 * 	->close();
 * </code>
 * @package HostCMS
 * @subpackage Core\Out
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Out_File extends Core_Out
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'filePath',
	);

	/**
	 * File handler
	 * @var resource
	 */
	protected $_handler = NULL;

	/**
	 * Open file 
	 * @return self
	 */
	public function open()
	{
		if (is_null($this->filePath))
		{
			throw new Core_Exception("filePath is not defined.");
		}

		$this->_handler = fopen($this->filePath, 'w');
		if ($this->_handler)
		{
			flock($this->_handler, LOCK_EX);
			return $this;
		}

		throw new Core_Exception("File '%fileName' open error.",
			array('%fileName' => Core::cutRootPath($this->filePath)));
	}

	/**
	 * Write content into file
	 * @param string $content content
	 * @return self
	 */
	public function write($content)
	{
		fwrite($this->_handler, $content);
		return $this;
	}

	/**
	 * Close file
	 * @return self
	 */
	public function close()
	{
		flock($this->_handler, LOCK_UN);
		fclose($this->_handler);

		@chmod($this->filePath, CHMOD_FILE);
		return $this;
	}
}