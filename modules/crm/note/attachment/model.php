<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Attachment_Model
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Crm_Note_Attachment_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'crm_note' => array(),
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * File directory
	 * @var string
	 */
	protected $_dir = NULL;

	/**
	 * Set file directory
	 * @param string $dir directory path
	 * @return self
	 */
	public function setDir($dir)
	{
		$this->_dir = $dir;
		return $this;
	}

	/**
	 * File href
	 * @var string
	 */
	protected $_href = NULL;

	/**
	 * Set file href
	 * @param string $href href
	 * @return self
	 */
	public function setHref($href)
	{
		$this->_href = $href;
		return $this;
	}

	/**
	 * Get file path
	 * @return string
	 */
	public function getPath()
	{
		return $this->_dir;
	}

	/**
	 * Get file href
	 * @return string
	 */
	public function getHref()
	{
		return $this->_href;
	}

	/**
	 * Get file path
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->_dir . $this->file;
	}

	/**
	 * Get file href
	 * @return string
	 */
	public function getFileHref()
	{
		return '/' . $this->_href . $this->file;
	}

	/**
	 * Get small file path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return Core_File::isValidExtension($this->file, Core_File::getResizeExtensions())
			? $this->_dir . 'small_' . $this->file
			: NULL;
	}

	/**
	 * Get small file href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return Core_File::isValidExtension($this->file, Core_File::getResizeExtensions())
			? '/' . $this->_href . 'small_' . $this->file
			: NULL;
	}

	/**
	 * Create message files directory
	 * @return self
	 */
	public function createDir()
	{
		if (!Core_File::isDir($this->_dir))
		{
			try
			{
				Core_File::mkdir($this->_dir, CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete message files directory
	 * @return self
	 */
	public function deleteDir()
	{
		if (Core_File::isDir($this->_dir))
		{
			try
			{
				Core_File::deleteDir($this->_dir);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Save attachment file
	 * @param string $fileSourcePath source path
	 * @param string $fileName file name
	 * @return self
	 */
	public function saveFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		if ($this->file != '')
		{
			// Delete old file
			Core_File::isFile($this->getFilePath()) && $this->deleteFile();

			// Delete old small file
			Core_File::isFile($this->getSmallFilePath()) && $this->deleteSmallFile();
		}

		$this->save();

		$this->file_name = $fileName;
		$this->file = $this->id . '.' . Core_File::getExtension($fileName);
		$this->save();

		Core_File::upload($fileSourcePath, $this->getFilePath());

		$smallFilePath = $this->getSmallFilePath();

		// ext is accepted
		if (!is_null($smallFilePath))
		{
			// Resize image
			Core_Image::instance()->resizeImage($this->getFilePath(), 70, 70, $smallFilePath);
		}

		return $this;
	}

	/**
	 * Delete attachment file
	 * @return self
	 */
	public function deleteFile()
	{
		try
		{
			$path = $this->getFilePath();
			Core_File::isFile($path) && Core_File::delete($path);
		} catch (Exception $e) {}

		return $this;
	}

	/**
	 * Delete attachment small file
	 * @return self
	 */
	public function deleteSmallFile()
	{
		try
		{
			$path = $this->getSmallFilePath();
			Core_File::isFile($path) && Core_File::delete($path);
		} catch (Exception $e) {}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event crm_note_attachment.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}
		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->deleteFile();
		$this->deleteSmallFile();

		return parent::delete($primaryKey);
	}

	/**
	 * Get attachments size
	 * @return string
	 */
	public function getTextSize()
	{
		$size = Core_File::filesize($this->getFilePath());

		return !is_null($size)
			? Core_Str::getTextSize($size)
			: '';
	}
}