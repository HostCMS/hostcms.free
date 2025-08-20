<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
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
		'property_value_bigint' => array(),
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
		'informationsystem_comment_property' => array(),
		'shop_item_property' => array(),
		'shop_group_property' => array(),
		'shop_order_property' => array(),
		'shop_comment_property' => array(),
		'shop_order_comment_property' => array(),
		'deal_template_property' => array()
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
		'indexing' => 1,
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

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Get all values for entity
	 * @param int $entityId entity id
	 * @param boolean $bCache cache mode
	 * @return array|NULL
	 */
	public function getValues($entityId, $bCache = TRUE, $bSorting = FALSE)
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
			->getValues($entityId, $bCache, $bSorting);
	}

	/**
	 * List of values for property
	 * @var array
	 */
	protected $_aAllValues = NULL;

	/**
	 * Isset all values for property
	 * @return boolean
	 */
	public function issetAllValues()
	{
		return !is_null($this->_aAllValues);
	}

	/**
	 * Load all values for property
	 * @return self
	 */
	public function loadAllValues()
	{
		if (!$this->issetAllValues())
		{
			$this->_aAllValues = array();

			if (is_numeric($this->type))
			{
				$aProperty_Values = Property_Controller_Value::factory($this->type)
					->setProperty($this)
					->getPropertyValueObject()->findAll(FALSE);

				foreach ($aProperty_Values as $oProperty_Value)
				{
					$this->_aAllValues[$oProperty_Value->entity_id][] = $oProperty_Value;
				}
			}
		}

		return $this;
	}

	/**
	 * Get all values for property
	 * @return array
	 */
	public function getAllValues()
	{
		$this->loadAllValues();

		return $this->_aAllValues;
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

		return isset($aObjects[0])
			? $aObjects[0]
			: NULL;
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

		// Values
		$this->Property_Value_Ints->deleteAll(FALSE);
		$this->Property_Value_Floats->deleteAll(FALSE);
		$this->Property_Value_Strings->deleteAll(FALSE);
		$this->Property_Value_Texts->deleteAll(FALSE);
		$this->Property_Value_Datetimes->deleteAll(FALSE);

		$nodeName = $methodName = NULL;

		if (Core::moduleIsActive('structure') && !is_null($this->Structure_Property->id))
		{
			$nodeName = 'Structure';
			$methodName = 'getDirPath';
		}
		elseif (Core::moduleIsActive('siteuser') && !is_null($this->Siteuser_Property->id))
		{
			$nodeName = 'Siteuser';
			$methodName = 'getDirPath';
		}
		elseif (Core::moduleIsActive('informationsystem') && !is_null($this->Informationsystem_Item_Property->id))
		{
			$nodeName = 'Informationsystem_Item';
			$methodName = 'getItemPath';
		}
		elseif (Core::moduleIsActive('informationsystem') && !is_null($this->Informationsystem_Group_Property->id))
		{
			$nodeName = 'Informationsystem_Group';
			$methodName = 'getGroupPath';
		}
		elseif (Core::moduleIsActive('informationsystem') && !is_null($this->Informationsystem_Comment_Property->id))
		{
			$nodeName = 'Comment';
			$methodName = 'getPath';
		}
		elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Item_Property->id))
		{
			$nodeName = 'Shop_Item';
			$methodName = 'getItemPath';

			// Fast filter
			if ($this->Shop_Item_Property->Shop->filter && $this->Shop_Item_Property->filter)
			{
				$oShop_Filter_Controller = new Shop_Filter_Controller($this->Shop_Item_Property->Shop);

				$oShop_Filter_Controller->checkPropertyExist($this->id)
					&& $oShop_Filter_Controller->removeProperty($this);
			}
		}
		elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Group_Property->id))
		{
			$nodeName = 'Shop_Group';
			$methodName = 'getGroupPath';
		}
		elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Order_Property->id))
		{
			$nodeName = 'Shop_Order';
			$methodName = 'getOrderPath';
		}
		elseif (Core::moduleIsActive('shop') && !is_null($this->Shop_Comment_Property->id))
		{
			$nodeName = 'Comment';
			$methodName = 'getPath';
		}
		elseif (Core::moduleIsActive('deal') && !is_null($this->Deal_Template_Property->id))
		{
			$nodeName = 'Deal';
			$methodName = 'getPath';
		}

		if (!is_null($nodeName))
		{
			do {
				$oProperty_Value_Files = $this->Property_Value_Files;
				$oProperty_Value_Files
					->queryBuilder()
					->limit(500);

				$aProperty_Value_Files = $oProperty_Value_Files->findAll(FALSE);

				foreach ($aProperty_Value_Files as $oProperty_Value_File)
				{
					$oProperty_Value_File
						->setDir(
							Core_Entity::factory($nodeName, $oProperty_Value_File->entity_id)->$methodName()
						)
						->delete();
				}
			} while (count($aProperty_Value_Files));
		}
		// Delte just from database
		else
		{
			$this->Property_Value_Files->deleteAll(FALSE);
		}

		// Relations
		$this->Structure_Property->delete();

		if (Core::moduleIsActive('informationsystem'))
		{
			$this->Informationsystem_Item_Property->delete();
			$this->Informationsystem_Group_Property->delete();
			$this->Informationsystem_Comment_Property->delete();
		}

		if (Core::moduleIsActive('shop'))
		{
			$this->Shop_Item_Property->delete();
			$this->Shop_Group_Property->delete();
			$this->Shop_Order_Property->delete();
			$this->Shop_Comment_Property->delete();
		}

		Core::moduleIsActive('siteuser') && $this->Siteuser_Property->delete();
		Core::moduleIsActive('deal') && $this->Deal_Template_Property->delete();

		return parent::delete($primaryKey);
	}

	/**
	 * Copy property
	 * @param boolean $bCopyRelation Copy related entities
	 * @return Property_Model
	 * @hostcms-event property.onAfterRedeclaredCopy
	 */
	public function copy($bCopyRelation = TRUE)
	{
		$newObject = clone $this;
		//$newObject->name .= ' [Копия от ' . date('d.m.Y H:i:s') . ']';
		$newObject->guid = Core_Guid::get();
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
			elseif (Core::moduleIsActive('deal') && !is_null($this->Deal_Template_Property->id))
			{
				$newObject->add(clone $this->Deal_Template_Property);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move item to another group
	 * @param int $iPropertyDirId target group id
	 * @return Core_Entity
	 * @hostcms-event property.onBeforeMove
	 * @hostcms-event property.onAfterMove
	 */
	public function move($iPropertyDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iPropertyDirId));

		$this->property_dir_id = $iPropertyDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Merge property with another one
	 * @param Property_Model $oProperty
	 * @return self
	 */
	public function merge(Property_Model $oProperty)
	{
		if ($this->type == $oProperty->type)
		{
			if (Core::moduleIsActive('list')
				&& $this->type == 3 && $oProperty->type == 3
				&& $this->list_id != $oProperty->list_id
			)
			{
				throw new Core_Exception(Core::_('Property.merge_error_lists'), array(), 0, FALSE);
			}

			$oProperty_Controller_Value_Type = Property_Controller_Value::factory($this->type);
			$tableName = $oProperty_Controller_Value_Type->getTableName();

			Core_QueryBuilder::update($tableName)
				->columns(array('property_id' => $this->id))
				->where('property_id', '=', $oProperty->id)
				->execute();

			$oProperty->markDeleted();
		}
		else
		{
			throw new Core_Exception(Core::_('Property.merge_error_type'), array(), 0, FALSE);
		}

		return $this;
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
	 * _aListItemsTree
	 * @var array
	 */
	protected $_aListItemsTree = array();

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event property.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		$this->setConfig(
			Core_Config::instance()->get('property_config', array()) + array('add_list_items' => TRUE)
		);

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event property.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		$this->setConfig(
			Core_Config::instance()->get('property_config', array()) + array('add_list_items' => TRUE)
		);

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Get List Items by $this->_limitListItems conditions
	 * @return array
	 */
	public function getListItems()
	{
		$oList_Items = $this->List->List_Items;
		$oList_Items->queryBuilder()
			->where('list_items.active', '=', 1);

		// MySQL 5.7 very slow with this subquery, changed to FROM (subquery) as prop_tmp
		/*if (!is_null($this->_limitListItems))
		{
			$oList_Items->queryBuilder()
				->where('list_items.id', 'IN', $this->_limitListItems);
		}*/

		if (is_array($this->_limitListItems))
		{
			if (count($this->_limitListItems))
			{
				$oList_Items->queryBuilder()
					->where('list_items.id', 'IN', $this->_limitListItems);
			}
			else
			{
				// Передан пустой массив, нет элементов для добавления
				return array();
			}
		}
		elseif (is_object($this->_limitListItems))
		{
			$oList_Items->queryBuilder()
				->select('list_items.*')
				->from('list_items')
				->from(array($this->_limitListItems, 'prop_tmp'))
				->where('list_items.id', '=', Core_QueryBuilder::expression('`prop_tmp`.`value`'));
		}

		Core_Event::notify($this->_modelName . '.onBeforeGetXmlAddListItems', $this, array($oList_Items));

		return $oList_Items->findAll(FALSE);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags();

		/*if ($this->type != 2)
		{
			$this->addForbiddenTag('image_large_max_width')
				->addForbiddenTag('image_large_max_height')
				->addForbiddenTag('image_small_max_width')
				->addForbiddenTag('image_small_max_height')
				->addForbiddenTag('hide_small_image');
		}*/

		$bIsList = $this->type == 3 && $this->list_id && Core::moduleIsActive('list');

		// List
		if ($bIsList)
		{
			$this->addEntity(
				$this->List->clearEntities()
			);

			if ($this->_isTagAvailable('list_items') &&
				(is_bool($this->_config['add_list_items']) && $this->_config['add_list_items']
					|| is_array($this->_config['add_list_items']) && in_array($this->id, $this->_config['add_list_items'])
				)
			)
			{
				$aList_Items = $this->getListItems();

				if (count($aList_Items))
				{
					foreach ($aList_Items as $oList_Item)
					{
						$this->_aListItemsTree[$oList_Item->parent_id][] = $oList_Item->clearEntities();

						// Добавить родителей иерархических значений, когда родители не были упомянуты в фильтрации
						$this->_addParentListItem($oList_Item);
					}

					$this->_addListItems(0, $this->List);
				}

				$this->_aListItemsTree = array();
			}
		}

		return $this;
	}

	/**
	 * Add a hierarchy of items missing from filtering
	 * @param List_Item_Model $oList_Item
	 * @return self
	 */
	protected function _addParentListItem(List_Item_Model $oList_Item)
	{
		if ($oList_Item->parent_id != 0 && is_array($this->_limitListItems) && !in_array($oList_Item->parent_id, $this->_limitListItems))
		{
			$oParent = $oList_Item->getParent();

			if ($oParent)
			{
				$this->_aListItemsTree[$oParent->parent_id][] = $oParent->clearEntities()->addAttribute('available', 'false');

				$this->_addParentListItem($oParent);
			}
		}

		return $this;
	}

	/**
	 * Add list items
	 * @param int $parentId
	 * @param object $oObject
	 * @return self
	 */
	protected function _addListItems($parentId, $oObject)
	{
		if (isset($this->_aListItemsTree[$parentId]))
		{
			foreach ($this->_aListItemsTree[$parentId] as $oList_Item)
			{
				$oObject->addEntity($oList_Item/*->clearEntities()*/); // ->clearEntities() moved above cause clears available="false"

				$this->_addListItems($oList_Item->id, $oList_Item);
			}
		}

		return $this;
	}

	/**
	 * Type backend
	 * @return string
	 */
	public function typeBackend()
	{
		$color = Core_Str::createColor($this->type);

		$return = '<span class="badge badge-round badge-max-width" style="border-color: ' . $color . '; color: ' . Core_Str::hex2darker($color, 0.2) . '; background-color: ' . Core_Str::hex2lighter($color, 0.88) . '">'
		. Core::_('Property.type' . $this->type)
		. '</span>';

		if ($this->type == 3 && $this->list_id && Core::moduleIsActive('list'))
		{
			$return .= '<a href="' . Admin_Form_Controller::correctBackendPath('/{admin}/list/item/index.php') . '?list_id=' . $this->list_id . '" target="_blank"><i title="' . Core::_('Property.move_to_list') . '" class="fa fa-external-link margin-left-5"></i></a>';
		}

		return $return;
	}

	/**
	 * Descroption backend
	 * @return string
	 */
	public function descriptionBackend()
	{
		return Core_Str::cut(strip_tags($this->description), 100);
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

	/**
	 * Change indexing status
	 * @return self
	 * @hostcms-event property.onBeforeChangeIndexing
	 * @hostcms-event property.onAfterChangeIndexing
	 */
	public function changeIndexing()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeIndexing', $this);

		$this->indexing = 1 - $this->indexing;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeIndexing', $this);

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (!is_null($this->dataTmp) && $this->Shop_Item_Property->filter)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-hostcms badge-square gray pull-right')
				->value('<i class="fa fa-filter fa-fw"></i>')
				->execute();
		}

		if ($this->obligatory)
		{
			Core_Html_Entity::factory('Span')
				->value('<i class="fa fa-asterisk darkorange fa-small"></i>')
				->execute();
		}

		if ($this->type == 3 && $this->list_id == 0)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-darkorange badge-ico white')
				->add(Core_Html_Entity::factory('I')->class('fa fa-chain-broken'))
				->execute();
		}

		/*if (is_numeric($this->type))
		{
			$countValues = Property_Controller_Value::factory($this->type)
				->setProperty($this)
				->getPropertyValueObject()->getCount(FALSE);

			$countValues && Core_Html_Entity::factory('Span')
				->class('badge badge-hostcms badge-square')
				->value(number_format($countValues, 0, ',', ' '))
				// ->title(Core::_('Property.count_values', number_format($countValues, 0, ',', ' ')))
				->execute();
		}*/

		if ($this->type == 2)
		{
			if ($this->prefix_large_file != '')
			{
				$color = '#53a93f';

				Core_Html_Entity::factory('Span')
					->class('badge badge-round badge-max-width')
					->style("border-color: " . $color . "; color: " . Core_Str::hex2darker($color, 0.2) . "; background-color: " . Core_Str::hex2lighter($color, 0.88))
					->title(Core::_('Property.prefix_large_file'))
					->value($this->prefix_large_file)
					->execute();
			}

			if ($this->prefix_small_file != '')
			{
				$smallColor = '#57b5e3';

				Core_Html_Entity::factory('Span')
					->class('badge badge-round badge-max-width')
					->style("border-color: " . $smallColor . "; color: " . Core_Str::hex2darker($smallColor, 0.2) . "; background-color: " . Core_Str::hex2lighter($smallColor, 0.88))
					->title(Core::_('Property.prefix_small_file'))
					->value($this->prefix_small_file)
					->execute();
			}
		}
	}

	/**
	 * Convert Object to Array
	 * @return array
	 */
	public function toArray()
	{
		$return = parent::toArray();

		// List
		if ($this->type != 3 && isset($return['list_id']))
		{
			unset($return['list_id']);
		}

		// Information_System
		if ($this->type != 5 && $this->type != 13)
		{
			unset($return['informationsystem_id']);
		}

		// Shop
		if ($this->type != 12 && $this->type != 14)
		{
			unset($return['shop_id']);
		}

		// File
		if ($this->type != 2)
		{
			unset($return['image_large_max_width']);
			unset($return['image_large_max_height']);
			unset($return['image_small_max_width']);
			unset($return['image_small_max_height']);
			unset($return['hide_small_image']);
			unset($return['preserve_aspect_ratio']);
			unset($return['preserve_aspect_ratio_small']);
			unset($return['watermark_default_use_large_image']);
			unset($return['watermark_default_use_small_image']);
		}

		// Wysiwyg
		if ($this->type != 6)
		{
			unset($return['typograph']);
			unset($return['trailing_punctuation']);
		}

		return $return;
	}
}