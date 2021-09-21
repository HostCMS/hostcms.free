<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * File helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_File
{
	/**
	 * File types with resize support
	 * @var array
	 */
	static public $resizeExtensions = array('JPG', 'JPEG', 'GIF', 'PNG');

	static public function getResizeExtensions()
	{
		$aReturn = self::$resizeExtensions;

		if (PHP_VERSION_ID >= 70100)
		{
			$aReturn[] = 'WEBP';
		}

		return $aReturn;
	}

	/**
	 * Moves an uploaded file to a new location
	 * @param string $source Path to the source file.
	 * @param string $destination The destination path.
	 * @param int $mode The mode parameter consists of three octal number components specifying access, e.g. 0644
	 */
	static public function moveUploadedFile($source, $destination, $mode = CHMOD_FILE)
	{
		if (is_uploaded_file($source))
		{
			$destination = str_replace(array("\r", "\n", "\0"), '', $destination);

			// Create destination dir
			self::mkdir(dirname($destination), CHMOD, TRUE);

			if (move_uploaded_file($source, $destination))
			{
				chmod($destination, $mode);
			}
			else
			{
				throw new Core_Exception("Move uploaded file '%source' error.",
					array('%source' => Core::cutRootPath($source)));
			}
		}
		else
		{
			throw new Core_Exception("The file '%source' is not uploaded file.",
				array('%source' => Core::cutRootPath($source)));
		}
	}

	/**
	 * Copies file
	 * @param string $source The source path.
	 * @param string $destination The destination path.
	 * @param int $mode The mode parameter consists of three octal number components specifying access, e.g. 0644
	 * @return bool
	 */
	static public function copy($source, $destination, $mode = CHMOD_FILE)
	{
		if (is_file($source))
		{
			// Create destination dir
			self::mkdir(dirname($destination), CHMOD, TRUE);

			if ($source != $destination)
			{
				if (copy($source, $destination))
				{
					chmod($destination, $mode);
					return TRUE;
				}
				else
				{
					throw new Core_Exception("Copy file '%source' error.",
						array('%source' => Core::cutRootPath($source)));
				}
			}
			return TRUE;
		}
		else
		{
			throw new Core_Exception("The file '%source' does not exist.",
				array('%source' => Core::cutRootPath($source)));
		}
	}

	/**
	 * Copies directory
	 * @param string $source The source directory.
	 * @param string $target The destination directory.
	 * @return bool
	 */
	static public function copyDir($source, $target)
	{
		$source = self::pathCorrection($source);
		$target = self::pathCorrection($target);

		if (is_dir($source) && !is_link($source))
		{
			!is_dir($target) && self::mkdir($target, CHMOD, TRUE);

			if ($dh = @opendir($source))
			{
				while (($file = @readdir($dh)) !== FALSE)
				{
					if ($file != '.' && $file!='..')
					{
						clearstatcache();

						is_file($source . DIRECTORY_SEPARATOR . $file)
							? self::copy($source . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file)
							: self::copyDir($source . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file);
					}
				}
				@closedir($dh);
			}
			else
			{
				throw new Core_Exception("Open dir '%source' error.",
					array('%source' => Core::cutRootPath($source)));
			}
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Copy or move uploaded file
	 * @param string $source The source path.
	 * @param string $destination The destination path.
	 * @param int $mode The mode parameter consists of three octal number components specifying access, e.g. 0644
	 * @see Core_File::moveUploadedFile()
	 * @see Core_File::copy()
	 */
	static public function upload($source, $destination, $mode = CHMOD_FILE)
	{
		return is_uploaded_file($source)
			? self::moveUploadedFile($source, $destination, $mode)
			: self::copy($source, $destination, $mode);
	}

	/**
	 * Renames a file or directory
	 * @param string $oldname The old name.
	 * @param string $newname The new name.
	 */
	static public function rename($oldname, $newname)
	{
		if (is_file($oldname) || is_dir($oldname))
		{
			if (!rename($oldname, $newname))
			{
				throw new Core_Exception("Rename file/dir '%oldname' error.",
					array('%oldname' => Core::cutRootPath($oldname)));
			}
		}
		else
		{
			throw new Core_Exception("The file/dir '%oldname' does not exist.",
				array('%oldname' => Core::cutRootPath($oldname)));
		}
	}

	/**
	 * Deletes a file
	 * @param string $fileName Path to the file.
	 */
	static public function delete($fileName)
	{
		if (is_file($fileName) || is_link($fileName))
		{
			if (!unlink($fileName))
			{
				throw new Core_Exception("Delete file '%fileName' error.",
					array('%fileName' => Core::cutRootPath($fileName)));
			}
		}
		else
		{
			throw new Core_Exception("The file '%fileName' does not exist.",
				array('%fileName' => Core::cutRootPath($fileName)));
		}
	}

	/**
	 * Deletes a directory with files and subdirectories
	 * @param string $dirname Path to the directory.
	 */
	static public function deleteDir($dirname)
	{
		$dirname = realpath(self::pathCorrection($dirname) . DIRECTORY_SEPARATOR);

		// Forbidden to delete home directory
		if (strtolower($dirname) == strtolower(CMS_FOLDER))
		{
			throw new Core_Exception('Forbidden to delete home directory.');
		}

		// Check $dirname and CMS_FOLDER
		if ($dirname !== FALSE && strpos($dirname, CMS_FOLDER) !== 0)
		{
			$bForbidden = TRUE;

			// Check $dirname and another cms_folders dirs
			if (isset(Core::$mainConfig['cms_folders']) && is_array(Core::$mainConfig['cms_folders']))
			{
				foreach (Core::$mainConfig['cms_folders'] as $dirToCheck)
				{
					if (strpos($dirname, $dirToCheck) === 0)
					{
						$bForbidden = FALSE;
						break;
					}
				}
			}

			if ($bForbidden)
			{
				throw new Core_Exception('Forbidden to delete directory %dir out of CMS_FOLDER.', array('%dir' => $dirname));
			}
		}

		if (is_dir($dirname) && !is_link($dirname))
		{
			if ($dh = @opendir($dirname))
			{
				while (($file = readdir($dh)) !== FALSE)
				{
					if ($file != '.' && $file != '..')
					{
						clearstatcache();
						$pathName = $dirname . DIRECTORY_SEPARATOR . $file;

						if (is_file($pathName))
						{
							self::delete($pathName);
						}
						elseif (is_dir($pathName))
						{
							self::deleteDir($pathName);
						}
					}
				}

				closedir($dh);
				clearstatcache();

				if (is_dir($dirname) && !@rmdir($dirname))
				{
					return FALSE;
				}
			}
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Deletes empty directories
	 * @param string $dirname Path to the directory.
	 */
	static public function deleteEmptyDirs($dirname)
	{
		$dirname = realpath(self::pathCorrection($dirname) . DIRECTORY_SEPARATOR);

		// Forbidden to delete home directory
		if (strtolower($dirname) == strtolower(CMS_FOLDER))
		{
			throw new Core_Exception("Forbidden to delete home directory.");
		}

		if ($dirname !== FALSE && strpos($dirname, CMS_FOLDER) !== 0)
		{
			throw new Core_Exception("Forbidden to delete directory out of CMS_FOLDER.");
		}

		$bReturn = TRUE;

		if (is_dir($dirname) && !is_link($dirname))
		{
			if ($dh = @opendir($dirname))
			{
				while (($file = readdir($dh)) !== FALSE)
				{
					if ($file != '.' && $file != '..')
					{
						clearstatcache();
						$pathName = $dirname . DIRECTORY_SEPARATOR . $file;

						if (is_file($pathName))
						{
							$bReturn = FALSE;
						}
						elseif (is_dir($pathName))
						{
							self::deleteEmptyDirs($pathName)
								? self::deleteDir($pathName)
								: $bReturn = FALSE;
						}
					}
				}

				closedir($dh);
				clearstatcache();

				return $bReturn;
			}
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Binary-safe file write
	 * @param string $fileName Path to the file.
	 * @param string $content The string that is to be written.
	 * @param int $mode The mode parameter consists of three octal number components specifying access, e.g. 0644
	 */
	static public function write($fileName, $content, $mode = CHMOD_FILE)
	{
		if (($handle = @fopen($fileName, 'w')) && flock($handle, LOCK_EX))
		{
			if (fwrite($handle, $content) === FALSE)
			{
				flock($handle, LOCK_UN);
				fclose($handle);

				throw new Core_Exception("File '%fileName' write error.",
					array('%fileName' => Core::cutRootPath($fileName)));
			}

			flock($handle, LOCK_UN);
			fclose($handle);

			@chmod($fileName, $mode);
			return TRUE;
		}
		else
		{
			throw new Core_Exception("File '%fileName' open error .",
				array('%fileName' => Core::cutRootPath($fileName)));
		}
	}

	/**
	 * Read all file content
	 * @param string $fileName file name
	 * @return string
	 */
	static public function read($fileName)
	{
		if (is_file($fileName))
		{
			return file_get_contents($fileName);
		}
		else
		{
			throw new Core_Exception("The file '%fileName' does not exist.",
				array('%fileName' => Core::cutRootPath($fileName)));
		}
	}

	/**
	 * Get filesize
	 * @param string $fileName The file path.
	 * @return mixed filesize or NULL
	 */
	static public function filesize($fileName)
	{
		return is_file($fileName)
			? filesize($fileName)
			: NULL;
	}

	/**
	 * Makes directory
	 * @param string $pathname The directory path.
	 * @param int $mode The mode parameter consists of three octal number components specifying access, e.g. 0644
	 * @param int $recursive Allows the creation of nested directories specified in the pathname. Defaults to FALSE.
	 */
	static public function mkdir($pathname, $mode = CHMOD, $recursive = FALSE)
	{
		clearstatcache();

		if (!is_dir($pathname) && !is_link($pathname))
		{
			umask(0);

			if (@mkdir($pathname, $mode, $recursive))
			{
				chmod($pathname, $mode);
			}
			else
			{
				throw new Core_Exception("The directory '%pathname' directory has not been created.",
					array('%pathname' => Core::cutRootPath($pathname)));
			}
		}
	}

	/**
	 * File name corretion
	 * @param string $fileName file name
	 * @return string
	 */
	static public function filenameCorrection($fileName)
	{
		$fileName = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $fileName);

		do {
			$fileName = str_replace(array(
				'..' . DIRECTORY_SEPARATOR,
				'.' . DIRECTORY_SEPARATOR,
				DIRECTORY_SEPARATOR
				), '', $fileName, $count);
		} while ($count);

		$fileName = ($fileName == '.') ? '' : $fileName;

		return $fileName;
	}

	/**
	 * Path correction
	 * @param string $path path
	 * @return string
	 */
	static public function pathCorrection($path)
	{
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$path = str_replace(array("\r", "\n", "\0"), '', $path);

		do {
			$path = str_replace(array(
				DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR,
				DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR,
				DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
				//DIRECTORY_SEPARATOR . '..',
				//DIRECTORY_SEPARATOR . '.'
				), array(
				DIRECTORY_SEPARATOR, //'',
				DIRECTORY_SEPARATOR, //'',
				DIRECTORY_SEPARATOR,
				//DIRECTORY_SEPARATOR,
				//DIRECTORY_SEPARATOR
				), $path, $count);
		} while ($count);

		$path = ($path == '.') ? '' : $path;

		return $path;
	}

	/**
	 * Get extension from path
	 * @param string $path path
	 * @return string
	 */
	static public function getExtension($path)
	{
		return strtolower(substr(strrchr($path, '.'), 1));
	}

	/**
	 * Checks if the extension is valid
	 * @param string $path file path
	 * @param array $aExtensions array of valid extensions
	 * @param boolean $case case sensitivity
	 * @return boolean
	 */
	static public function isValidExtension($path, array $aExtensions, $case = FALSE)
	{
		$sExtension = self::getExtension($path);

		if ($case)
		{
			return in_array($sExtension, $aExtensions);
		}
		else
		{
			foreach ($aExtensions as $extension)
			{
				if (strtolower($sExtension) == strtolower($extension))
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Получение пути к директории определенного уровня вложенности по идентификатору сущности.
	 * Например, для сущности с кодом 17 и уровнем вложенности 3 вернется строка 0/1/7 или массив из 3-х элементов - 0,1,7
	 * Для сущности с кодом 23987 и уровнем вложенности 3 возвращается строка 2/3/9 или массив из 3-х элементов - 2,3,9.
	 *
	 * @param $id идентификатор сущности
	 * @param $level уровень вложенности, по умолчанию 3
	 * @param $type тип возвращаемого результата, 0 (по умолчанию) - строка, 1 - массив
	 * @return mixed строка или массив названий групп
	 */
	static public function getNestingDirPath($id, $level = 3, $type = 0)
	{
		$id = intval($id);
		$level = intval($level);

		$sId = sprintf("%0{$level}d", $id);

		$aPath = array();
		for ($i = 0; $i < $level; $i ++)
		{
			$aPath[$i] = $sId[$i];
		}

		if ($type == 0)
		{
			return implode('/', $aPath);
		}

		return $aPath;
	}

	/**
	 * Convert file name to local encoding
	 * @param string $fileName file name
	 * @return string
	 */
	static public function convertfileNameToLocalEncoding($fileName)
	{
		return @iconv("UTF-8", "Windows-1251//IGNORE//TRANSLIT", $fileName);
	}

	/**
	 * Convert file name from local encoding
	 * @param string $fileName file name
	 * @return string
	 */
	static public function convertfileNameFromLocalEncoding($fileName)
	{
		return @iconv("Windows-1251", "UTF-8//IGNORE//TRANSLIT", $fileName);
	}

	/**
	 * ob_flush()
	 */
	static public function flush()
	{
		if (@ini_get('output_handler') == 'ob_gzhandler')
		{
			return NULL;
		}

		ob_get_length() && ob_flush();
		flush();

		return TRUE;
	}

	/**
	* Вывод содержимого файла
	*
	* @param string $file путь к файлу
	* @param string $fileName имя файла
	* @param array $param массив дополнительных параметров
	* - $param['content_disposition'] заголовок, определяющий вывод файла
	* (inline - открывается в браузере (по умолчанию), attachment - скачивается)
	* <code>
	* <?php
	* $file = CMS_FOLDER . 'file.dat';
	* $fileName = 'Пользовательское_имя_файла.dat';
	*
	* Core_File::download($file, $fileName);
	* exit();
	* ?>
	* </code>
	*/
	static public function download($file, $fileName, $param = array())
	{
		$file = self::pathCorrection($file);

		if (strpos($file, CMS_FOLDER) !== 0)
		{
			throw new Core_Exception("Forbidden to access directory out of CMS_FOLDER.");
		}

		if (!is_file($file))
		{
			throw new Core_Exception("The file '%file' does not exist.",
				array('%file' => Core::cutRootPath($file)));
		}

		$fileName = str_replace(array("\r", "\n", "\0"), '', $fileName);

		header("Pragma: public");
		header("Content-Type: " . Core_Mime::getFileMime($file));

		$contentDisposition = isset($param['content_disposition']) && strtolower($param['content_disposition']) == 'attachment'
			? 'attachment'
			: 'inline';

		header("Content-Disposition: {$contentDisposition}; fileName = \"{$fileName}\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($file));

		self::flush();

		if ($fd = fopen($file, "rb"))
		{
			while (!feof($fd))
			{
				echo fread($fd, 1048576);
				self::flush();
			}
			fclose($fd);
		}

		return TRUE;
	}

	/**
	* Получение строки прав доступа к файлу
	*
	* @param string $filename имя файла
	* @param int $octalValue тип строки (TRUE - "-rw-rw-rw-"; FALSE - "0755")
	* <code>
	* <?php
	* $filename = CMS_FOLDER . 'index.php';
	* $type = 1;
	*
	* $fileperms = Core_File::getFilePerms($filename, TRUE);
	*
	* // Распечатаем результат
	* echo $fileperms;
	* ?>
	* </code>
	* @return string строка прав доступа к файлу
	*/
	static public function getFilePerms($filename, $octalValue = FALSE)
	{
		// Вид результата
		// FALSE - -rw-rw-rw-
		// TRUE - 0755
		$perms = fileperms($filename);

		if ($octalValue)
		{
			$info = substr(sprintf('%o', $perms), -4);
		}
		else
		{
			if (($perms & 0xC000) == 0xC000)
			{
				// Сокет
				$info = 's';
			}
			elseif (($perms & 0xA000) == 0xA000)
			{
				// Символическая ссылка
				$info = 'l';
			}
			elseif (($perms & 0x8000) == 0x8000)
			{
				// Обычный
				$info = '-';
			}
			elseif (($perms & 0x6000) == 0x6000)
			{
				// Специальный блок
				$info = 'b';
			}
			elseif (($perms & 0x4000) == 0x4000)
			{
				// Директория
				$info = 'd';
			}
			elseif (($perms & 0x2000) == 0x2000)
			{
				// Специальный символ
				$info = 'c';
			}
			elseif (($perms & 0x1000) == 0x1000)
			{
				// Поток FIFO
				$info = 'p';
			}
			else
			{
				// Неизвестный
				$info = 'u';
			}
			// Владелец
			$info .= (($perms & 0x0100) ? 'r' : '-');
			$info .= (($perms & 0x0080) ? 'w' : '-');
			$info .= (($perms & 0x0040) ?
			(($perms & 0x0800) ? 's' : 'x') :
			(($perms & 0x0800) ? 'S' : '-'));
			// Группа
			$info .= (($perms & 0x0020) ? 'r' : '-');
			$info .= (($perms & 0x0010) ? 'w' : '-');
			$info .= (($perms & 0x0008) ?
			(($perms & 0x0400) ? 's' : 'x') :
			(($perms & 0x0400) ? 'S' : '-'));
			// Мир
			$info .= (($perms & 0x0004) ? 'r' : '-');
			$info .= (($perms & 0x0002) ? 'w' : '-');
			$info .= (($perms & 0x0001) ?
			(($perms & 0x0200) ? 't' : 'x') :
			(($perms & 0x0200) ? 'T' : '-'));
		}

		return $info;
	}

	/**
	* Получение строки владельцев к файлу
	*
	* @param string $filename имя файла
	* @return string строка владельцев к файлу
	*/
	static public function getFileOwners($filename)
	{
		$aReturn = array();

		if (function_exists('posix_getpwuid'))
		{
			$fileowner = fileowner($filename);

			if ($fileowner)
			{
				$aOwnerInfo = posix_getpwuid($fileowner);
				is_array($aOwnerInfo)
					&& $aReturn[] = $aOwnerInfo['name'];
			}
		}

		if (function_exists('posix_getgrgid'))
		{
			$filegroup = filegroup($filename);

			if ($filegroup)
			{
				$aGroupOwnerInfo = posix_getgrgid($fileowner);
				is_array($aGroupOwnerInfo)
					&& $aReturn[] = $aGroupOwnerInfo['name'];
			}
		}

		return count($aReturn)
			? implode(':', $aReturn)
			: '—';
	}

	/**
	 * Загрузка файлов в центре администрирования
	 * @param array $param массив параметров
	 * - $param['large_image_source'] путь к файлу-источнику большого изображения
	 * - $param['small_image_source'] путь к файлу-источнику малого изображения
	 * - $param['large_image_name'] оригинальное имя файла большого изображения
	 * - $param['small_image_name'] оригинальное имя файла малого изображения
	 * - $param['large_image_target'] путь к создаваемому файлу большого изображения
	 * - $param['small_image_target'] путь к создаваемому файлу малого изображения
	 * - $param['create_small_image_from_large'] использовать большое изображение для создания малого (TRUE - использовать (по умолчанию), FALSE - не использовать)
	 * - $param['large_image_max_width'] значение максимальной ширины большого изображения
	 * - $param['large_image_max_height'] значение максимальной высоты большого изображения
	 * - $param['small_image_max_width'] значение максимальной ширины малого изображения
	 * - $param['small_image_max_height'] значение максимальной высоты малого изображения
	 * - $param['watermark_file_path'] путь к файлу с "водяным знаком", если водяной знак не должен накладываться, не передавайте этот параметр
	 * - $param['watermark_position_x'] позиция "водяного знака" по оси X
	 * - $param['watermark_position_y'] позиция "водяного знака" по оси Y
	 * - $param['large_image_watermark'] наложить "водяной знак" на большое изображение (TRUE - наложить (по умолчанию), FALSE - не наложить)
	 * - $param['small_image_watermark'] наложить "водяной знак" на малое изображение (TRUE - наложить (по умолчанию), FALSE - не наложить)
	 * - $param['large_image_preserve_aspect_ratio'] сохранять пропорции изображения для большого изображения (TRUE - по умолчанию)
	 * - $param['small_image_preserve_aspect_ratio'] сохранять пропорции изображения для большого изображения (TRUE - по умолчанию)
	 * @return array $result
	 * - $result['large_image'] = TRUE в случае успешного создания большого изображения, FALSE - в противном случае
	 * - $result['small_image'] = TRUE в случае успешного создания малого изображения, FALSE - в противном случае
	 * @hostcms-event Core_File.onBeforeAdminUpload
	 * @hostcms-event Core_File.onAfterAdminUpload
	 */
	static public function adminUpload($param)
	{
		Core_Event::notify('Core_File.onBeforeAdminUpload', NULL, $param);

		$result = array(
			'large_image' => FALSE,
			'small_image' => FALSE
		);

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult + $result;
		}

		// Путь к файлу-источнику большого изображения
		$large_image_source = isset($param['large_image_source'])
			? $param['large_image_source']
			: '';

		if (isset($param['small_image_source']))
		{
			// Путь к файлу-источнику малого изображения
			$small_image_source = $param['small_image_source'];
		}
		else
		{
			// Не задан файл-источник большого изображения
			if ($large_image_source == '')
			{
				return $result;
			}
			$small_image_source = '';
		}

		// Задано оригинальное имя большого файла-источника
		if (isset($param['large_image_name']))
		{
			$large_image_name = $param['large_image_name'];
		}
		else
		{
			// Имени нет, а источник не пустой
			if ($large_image_source != '')
			{
				return $result;
			}
			$large_image_name = '';
		}

		// Путь к файлу-получателю большого изображения
		if (isset($param['large_image_target']))
		{
			$large_image_target = $param['large_image_target'];
		}
		else
		{
			// Задан источник большого изображения
			if ($large_image_source != '')
			{
				return $result;
			}
			$large_image_target = '';
		}

		if (isset($param['small_image_target']))
		{
			$small_image_target = $param['small_image_target'];
		}
		else
		{
			if ($small_image_source != '')
			{
				return $result;
			}
			$small_image_target = '';
		}

		if (isset($param['small_image_name']) && $param['small_image_name'] != '')
		{
			$small_image_name = $param['small_image_name'];
		}
		else
		{
			// Задан путь к файлу-источнику малого изображения
			if ($small_image_source != '')
			{
				return $result;
			}
			$small_image_name = '';
		}

		// Создавать ли малое из большого
		$create_small_image_from_large = isset($param['create_small_image_from_large'])
			? $param['create_small_image_from_large']
			: TRUE;

		// Проверка на доступность разрешения для уменьшения
		if (!self::isValidExtension($large_image_target, self::getResizeExtensions())
			|| $small_image_source != '')
		{
			$create_small_image_from_large = FALSE;
		}

		$large_image_max_width = isset($param['large_image_max_width'])
			? intval($param['large_image_max_width'])
			: 500;

		$large_image_max_height = isset($param['large_image_max_height'])
			? intval($param['large_image_max_height'])
			: 500;

		$small_image_max_width = isset($param['small_image_max_width'])
			? intval($param['small_image_max_width'])
			: 150;

		$small_image_max_height = isset($param['small_image_max_height'])
			? $param['small_image_max_height']
			: 150;

		$watermark_file_path = isset($param['watermark_file_path'])
			? $param['watermark_file_path']
			: '';

		$watermark_position_x = isset($param['watermark_position_x'])
			? $param['watermark_position_x']
			: '50%';

		$watermark_position_y = isset($param['watermark_position_y'])
			? $param['watermark_position_y']
			: '100%';

		$large_image_watermark = isset($param['large_image_watermark'])
			? $param['large_image_watermark']
			: TRUE;

		$small_image_watermark = isset($param['small_image_watermark'])
			? $param['small_image_watermark']
			: TRUE;

		// Сохранять пропорции изображения для большого изображения
		$large_image_preserve_aspect_ratio = isset($param['large_image_preserve_aspect_ratio'])
			? $param['large_image_preserve_aspect_ratio']
			: TRUE;

		// Сохранять пропорции изображения для малого изображения
		$small_image_preserve_aspect_ratio = isset($param['small_image_preserve_aspect_ratio'])
			? $param['small_image_preserve_aspect_ratio']
			: TRUE;

		$aCore_Config = Core::$mainConfig;

		// Задан файл-источник большого изображения
		if ($large_image_source != '')
		{
			if (self::isValidExtension($large_image_target, $aCore_Config['availableExtension']))
			{
				$large_image_created = $small_image_created = TRUE;

				self::upload($large_image_source, $large_image_target);

				// Уменьшаем большую картинку до максимального размера.
				if (self::isValidExtension($large_image_target, self::getResizeExtensions())
					&& !Core_Image::instance()->resizeImage($large_image_target, $large_image_max_width, $large_image_max_height, $large_image_target, NULL, $large_image_preserve_aspect_ratio))
				{
					throw new Core_Exception(Core::_('Core.error_resize'));
				}

				//@chmod($large_image_target, CHMOD_FILE);

				// Если не передан флаг ватермарка для маленькой картинки - то копируем ее до наложения ватермарка
				if (!$small_image_watermark)
				{
					// задан флажок создания малого изображения из большого или загрузили файл малого изображения
					if ($create_small_image_from_large || $small_image_source)
					{
						// Определяем создавать маленькую картинку из большой или из загруженной малой
						// Создать малое изображение из большого
						if ($create_small_image_from_large)
						{
							// Используем $large_image_target в качестве источника,
							// т.к. переместили файл большого изображения при его уменьшении до максимальных параметров
							$source_file_path = $large_image_target;
						}
						// Создать малое из загруженного малого
						else
						{
							$source_file_path = $small_image_source;
						}

						if (self::isValidExtension($small_image_target, self::getResizeExtensions()))
						{
							// Делаем уменьшенный файл.
							if (!Core_Image::instance()->resizeImage($source_file_path, $small_image_max_width, $small_image_max_height, $small_image_target, NULL, $small_image_preserve_aspect_ratio))
							{
								throw new Core_Exception(Core::_('Core.error_resize'));
							}
							//@chmod($small_image_target, CHMOD_FILE);
						}
						else
						{
							$small_image_created = FALSE;
						}
					}
					else
					{
						$small_image_created = FALSE;
					}
				}
				// Применить ватермарк к малому изображению и не применять его к большому
				elseif ($create_small_image_from_large && !empty($small_image_target) && !$large_image_watermark)
				{
					// Создаем малое изображение из большого
					$create_small_from_small = Core_Image::instance()->resizeImage($large_image_target, $large_image_max_width, $large_image_max_height, $small_image_target, NULL, $small_image_preserve_aspect_ratio);
				}

				// Накладываем Watermark на большое изображение и указан ватермарк
				if ($large_image_watermark && $watermark_file_path != '')
				{
					Core_Image::instance()->addWatermark($large_image_target, $large_image_target, $watermark_file_path, $watermark_position_x, $watermark_position_y);
				}

				// Если передан флаг ватермарка для маленькой картинки - то копируем ее после наложения ватермарка на большую
				if ($small_image_watermark)
				{
					// задан флажок создания малого изображения из большой или загрузили файл малого изображения
					if ($create_small_image_from_large && $small_image_target != '')
					{
						// Определяем, создавать маленькую картинку из большой или из загруженной малой
						$bSuccess = FALSE;

						if (isset($create_small_from_small) && $create_small_from_small)
						{
							Core_Image::instance()->addWatermark($small_image_target, $small_image_target, $watermark_file_path, $watermark_position_x, $watermark_position_y);

							$bSuccess = Core_Image::instance()->resizeImage($small_image_target, $small_image_max_width, $small_image_max_height, $small_image_target, NULL, $small_image_preserve_aspect_ratio);
						}
						else
						{
							// Создать малое изображение из большого
							$bSuccess = Core_Image::instance()->resizeImage($large_image_target, $small_image_max_width, $small_image_max_height, $small_image_target, NULL, $small_image_preserve_aspect_ratio);
						}

						if (!$bSuccess)
						{
							throw new Core_Exception(Core::_('Core.error_resize'));
						}
						//@chmod($small_image_target, CHMOD_FILE);
					}
					else
					{
						$small_image_created = FALSE;
					}
				}
			}
			else
			{
				$large_image_created = $small_image_created = FALSE;
			}
		}
		else
		{
			$large_image_created = $small_image_created = FALSE;
		}

		// Задано малое изображение и при этом не задано создание малого изображения
		// из большого или задано создание малого изображения из большого и
		// при этом не задано большое изображение или
		// было задано создание малого изображения из большого и оно не было создано
		if ($small_image_source && ($create_small_image_from_large && !$large_image_source || !$create_small_image_from_large || $create_small_image_from_large && !$small_image_created))
		{
			$small_image_created = TRUE;

			// Не создаем большое изображение из малого
			$create_large_image = FALSE;

			if (self::isValidExtension($small_image_target, $aCore_Config['availableExtension']))
			{
				self::upload($small_image_source, $small_image_target);

				// Если не передан флаг ватермарка для маленькой картинки - то копируем ее до наложения ватермарка
				if (!$small_image_watermark || $watermark_file_path == '')
				{
					if (self::isValidExtension($small_image_target, self::getResizeExtensions()))
					{
						if (!Core_Image::instance()->resizeImage($small_image_target, $small_image_max_width, $small_image_max_height, $small_image_target, NULL, $small_image_preserve_aspect_ratio))
						{
							throw new Core_Exception(Core::_('Core.error_resize'));
						}

						// Устанавливаем права доступа к файлу малой картинки.
						@chmod($small_image_target, CHMOD_FILE);
					}
				}
				// Применить ватермарк к малой картинке
				else
				{
					$bSuccess = Core_Image::instance()->addWatermark($small_image_target, $small_image_target, $watermark_file_path, $watermark_position_x, $watermark_position_y);

					if ($bSuccess)
					{
						$bSuccess = Core_Image::instance()->resizeImage($small_image_target, $small_image_max_width, $small_image_max_height, $small_image_target, NULL, $small_image_preserve_aspect_ratio);

						$bSuccess
							? @chmod($small_image_target, CHMOD_FILE)
							: $small_image_created = FALSE;
					}
				}
			}
			else
			{
				if ($create_large_image)
				{
					$large_image_created = FALSE;
				}

				$small_image_created = FALSE;
			}
		}

		Core_Event::notify('Core_File.onAfterAdminUpload', NULL, $param);

		$result['large_image'] = $large_image_created;
		$result['small_image'] = $small_image_created;

		return $result;
	}
}
