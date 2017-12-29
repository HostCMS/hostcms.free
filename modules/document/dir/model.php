<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Dir_Model
 *
 * @package HostCMS
 * @subpackage Document
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $template_name = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'document_dir' => array('foreign_key' => 'parent_id'),
		'document' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'document_dir' => array('foreign_key' => 'parent_id'),
		'user' => array(),
		'site' => array()
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Get parent comment
	 * @return Document_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Document_Dir', $this->parent_id);
		}

		return NULL;
	}

	/**
	 * Get directory by site id
	 * @param int $site_id site id
	 * @return array
	 */
	public function getBySiteId($site_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('site_id', '=', $site_id)
			->orderBy('name');

		return $this->findAll();
	}

	/**
	 * Get directory by parent id and site id
	 * @param int $parent_id parent directory id
	 * @param int $site_id site id
	 * @return array
	 */
	public function getByParentIdAndSiteId($parent_id, $site_id)
	{
		$this->queryBuilder()
			->clear()
			->where('parent_id', '=', $parent_id)
			->where('site_id', '=', $site_id)
			->orderBy('name');

		return $this->findAll();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event document_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Documents->deleteAll(FALSE);
		$this->Document_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aChildrenDirs = $this->Document_Dirs->findAll();
		foreach($aChildrenDirs as $oChildrenDir)
		{
			$newDir = $oChildrenDir->copy();
			$newObject->add($newDir);
		}

		$aDocuments = $this->Documents->findAll();
		foreach($aDocuments as $oDocument)
		{
			$newDocument = $oDocument->copy();
			$newObject->add($newDocument);
		}

		return $newObject;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->getChildrenCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value($count)
			->execute();
	}
	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildrenCount()
	{
		$count = $this->Documents->getCount();

		$aDocument_Dirs = $this->Document_Dirs->findAll(FALSE);
		foreach ($aDocument_Dirs as $oDocument_Dir)
		{
			$count += $oDocument_Dir->getChildrenCount();
		}

		return $count;
	}
}