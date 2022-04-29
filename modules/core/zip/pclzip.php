<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Implements ZipInterface
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Zip_Pclzip
{
	/**
	 * @var PclZip
	 */
	protected $_oPclZip;

	/**
	 * @var string
	 */
	protected $_tmpDir;

	public function __construct()
	{
		$this->_tmpDir = CMS_FOLDER . rtrim(TMP_DIR, '/\\');

		if (!defined('PCLZIP_TEMPORARY_DIR')) {
			define('PCLZIP_TEMPORARY_DIR', $this->_tmpDir . DIRECTORY_SEPARATOR);
		}

		require_once(CMS_FOLDER . 'modules/vendor/pclzip/pclzip.lib.php');
	}

	public function open($filename, $flags = NULL)
	{
		$this->_oPclZip = new PclZip($filename);
		return TRUE;
	}

	public function close()
	{
		return TRUE;
	}

	/**
	 * Catch function calls
	 *
	 * @param mixed $function
	 * @param mixed $args
	 * @return mixed
	 */
	public function __call($function, $args)
	{
		$zipFunction = "pclzip{$function}";

		// Run function
		if (method_exists($this->_oPclZip, $zipFunction))
		{
			@call_user_func_array(array($this->_oPclZip, $zipFunction), $args);
		}
		else
		{
			throw new Core_Exception("%class: method %function does not exist", array('%class' => __CLASS__, '%function' => $function));
		}
	}

	/**
	 * Add a new file to the zip archive
	 *
	 * @param string $filename Directory/Name of the file to add to the zip archive
	 * @param string $localname Directory/Name of the file added to the zip
	 * @return bool
	 */
	public function addFile($filename, $localname = NULL)
	{
		$realpathFilename = realpath($filename);
		if ($realpathFilename !== FALSE) {
			$filename = $realpathFilename;
		}

		$filenameParts = pathinfo($filename);
		$localnameParts = pathinfo($localname);

		$tempFile = FALSE;
		if ($filenameParts['basename'] != $localnameParts['basename']) {
			$tempFile = TRUE;
			$temppath = $this->_tmpDir . DIRECTORY_SEPARATOR . $localnameParts['basename'];
			copy($filename, $temppath);
			$filename = $temppath;
			$filenameParts = pathinfo($temppath);
		}

		$pathRemoved = $filenameParts['dirname'];
		$pathAdded = $localnameParts['dirname'];

		$res = $this->_oPclZip->add($filename, PCLZIP_OPT_REMOVE_PATH, $pathRemoved, PCLZIP_OPT_ADD_PATH, $pathAdded);

		if ($tempFile) {
			// Remove temp file, if created
			unlink($this->_tmpDir . DIRECTORY_SEPARATOR . $localnameParts['basename']);
		}

		return $res != 0;
	}

	/**
	 * Add a file to a ZIP archive using its contents
	 *
	 * @param string $localname The name of the entry to create.
	 * @param string $contents The contents to use to create the entry. It is used in a binary safe mode.
	 * @return bool
	 */
	public function addFromString($localname, $contents)
	{
		$pathData = pathinfo($localname);

		$hFile = fopen($this->_tmpDir . DIRECTORY_SEPARATOR . $pathData['basename'], "wb");
		fwrite($hFile, $contents);
		fclose($hFile);

		$res = $this->_oPclZip->add($this->_tmpDir . DIRECTORY_SEPARATOR . $pathData['basename'], PCLZIP_OPT_REMOVE_PATH, $this->_tmpDir, PCLZIP_OPT_ADD_PATH, $pathData['dirname']);
		if ($res == 0) {
			throw new Core_Exception("Error zipping files : " . $this->_oPclZip->errorInfo(TRUE));
		}
		unlink($this->_tmpDir . DIRECTORY_SEPARATOR . $pathData['basename']);

		return TRUE;
	}

	/**
	 * Extract file from archive by given file name
	 *
	 * @param string $filename Filename for the file in zip archive
	 * @return string $contents File string contents
	 */
	public function getFromName($filename)
	{
		$listIndex = $this->pclzipLocateName($filename);
		$contents = FALSE;

		if ($listIndex !== FALSE) {
			$extracted = $this->_oPclZip->extractByIndex($listIndex, PCLZIP_OPT_EXTRACT_AS_STRING);
		} else {
			$filename = substr($filename, 1);
			$listIndex = $this->pclzipLocateName($filename);
			$extracted = $this->_oPclZip->extractByIndex($listIndex, PCLZIP_OPT_EXTRACT_AS_STRING);
		}
		if ((is_array($extracted)) && ($extracted != 0)) {
			$contents = $extracted[0]['content'];
		}

		return $contents;
	}

	/**
	 * Returns the index of the entry in the archive
	 *
	 * @param string $filename Filename for the file in zip archive
	 * @return int
	 */
	public function pclzipLocateName($filename)
	{
		$list = $this->_oPclZip->listContent();
		$listCount = count($list);
		$listIndex = -1;
		for ($i = 0; $i < $listCount; ++$i) {
			if (strtolower($list[$i]['filename']) == strtolower($filename) ||
				strtolower($list[$i]['stored_filename']) == strtolower($filename)) {
				$listIndex = $i;
				break;
			}
		}

		return ($listIndex > -1) ? $listIndex : FALSE;
	}

	 /**
	 * Delete file from archive by given file name
	 *
	 * @param string $filename Filename for the file in zip archive
	 * @return boolean
	 */
	public function deleteName($filename)
	{
		$this->_oPclZip->delete(PCLZIP_OPT_BY_NAME, $filename);

		return TRUE;
	}

	/**
	 * Add Empty Dir
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public function addEmptyDir($filename)
	{
		$this->_oPclZip->add($filename);
		return TRUE;
	}
}