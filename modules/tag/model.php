<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag_Model
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$queryBuilder = Core_QueryBuilder::select(
				array('COUNT(*)', 'count'))
				->from('tags')
				->leftJoin('tag_shop_items', 'tags.id', '=', 'tag_shop_items.tag_id'/*,
					array(
						array('AND' => array('tag_shop_items.tag_id', '=', $this->id))
					)*/
				)
				->leftJoin('tag_informationsystem_items', 'tags.id', '=', 'tag_informationsystem_items.tag_id'/*,
					array(
						array('AND' => array('tag_informationsystem_items.tag_id', '=', $this->id))
					)*/)
				->open()
				->where('tag_informationsystem_items.tag_id', '=', $this->id)
				->setOr()
				->where('tag_shop_items.tag_id', '=', $this->id)
				->close()
				//->where('tags.id', '=', $this->id)
				->where('tags.deleted', '=', 0);

			$row = $queryBuilder->execute()->asAssoc()->current();
			$this->_all_count = $row['count'];

			$queryBuilder
				->open()
				->where('tag_informationsystem_items.site_id', '=', CURRENT_SITE)
				->setOr()
				->where('tag_shop_items.site_id', '=', CURRENT_SITE)
				->close();

			$row = $queryBuilder->execute()->asAssoc()->current();
			$this->_site_count = $row['count'];
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
		$oTagDublicate = Core_Entity::factory('Tag')->getByName($this->name);

		// Дубликат по имени найден
		if (!is_null($oTagDublicate) && $oTagDublicate->id != $this->id)
		{
			$this->id = $oTagDublicate->id;
		}
		// Дубликат по имени не найден
		else
		{
			// Проверяем наличие дубликата по пути
			$oTagDublicate = Core_Entity::factory('Tag')->getByPath($this->path);

			// Дубликат по пути найден
			if (!is_null($oTagDublicate) && $oTagDublicate->id != $this->id)
			{
				$this->id = $oTagDublicate->id;
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

		$oObject->delete();

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

		$this->clearXmlTags()
			->addXmlTag('urlencode', rawurlencode($this->path));

		if (!is_null($this->count))
		{
			$this->addXmlTag('count', $this->count);
		}

		return parent::getXml();
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