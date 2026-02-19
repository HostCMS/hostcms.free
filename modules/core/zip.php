<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Creates a zip archive with password support (ZipCrypto)
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Zip
{
	/**
	 * Handle for writing
	 * @var resource|NULL
	 */
	protected $_zipFileHandle = NULL;

	/**
	 * Handle for reading
	 * @var resource|NULL
	 */
	protected $_srcFileHandle = NULL;

	/**
	 * Final output filename
	 * @var string
	 */
	protected $_filename;

	/**
	 * Temporary filename
	 * @var string|NULL
	 */
	protected $_tempFilename = NULL;

	/**
	 * Files added in current session
	 * @var array
	 */
	protected $_centralDirectory = array();

	/**
	 * Parsed files from existing archive
	 * @var array
	 */
	protected $_srcCentralDirectory = array();

	/**
	 * List of files to delete from source
	 * @var array
	 */
	protected $_deletedFiles = array();

	/**
	 * Current offset
	 * @var integer
	 */
	protected $_offset = 0;

	/**
	 * Is archive closed
	 * @var boolean
	 */
	protected $_isClose = FALSE;

	/**
	 * Threshold for large files (20 MB)
	 * @var integer
	 */
	protected $_largeFileThreshold = 20971520;

	/**
	 * Excluded directories paths
	 * @var array
	 */
	protected $_excludedDirs = array();

	/**
	 * Archive password
	 * @var string|NULL
	 */
	protected $_password = NULL;

	/**
	 * ZipCrypto Keys
	 * @var array
	 */
	protected $_keys = array();

	/**
	 * CRC32 Table
	 * @var array|NULL
	 */
	protected static $_crc32Table = NULL;

	/**
	 * Constructor
	 * @param string $filename Path to the zip file
	 */
	public function __construct(string $filename)
	{
		$this->_filename = $filename;
		$this->_open();
	}

	/**
	 * Set password for new files
	 * @param string $password
	 * @return self
	 */
	public function setPassword(string $password)
	{
		$this->_password = $password;
		return $this;
	}

	/**
	 * Open archive
	 */
	protected function _open()
	{
		if (file_exists($this->_filename) && filesize($this->_filename) > 0)
		{
			// Редактирование
			$this->_srcFileHandle = fopen($this->_filename, 'rb');

			if (!$this->_srcFileHandle)
			{
				throw new RuntimeException("Can't read the file: {$this->_filename}");
			}

			$this->_readCentralDirectory();

			// Создаем временный файл в ТОЙ ЖЕ директории, чтобы rename() сработал атомарно
			$dir = dirname($this->_filename);
			$this->_tempFilename = tempnam($dir, 'zip_tmp_');

			if (!$this->_tempFilename)
			{
				// Если tempnam не сработал (прав нет), пробуем просто добавить суффикс
				$this->_tempFilename = $this->_filename . '.' . uniqid() . '.tmp';
			}

			$this->_zipFileHandle = fopen($this->_tempFilename, 'wb');
		}
		else
		{
			$this->_zipFileHandle = fopen($this->_filename, 'wb');
			$this->_tempFilename = NULL; // Временный файл не нужен
		}

		if (!$this->_zipFileHandle)
		{
			throw new RuntimeException("Failed to open file for writing: " . ($this->_tempFilename ?: $this->_filename));
		}
	}

/**
	 * Read Central Directory from existing ZIP
	 */
	protected function _readCentralDirectory()
	{
		$size = filesize($this->_filename);
		if ($size < 22) return;

		// Ищем EOCD
		$pos = max(0, $size - 65536);
		fseek($this->_srcFileHandle, $pos, SEEK_SET);
		$data = fread($this->_srcFileHandle, $size - $pos);

		$eocdPos = strrpos($data, "\x50\x4b\x05\x06");
		if ($eocdPos === FALSE) return;

		$eocdData = substr($data, $eocdPos);
		$eocd = unpack('vdisk/vdisk_start/vnum_disk/vnum_total/Vsize/Voffset/vcomment_len', substr($eocdData, 4, 18));

		fseek($this->_srcFileHandle, $eocd['offset'], SEEK_SET);

		for ($i = 0; $i < $eocd['num_total']; $i++)
		{
			$binary = fread($this->_srcFileHandle, 46);
			if (strlen($binary) < 46) break;

			$header = unpack(
				'Vsig/vversion/vversion_needed/vflags/vmethod/vmtime/vmdate/Vcrc32/Vcomp_size/Vsize/vname_len/vextra_len/vcomment_len/vdisk/vattr_int/Vattr_ext/Voffset',
				$binary
			);

			if ($header['sig'] !== 0x02014b50) break;

			$name = fread($this->_srcFileHandle, $header['name_len']);
			if ($header['extra_len'] > 0) fread($this->_srcFileHandle, $header['extra_len']);
			if ($header['comment_len'] > 0) fread($this->_srcFileHandle, $header['comment_len']);

			// Проверяем наличие флага UTF-8 (Bit 11)
			$isUtf8 = ($header['flags'] & 0x0800);

			// Если флага нет, пытаемся конвертировать из CP866 в UTF-8
			if (!$isUtf8) {
				$nameConverted = @iconv('CP866', 'UTF-8//IGNORE', $name);
				if ($nameConverted) {
					$name = $nameConverted;
				}
			}

			$this->_srcCentralDirectory[$name] = [
				'name' => $name,
				'method' => $header['method'],
				'mtime' => $header['mtime'],
				'mdate' => $header['mdate'],
				'crc32' => $header['crc32'],
				'compressed_size' => $header['comp_size'],
				'size' => $header['size'],
				'offset' => $header['offset'],
				'flags' => $header['flags']
			];
		}
	}

	/**
	 * Get file content
	 * @param string $name
	 * @return string
	 */
	public function getFromName(string $name)
	{
		$name = str_replace('\\', '/', $name);

		if (!isset($this->_srcCentralDirectory[$name]) || !$this->_srcFileHandle)
		{
			return FALSE;
		}

		$info = $this->_srcCentralDirectory[$name];

		// Проверка на зашифрованный файл
		if ($info['flags'] & 1)
		{
			return FALSE;
		}

		fseek($this->_srcFileHandle, $info['offset'], SEEK_SET);
		$headerData = fread($this->_srcFileHandle, 30);

		// Важно: vextra_len создает ключ extra_len
		$header = unpack('vversion/vflags/vmethod/vmtime/vmdate/Vcrc32/Vcomp_size/Vsize/vname_len/vextra_len', substr($headerData, 4, 26));

		$skipLen = $header['name_len'] + $header['extra_len'];
		fseek($this->_srcFileHandle, $skipLen, SEEK_CUR);

		$data = fread($this->_srcFileHandle, $info['compressed_size']);

		if ($info['method'] == 8)
		{
			return gzinflate($data);
		}

		return $data;
	}

	/**
	 * Delete file
	 * @param string $name
	 */
	public function delete(string $name)
	{
		$name = str_replace('\\', '/', $name);
		$this->_deletedFiles[$name] = TRUE;

		if (isset($this->_srcCentralDirectory[$name]))
		{
			unset($this->_srcCentralDirectory[$name]);
		}
	}

	/**
	 * Exclude dir
	 * @param string $dir
	 */
	public function excludeDir(string $dir)
	{
		$dir = str_replace('\\', '/', $dir);
		$this->_excludedDirs[] = rtrim($dir, '/');
	}

	/**
	 * Add dir
	 * @param string $dir
	 * @param string $zipPathPrefix
	 */
	public function addDir(string $dir, string $zipPathPrefix = '')
	{
		$dir = rtrim($dir, '/\\');
		if (!is_dir($dir)) return;

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($iterator as $file)
		{
			if (!$file->isFile()) continue;
			$realPath = $file->getRealPath();

			if ($this->_isFileExcluded($realPath)) continue;

			$relativePath = substr($realPath, strlen($dir) + 1);
			$zipName = $zipPathPrefix ? $zipPathPrefix . '/' . $relativePath : $relativePath;
			$zipName = str_replace('\\', '/', $zipName);

			$this->delete($zipName); // Если обновляем существующий архив
			$this->_addFileFromPath($realPath, $zipName);
		}
	}

	/**
	 * Check is file excluded
	 * @param string $realPath
	 * @return boolean
	 */
	protected function _isFileExcluded(string $realPath): bool
	{
		if (empty($this->_excludedDirs)) return FALSE;

		$normalizedPath = str_replace('\\', '/', $realPath);

		foreach ($this->_excludedDirs as $excludedDir)
		{
			if (strpos($normalizedPath, $excludedDir . '/') === 0 || $normalizedPath === $excludedDir)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Add file
	 * @param string $data
	 * @param string $name
	 * @param integer $timestamp
	 */
	public function addFile(string $data, string $name, int $timestamp = 0)
	{
		if ($this->_isClose) throw new RuntimeException("Archive closed.");

		$name = str_replace('\\', '/', $name);
		$this->delete($name);

		$crc32 = crc32($data);
		$compressedData = gzdeflate($data);
		$size = strlen($data);
		$compressedSize = strlen($compressedData);

		$this->_writeHeaderAndData(
			$name, 8, $timestamp ?: time(), $crc32, $compressedSize, $size, $compressedData, NULL
		);
	}

	/**
	 * Add file from path
	 * @param string $fsPath
	 * @param string $zipName
	 */
	protected function _addFileFromPath(string $fsPath, string $zipName)
	{
		if ($this->_isClose) throw new RuntimeException("Archive closed.");

		$size = filesize($fsPath);
		$mtime = filemtime($fsPath);

		if ($size > $this->_largeFileThreshold)
		{
			$this->_processStore($fsPath, $zipName, $size, $mtime);
		}
		else
		{
			$this->_processDeflate($fsPath, $zipName, $size, $mtime);
		}
	}

	/**
	 * Process store
	 * @param string $fsPath
	 * @param string $name
	 * @param integer $size
	 * @param integer $timestamp
	 */
	protected function _processStore(string $fsPath, string $name, int $size, int $timestamp)
	{
		$crc32 = hexdec(hash_file('crc32b', $fsPath));
		$this->_writeHeaderAndData($name, 0, $timestamp, $crc32, $size, $size, NULL, $fsPath);
	}

	/**
	 * Process deflate
	 * @param string $fsPath
	 * @param string $name
	 * @param integer $size
	 * @param integer $timestamp
	 */
	protected function _processDeflate(string $fsPath, string $name, int $size, int $timestamp)
	{
		$data = file_get_contents($fsPath);
		$crc32 = crc32($data);
		$compressedData = gzdeflate($data);
		$compressedSize = strlen($compressedData);
		unset($data);

		$this->_writeHeaderAndData($name, 8, $timestamp, $crc32, $compressedSize, $size, $compressedData, NULL);
	}

/**
	 * Write metadata and data (encrypted if password set)
	 * @param string $name
	 * @param integer $method
	 * @param integer $timestamp
	 * @param integer $crc32
	 * @param integer $compressedSize
	 * @param integer $size
	 * @param string|null $dataString
	 * @param string|null $sourceFile
	 */
	protected function _writeHeaderAndData(string $name, int $method, int $timestamp, int $crc32, int $compressedSize, int $size, ?string $dataString, ?string $sourceFile)
	{
		$dostime = $this->_unix2DosTime($timestamp);

		// Устанавливаем флаг UTF-8 (Bit 11 = 0x0800 = 2048)
		$flags = (1 << 11);

		$encryptionHeader = '';

		if ($this->_password !== NULL)
		{
			$flags |= 1; // Устанавливаем бит шифрования (Bit 0)
			$this->_initKeys($this->_password);
			$encryptionHeader = $this->_makeDecryptHeader($crc32);
			$compressedSize += 12; // Header size
		}

		$header = pack('V', 0x04034b50) . pack('v', 20) . pack('v', $flags) . pack('v', $method) .
			pack('V', $dostime) . pack('V', $crc32) . pack('V', $compressedSize) . pack('V', $size) .
			pack('v', strlen($name)) . pack('v', 0) . $name;

		fwrite($this->_zipFileHandle, $header);

		// Write Encryption Header if needed
		if ($this->_password !== NULL)
		{
			fwrite($this->_zipFileHandle, $encryptionHeader);
		}

		if ($dataString !== NULL)
		{
			if ($this->_password !== NULL)
			{
				$dataString = $this->_encryptData($dataString);
			}
			fwrite($this->_zipFileHandle, $dataString);
		}
		elseif ($sourceFile !== NULL)
		{
			$fpIn = fopen($sourceFile, 'rb');
			if ($fpIn)
			{
				if ($this->_password !== NULL)
				{
					// Encrypt stream
					while (!feof($fpIn))
					{
						$block = fread($fpIn, 8192);
						if ($block !== FALSE && $block !== '') {
							fwrite($this->_zipFileHandle, $this->_encryptData($block));
						}
					}
				}
				else
				{
					stream_copy_to_stream($fpIn, $this->_zipFileHandle);
				}
				fclose($fpIn);
			}
		}

		$this->_centralDirectory[] = [
			'name' => $name,
			'method' => $method,
			'dostime' => $dostime,
			'crc32' => $crc32,
			'compressed_size' => $compressedSize,
			'size' => $size,
			'offset' => $this->_offset,
			'flags' => $flags
		];

		$this->_offset += strlen($header) + $compressedSize;
	}

	/**
	 * Copy existing file from source to dest directly
	 * @param array $info
	 */
protected function _copyFileFromSource($info)
	{
		fseek($this->_srcFileHandle, $info['offset']);
		$headerData = fread($this->_srcFileHandle, 30);

		$header = unpack('vversion/vflags/vmethod/vmtime/vmdate/Vcrc32/Vcomp_size/Vsize/vname_len/vextra_len', substr($headerData, 4, 26));

		$nameLen = $header['name_len'];
		$extraLen = $header['extra_len'];

		fseek($this->_srcFileHandle, $nameLen + $extraLen, SEEK_CUR);

		// Используем флаги из исходного файла
		$flags = $header['flags'];

		// Rebuild header
		$newHeader = pack('V', 0x04034b50) .
			pack('v', 20) . pack('v', $flags) . pack('v', $info['method']) .
			pack('V', ($info['mdate'] << 16 | $info['mtime'])) .
			pack('V', $info['crc32']) .
			pack('V', $info['compressed_size']) .
			pack('V', $info['size']) .
			pack('v', strlen($info['name'])) .
			pack('v', 0) .
			$info['name'];

		fwrite($this->_zipFileHandle, $newHeader);

		stream_copy_to_stream($this->_srcFileHandle, $this->_zipFileHandle, $info['compressed_size']);

		$this->_centralDirectory[] = [
			'name' => $info['name'],
			'method' => $info['method'],
			'dostime' => ($info['mdate'] << 16 | $info['mtime']),
			'crc32' => $info['crc32'],
			'compressed_size' => $info['compressed_size'],
			'size' => $info['size'],
			'offset' => $this->_offset,
			'flags' => $flags
		];

		$this->_offset += strlen($newHeader) + $info['compressed_size'];
	}

	/**
	 * Initialize keys
	 * @param string $password
	 */
	protected function _initKeys(string $password)
	{
		$this->_keys = [305419896, 591751049, 878082192];
		$this->_initCrc32Table();

		$len = strlen($password);
		for ($i = 0; $i < $len; $i++)
		{
			$this->_updateKeys(ord($password[$i]));
		}
	}

	/**
	 * Update keys with a byte
	 * @param int $char
	 */
	protected function _updateKeys(int $char)
	{
		// Key 0: CRC32 update
		// Используем таблицу, так как нативный crc32() не умеет обновлять состояние
		$this->_keys[0] = (($this->_keys[0] >> 8) & 0x00FFFFFF) ^ self::$_crc32Table[($this->_keys[0] ^ $char) & 0xFF];

		// Key 1: Addition & Multiplication
		$this->_keys[1] = (($this->_keys[1] + ($this->_keys[0] & 0xFF)) * 134775813 + 1) & 0xFFFFFFFF;

		// Key 2: CRC32 update based on Key 1 high byte
		$key1_high = ($this->_keys[1] >> 24) & 0xFF;
		$this->_keys[2] = (($this->_keys[2] >> 8) & 0x00FFFFFF) ^ self::$_crc32Table[($this->_keys[2] ^ $key1_high) & 0xFF];
	}

	/**
	 * Encrypt a single byte
	 * @param int $char
	 * @return int
	 */
	protected function _encryptByte(int $char)
	{
		$temp = ($this->_keys[2] & 0xFFFF) | 2;
		// Важно: приведение к int для сброса возможных float при умножении на 32-битных системах
		$key = (int)((($temp * ($temp ^ 1)) >> 8) & 0xFF);
		$result = $char ^ $key;

		$this->_updateKeys($char);

		return $result;
	}

	/**
	 * Encrypt string data
	 * @param string $data
	 * @return string
	 */
	protected function _encryptData(string $data)
	{
		$len = strlen($data);
		$out = '';
		for ($i = 0; $i < $len; $i++)
		{
			$out .= chr($this->_encryptByte(ord($data[$i])));
		}

		return $out;
	}

	/**
	 * Create encryption header
	 * @param int $crc32
	 * @return string
	 */
	protected function _makeDecryptHeader(int $crc32)
	{
		$header = '';

		// Генерируем 11 случайных байт
		for ($i = 0; $i < 11; $i++)
		{
			$header .= chr(mt_rand(0, 255));
		}

		// 12-й байт — это проверочный байт.
		// Для ZipCrypto (без Bit 3 flag) это старший байт CRC32
		$checkByte = ($crc32 >> 24) & 0xFF;
		$header .= chr($checkByte);

		// Шифруем заголовок с текущими ключами (инициализированными паролем)
		return $this->_encryptData($header);
	}

	/**
	 * Initialize CRC32 table
	 */
	protected function _initCrc32Table()
	{
		if (self::$_crc32Table === NULL)
		{
			self::$_crc32Table = [];
			for ($i = 0; $i < 256; $i++)
			{
				$r = $i;
				for ($j = 0; $j < 8; $j++)
				{
					if ($r & 1)
					{
						$r = ($r >> 1) ^ 0xEDB88320;
					}
					else
					{
						$r >>= 1;
					}
				}
				// Принудительно приводим к знаковому int, так как PHP работает с signed int в битовых операциях
				self::$_crc32Table[$i] = (int)$r;
			}
		}
	}

	/**
	 * Extract archive to directory
	 * @param string $destination Destination directory path
	 * @return boolean
	 */
	public function extract(string $destination)
	{
		$destination = rtrim($destination, '/\\');

		// Создаем целевую директорию, если нет
		if (!is_dir($destination) && !mkdir($destination, 0755, TRUE))
		{
			throw new RuntimeException("Directory '{$destination}' does not exist and cannot be created.");
		}

		$destination = realpath($destination);

		if (!$this->_srcFileHandle)
		{
			return FALSE;
		}

		foreach ($this->_srcCentralDirectory as $name => $info)
		{
			// Защита от Zip Slip (выход за пределы директории через ../)
			// Нормализуем имя для проверки безопасности
			$safeName = str_replace(array('../', '..\\'), '', $name);

			foreach ($this->_replaces as $key => $value)
			{
				if (strpos($safeName, $key) === 0) {
					$safeName = preg_replace('/^' . preg_quote($key, '/') . '/', $value, $safeName);
				}
			}

			// Формируем полный путь
			$cleanPath = $destination . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $safeName);

			// Если это директория (имя заканчивается на /)
			if (substr($name, -1) === '/')
			{
				if (!is_dir($cleanPath))
				{
					mkdir($cleanPath, 0755, TRUE);
				}
				continue;
			}

			// Если это файл, создаем для него директорию
			$dir = dirname($cleanPath);
			if (!is_dir($dir))
			{
				mkdir($dir, 0755, TRUE);
			}

			// Получаем контент и пишем в файл
			$content = $this->getFromName($name);

			if ($content !== FALSE)
			{
				if (file_put_contents($cleanPath, $content) !== FALSE)
				{
					// Восстанавливаем время модификации файла
					if (isset($info['mdate']) && isset($info['mtime']))
					{
						$timestamp = $this->_dos2UnixTime($info['mdate'], $info['mtime']);
						touch($cleanPath, $timestamp);
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * Unix to DOS time
	 * @param integer $timestamp
	 * @return integer
	 */
	protected function _unix2DosTime(int $timestamp)
	{
		$date = getdate($timestamp);

		if ($date['year'] < 1980) {
			return (1 << 21) | (1 << 16);
		}

		return (($date['year'] - 1980) << 25) | (($date['mon'] + 1) << 21) |
			($date['mday'] << 16) | ($date['hours'] << 11) |
			($date['minutes'] << 5) | ($date['seconds'] >> 1);
	}

	/**
	 * Convert DOS time to Unix timestamp
	 * @param integer $mdate
	 * @param integer $mtime
	 * @return integer
	 */
	protected function _dos2UnixTime(int $mdate, int $mtime)
	{
		$year = (($mdate >> 9) & 0x7F) + 1980;
		$mon = ($mdate >> 5) & 0x0F;
		$day = $mdate & 0x1F;

		$hour = ($mtime >> 11) & 0x1F;
		$min = ($mtime >> 5) & 0x3F;
		$sec = ($mtime << 1) & 0x3E;

		return mktime($hour, $min, $sec, $mon, $day, $year);
	}

	/**
	 * Close zip
	 */
	public function close($mode = CHMOD_FILE)
	{
		if ($this->_isClose) return;

		if ($this->_srcFileHandle)
		{
			foreach ($this->_srcCentralDirectory as $name => $info)
			{
				if (isset($this->_deletedFiles[$name])) continue;
				$this->_copyFileFromSource($info);
			}

			fclose($this->_srcFileHandle);
		}

		$cdStart = $this->_offset;

		foreach ($this->_centralDirectory as $file)
		{
			$flags = isset($file['flags']) ? $file['flags'] : 0;

			$cdHeader = pack('V', 0x02014b50) .
				pack('v', 0) . pack('v', 20) .
				pack('v', $flags) . // <-- Вот здесь раньше был 0, теперь реальные флаги
				pack('v', $file['method']) . pack('V', $file['dostime']) .
				pack('V', $file['crc32']) . pack('V', $file['compressed_size']) .
				pack('V', $file['size']) . pack('v', strlen($file['name'])) .
				pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0) .
				pack('V', 32) . pack('V', $file['offset']) .
				$file['name'];

			fwrite($this->_zipFileHandle, $cdHeader);
		}

		$cdEnd = ftell($this->_zipFileHandle);
		$cdSize = $cdEnd - $cdStart;
		$count = count($this->_centralDirectory);

		$footer = pack('V', 0x06054b50) .
			pack('v', 0) . pack('v', 0) .
			pack('v', $count) . pack('v', $count) .
			pack('V', $cdSize) . pack('V', $cdStart) .
			pack('v', 0);

		fwrite($this->_zipFileHandle, $footer);
		fclose($this->_zipFileHandle);

		if ($this->_tempFilename !== NULL)
		{
			if (file_exists($this->_filename))
			{
				unlink($this->_filename);
			}

			if (!rename($this->_tempFilename, $this->_filename))
			{
				copy($this->_tempFilename, $this->_filename);
				unlink($this->_tempFilename);
			}
		}

		$this->_isClose = TRUE;

		@chmod($this->_filename, $mode);
	}

	/**
	 * Replaces
	 * @var array
	 */
	protected $_replaces = array();

	/**
	 * Add replace
	 * @param string $key
	 * @param string $value
	 * @return self
	 */
	public function addReplace($key, $value)
	{
		$this->_replaces[$key] = $value;
		return $this;
	}

	/**
	 * Context for streaming write
	 * @var array|NULL
	 */
	protected $_streamState = NULL;

/**
     * Begin streaming a file into the zip
     * @param string $name Filename inside zip
     * @param int $timestamp
     */
    public function beginWriteStream(string $name, int $timestamp = 0)
    {
        if ($this->_isClose) throw new RuntimeException("Archive closed.");

        $name = str_replace('\\', '/', $name);
        $dostime = $this->_unix2DosTime($timestamp ?: time());

        // Флаги:
        // Bit 11 = UTF-8
        // Bit 3  = Data Descriptor (ОБЯЗАТЕЛЬНО для потокового шифрования)
        $flags = (1 << 11) | (1 << 3);

        $encryptionHeader = '';
        $headerSize = 0;

        if ($this->_password !== NULL)
        {
            $flags |= 1; // Bit 0 = Encrypted
            $this->_initKeys($this->_password);

            // Важный момент: При установленном бите 3 (Data Descriptor),
            // проверочный байт шифрования должен быть старшим байтом DOS-времени.
            // (Time >> 8) & 0xFF.
            // Метод _makeDecryptHeader берет (arg >> 24), поэтому сдвигаем время:
            $encryptionHeader = $this->_makeDecryptHeader($dostime << 16);
            $headerSize = 12;
        }

        // Подготовка контекста
        $this->_streamState = [
            'name' => $name,
            'method' => 8,
            'dostime' => $dostime,
            'offset' => $this->_offset,
            'crc_ctx' => hash_init('crc32b'),
            'deflate_ctx' => deflate_init(ZLIB_ENCODING_RAW, ['level' => -1]),
            'size' => 0,
            'compressed_size' => $headerSize,
            'flags' => $flags
        ];

        // Пишем Local File Header
        // При установленном Bit 3 поля CRC, Compressed и Uncompressed ДОЛЖНЫ быть 0.
        $header = pack('V', 0x04034b50) .
                  pack('v', 20) .
                  pack('v', $flags) .
                  pack('v', 8) .
                  pack('V', $dostime) .
                  pack('V', 0) . // CRC (0, так как есть Bit 3)
                  pack('V', 0) . // Comp Size (0)
                  pack('V', 0) . // Uncomp Size (0)
                  pack('v', strlen($name)) .
                  pack('v', 0) .
                  $name;

        fwrite($this->_zipFileHandle, $header);
        $this->_offset += strlen($header);

        if ($encryptionHeader !== '') {
            fwrite($this->_zipFileHandle, $encryptionHeader);
            $this->_offset += strlen($encryptionHeader);
        }
    }

	/**
	 * Write a chunk of data to the current stream
	 * @param string $data
	 */
	public function writeStreamChunk(string $data)
	{
		if (!$this->_streamState) throw new RuntimeException("No active stream.");
		if ($data === '') return;

		// Считаем CRC от ОРИГИНАЛЬНЫХ данных
		hash_update($this->_streamState['crc_ctx'], $data);
		$this->_streamState['size'] += strlen($data);

		// Сжимаем
		$compressed = deflate_add($this->_streamState['deflate_ctx'], $data, ZLIB_NO_FLUSH);

		if ($compressed !== '') {
			// Если пароль установлен, шифруем сжатые данные
			if ($this->_password !== NULL) {
				$compressed = $this->_encryptData($compressed);
			}

			$len = strlen($compressed);
			$this->_streamState['compressed_size'] += $len;
			fwrite($this->_zipFileHandle, $compressed);
			$this->_offset += $len;
		}
	}

	/**
	 * Finalize the current file stream
	 */
	public function endWriteStream()
	{
		if (!$this->_streamState) return;

		$finalCompressed = deflate_add($this->_streamState['deflate_ctx'], '', ZLIB_FINISH);

		if ($finalCompressed !== '') {
			if ($this->_password !== NULL) {
				$finalCompressed = $this->_encryptData($finalCompressed);
			}
			$len = strlen($finalCompressed);
			fwrite($this->_zipFileHandle, $finalCompressed);
			$this->_streamState['compressed_size'] += $len;
			$this->_offset += $len;
		}

		$crc32 = hexdec(hash_final($this->_streamState['crc_ctx']));

		// Так как мы установили Bit 3, мы НЕ возвращаемся назад (fseek) править заголовок.
		// Вместо этого мы дописываем информацию сразу после данных.
		// Формат: [Signature] [CRC32] [CompSize] [UncompSize]

		$dataDescriptor = pack('V', 0x08074b50) .
						pack('V', $crc32) .
						pack('V', $this->_streamState['compressed_size']) .
						pack('V', $this->_streamState['size']);

		fwrite($this->_zipFileHandle, $dataDescriptor);
		$this->_offset += strlen($dataDescriptor);

		$this->_centralDirectory[] = [
			'name' => $this->_streamState['name'],
			'method' => 8,
			'dostime' => $this->_streamState['dostime'],
			'crc32' => $crc32,
			'compressed_size' => $this->_streamState['compressed_size'],
			'size' => $this->_streamState['size'],
			'offset' => $this->_streamState['offset'], // Оффсет указывает на начало Local Header
			'flags' => $this->_streamState['flags']
		];

		$this->_streamState = NULL;
	}

	/**
	 * __destruct
	 */
	public function __destruct()
	{
		if (is_resource($this->_zipFileHandle)) fclose($this->_zipFileHandle);
		if (is_resource($this->_srcFileHandle)) fclose($this->_srcFileHandle);

		if ($this->_tempFilename && file_exists($this->_tempFilename))
		{
			@unlink($this->_tempFilename);
		}
	}
}