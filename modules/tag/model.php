<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag_Model
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var string
	 */
	public $_site_count = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $_all_count = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $count = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'informationsystem_item' => array('through' => 'tag_informationsystem_item'),
		'shop_item' => array('through' => 'tag_shop_item'),
		'tag_informationsystem_item' => array(),
		'tag_shop_item' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'tag_dir' => array(),
		'user' => array()
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description'
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

		//$this->_calculateCounts();
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if (in_array($property, array('site_count', 'all_count')))
		{
			$this->_calculateCounts();
			$name = '_' . $property;
			return $this->$name;
		}

		return parent::__get($property);
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		$property = strtolower($property);
		if (in_array($property, array('site_count', 'all_count')))
		{
			return TRUE;
		}

		return parent::__isset($property);
	}

	/**
	 * Calculate count
	 */
	protected function _calculateCounts()
	{
		if (!is_null($this->id) && is_null($this->_site_count))
		{
			// IS
			$queryBuilder = Core_QueryBuilder::select(
				array('COUNT(*)', 'count'))
				->from('tags')
				->leftJoin('tag_informationsystem_items', 'tags.id', '=', 'tag_informationsystem_items.tag_id')
				->where('tag_informationsystem_items.tag_id', '=', $this->id)
				->where('tags.deleted', '=', 0);

			$row = $queryBuilder->execute()->asAssoc()->current();
			$this->_all_count = $row['count'];

			$queryBuilder
				->where('tag_informationsystem_items.site_id', '=', CURRENT_SITE);

			$row = $queryBuilder->execute()->asAssoc()->current();
			$this->_site_count = $row['count'];

			// Shop
			$queryBuilder = Core_QueryBuilder::select(
				array('COUNT(*)', 'count'))
				->from('tags')
				->leftJoin('tag_shop_items', 'tags.id', '=', 'tag_shop_items.tag_id')
				->where('tag_shop_items.tag_id', '=', $this->id)
				->where('tags.deleted', '=', 0);

			$row = $queryBuilder->execute()->asAssoc()->current();
			$this->_all_count += $row['count'];

			$queryBuilder
				->where('tag_shop_items.site_id', '=', CURRENT_SITE);

			$row = $queryBuilder->execute()->asAssoc()->current();
			$this->_site_count += $row['count'];
		}
	}

	/**
	 * Set path
	 * @return self
	 */
	protected function _setPath()
	{
		if (!$this->_saved)
		{
			if ($this->path == '')
			{
				$this->path = $this->name;
			}
		}

		return $this;
	}

	/**
	 * Check if there another tag with this name is
	 * @return self
	 */
	protected function _checkDuplicate()
	{
		$oTagDuplicate = Core_Entity::factory('Tag')->getByName($this->name);

		// Дубликат по имени найден
		if (!is_null($oTagDuplicate) && $oTagDuplicate->id != $this->id)
		{
			$this->id = $oTagDuplicate->id;
		}
		// Дубликат по имени не найден
		else
		{
			// Проверяем наличие дубликата по пути
			$oTagDuplicate = Core_Entity::factory('Tag')->getByPath($this->path);

			// Дубликат по пути найден
			if (!is_null($oTagDuplicate) && $oTagDuplicate->id != $this->id)
			{
				$this->id = $oTagDuplicate->id;
			}
		}

		return $this;
	}

	/**
	 * Check if there another tag with this name is
	 * @return self
	 */
	public function update()
	{
		!$this->deleted && $this->_setPath()
			->_checkDuplicate();

		return parent::update();
	}

	/**
	 * Save object. Use self::update() or self::create()
	 * @return Tag_Model
	 */
	public function save()
	{
		!$this->deleted && $this->_setPath()
			->_checkDuplicate();

		return parent::save();
	}

	/**
	 * Merge tags
	 * @param Tag_Model $oObject
	 * @return self
	 */
	public function merge(Tag_Model $oObject)
	{
		$aTag_Informationsystem_Item = $oObject->Tag_Informationsystem_Items->findAll(FALSE);
		foreach ($aTag_Informationsystem_Item as $oTag_Informationsystem_Item)
		{
			$oTmp = $this->Tag_Informationsystem_Items->getByInformationsystem_item_id($oTag_Informationsystem_Item->informationsystem_item_id, FALSE);

			is_null($oTmp)
				? $this->add($oTag_Informationsystem_Item)
				: $oTag_Informationsystem_Item->delete();
		}

		$aTag_Shop_Item = $oObject->Tag_Shop_Items->findAll(FALSE);
		foreach ($aTag_Shop_Item as $oTag_Shop_Item)
		{
			$oTmp = $this->Tag_Shop_Items->getByShop_item_id($oTag_Shop_Item->shop_item_id, FALSE);

			is_null($oTmp)
				? $this->add($oTag_Shop_Item)
				: $oTag_Shop_Item->delete();
		}

		$oObject->markDeleted();

		return $this;
	}

	/**
	 * Move tag to another dir
	 * @param int $tag_dir_id dir id
	 * @return self
	 */
	public function move($tag_dir_id)
	{
		$this->tag_dir_id = $tag_dir_id;
		$this->save();
		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event tag.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Tag_Informationsystem_Items->deleteAll(FALSE);
		$this->Tag_Shop_Items->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event tag.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event tag.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('urlencode', rawurlencode($this->path));

		if (!is_null($this->count))
		{
			$this->addXmlTag('count', $this->count);
		}

		return $this;
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}
}