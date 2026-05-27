<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Xsl_Dir_Model
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
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
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event xsl_dir.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aXsl_Dirs = $this->Xsl_Dirs->findAll();
		foreach ($aXsl_Dirs as $oChildrenDir)
		{
			$newDir = $oChildrenDir->copy();
			$newDir->parent_id = $newObject->id;
			$newDir->save();
			// $newObject->add($newDir);
		}

		$aXsls = $this->Xsls->findAll();
		foreach ($aXsls as $oXsl)
		{
			$newObject->add(
				$oXsl->changeCopiedName(TRUE)->copy()
			);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move group to another group
	 * @param int $xsl_dir_id dir id
	 * @return self
	 * @hostcms-event xsl_dir.onBeforeMove
	 * @hostcms-event xsl_dir.onAfterMove
	 */
	public function move($xsl_dir_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($xsl_dir_id));

		$this->parent_id = $xsl_dir_id;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
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

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Xsls->getCount();

		$aXsl_Dirs = $this->Xsl_Dirs->findAll(FALSE);
		foreach ($aXsl_Dirs as $oXsl_Dir)
		{
			$count += $oXsl_Dir->getChildCount();
		}

		return $count;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('A')
					->href($link)
					->onclick($onclick)
					->value(htmlspecialchars($this->name))
			);

		$iCount = $this->getChildCount();

		$iCount > 0 && $oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($iCount)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
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
}
