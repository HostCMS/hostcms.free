<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Attachment_Model
 *
 * @package HostCMS
 * @subpackage Events
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Attachment_Model extends Core_Entity
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
		'event' => array(),
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
	 * Get attachment file path
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->Event->getPath() . $this->file_path;
	}

	/**
	 * Get attachment file href
	 * @return string
	 */
	public function getFileHref()
	{
		return '/' . $this->Event->getHref() . rawurlencode($this->file_path);
	}

	/**
	 * Save attachment file
	 * @param string $fileSourcePath source path
	 * @param string $fileName file name
	 * @return self
	 */
	public function saveFile($fileSourcePath, $fileName)
	{
		//$this->_upload($fileName);

		$fileName = Core_File::filenameCorrection($fileName);
		$this->Event->createDir();

		// Delete old file
		if ($this->file_path != '' && Core_File::isFile($this->Event->getPath() . $this->file_path))
		{
			$this->deleteFile();
		}

		$this->save();

		$this->file_name = $fileName;
		$this->file_path = $this->id . '.' . Core_File::getExtension($fileName);
		$this->save();

		Core_File::upload($fileSourcePath, $this->Event->getPath() . $this->file_path);
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event event_attachment.onBeforeRedeclaredDelete
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

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event event_attachment.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event event_attachment.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$size = Core_File::filesize($this->getFilePath());

		$this
			->addXmlTag('size', Core_Str::getTextSize($size))
			/*->addXmlTag('size_measure', $textSize)*/;

		return $this;
	}

	/**
	 * Get attachments size
	 * @return string
	 */
	public function getTextSize()
	{
		$size = Core_File::filesize($this->getFilePath());

		return Core_Str::getTextSize($size);
	}
}