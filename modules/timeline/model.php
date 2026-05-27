<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_Model
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'timeline';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		// 'timeline' => array('foreign_key' => 'parent_id'),
		'timeline_user' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		// 'timeline' => array('foreign_key' => 'parent_id'),
		'crm_note' => array()
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get parent
	 * @return Timeline_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Timeline', $this->parent_id)
			: NULL;
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Timelines->getCount();

		$aTimelines = $this->Timelines->findAll(FALSE);
		foreach ($aTimelines as $oTimeline)
		{
			$count += $oTimeline->getChildCount();
		}

		return $count;
	}

	/**
	 * Get message files href
	 * @return string
	 */
	public function getHref()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		return $oSite->uploaddir . 'private/timelines/' . Core_File::getNestingDirPath($this->id, 3) . '/timeline_' . $this->id . '/';
	}

	/**
	 * Get path for files
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Create message files directory
	 * @return self
	 */
	public function createDir()
	{
		if (!Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
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
		if (Core_File::isDir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event timeline.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('crm') && $this->crm_note_id)
		{
			$this->Crm_Note->delete();
		}

		// $this->Timelines->deleteAll(FALSE);
		$this->Timeline_Users->deleteAll(FALSE);

		$this->deleteDir();

		return parent::delete($primaryKey);
	}
}