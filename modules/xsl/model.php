<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Xsl_Model
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'xsl_dir' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'format' => 1, 'sorting' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'xsls.sorting' => 'ASC',
		'xsls.name' => 'ASC'
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get XSL file path
	 * @return string
	 */
	public function getXslFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/xsl/" . intval($this->id) . ".xsl";
	}

	/**
	 * Get DTD language path
	 * @param string $lng
	 * @return string
	 */
	public function getLngDtdPath($lng)
	{
		return CMS_FOLDER . "hostcmsfiles/xsl/" . intval($this->id) . "." . $lng . ".dtd";
	}

	/**
	 * Get DTD language file content
	 * @param string $lng
	 * @return string|NULL
	 */
	public function loadLngDtdFile($lng)
	{
		$path = $this->getLngDtdPath($lng);

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Specify XSL file content
	 * @param string $lng
	 * @param string $content content
	 */
	public function saveLngDtdFile($lng, $content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getLngDtdPath($lng), $content);
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event xsl.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));
		
		// Удаляем файл
		$filename = $this->getXslFilePath();

		try
		{
			Core_File::delete($filename);
		} catch (Exception $e) {}

		return parent::delete($primaryKey);
	}

	/**
	 * Specify XSL file content
	 * @param string $content content
	 */
	public function saveXslFile($content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getXslFilePath(), $content);
	}

	/**
	 * Get XSL file content
	 * @return string|NULL
	 */
	public function loadXslFile()
	{
		$path = $this->getXslFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getXslFilePath(), $newObject->getXslFilePath());
		}
		catch (Exception $e) {}

		return $newObject;
	}

	/**
	 * Get XSL by name
	 * @param string $name name
	 * @return Xsl_Model|NULL
	 */
	public function getByName($name)
	{
		$this->queryBuilder()
			->clear()
			->where('name', '=', $name)
			->clearOrderBy()
			->limit(1);

		$aXsls = $this->findAll();

		return isset($aXsls[0])
			? $aXsls[0]
			: NULL;
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
				'xsl_dir_id' => $this->xsl_dir_id,
				'description' => $this->description,
				'xsl' => $this->loadXslFile(),
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
				$this->description = Core_Array::get($aBackup, 'description');
				$this->save();

				$this->saveXslFile(Core_Array::get($aBackup, 'xsl'));
			}
		}

		return $this;
	}
}