<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Stream core out
 *
 * @package HostCMS
 * @subpackage Core\Out
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Out_ZipStream extends Core_Out
{
	/** @var Core_Zip */
	protected $_zip;

	protected $_filenameInZip;

	public function __construct(Core_Zip $zip, string $filenameInZip)
	{
		$this->_zip = $zip;

		$this->_filenameInZip = $filenameInZip;
	}

	/**
	 * Open stream
	 */
	public function open()
	{
		// Начинаем файл внутри архива
		$this->_zip->beginWriteStream($this->_filenameInZip);

		return $this;
	}

	// Перехватываем запись
	public function write($content)
	{
		$this->_zip->writeStreamChunk($content);

		return $this;
	}

	// Завершаем файл при уничтожении объекта или явном закрытии
	public function close()
	{
		$this->_zip->endWriteStream();
	}
}