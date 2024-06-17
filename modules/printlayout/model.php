<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Model
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Printlayout_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Backend property
	 * @var string
	 */
	public $modules = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'printlayout_dir' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'printlayout_module' => array(),
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'printlayouts.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'file_mask' => '{this.id}'
	);

	/**
	 * Has revisions
	 *
	 * @param boolean
	 */
	protected $_hasRevisions = TRUE;

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
	 * Get printlayout dir
	 * @return string
	 */
	public function getDir()
	{
		return CMS_FOLDER . "hostcmsfiles/printlayout/";
	}

	/**
	 * Get copy file path
	 * @return string
	 */
	public function getCopyFilePath()
	{
		return tempnam(CMS_FOLDER . TMP_DIR, 'PLO');
	}

	/**
	 * Get file path
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->getDir() . $this->file_name;
	}

	/**
	 * Create directory for printlayout
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		$path = $this->getDir();

		if (!Core_File::isDir($path))
		{
			try
			{
				Core_File::mkdir($path, CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete file
	 * @return self
	 */
	public function deleteFile()
	{
		$fileName = $this->getFilePath();

		if (Core_File::isFile($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'name' => $this->name,
				'printlayout_dir_id' => $this->printlayout_dir_id,
				'active' => $this->active,
				'file_mask' => $this->file_mask,
				'mail_template' => $this->mail_template,
				'sorting' => $this->sorting,
				'user_id' => $this->user_id
			);

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->name = Core_Array::get($aBackup, 'name');
				$this->printlayout_dir_id = Core_Array::get($aBackup, 'printlayout_dir_id');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->file_mask = Core_Array::get($aBackup, 'file_mask');
				$this->mail_template = Core_Array::get($aBackup, 'mail_template');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event printlayout.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// Удаляем информационные группы
		$this->Printlayout_Modules->deleteAll(FALSE);

		// Удаляем файл
		$this->deleteFile();

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Get available printlayout
	 * @param int $module_id module id
	 * @param int $type type
	 * @return array
	 */
	public function getAvailable($module_id, $type)
	{
		$aReturn = array();

		$oPrintlayout_Modules = Core_Entity::factory('Printlayout_Module');
		$oPrintlayout_Modules->queryBuilder()
			->join('printlayouts', 'printlayout_modules.printlayout_id', '=', 'printlayouts.id')
			->where('printlayout_modules.module_id', '=', $module_id)
			->where('printlayout_modules.type', '=', $type)
			->clearOrderBy()
			->orderBy('printlayouts.sorting', 'ASC');

		$aPrintlayout_Modules = $oPrintlayout_Modules->findAll(FALSE);
		foreach ($aPrintlayout_Modules as $oPrintlayout_Module)
		{
			$aReturn[] = $oPrintlayout_Module->Printlayout;
		}

		return $aReturn;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function imgBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$ext = Core_File::getExtension($this->file_name);

		switch ($ext)
		{
			case 'docx':
				$img = '<i class="fa-regular fa-file-word"></i>';
			break;
			case 'xlsx':
				$img = '<i class="fa-regular fa-file-excel"></i>';
			break;
			default:
				$img = '<i class="fa-regular fa-file"></i>';
		}

		return $img;
	}
}