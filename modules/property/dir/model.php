<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Dir_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Property_Dir_Model extends Core_Entity
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
	public $enable = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $tag_name = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'property_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'property' => array(),
		'property_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'structure_property_dir' => array(),
		'siteuser_property_dir' => array(),
		'informationsystem_item_property_dir' => array(),
		'informationsystem_group_property_dir' => array(),
		'shop_item_property_dir' => array(),
		'shop_group_property_dir' => array(),
		'deal_template_property_dir' => array(),
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
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'property_dirs.sorting' => 'ASC'
	);

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event property_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Properties->deleteAll(FALSE);
		$this->Property_Dirs->deleteAll(FALSE);

		// Relations
		$this->Structure_Property_Dir->delete();

		if (Core::moduleIsActive('informationsystem'))
		{
			$this->Informationsystem_Item_Property_Dir->delete();
			$this->Informationsystem_Group_Property_Dir->delete();
		}

		if (Core::moduleIsActive('shop'))
		{
			$this->Shop_Item_Property_Dir->delete();
			$this->Shop_Group_Property_Dir->delete();
		}

		Core::moduleIsActive('siteuser') && $this->Siteuser_Property_Dir->delete();
		Core::moduleIsActive('deal') && $this->Deal_Template_Property_Dir->delete();

		return parent::delete($primaryKey);
	}

	/**
	 * Get directories by parent id
	 * @param int $parent_id parent id
	 * @return array
	 */
	public function getByParentId($parent_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('parent_id', '=', $parent_id);

		return $this->findAll();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event property_dir.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aChildrenDirs = $this->Property_Dirs->findAll();
		foreach ($aChildrenDirs as $oChildrenDir)
		{
			$newObject->add($oChildrenDir->copy());
		}

		$aProperties = $this->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			$newObject->add($oProperty->copy(FALSE));
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move group to another
	 * @param int $parent_id group id
	 * @return self
	 * @hostcms-event property_dir.onBeforeMove
	 * @hostcms-event property_dir.onAfterMove
	 */
	public function move($parent_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($parent_id));

		$this->parent_id = $parent_id;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Get parent comment
	 * @return Property_Dir_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Property_Dir', $this->parent_id);
		}

		return NULL;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->getChildrenCount();
		$count && Core_Html_Entity::factory('Span')
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
		$count = $this->Properties->getCount();

		$aProperty_Dirs = $this->Property_Dirs->findAll(FALSE);
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$count += $oProperty_Dir->getChildrenCount();
		}

		return $count;
	}
}
