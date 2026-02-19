<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Creates a tar archive
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Tar
{
	/**
	 * Name of the Tar
	 * @var string
	 */
	protected $_tarname = '';

	/**
	 * If true, the Tar file will be gzipped
	 * @var bool|NULL
	 */
	protected $_compress = NULL;

	/**
	 * Type of compression : 'none', 'gz' or 'bz2'
	 * @var string
	 */
	protected $_compress_type = 'none';

	/**
	 * Explode separator
	 * @var string
	 */
	protected $_separator = ' ';

	/**
	 * File descriptor
	 * @var resource|NULL
	 */
	protected $_file = NULL;

	/**
	 * Local Tar name of a remote Tar (http:// or ftp://)
	 * @var string
	 */
	protected $_temp_tarname = '';

	/** Exclude dir
	 *
	 * @var array
	 */
	protected $_excludeDir = [];

	/**
	 * Replacements for paths
	 * @var array
	 */
	protected $_replaces = [];

	/**
	 * Callback function for progress tracking
	 * @var callable|NULL
	 */
	protected $_callback = NULL;

	/**
	 * Размер буфера для чтения/записи (64 КБ), должен быть кратен 512
	 */
	const BUFFER_SIZE = 65536;

	/**
	 * Add Excluding Dir
	 * @param string $dirPath
	 * @return self
	 */
	public function excludeDir($dirPath)
	{
		$this->_excludeDir[] = rtrim($dirPath, '\/');
		return $this;
	}

	/**
	 * Set callback function
	 * Callback signature: function($filename, $bytesProcessed, $totalBytes)
	 * @param callable $callback
	 * @return self
	 */
	public function setCallback($callback)
	{
		if (is_callable($callback))
		{
			$this->_callback = $callback;
		}
		return $this;
	}

	/**
	 * Constructor
	 * @param string $p_tarname
	 * @param string|bool|NULL $p_compress
	 */
	public function __construct($p_tarname, $p_compress = NULL)
	{
		$this->_compress = FALSE;
		$this->_compress_type = 'none';

		if ($p_compress === NULL || $p_compress === '') {
			if (@file_exists($p_tarname)) {
				if ($fp = @fopen($p_tarname, "rb")) {
					$data = fread($fp, 2);
					fclose($fp);
					if ($data === "\37\213") {
						$this->_compress = true;
						$this->_compress_type = 'gz';
					} elseif ($data === "BZ") {
						$this->_compress = true;
						$this->_compress_type = 'bz2';
					}
				}
			} else {
				if (substr($p_tarname, -2) === 'gz') {
					$this->_compress = true;
					$this->_compress_type = 'gz';
				} elseif (substr($p_tarname, -3) === 'bz2' || substr($p_tarname, -2) === 'bz') {
					$this->_compress = true;
					$this->_compress_type = 'bz2';
				}
			}
		} else {
			if ($p_compress === true || $p_compress === 'gz') {
				$this->_compress = true;
				$this->_compress_type = 'gz';
			} elseif ($p_compress === 'bz2') {
				$this->_compress = true;
				$this->_compress_type = 'bz2';
			} else {
				$this->_error("Unsupported compression type '$p_compress'. Supported: 'gz', 'bz2'.");
				return;
			}
		}

		$this->_tarname = $p_tarname;

		if ($this->_compress) {
			$extname = ($this->_compress_type === 'gz') ? 'zlib' : 'bz2';
			if (!extension_loaded($extname)) {
				$this->_error("The extension '$extname' couldn't be found.");
			}
		}
	}

	/**
	 * Create archive from file list
	 * @param mixed $p_filelist
	 * @return bool
	 */
	public function create($p_filelist)
	{
		return $this->createModify($p_filelist, '', '');
	}

	/**
	 * Add file list to archive
	 * @param mixed $p_filelist
	 * @return bool
	 */
	public function add($p_filelist)
	{
		return $this->addModify($p_filelist, '', '');
	}

	/**
	 * Extract archive
	 * @param string $p_path
	 * @return bool
	 */
	public function extract($p_path = '')
	{
		return $this->extractModify($p_path, '');
	}

	/**
	 * List archive content
	 * @return array|int
	 */
	public function listContent()
	{
		$v_list_detail = [];

		if ($this->_openRead()) {
			if (!$this->_extractList('', $v_list_detail, "list", '', '')) {
				unset($v_list_detail);
				$v_list_detail = 0;
			}
			$this->_close();
		}

		return $v_list_detail;
	}

	/**
	 * Create archive with path modification
	 * @param mixed $p_filelist
	 * @param string $p_add_dir
	 * @param string $p_remove_dir
	 * @return bool
	 */
	public function createModify($p_filelist, $p_add_dir, $p_remove_dir = '')
	{
		$v_result = true;

		if (!$this->_openWrite()) {
			return FALSE;
		}

		if ($p_filelist != '') {
			if (is_array($p_filelist)) {
				$v_list = $p_filelist;
			} elseif (is_string($p_filelist)) {
				$v_list = explode($this->_separator, $p_filelist);
			} else {
				$this->_cleanFile();
				$this->_error('Tar: File List Error!');
				return FALSE;
			}

			$v_result = $this->_addList($v_list, $p_add_dir, $p_remove_dir);
		}

		if ($v_result) {
			$this->_writeFooter();
			$this->_close();
		} else {
			$this->_cleanFile();
		}

		return $v_result;
	}

	/**
	 * Add files to archive with path modification
	 * @param mixed $p_filelist
	 * @param string $p_add_dir
	 * @param string $p_remove_dir
	 * @return bool
	 */
	public function addModify($p_filelist, $p_add_dir, $p_remove_dir = '')
	{
		if (!$this->_isArchive()) {
			return $this->createModify($p_filelist, $p_add_dir, $p_remove_dir);
		}

		if (is_array($p_filelist)) {
			$v_list = $p_filelist;
		} elseif (is_string($p_filelist)) {
			$v_list = explode($this->_separator, $p_filelist);
		} else {
			$this->_error('Tar: File List Error!');
			return FALSE;
		}

		return $this->_append($v_list, $p_add_dir, $p_remove_dir);
	}

	/**
	 * Add a string as a file to the archive
	 * @param string $p_filename
	 * @param string $p_string
	 * @return bool
	 */
	public function addString($p_filename, $p_string)
	{
		if (!$this->_isArchive()) {
			if (!$this->_openWrite()) {
				return FALSE;
			}
			$this->_close();
		}

		if (!$this->_openAppend()) {
			return FALSE;
		}

		$v_result = $this->_addString($p_filename, $p_string);

		$this->_writeFooter();
		$this->_close();

		return $v_result;
	}

	/**
	 * Extract with path modification
	 * @param string $p_path
	 * @param string $p_remove_path
	 * @return bool
	 */
	public function extractModify($p_path, $p_remove_path)
	{
		$v_result = true;
		$v_list_detail = [];

		if ($this->_openRead()) {
			$v_result = $this->_extractList($p_path, $v_list_detail, "complete", [], $p_remove_path);
			$this->_close();
		}

		return $v_result;
	}

	/**
	 * Extract file content to string
	 * @param string $p_filename
	 * @return string|NULL
	 */
	public function extractInString($p_filename)
	{
		if ($this->_openRead()) {
			$v_result = $this->_extractInString($p_filename);
			$this->_close();
		} else {
			$v_result = NULL;
		}

		return $v_result;
	}

	/**
	 * Extract specific list of files
	 * @param mixed $p_filelist
	 * @param string $p_path
	 * @param string $p_remove_path
	 * @return bool
	 */
	public function extractList($p_filelist, $p_path = '', $p_remove_path = '')
	{
		$v_list_detail = [];

		if (is_array($p_filelist)) {
			$v_list = $p_filelist;
		} elseif (is_string($p_filelist)) {
			$v_list = explode($this->_separator, $p_filelist);
		} else {
			$this->_error('Tar: Row List Error!');
			return FALSE;
		}

		if ($this->_openRead()) {
			$v_result = $this->_extractList($p_path, $v_list_detail, "partial", $v_list, $p_remove_path);
			$this->_close();
			return $v_result;
		}

		return FALSE;
	}

	/**
	 * Show error message
	 * @param string $p_message message text
	 */
	protected function _error($p_message)
	{
		Core_Message::show($p_message, 'error');
	}

	/**
	 * Show warning message
	 * @param string $p_message message text
	 */
	protected function _warning($p_message)
	{
		Core_Message::show($p_message, 'error');
	}

	/**
	 * Check if file is archive
	 * @param string $p_filename file name
	 * @return boolean
	 */
	protected function _isArchive($p_filename = NULL)
	{
		if ($p_filename === NULL) {
			$p_filename = $this->_tarname;
		}
		return @is_file($p_filename);
	}

	/**
	 * Open archive for writing
	 * @return bool
	 */
	protected function _openWrite()
	{
		if ($this->_compress_type === 'gz') {
			$this->_file = $this->gzopen($this->_tarname, "wb9");
		} elseif ($this->_compress_type === 'bz2') {
			$this->_file = bzopen($this->_tarname, "wb");
		} elseif ($this->_compress_type === 'none') {
			$this->_file = fopen($this->_tarname, "wb");
		} else {
			$this->_error('Unknown compression type (' . $this->_compress_type . ')');
			return FALSE;
		}

		if ($this->_file === FALSE) {
			$this->_error("File open error '{$this->_tarname}'");
			return FALSE;
		}

		return true;
	}

	/**
	 * Open archive for reading
	 * @return bool
	 */
	protected function _openRead()
	{
		if (strpos(strtolower($this->_tarname), 'http://') === 0) {
			if ($this->_temp_tarname == '') {
				$this->_temp_tarname = uniqid('tar') . '.tmp';

				$v_file_from = @fopen($this->_tarname, 'rb');
				if (!$v_file_from) {
					$this->_error("Read file error '{$this->_tarname}'");
					$this->_temp_tarname = '';
					return FALSE;
				}

				$v_file_to = @fopen($this->_temp_tarname, 'wb');
				if (!$v_file_to) {
					$this->_error("Write file error '{$this->_temp_tarname}'");
					$this->_temp_tarname = '';
					@fclose($v_file_from);
					return FALSE;
				}

				if (function_exists('stream_copy_to_stream'))
				{
					@stream_copy_to_stream($v_file_from, $v_file_to);
				}
				else
				{
					while (!feof($v_file_from)) {
						@fwrite($v_file_to, @fread($v_file_from, self::BUFFER_SIZE));
					}
				}

				@fclose($v_file_from);
				@fclose($v_file_to);
			}
			$v_filename = $this->_temp_tarname;
		} else {
			$v_filename = $this->_tarname;
		}

		if ($this->_compress_type === 'gz') {
			$this->_file = $this->gzopen($v_filename, "rb");
		} elseif ($this->_compress_type === 'bz2') {
			$this->_file = bzopen($v_filename, "rb");
		} elseif ($this->_compress_type === 'none') {
			$this->_file = fopen($v_filename, "rb");
		} else {
			$this->_error('Unknown compression type (' . $this->_compress_type . ')');
			return FALSE;
		}

		if ($this->_file === FALSE) {
			$this->_error("Can not open to read '$v_filename'");
			return FALSE;
		}

		return true;
	}

	/**
	 * Open archive for reading and writing
	 * @return bool
	 */
	protected function _openReadWrite()
	{
		if ($this->_compress_type === 'gz') {
			$this->_file = $this->gzopen($this->_tarname, "r+b");
		} elseif ($this->_compress_type === 'bz2') {
			$this->_file = bzopen($this->_tarname, "r+b");
		} elseif ($this->_compress_type === 'none') {
			$this->_file = fopen($this->_tarname, "r+b");
		} else {
			$this->_error('Unknown compression type (' . $this->_compress_type . ')');
			return FALSE;
		}

		if ($this->_file === FALSE) {
			$this->_error("Tar: Open file error '{$this->_tarname}'");
			return FALSE;
		}

		return true;
	}

	/**
	 * Close file descriptor and clean up temporary files
	 * @return bool
	 */
	protected function _close()
	{
		if (is_resource($this->_file)) {
			if ($this->_compress_type === 'gz') {
				@gzclose($this->_file);
			} elseif ($this->_compress_type === 'bz2') {
				@bzclose($this->_file);
			} elseif ($this->_compress_type === 'none') {
				@fclose($this->_file);
			}
			$this->_file = NULL;
		}

		if ($this->_temp_tarname != '') {
			@unlink($this->_temp_tarname);
			$this->_temp_tarname = '';
		}

		return true;
	}

	/**
	 * Clean up and delete the archive file
	 * @return bool
	 */
	protected function _cleanFile()
	{
		$this->_close();
		if ($this->_temp_tarname != '') {
			@unlink($this->_temp_tarname);
			$this->_temp_tarname = '';
		} else {
			@unlink($this->_tarname);
		}
		$this->_tarname = '';
		return true;
	}

	/**
	 * Write a block of data to the archive
	 * @param string $p_binary_data Binary data to write
	 * @param int|NULL $p_len Length of data (optional)
	 * @return bool
	 */
	protected function _writeBlock($p_binary_data, $p_len = NULL)
	{
		if (is_resource($this->_file)) {
			if ($p_len === NULL) {
				if ($this->_compress_type === 'gz') {
					@gzputs($this->_file, $p_binary_data);
				} elseif ($this->_compress_type === 'bz2') {
					@bzwrite($this->_file, $p_binary_data);
				} elseif ($this->_compress_type === 'none') {
					@fputs($this->_file, $p_binary_data);
				}
			} else {
				if ($this->_compress_type === 'gz') {
					@gzputs($this->_file, $p_binary_data, $p_len);
				} elseif ($this->_compress_type === 'bz2') {
					@bzwrite($this->_file, $p_binary_data, $p_len);
				} elseif ($this->_compress_type === 'none') {
					@fputs($this->_file, $p_binary_data, $p_len);
				}
			}
		}
		return true;
	}

	/**
	 * Read a 512-byte block from the archive
	 * @return string|NULL
	 */
	protected function _readBlock()
	{
		$v_block = NULL;
		if (is_resource($this->_file)) {
			if ($this->_compress_type === 'gz') {
				$v_block = @gzread($this->_file, 512);
			} elseif ($this->_compress_type === 'bz2') {
				$v_block = @bzread($this->_file, 512);
			} elseif ($this->_compress_type === 'none') {
				$v_block = @fread($this->_file, 512);
			}
		}
		return $v_block;
	}

	/**
	 * Jump forward in the file by a number of blocks
	 * @param int|NULL $p_len Number of blocks to jump (default 1)
	 * @return bool
	 */
	protected function _jumpBlock($p_len = NULL)
	{
		if (is_resource($this->_file)) {
			if ($p_len === NULL) $p_len = 1;

			if ($this->_compress_type === 'gz') {
				@gzseek($this->_file, @gztell($this->_file) + ($p_len * 512));
			} elseif ($this->_compress_type === 'bz2') {
				for ($i = 0; $i < $p_len; $i++) $this->_readBlock();
			} elseif ($this->_compress_type === 'none') {
				@fseek($this->_file, @ftell($this->_file) + ($p_len * 512));
			}
		}
		return true;
	}

	/**
	 * Write the footer (empty block) to the end of the archive
	 * @return bool
	 */
	protected function _writeFooter()
	{
		if (is_resource($this->_file)) {
			$v_binary_data = pack("a512", '');
			$this->_writeBlock($v_binary_data);
		}
		return true;
	}

	/**
	 * Add a list of files to the archive
	 * @param array $p_list List of files
	 * @param string $p_add_dir Path to add to filenames
	 * @param string $p_remove_dir Path to remove from filenames
	 * @return bool
	 */
	protected function _addList($p_list, $p_add_dir, $p_remove_dir)
	{
		$v_result = true;
		$v_header = [];

		$p_add_dir = $this->_translateWinPath($p_add_dir);
		$p_remove_dir = $this->_translateWinPath($p_remove_dir, FALSE);

		if (!$this->_file) {
			$this->_error('_addList: Invalid file descriptor');
			return FALSE;
		}

		if (count($p_list) == 0) return true;

		$extension_list = defined('EXTENSION_NOT_IN_BACKUP')
			? explode(' ', EXTENSION_NOT_IN_BACKUP)
			: [];

		// List of paths/files to always ignore
		$aIgnoredPatterns = ['.git', '.idea', '.DS_Store', 'node_modules', '.vscode'];

		foreach ($p_list as $v_filename)
		{
			if (!$v_result) break;

			$v_filename = str_replace(['//', '\\\\'], ['/', '\\'], $v_filename);
			$v_filename = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $v_filename);

			if (defined('BACKUP_DIR') && strpos($v_filename, rtrim(BACKUP_DIR, DIRECTORY_SEPARATOR)) === 0) {
				continue;
			}

			foreach ($this->_excludeDir as $excludeDir) {
				if (strpos($v_filename, $excludeDir) === 0) continue 2;
			}

			// Check against ignored patterns
			foreach ($aIgnoredPatterns as $pattern)
			{
				if (strpos($v_filename, $pattern) !== FALSE) {
					continue 2;
				}
			}

			$extension = Core_File::getExtension($v_filename);

			if (in_array($extension, $extension_list)) continue;

			if ($v_filename == $this->_tarname || empty($v_filename)) continue;

			if (!file_exists($v_filename)) {
				$this->_warning("Предупреждение: Файл '$v_filename' не существует!");
				continue;
			}

			if (!$this->_addFile($v_filename, $v_header, $p_add_dir, $p_remove_dir)) {
				return FALSE;
			}

			if (@is_dir($v_filename) && !is_link($v_filename)) {
				if (!($p_hdir = opendir($v_filename))) {
					$this->_warning("Предупреждение: Не могу прочитать директорию '$v_filename'!");
					continue;
				}
				while (false !== ($p_hitem = readdir($p_hdir))) {
					if (($p_hitem != '.') && ($p_hitem != '..')) {
						$p_temp_list = [$v_filename != "." ? $v_filename . '/' . $p_hitem : $p_hitem];
						$v_result = $this->_addList($p_temp_list, $p_add_dir, $p_remove_dir);
					}
				}
				closedir($p_hdir);
			}
		}

		return $v_result;
	}

	/**
	 * Reads in chunks of BUFFER_SIZE
	 * @param string $p_filename
	 * @param array $p_header
	 * @param string $p_add_dir
	 * @param string $p_remove_dir
	 * @return bool
	 */
	protected function _addFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir)
	{
		if (!$this->_file) {
			$this->_error('_addFile: Invalid file descriptor');
			return FALSE;
		}

		if ($p_filename == '') {
			$this->_error('_addFile: Wrong file name');
			return FALSE;
		}

		$p_filename = $this->_translateWinPath($p_filename, FALSE);
		$v_stored_filename = $p_filename;

		if (strcmp($p_filename, $p_remove_dir) == 0) return true;

		if ($p_remove_dir != '') {
			if (substr($p_remove_dir, -1) != '/') $p_remove_dir .= '/';
			if (substr($p_filename, 0, strlen($p_remove_dir)) == $p_remove_dir) {
				$v_stored_filename = substr($p_filename, strlen($p_remove_dir));
			}
		}
		$v_stored_filename = $this->_translateWinPath($v_stored_filename);
		if ($p_add_dir != '') {
			$v_stored_filename = (substr($p_add_dir, -1) == '/')
				? $p_add_dir . $v_stored_filename
				: $p_add_dir . '/' . $v_stored_filename;
		}

		$v_stored_filename = $this->_pathReduction($v_stored_filename);

		if ($this->_isArchive($p_filename)) {
			if (($v_file = @fopen($p_filename, "rb")) === FALSE) {
				$this->_warning("Предупреждение: Не могу открыть файл '" . $p_filename . "' для чтения в бинарном режиме!");
				return true;
			}

			if (!$this->_writeHeader($p_filename, $v_stored_filename)) return FALSE;

			$fileSize = filesize($p_filename);
			$bytesProcessed = 0;

			while (!feof($v_file)) {
				// Prevent timeout on large files
				if (function_exists('set_time_limit')) {
					@set_time_limit(30);
				}

				$v_buffer = fread($v_file, self::BUFFER_SIZE);
				if ($v_buffer === FALSE || $v_buffer === '') break;

				$len = strlen($v_buffer);
				$bytesProcessed += $len;

				$remainder = $len % 512;

				// Padding if needed (usually at end of file)
				if ($remainder > 0) {
					$v_buffer .= str_repeat("\0", 512 - $remainder);
				}

				$this->_writeBlock($v_buffer);

				// Trigger callback
				if ($this->_callback) {
					call_user_func($this->_callback, $p_filename, $bytesProcessed, $fileSize);
				}
			}

			fclose($v_file);
		} else {
			if (!$this->_writeHeader($p_filename, $v_stored_filename)) return FALSE;
		}

		return true;
	}

	/**
	 * Add a string as a file to the archive (internal)
	 * @param string $p_filename
	 * @param string $p_string
	 * @return bool
	 */
	protected function _addString($p_filename, $p_string)
	{
		if (!$this->_file) {
			$this->_error('_addString: Invalid file descriptor');
			return FALSE;
		}

		if ($p_filename == '') {
			$this->_error('_addString: Wrong file name');
			return FALSE;
		}

		$p_filename = $this->_translateWinPath($p_filename, FALSE);

		if (!$this->_writeHeaderBlock($p_filename, strlen($p_string), 0, 0, "", 0, 0)) {
			return FALSE;
		}

		$i = 0;
		while (($v_buffer = substr($p_string, (($i++) * 512), 512)) != '') {
			$v_binary_data = pack("a512", $v_buffer);
			$this->_writeBlock($v_binary_data);
		}

		return true;
	}

	/**
	 * Write file header based on real file
	 * @param string $p_filename Real path
	 * @param string $p_stored_filename Path stored in tar
	 * @return bool
	 */
	protected function _writeHeader($p_filename, $p_stored_filename)
	{
		if ($p_stored_filename == '') $p_stored_filename = $p_filename;
		$v_reduce_filename = $this->_pathReduction($p_stored_filename);

		if (strlen($v_reduce_filename) > 99) {
			if (!$this->_writeLongHeader($v_reduce_filename)) return FALSE;
		}

		$v_info = stat($p_filename);
		$v_uid = sprintf("%6s ", decoct($v_info[4]));
		$v_gid = sprintf("%6s ", decoct($v_info[5]));
		$v_perms = sprintf("%6s ", decoct(fileperms($p_filename)));
		$v_mtime = sprintf("%11s", decoct(filemtime($p_filename)));

		if (@is_dir($p_filename) && !is_link($p_filename)) {
			$v_typeflag = "5";
			$v_size = sprintf("%11s ", decoct(0));
		} else {
			$v_typeflag = '';
			$v_size = sprintf("%11s ", decoct(filesize($p_filename)));
		}

		$v_linkname = $v_magic = $v_version = $v_uname = $v_gname = $v_devmajor = $v_devminor = $v_prefix = '';

		$v_binary_data_first = pack("a100a8a8a8a12A12",
			$v_reduce_filename, $v_perms, $v_uid, $v_gid, $v_size, $v_mtime);
		$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12",
			$v_typeflag, $v_linkname, $v_magic, $v_version, $v_uname, $v_gname, $v_devmajor, $v_devminor, $v_prefix, '');

		$v_checksum = 0;
		for ($i = 0; $i < 148; $i++) $v_checksum += ord(substr($v_binary_data_first, $i, 1));
		for ($i = 148; $i < 156; $i++) $v_checksum += ord(' ');
		for ($i = 156, $j = 0; $i < 512; $i++, $j++) $v_checksum += ord(substr($v_binary_data_last, $j, 1));

		$this->_writeBlock($v_binary_data_first, 148);

		$v_checksum = sprintf("%6s ", decoct($v_checksum));
		$v_binary_data = pack("a8", $v_checksum);
		$this->_writeBlock($v_binary_data, 8);

		$this->_writeBlock($v_binary_data_last, 356);

		return true;
	}

	/**
	 * Write header block for generated content
	 * @param string $p_filename
	 * @param int $p_size
	 * @param int $p_mtime
	 * @param int $p_perms
	 * @param string $p_type
	 * @param int $p_uid
	 * @param int $p_gid
	 * @return bool
	 */
	protected function _writeHeaderBlock($p_filename, $p_size, $p_mtime = 0, $p_perms = 0, $p_type = '', $p_uid = 0, $p_gid = 0)
	{
		$p_filename = $this->_pathReduction($p_filename);

		if (strlen($p_filename) > 99) {
			if (!$this->_writeLongHeader($p_filename)) return FALSE;
		}

		$v_size = ($p_type == "5") ? sprintf("%11s ", decoct(0)) : sprintf("%11s ", decoct($p_size));
		$v_uid = sprintf("%6s ", decoct($p_uid));
		$v_gid = sprintf("%6s ", decoct($p_gid));
		$v_perms = sprintf("%6s ", decoct($p_perms));
		$v_mtime = sprintf("%11s", decoct($p_mtime));

		$v_linkname = $v_magic = $v_version = $v_uname = $v_gname = $v_devmajor = $v_devminor = $v_prefix = '';

		$v_binary_data_first = pack("a100a8a8a8a12A12", $p_filename, $v_perms, $v_uid, $v_gid, $v_size, $v_mtime);
		$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", $p_type, $v_linkname, $v_magic, $v_version, $v_uname, $v_gname, $v_devmajor, $v_devminor, $v_prefix, '');

		$v_checksum = 0;
		for ($i = 0; $i < 148; $i++) $v_checksum += ord(substr($v_binary_data_first, $i, 1));
		for ($i = 148; $i < 156; $i++) $v_checksum += ord(' ');
		for ($i = 156, $j = 0; $i < 512; $i++, $j++) $v_checksum += ord(substr($v_binary_data_last, $j, 1));

		$this->_writeBlock($v_binary_data_first, 148);
		$v_binary_data = pack("a8", sprintf("%6s ", decoct($v_checksum)));
		$this->_writeBlock($v_binary_data, 8);
		$this->_writeBlock($v_binary_data_last, 356);

		return true;
	}

	/**
	 * Write long filename header (GNU extension)
	 * @param string $p_filename
	 * @return bool
	 */
	protected function _writeLongHeader($p_filename)
	{
		$v_size = sprintf("%11s ", decoct(strlen($p_filename)));
		$v_typeflag = 'L';
		$v_linkname = $v_magic = $v_version = $v_uname = $v_gname = $v_devmajor = $v_devminor = $v_prefix = '';

		$v_binary_data_first = pack("a100a8a8a8a12A12", '././@LongLink', 0, 0, 0, $v_size, 0);
		$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", $v_typeflag, $v_linkname, $v_magic, $v_version, $v_uname, $v_gname, $v_devmajor, $v_devminor, $v_prefix, '');

		$v_checksum = 0;
		for ($i = 0; $i < 148; $i++) $v_checksum += ord(substr($v_binary_data_first, $i, 1));
		for ($i = 148; $i < 156; $i++) $v_checksum += ord(' ');
		for ($i = 156, $j = 0; $i < 512; $i++, $j++) $v_checksum += ord(substr($v_binary_data_last, $j, 1));

		$this->_writeBlock($v_binary_data_first, 148);
		$this->_writeBlock(pack("a8", sprintf("%6s ", decoct($v_checksum))), 8);
		$this->_writeBlock($v_binary_data_last, 356);

		$i = 0;
		while (($v_buffer = substr($p_filename, (($i++) * 512), 512)) != '') {
			$v_binary_data = pack("a512", "$v_buffer");
			$this->_writeBlock($v_binary_data);
		}

		return true;
	}

	/**
	 * Decode the checksum and read header info
	 * @param string $v_binary_data
	 * @param array $v_header Reference to header array
	 * @return bool
	 */
	protected function _readHeader($v_binary_data, &$v_header)
	{
		if (strlen($v_binary_data) == 0) {
			$v_header['filename'] = '';
			return true;
		}

		if (strlen($v_binary_data) != 512) {
			$v_header['filename'] = '';
			$this->_error('Wrong block size: ' . strlen($v_binary_data));
			return FALSE;
		}

		$v_checksum = 0;
		for ($i = 0; $i < 148; $i++) $v_checksum += ord(substr($v_binary_data, $i, 1));
		for ($i = 148; $i < 156; $i++) $v_checksum += ord(' ');
		for ($i = 156; $i < 512; $i++) $v_checksum += ord(substr($v_binary_data, $i, 1));

		$v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $v_binary_data);

		$v_header['checksum'] = octdec(trim($v_data['checksum']));
		if ($v_header['checksum'] != $v_checksum) {
			$v_header['filename'] = '';
			if ($v_checksum == 256 && $v_header['checksum'] == 0) return true;
			$this->_error(Core::_('Core.unpack_wrong_crc', $v_data['filename'], $v_checksum, $v_header['checksum']));
			return FALSE;
		}

		$v_header['filename'] = trim($v_data['filename']);
		$v_header['mode'] = octdec(trim($v_data['mode']));
		$v_header['uid'] = octdec(trim($v_data['uid']));
		$v_header['gid'] = octdec(trim($v_data['gid']));
		$v_header['size'] = octdec(trim($v_data['size']));
		$v_header['mtime'] = octdec(trim($v_data['mtime']));
		if (($v_header['typeflag'] = $v_data['typeflag']) == "5") {
			$v_header['size'] = 0;
		}

		return true;
	}

	/**
	 * Read long filename from subsequent blocks
	 * @param array $v_header Reference to header array
	 * @return bool
	 */
	protected function _readLongHeader(&$v_header)
	{
		$v_filename = '';
		$n = floor($v_header['size'] / 512);
		for ($i = 0; $i < $n; $i++) {
			$v_filename .= $this->_readBlock();
		}
		if (($v_header['size'] % 512) != 0) {
			$v_filename .= $this->_readBlock();
		}

		$v_binary_data = $this->_readBlock();
		if (!$this->_readHeader($v_binary_data, $v_header)) return FALSE;

		$v_header['filename'] = trim($v_filename);
		return true;
	}

	/**
	 * Extract a file content into a string (internal)
	 * @param string $p_filename
	 * @return string|NULL
	 */
	protected function _extractInString($p_filename)
	{
		$v_result_str = "";

		while (strlen($v_binary_data = $this->_readBlock()) != 0) {
			$v_header = [];
			if (!$this->_readHeader($v_binary_data, $v_header)) return NULL;

			if ($v_header['filename'] == '') continue;

			if ($v_header['typeflag'] == 'L') {
				if (!$this->_readLongHeader($v_header)) return NULL;
			}

			if ($v_header['filename'] == $p_filename) {
				if ($v_header['typeflag'] == "5") {
					$this->_error('Can\'t extract string in directory ' . $v_header['filename']);
					return NULL;
				} else {
					$n = floor($v_header['size'] / 512);
					for ($i = 0; $i < $n; $i++) {
						$v_result_str .= $this->_readBlock();
					}
					if (($v_header['size'] % 512) != 0) {
						$v_content = $this->_readBlock();
						$v_result_str .= substr($v_content, 0, ($v_header['size'] % 512));
					}
					return $v_result_str;
				}
			} else {
				$this->_jumpBlock((int)ceil(($v_header['size'] / 512)));
			}
		}

		return NULL;
	}

	/**
	 * Extract files from archive
	 * @param string $p_path Extraction path
	 * @param array $p_list_detail Reference to list detail
	 * @param string $p_mode Mode: 'complete', 'list', or 'partial'
	 * @param array|string $p_file_list List of specific files to extract
	 * @param string $p_remove_path Path to remove from extracted files
	 * @return bool
	 */
	protected function _extractList($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path)
	{
		$v_result = true;
		$v_nb = 0;
		$v_extract_all = ($p_mode === "complete");
		$v_listing = ($p_mode === "list");

		$p_path = $this->_translateWinPath($p_path, FALSE);
		if ($p_path == '' || (substr($p_path, 0, 1) != '/' && substr($p_path, 0, 3) != "../" && !strpos($p_path, ':'))) {
			$p_path = "./" . $p_path;
		}
		$p_remove_path = $this->_translateWinPath($p_remove_path);

		if (($p_remove_path != '') && (substr($p_remove_path, -1) != '/')) {
			$p_remove_path .= '/';
		}
		$p_remove_path_size = strlen($p_remove_path);

		while (strlen($v_binary_data = $this->_readBlock()) != 0) {
			// Prevent timeout on large files
			if (function_exists('set_time_limit')) {
				@set_time_limit(30);
			}

			$v_extract_file = FALSE;
			$v_extraction_stopped = 0;
			$v_header = [];

			if (!$this->_readHeader($v_binary_data, $v_header)) return FALSE;

			if ($v_header['filename'] == '') continue;

			if ($v_header['typeflag'] == 'L') {
				if (!$this->_readLongHeader($v_header)) return FALSE;
			}

			if ((!$v_extract_all) && (is_array($p_file_list))) {
				for ($i = 0; $i < count($p_file_list); $i++) {
					if (substr($p_file_list[$i], -1) == '/') {
						if ((strlen($v_header['filename']) > strlen($p_file_list[$i]))
							&& (substr($v_header['filename'], 0, strlen($p_file_list[$i])) == $p_file_list[$i])) {
							$v_extract_file = true;
							break;
						}
					} elseif ($p_file_list[$i] == $v_header['filename']) {
						$v_extract_file = true;
						break;
					}
				}
			} else {
				$v_extract_file = true;
			}

			if (($v_extract_file) && (!$v_listing)) {
				if (($p_remove_path != '') && (substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path)) {
					$v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);
				}

				if (($p_path != './') && ($p_path != '/')) {
					while (substr($p_path, -1) == '/') $p_path = substr($p_path, 0, strlen($p_path) - 1);

					foreach ($this->_replaces as $key => $value) {
						if (strpos($v_header['filename'], $key) === 0) {
							$v_header['filename'] = preg_replace('/^' . preg_quote($key, '/') . '/', $value, $v_header['filename']);
						}
					}

					$v_header['filename'] = substr($v_header['filename'], 0, 1) == '/'
						? $p_path . $v_header['filename']
						: $p_path . '/' . $v_header['filename'];
				}

				$v_header['filename'] = trim($v_header['filename']);

				if (file_exists($v_header['filename'])) {
					if ((@is_dir($v_header['filename']) && !is_link($v_header['filename'])) && ($v_header['typeflag'] == '')) {
						$this->_error(Core::_('Core.unpack_file_already_exists_and_directory', $v_header['filename']));
						return FALSE;
					}
					if (($this->_isArchive($v_header['filename'])) && ($v_header['typeflag'] == "5")) {
						$this->_error(Core::_('Core.unpack_dir_already_exists_and_file', $v_header['filename']));
						return FALSE;
					}
					if (!is_writeable($v_header['filename'])) {
						$this->_error(Core::_('Core.unpack_file_already_exists_and_protected', $v_header['filename']));
						return FALSE;
					}
				} elseif (($v_result = $this->_dirCheck(($v_header['typeflag'] == "5" ? $v_header['filename'] : dirname($v_header['filename'])), (int)Core_Array::get($v_header, 'mode', 0755))) != 1) {
					$this->_error(Core::_('Core.unpack_error_creating_dir', $v_header['filename']));
					return FALSE;
				}

				if ($v_extract_file) {
					if ($v_header['typeflag'] == "5") {
						if (!@file_exists($v_header['filename'])) {
							$chmod = (int)Core_Array::get($v_header, 'mode', 0755);
							if (!@mkdir($v_header['filename'], $chmod)) {
								$this->_error(Core::_('Core.unpack_error_creating_dir', $v_header['filename']));
								return FALSE;
							}
							@chmod($v_header['filename'], $chmod);
						}
					} else {
						if (($v_dest_file = @fopen($v_header['filename'], "wb")) == 0) {
							$this->_error(Core::_('Core.unpack_error_opening_binary_mode', $v_header['filename']));
							return FALSE;
						} else {
							$n = floor($v_header['size'] / 512);
							for ($i = 0; $i < $n; $i++) {
								$v_content = $this->_readBlock();
								fwrite($v_dest_file, $v_content, 512);
							}
							if (($v_header['size'] % 512) != 0) {
								$v_content = $this->_readBlock();
								fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
							}
							@fclose($v_dest_file);
							@touch($v_header['filename'], $v_header['mtime']);
							$chmod = (int)Core_Array::get($v_header, 'mode', 0644);
							@chmod($v_header['filename'], $chmod);
						}

						clearstatcache(true, $v_header['filename']);
						if (filesize($v_header['filename']) != $v_header['size']) {
							$this->_error(Core::_('Core.unpack_file_incorrect_size', $v_header['filename'], filesize($v_header['filename']), $v_header['size']));
							return FALSE;
						}
					}
				} else {
					$this->_jumpBlock((int)ceil(($v_header['size'] / 512)));
				}
			} else {
				$this->_jumpBlock((int)ceil(($v_header['size'] / 512)));
			}

			if ($v_listing || $v_extract_file || $v_extraction_stopped) {
				$p_list_detail[$v_nb++] = $v_header;
			}
		}

		return true;
	}

	/**
	 * Open archive in append mode
	 * @return bool
	 */
	protected function _openAppend()
	{
		if (filesize($this->_tarname) == 0) return $this->_openWrite();

		if ($this->_compress) {
			$this->_close();

			if (!@rename($this->_tarname, $this->_tarname . ".tmp")) {
				$this->_error("Can't rename {$this->_tarname} => {$this->_tarname}.tmp");
				return FALSE;
			}

			if ($this->_compress_type == 'gz') {
				$v_temp_tar = $this->gzopen($this->_tarname . ".tmp", "rb");
			} elseif ($this->_compress_type == 'bz2') {
				$v_temp_tar = bzopen($this->_tarname . ".tmp", "rb");
			} else {
				$v_temp_tar = FALSE;
			}

			if ($v_temp_tar == 0) {
				$this->_error(Core::_('Core.unpack_error_opening_binary_mode', $this->_tarname));
				@rename($this->_tarname . ".tmp", $this->_tarname);
				return FALSE;
			}

			if (!$this->_openWrite()) {
				@rename($this->_tarname . ".tmp", $this->_tarname);
				return FALSE;
			}

			if ($this->_compress_type == 'gz') {
				$v_buffer = @gzread($v_temp_tar, 512);
				if (!@gzeof($v_temp_tar)) {
					do {
						$v_binary_data = pack("a512", $v_buffer);
						$this->_writeBlock($v_binary_data);
						$v_buffer = @gzread($v_temp_tar, 512);
					} while (!@gzeof($v_temp_tar));
				}
				@gzclose($v_temp_tar);
			} elseif ($this->_compress_type == 'bz2') {
				while (strlen($buff = @bzread($v_temp_tar, 512)) > 0) {
					$v_binary_data = pack("a512", $buff);
					$this->_writeBlock($v_binary_data);
				}
				@bzclose($v_temp_tar);
			}

			if (!@unlink($this->_tarname . '.tmp')) {
				$this->_error("Can't delete {$this->_tarname}.tmp");
			}
		} else {
			if (!$this->_openReadWrite()) return FALSE;
			clearstatcache(true, $this->_tarname);
			$v_size = filesize($this->_tarname);
			fseek($this->_file, $v_size - 512);
		}

		return true;
	}

	/**
	 * Append files to existing archive
	 * @param mixed $p_filelist
	 * @param string $p_add_dir
	 * @param string $p_remove_dir
	 * @return bool
	 */
	protected function _append($p_filelist, $p_add_dir = '', $p_remove_dir = '')
	{
		if (!$this->_openAppend()) return FALSE;
		if ($this->_addList($p_filelist, $p_add_dir, $p_remove_dir)) {
			$this->_writeFooter();
		}
		$this->_close();
		return true;
	}

	/**
	 * Check and create directory recursively
	 * @param string $p_dir
	 * @param int $chmod
	 * @return bool
	 */
	protected function _dirCheck($p_dir, $chmod = 0755)
	{
		if ((@is_dir($p_dir)) || ($p_dir == '')) return true;

		$p_parent_dir = dirname($p_dir);

		if ($p_parent_dir != $p_dir && $p_parent_dir != '' && !$this->_dirCheck($p_parent_dir, $chmod)) {
			return FALSE;
		}

		if (strlen($p_dir) > 2 && substr($p_dir, -1) == '/') {
			$p_dir = substr($p_dir, 0, -1);
		}

		if (!@mkdir($p_dir, $chmod)) {
			$this->_error("Error creating directory '{$p_dir}'");
			return FALSE;
		}
		@chmod($p_dir, $chmod);

		return true;
	}

	/**
	 * Reduce path (remove . and ..)
	 * @param string $p_dir
	 * @return string
	 */
	protected function _pathReduction($p_dir)
	{
		$v_result = '';
		if ($p_dir != '') {
			$v_list = explode('/', $p_dir);
			for ($i = count($v_list) - 1; $i >= 0; $i--) {
				if ($v_list[$i] == ".") {
					// ignore
				} else if ($v_list[$i] == "..") {
					$i--;
				} else if (($v_list[$i] == '') && ($i != (count($v_list) - 1)) && ($i != 0)) {
					// ignore double slash
				} else {
					$v_result = $v_list[$i] . ($i != (count($v_list) - 1) ? '/' . $v_result : '');
				}
			}
		}
		return strtr($v_result, '\\', '/');
	}

	/**
	 * Translate Windows paths to standard format
	 * @param string $p_path
	 * @param bool $p_remove_disk_letter
	 * @return string
	 */
	protected function _translateWinPath($p_path, $p_remove_disk_letter = true)
	{
		if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
			if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) !== FALSE)) {
				$p_path = substr($p_path, $v_position + 1);
			}
			if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0, 1) == '\\')) {
				$p_path = strtr($p_path, '\\', '/');
			}
		}
		return $p_path;
	}

	/**
	 * Open a gzip file (wrapper for gzopen/gzopen64)
	 * @param string $filename
	 * @param string $mode
	 * @param int $use_include_path
	 * @return resource|bool
	 */
	public function gzopen($filename, $mode, $use_include_path = 0)
	{
		$sFunctionName = function_exists('gzopen') ? 'gzopen' : 'gzopen64';
		return $sFunctionName($filename, $mode, $use_include_path);
	}

	/**
	 * Add path replacement rule
	 * @param string $key Search path
	 * @param string $value Replace with
	 * @return self
	 */
	public function addReplace($key, $value)
	{
		$this->_replaces[$key] = $value;
		return $this;
	}
}