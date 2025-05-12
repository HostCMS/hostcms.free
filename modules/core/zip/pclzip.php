<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Implements ZipInterface
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Zip_Pclzip
{
	const CREATE = 1;
	const EXCL = 2;
	const CHECKCONS = 4;
	const OVERWRITE = 8;
	const FL_NOCASE = 1;
	const FL_NODIR = 2;
	const FL_COMPRESSED = 4;
	const FL_UNCHANGED = 8;
	const CM_DEFAULT = -1;
	const CM_STORE = 0;
	const CM_SHRINK = 1;
	const CM_REDUCE_1 = 2;
	const CM_REDUCE_2 = 3;
	const CM_REDUCE_3 = 4;
	const CM_REDUCE_4 = 5;
	const CM_IMPLODE = 6;
	const CM_DEFLATE = 8;
	const CM_DEFLATE64 = 9;
	const CM_PKWARE_IMPLODE = 10;
	const CM_BZIP2 = 12;
	const CM_LZMA = 14;
	const CM_TERSE = 18;
	const CM_LZ77 = 19;
	const CM_WAVPACK = 97;
	const CM_PPMD = 98;
	const ER_OK = 0;
	const ER_MULTIDISK = 1;
	const ER_RENAME = 2;
	const ER_CLOSE = 3;
	const ER_SEEK = 4;
	const ER_READ = 5;
	const ER_WRITE = 6;
	const ER_CRC = 7;
	const ER_ZIPCLOSED = 8;
	const ER_NOENT = 9;
	const ER_EXISTS = 10;
	const ER_OPEN = 11;
	const ER_TMPOPEN = 12;
	const ER_ZLIB = 13;
	const ER_MEMORY = 14;
	const ER_CHANGED = 15;
	const ER_COMPNOTSUPP = 16;
	const ER_EOF = 17;
	const ER_INVAL = 18;
	const ER_NOZIP = 19;
	const ER_INTERNAL = 20;
	const ER_INCONS = 21;
	const ER_REMOVE = 22;
	const ER_DELETED = 23;
	const OPSYS_DOS = 0;
	const OPSYS_AMIGA = 1;
	const OPSYS_OPENVMS = 2;
	const OPSYS_UNIX = 3;
	const OPSYS_VM_CMS = 4;
	const OPSYS_ATARI_ST = 5;
	const OPSYS_OS_2 = 6;
	const OPSYS_MACINTOSH = 7;
	const OPSYS_Z_SYSTEM = 8;
	const OPSYS_Z_CPM = 9;
	const OPSYS_WINDOWS_NTFS = 10;
	const OPSYS_MVS = 11;
	const OPSYS_VSE = 12;
	const OPSYS_ACORN_RISC = 13;
	const OPSYS_VFAT = 14;
	const OPSYS_ALTERNATE_MVS = 15;
	const OPSYS_BEOS = 16;
	const OPSYS_TANDEM = 17;
	const OPSYS_OS_400 = 18;
	const OPSYS_OS_X = 19;
	const OPSYS_DEFAULT = 3;

	/**
	 * @var PclZip
	 */
	protected $_oPclZip;

	/**
	 * @var string
	 */
	protected $_tmpDir;

	/**
     * Number of files (emulate ZipArchive::$numFiles)
     * @var int
     */
    public $numFiles = 0;

    /**
     * Archive filename (emulate ZipArchive::$filename)
     * @var string
     */
    public $filename;

	public $context;

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
		$this->filename = $filename;

		$this->_oPclZip = new PclZip($filename);

		$this->numFiles = count($this->_oPclZip->listContent());

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
		// if fopen('zip://...) $this->_oPclZip may be NULL
		if ($this->_oPclZip)
		{
			$zipFunction = "pclzip{$function}";

			// Run function
			if (method_exists($this->_oPclZip, $zipFunction))
			{
				return @call_user_func_array(array($this->_oPclZip, $zipFunction), $args);
			}
			elseif (strtolower($zipFunction) === 'pclziplocatename')
			{
				return @call_user_func_array(array($this, 'pclzipLocateName'), $args);
			}
			else
			{
				throw new Core_Exception("%class: method %function does not exist", array('%class' => __CLASS__, '%function' => $function));
			}
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

/*if (function_exists('stream_get_wrappers') && !in_array('zip', stream_get_wrappers()))
{
	// stream_* методы не реализованы
	stream_wrapper_register('zip', 'Core_Zip_Pclzip');
}*/
