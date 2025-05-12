<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Attachment_Model
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Project_Attachment_Model extends Core_Entity
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
		'crm_project' => array(),
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get attachment file path
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->Crm_Project->getPath() . $this->file;
	}

	/**
	 * Get attachment file href
	 * @return string
	 */
	public function getFileHref()
	{
		return '/' . $this->Crm_Project->getHref() . rawurlencode($this->file);
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
		$this->Crm_Project->createDir();

		// Delete old file
		if ($this->file != '' && Core_File::isFile($this->Crm_Project->getPath() . $this->file))
		{
			$this->deleteFile();
		}

		$this->save();

		$this->file_name = $fileName;
		$this->file = $this->id . '.' . Core_File::getExtension($fileName);
		$this->save();

		Core_File::upload($fileSourcePath, $this->Crm_Project->getPath() . $this->file);
		return $this;
	}

	/**
	 * Delete attachment file
	 * @return self
	 * @hostcms-event cem_project_attachment.onAfterDeleteFile
	 */
	public function deleteFile()
	{
		try
		{
			$path = $this->getFilePath();
			Core_File::isFile($path) && Core_File::delete($path);
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteFile', $this);

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event cem_project_attachment.onBeforeRedeclaredDelete
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
	 * Get attachments size
	 * @return string
	 */
	public function getTextSize()
	{
		$size = Core_File::filesize($this->getFilePath());

		return Core_Str::getTextSize($size);
	}
}