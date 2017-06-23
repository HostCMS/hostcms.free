<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Xsl_Dir_Model
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'xsl' => array(),
		'xsl_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'xsl_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
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
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'parent_id' => 0
	);

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Xsl_Dir_Model
	 * @hostcms-event xsl_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Xsls->deleteAll(FALSE);
		$this->Xsl_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aXsl_Dirs = $this->Xsl_Dirs->findAll();
		foreach($aXsl_Dirs as $oChildrenDir)
		{
			$newDir = $oChildrenDir->copy();
			$newObject->add($newDir);
		}

		$aXsls = $this->Xsls->findAll();
		foreach($aXsls as $oXsl)
		{
			$newObject->add(
				$oXsl->changeCopiedName(TRUE)->copy()
			);
		}

		return $newObject;
	}

	/**
	 * Get parent comment
	 * @return Xsl_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Xsl_Dir', $this->parent_id);
		}

		return NULL;
	}
}
