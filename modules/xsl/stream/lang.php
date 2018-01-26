<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Implement "lang://" protocol
 *
 * Use to add DTD depends on site language
 * <code>
 * <!DOCTYPE xsl:stylesheet SYSTEM "lang://1">
 * </code>
 * or
 * <code>
 * <!DOCTYPE xsl:stylesheet SYSTEM "lang://xslname">
 * </code>
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Stream_Lang
{
	/**
	 * Current position of a stream
	 * @var int
	 */
	protected $_position = 0;

	/**
	 * XSL name
	 * @var string
	 */
	protected $_xslName = NULL;

	/**
	 * XSL
	 * @var Xsl_Model
	 */
	protected $_oXsl = NULL;

	/**
	 * Array of DTDs
	 * @var array
	 */
	protected static $_aDTD = array();

	/**
	 * Opens file or URL
	 * @param string $path Specifies the URL that was passed to the original function.
	 * @param string $mode The mode used to open the file, as detailed for fopen().
	 * @param string $options Holds additional flags set by the streams API.
	 * @param string $opened_path
	 * @return boolean
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->_xslName = substr($path, 7);

		if (!is_numeric($this->_xslName))
		{
			// Search XSL by name
			$oXsl = Core_Entity::factory('Xsl')->getByName($this->_xslName);

			if (!is_null($oXsl))
			{
				$this->_oXsl = $oXsl;
			}
			else
			{
				throw new Core_Exception("Xsl_Stream_Lang: Undefined XSL '%name'", array('%name' => $this->_xslName));
			}
		}
		else
		{
			$this->_oXsl = Core_Entity::factory('Xsl', intval($this->_xslName));
		}

		$lng = Core::getLng();
		
		if (!isset(self::$_aDTD[$this->_xslName][$lng]))
		{
			$filePath = $this->_oXsl->getLngDtdPath($lng);

			self::$_aDTD[$this->_xslName][$lng] = '<?xml version="1.0" encoding="UTF-8"?>'
				. (is_file($filePath)
					 ? Core_File::read($filePath)
					 : '');
		}

		return TRUE;
	}

	/**
	 * Read from stream
	 * @param int $count How many bytes of data from the current position should be returned.
	 * @return string
	 */
	public function stream_read($count)
	{
		$lng = Core::getLng();
		
		$ret = substr(self::$_aDTD[$this->_xslName][$lng], $this->_position, $count);
		$this->_position += strlen($ret);

		return $ret;
	}

	/**
	 * Write to stream
	 * @param string $data Should be stored into the underlying stream.
	 * @return FALSE
	 */
	public function stream_write($data)
	{
		return FALSE;
	}

	/**
	 * Retrieve the current position of a stream
	 * @return int Current position of the stream
	 */
	public function stream_tell()
	{
		return $this->_position;
	}

	/**
	 * Tests for end-of-file on a file pointer
	 * @return Should return TRUE if the read/write position is at the end of the stream and if no more data is available to be read, or FALSE otherwise.
	 */
	public function stream_eof()
	{
		$lng = Core::getLng();
		return $this->_position >= strlen(self::$_aDTD[$this->_xslName][$lng]);
	}

	/**
	 * Seeks to specific location in a stream
	 * @param int $offset The stream offset to seek to.
	 * @param int $whence Possible values: SEEK_SET - Set position equal to offset bytes. SEEK_CUR - Set position to current location plus offset. SEEK_END - Set position to end-of-file plus offset.
	 * @return boolean Return TRUE if the position was updated, FALSE otherwise.
	 */
	public function stream_seek($offset, $whence)
	{
		$lng = Core::getLng();
		
		switch ($whence) {
			case SEEK_SET:
			if ($offset < strlen(self::$_aDTD[$this->_xslName][$lng]) && $offset >= 0)
			{
				 $this->position = $offset;
				 return TRUE;
			}
			else
			{
				 return FALSE;
			}
			break;
			case SEEK_CUR:
				if ($offset >= 0)
				{
					$this->position += $offset;
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			break;

			case SEEK_END:
			if (strlen(self::$_aDTD[$this->_xslName][$lng]) + $offset >= 0) {
				 $this->position = strlen(self::$_aDTD[$this->_xslName][$lng]) + $offset;
				 return TRUE;
			}
			else
			{
				return FALSE;
			}
			break;
			default:
				return FALSE;
		}
	}

	/**
	 * Retrieve information about a file
	 * @param string $path The file path or URL to stat. Note that in the case of a URL, it must be a :// delimited URL. Other URL forms are not supported.
	 * @param string $flags Holds additional flags set by the streams API.
	 * @return array
	 */
	public function url_stat($path, $flags)
	{
		return array();
	}
}