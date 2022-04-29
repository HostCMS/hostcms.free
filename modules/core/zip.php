<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Creates a zip archive
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Zip
{
	protected $_Zip = NULL;

	protected $_excludeDir = array();

	/**
	 * Check Available
	 * @return boolean
	 */
	static public function available()
	{
		return TRUE;
	}

	/**
	 * Get Zip Class Name
	 * @return boolean
	 */
	static public function getZipClassName()
	{
		return class_exists('ZipArchive')
			? 'ZipArchive'
			: 'Core_Zip_Pclzip';
	}

	/**
	 * Get Zip Class
	 * @return object
	 */
	static public function getZipClass()
	{
		$name = self::getZipClassName();
		return new $name();
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
					if (is_readable($filePath))
					{
						$this->_Zip->addFile($filePath, $localPath);
						$this->_iFiles++;

						// Show SPACE-bytes for nginx
						if ($this->_iFiles % 200 == 0)
						{
							echo " "; // space
							ob_flush();
							flush();
						}

						if ($this->_iFiles == 8192) // 2048
						{
							// Reopen
							if ($this->_Zip->close())
							{
								$result = $this->_Zip->open($this->_outputPath);

								if ($result !== TRUE)
								{
									throw new Core_Exception('ZipArchive re-open error, code: %code', array('%code' => $result));
								}

								$this->_iFiles = 0;
							}
							else
							{
								throw new Core_Exception('ZipArchive close error');
							}
						}
					}
					else
					{
						throw new Core_Exception('ZipArchive error read file %file', array('%file' => $filePath));
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
						$this->_Zip->addEmptyDir($localPath);
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

		//$this->_Zip = new ZipArchive();
		$this->_Zip = self::getZipClass();
		$result = $this->_Zip->open($this->_outputPath, class_exists('ZipArchive') ? ZipArchive::CREATE : NULL);

		if ($result === TRUE)
		{
			$this->_folderToZip($sourcePath, strlen($sourcePath . DIRECTORY_SEPARATOR));
			$this->_Zip->close();
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