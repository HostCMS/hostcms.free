<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Creates a zip archive
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Zip
{
	protected $_ZipArchive = NULL;

	protected $_excludeDir = array();

	/**
	 * Check Available
	 * @return boolean
	 */
	static public function available()
	{
		return class_exists('ZipArchive');
	}

	protected $_iFiles = 0;

	/**
	* Add files and sub-directories in a folder to zip file.
	* @param string $folder
	* @param int $exclusiveLength Number of text to be exclusived from the file path.
	*/
	protected function _folderToZip($folder, $exclusiveLength)
	{
		$dh = opendir($folder);

		while (($file = readdir($dh)) !== FALSE)
		{
			if ($file != '.' && $file != '..')
			{
				$filePath = $folder . DIRECTORY_SEPARATOR . $file;

				// Remove prefix from file path before add to zip.
				$localPath = substr($filePath, $exclusiveLength);

				if (is_file($filePath))
				{
					$this->_ZipArchive->addFile($filePath, $localPath);
					$this->_iFiles++;

					if ($this->_iFiles == 2048)
					{
						// Reopen
						$this->_ZipArchive->close();
						$this->_ZipArchive->open($this->_outputPath);

						$this->_iFiles = 0;
					}
				}
				elseif (is_dir($filePath))
				{
					$use = TRUE;

					foreach ($this->_excludeDir as $excludeDir)
					{
						if (strpos($filePath, $excludeDir) === 0)
						{
							$use = FALSE;
							break;
						}
					}

					if ($use)
					{
						// Add sub-directory.
						$this->_ZipArchive->addEmptyDir($localPath);
						$this->_folderToZip($filePath, $exclusiveLength);
					}
				}
			}
		}

		closedir($dh);
	}

	protected $_outputPath = NULL;

	/**
	* Zip a folder.
	*
	* @param string $sourcePath Path of directory to be zip.
	* @param string $outZipPath Path of output zip file.
	*/
	public function zipDir($sourcePath, $outZipPath)
	{
		clearstatcache();

		$sourcePath = realpath($sourcePath);

		$this->_outputPath = $outZipPath;

		$this->_iFiles = 0;

		$this->_ZipArchive = new ZipArchive();
		$result = $this->_ZipArchive->open($this->_outputPath, ZIPARCHIVE::CREATE);

		if ($result === TRUE)
		{
			$this->_folderToZip($sourcePath, strlen($sourcePath . DIRECTORY_SEPARATOR));
			$this->_ZipArchive->close();
		}
		else
		{
			throw new Core_Exception('ZipArchive open error, code: %code', array('%code' => $result));
		}
	}

	/**
	 * Add Excluding Dir
	 */
	public function excludeDir($dirPath)
	{
		$this->_excludeDir[] = rtrim($dirPath, '\/');
		return $this;
	}
}