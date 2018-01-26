<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag_Dir_Model
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var string
	 */
	public $site_count = '';

	/**
	 * Backend property
	 * @var string
	 */
	public $all_count = '';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'tag' => array(),
		'tag_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array('sorting' => 0);

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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event tag_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Tags->deleteAll(FALSE);

		return parent::delete();
	}

	/**
	 * Get parent comment
	 * @return Tag_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Tag_Dir', $this->parent_id);
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Get dir by name
	 * @param string $name
	 * @return Tag_Dir_Model
	 */
	public function getByName($name)
	{
		$this->queryBuilder()
			->clear()
			->where('name', '=', $name)
			->limit(1);

		return $this->find();
	}

	/**
	 * Move dir to another
	 * @param int $tag_dir_id dir id
	 * @return self
	 */
	public function move($tag_dir_id)
	{
		$oDestinationDir = Core_Entity::factory('Tag_Dir', $tag_dir_id);

		do
		{
			if ($oDestinationDir->parent_id == $this->id
				|| $oDestinationDir->id == $this->id)
			{
				// Группа назначения является потомком текущей группы, перенос невозможен
				return $this;
			}
		} while ($oDestinationDir = $oDestinationDir->getParent());

		$this->parent_id = $tag_dir_id;
		$this->save();
		return $this;
	}
}