<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Version_Model
 *
 * @package HostCMS
 * @subpackage Document
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Version_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $user_name = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'datetime';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'document' => array(),
		'user' => array(),
		'template' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'template_id' => 0,
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
	 * Get document's file path
	 * @return string
	 */
	public function getPath()
	{
		$id = intval($this->id);
		return CMS_FOLDER . "hostcmsfiles/documents/documents{$id}.html";
	}

	/**
	 * Save object.
	 *
	 * @return Core_ORM
	 * @hostcms-event modelname.onBeforeSave
	 * @hostcms-event modelname.onAfterSave
	 */
	public function save()
	{
		// disable save
		return $this;
	}

	/**
	 * Execute
	 * @return self
	 */
	public function execute()
	{
		$this->Document->execute();

		return $this;
	}

	/**
	 * Save document file
	 * @param string $content content
	 * @return boolean
	 */
	public function saveFile($content)
	{
		//$this->save();

		$content = trim($content);
		//Core_File::write($this->getPath(), $content);

		$oDocument = Core_Entity::factory('Document', $this->document_id);
		$oDocument->text = $content;
		$oDocument->template_id = $this->template_id;
		$oDocument->datetime = $this->datetime;
		$oDocument->save();
	}

	/**
	 * Load document file
	 * @return string|NULL
	 */
	public function loadFile()
	{
		$path = $this->getPath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get current version of the document
	 * @param boolean $bCache cache status
	 * @return Document_Version|NULL
	 */
	public function getCurrent($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('current', '=', '1')
			->limit(1);

		$aDocument_Versions = $this->findAll($bCache);
		return isset($aDocument_Versions[0]) ? $aDocument_Versions[0] : NULL;
	}

	/**
	 * Set current version of the document
	 * @return self
	 */
	public function setCurrent()
	{
		$this->save();

		$oDocument_Versions = $this->Document->Document_Versions;
		$oDocument_Versions
			->queryBuilder()
			->where('current', '=', 1);

		$aDocument_Versions = $oDocument_Versions->findAll();

		foreach ($aDocument_Versions as $oDocument_Version)
		{
			$oDocument_Version->current = 0;
			$oDocument_Version->update();
		}

		$this->current = 1;
		$this->save();
	}

	/**
	 * Clone entity
	 * @return void
	 */
	public function __clone()
	{
		parent::__clone();
		$this->datetime = Core_Date::timestamp2sql(time());
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event document_version.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		try
		{
			Core_File::copy($this->getPath(), $newObject->getPath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		try
		{
			Core_File::delete($this->getPath());
		} catch (Exception $e) {}

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event document_version.onBeforeGetRelatedSite
	 * @hostcms-event document_version.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Document->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}