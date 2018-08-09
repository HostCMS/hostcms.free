<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property';

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
		'property_dir' => array(),
		'list' => array(),
		'informationsystem' => array(),
		'shop' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'property_value_int' => array(),
		'property_value_float' => array(),
		'property_value_string' => array(),
		'property_value_text' => array(),
		'property_value_datetime' => array(),
		'property_value_file' => array()
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'structure_property' => array(),
		'siteuser_property' => array(),
		'informationsystem_item_property' => array(),
		'informationsystem_group_property' => array(),
		'shop_item_property' => array(),
		'shop_group_property' => array(),
		'shop_order_property' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'list_id',
		'informationsystem_id',
		'shop_id',
		'guid',
		'image_large_max_width',
		'image_large_max_height',
		'image_small_max_width',
		'image_small_max_height',
		'hide_small_image',
		'preserve_aspect_ratio',
		'preserve_aspect_ratio_small'
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'properties.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'list_id' => 0,
		'informationsystem_id' => 0,
		'shop_id' => 0,
		'type' => 0,
		'description' => '',
		'tag_name' => '',
		'image_large_max_width' => 300,
		'image_large_max_height' => 300,
		'image_small_max_width' => 70,
		'image_small_max_height' => 70,
		'default_value' => '',
		'hide_small_image' => 0,
		'sorting' => 0,
		'multiple' => 1,
		'preserve_aspect_ratio' => 1,
		'preserve_aspect_ratio_small' => 1
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
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Get all values for entity
	 * @param int $entityId entity id
	 * @param boolean $bCache cache mode
	 * @return array|NULL
	 */
	public function getValues($entityId, $bCache = TRUE)
	{
		if (is_null($this->type))
		{
			return array();
		}

		// Кэшировать и данные со значениями всех св-в были загружены
		if ($bCache && !is_null($this->_aAllValues))
		{
			return isset($this->_aAllValues[$entityId])
				? $this->_aAllValues[$entityId]
				: array();
		}

		return Property_Controller_Value::factory($this->type)
			->setProperty($this)
			->getValues($entityId, $bCache);
	}

	/**
	 * List of values for property
	 * @var array
	 */
	protected $_aAllValues = NULL;

	/**
	 * Load all values for property
	 * @return self
	 */
	public function loadAllValues()
	{
		if (is_null($this->_aAllValues))
		{
			$this->_aAllValues = array();

			$aPropertyValues = Property_Controller_Value::factory($this->type)
				->setProperty($this)
				->getPropertyValueObject()->findAll();

			foreach ($aPropertyValues as $oPropertyValue)
			{
				$this->_aAllValues[$oPropertyValue->entity_id][] = $oPropertyValue;
			}
		}

		return $this;
	}

	/**
	 * Получить значение свойства объекта по идентификатору $valueId в таблице значений
	 * @param $valueId идентифитор в таблице значений
	 * @return mixed object or NULL
	 */
	public function getValueById($valueId)
	{
		return Property_Controller_Value::factory($this->type)
			->setProperty($this)
			->getValueById($valueId);
	}

	/**
	 * Получить значение свойства объекта по значению в таблице значений
	 * @param string $value значение
	 * @param string $condition condition
	 * @param boolean $bCache use cache
	 * @return mixed array of objects or NULL
	 */
	public function getValuesByValue($value, $condition = '=', $bCache = TRUE)
	{
		return Property_Controller_Value::factory($this->type)
			->setProperty($this)
			->getValuesByValue($value, $condition, $bCache);
	}

	/**
	 * Create new value for entity
	 * @param int $entityId entity id
	 * @return Property_Value_Model
	 */
	public function createNewValue($entityId)
	{
		return Property_Controller_Value::factory($this->type)
			->setProperty($this)
			->createNewValue($entityId);
	}

	/**
	 * Get property by guid
	 * @param string $guid guid
	 * @return Property_Model|NULL
	 */
	public function getByGuid($guid)
	{
		$this->queryBuilder()
			->where('guid', '=', $guid)
			->limit(1);

		$aObjects = $this->findAll(FALSE);

		return isset($aObjects[0]) ? $aObjects[0] : NULL;
	}

	/**
	 * Get property by tag name
	 * @param string $tag_name tag name
	 * @return Property_Model|NULL
	 */
	public function getByTagName($tag_name)
	{
		$this->queryBuilder()
			->where('tag_name', '=', $tag_name)
			->limit(1);

		$aObjects = $this->findAll();

		return isset($aObjects[0])
			? $aObjects[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event property.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// Relations
		$this->Structure_Property->delete();
		$this->Informationsystem_Item_Property->delete();
		$this->Informationsystem_Group_Property->delete();
		$this->Shop_Item_Property->delete();
		$this->Shop_Group_Property->delete();
		$this->Shop_Order_Property->delete();
		Core::moduleIsActive('siteuser') && $this->Siteuser_Property->delete();

		$this->Property_Value_Ints->deleteAll(FALSE);
		$this->Property_Value_Floats->deleteAll(FALSE);
		$this->Property_Value_Strings->deleteAll(FALSE);
		$this->Property_Value_Texts->deleteAll(FALSE);
		$this->Property_Value_Datetimes->deleteAll(FALSE);
		$this->Property_Value_Files->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Copy property
	 * @param boolean $bCopyRelation Copy related entities
	 * @return Property_Model
	 */
	public function copy($bCopyRelation = TRUE)
	{
		$newObject = clone $this;
		//$newObject->name .= ' [Копия от ' . date('d.m.Y H:i:s') . ']';
		$newObject->save();

		if ($bCopyRelation)
		{
			if (Core::moduleIsActive('structure') && !is_null($this->Structure_Property->id))
			{
				$newObject->add(clone $this->Structure_Property);
			}
			elseif (Core::moduleIsActive('siteuser') && !is_null($this->Siteuser_Property->id))
			{
				$newObject->add(clone $this->Siteuser_Property);
			}
			elseif (Core::moduleIsActive('informationsystem') && !is_null($this->Informationsystem_Item_Property->id))
			{
				$newObject->add(clone $this->Informationsystem_Item_Property);
			}
			elseif (Core::moduleIsActive('informationsystem') && !is_null($this->Informationsystem_Group_Property->id))
			{
				$newObject->add(clone $this->Informationsystem_Group_Property);
			}
			elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Item_Property->id))
			{
				$newObject->add(clone $this->Shop_Item_Property);
			}
			elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Group_Property->id))
			{
				$newObject->add(clone $this->Shop_Group_Property);
			}
			elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Order_Property->id))
			{
				$newObject->add(clone $this->Shop_Order_Property);
			}
		}

		return $newObject;
	}

	/**
	 * Config
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Set config
	 * @param array $config
	 * @return self
	 */
	public function setConfig(array $config)
	{
		$this->_config = $config;
		return $this;
	}

	/**
	 * Get config
	 * @return array
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Limit List Items in XML
	 * @var array|Core_QueryBuilder_Select
	 */
	protected $_limitListItems = NULL;

	/**
	 * Limit List Items in XML
	 * @param array|Core_QueryBuilder_Select $limitListItems
	 * @return self
	 */
	public function limitListItems($limitListItems)
	{
		$this->_limitListItems = $limitListItems;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event property.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		$bIsList = $this->type == 3 && $this->list_id != 0 && Core::moduleIsActive('list');

		$this->setConfig(
			Core_Config::instance()->get('property_config', array()) + array('add_list_items' => TRUE)
		);

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->clearXmlTags();

		/*if ($this->type != 2)
		{
			$this->addForbiddenTag('image_large_max_width')
				->addForbiddenTag('image_large_max_height')
				->addForbiddenTag('image_small_max_width')
				->addForbiddenTag('image_small_max_height')
				->addForbiddenTag('hide_small_image');
		}*/

		// List
		if ($bIsList)
		{
			$this->addEntity(
				$this->List->clearEntities()
			);

			if ($this->_config['add_list_items'])
			{
				$oList_Items = $this->List->List_Items;
				$oList_Items->queryBuilder()
					->where('list_items.active', '=', 1);

				if (!is_null($this->_limitListItems))
				{
					$oList_Items->queryBuilder()
						->where('list_items.id', 'IN', $this->_limitListItems);
				}

				Core_Event::notify($this->_modelName . '.onBeforeGetXmlAddListItems', $this, array($oList_Items));

				$this->List->addEntities(
					$oList_Items->findAll()
				);
			}
		}

		return parent::getXml();
	}

	public function typeBackend()
	{
		return Core::_('Property.type' . $this->type);
	}

	/**
	 * Change multiple status
	 * @return self
	 * @hostcms-event property.onBeforeChangeMultiple
	 * @hostcms-event property.onAfterChangeMultiple
	 */
	public function changeMultiple()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeMultiple', $this);

		$this->multiple = 1 - $this->multiple;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeMultiple', $this);

		return $this;
	}
}