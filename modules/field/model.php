<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'field_dir' => array(),
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
		'field_value_int' => array(),
		'field_value_float' => array(),
		'field_value_string' => array(),
		'field_value_text' => array(),
		'field_value_datetime' => array(),
		'field_value_file' => array()
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

		return Field_Controller_Value::factory($this->type)
			->setField($this)
			->getValues($entityId, $bCache);
	}

	/**
	 * List of values for field
	 * @var array
	 */
	protected $_aAllValues = NULL;

	/**
	 * Load all values for field
	 * @return self
	 */
	public function loadAllValues()
	{
		if (is_null($this->_aAllValues))
		{
			$this->_aAllValues = array();

			$aField_Values = Field_Controller_Value::factory($this->type)
				->setField($this)
				->getFieldValueObject()->findAll();

			foreach ($aField_Values as $oField_Value)
			{
				$this->_aAllValues[$oField_Value->entity_id][] = $oField_Value;
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
		return Field_Controller_Value::factory($this->type)
			->setField($this)
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
		return Field_Controller_Value::factory($this->type)
			->setField($this)
			->getValuesByValue($value, $condition, $bCache);
	}

	/**
	 * Create new value for entity
	 * @param int $entityId entity id
	 * @return object
	 */
	public function createNewValue($entityId)
	{
		return Field_Controller_Value::factory($this->type)
			->setField($this)
			->createNewValue($entityId);
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
	 * @hostcms-event field.onBeforeRedeclaredDelete
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
		$this->Field_Value_Ints->deleteAll(FALSE);
		$this->Field_Value_Floats->deleteAll(FALSE);
		$this->Field_Value_Strings->deleteAll(FALSE);
		$this->Field_Value_Texts->deleteAll(FALSE);
		$this->Field_Value_Datetimes->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->obligatory)
		{
			Core_Html_Entity::factory('Span')
				->value('<i class="fa fa-asterisk darkorange fa-small"></i>')
				->execute();
		}
	}

	public function typeBackend()
	{
		return Core::_('Field.type' . $this->type);
	}

	/**
	 * Move field to another group
	 * @param int $iFieldDirId target group id
	 * @return Core_Entity
	 * @hostcms-event field.onBeforeMove
	 * @hostcms-event field.onAfterMove
	 */
	public function move($iFieldDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iFieldDirId));

		$this->field_dir_id = $iFieldDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

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
	 * @hostcms-event field.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		$this->setConfig(
			Core_Config::instance()->get('field_config', array()) + array('add_list_items' => TRUE)
		);

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event field.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		$this->setConfig(
			Core_Config::instance()->get('field_config', array()) + array('add_list_items' => TRUE)
		);

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
		$this->clearXmlTags();

		/*if ($this->type != 2)
		{
			$this->addForbiddenTag('image_large_max_width')
				->addForbiddenTag('image_large_max_height')
				->addForbiddenTag('image_small_max_width')
				->addForbiddenTag('image_small_max_height')
				->addForbiddenTag('hide_small_image');
		}*/

		$bIsList = $this->type == 3 && $this->list_id != 0 && Core::moduleIsActive('list');

		// List
		if ($bIsList)
		{
			$this->addEntity(
				$this->List->clearEntities()
			);

			if (is_bool($this->_config['add_list_items']) && $this->_config['add_list_items']
				|| is_array($this->_config['add_list_items']) && in_array($this->id, $this->_config['add_list_items'])
			)
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
						return $this;
					}
				}
				elseif (is_object($this->_limitListItems))
				{
					$oList_Items->queryBuilder()
						->from(array($this->_limitListItems, 'prop_tmp'))
						->where('list_items.id', '=', Core_QueryBuilder::expression('`prop_tmp`.`value`'));
				}

				Core_Event::notify($this->_modelName . '.onBeforeGetXmlAddListItems', $this, array($oList_Items));

				$aList_Items = $oList_Items->findAll(FALSE);
				foreach ($aList_Items as $oList_Item)
				{
					$this->_aListItemsTree[$oList_Item->parent_id][] = $oList_Item;
				}

				$this->_addListItems(0, $this->List);

				$this->_aListItemsTree = array();
			}
		}

		return $this;
	}

	protected function _addListItems($parentId, $oObject)
	{
		if (isset($this->_aListItemsTree[$parentId]))
		{
			foreach ($this->_aListItemsTree[$parentId] as $oList_Item)
			{
				$oObject->addEntity($oList_Item->clearEntities());

				$this->_addListItems($oList_Item->id, $oList_Item);
			}
		}

		return $this;
	}

	/**
	 * Change field visibility
	 * @return self
	 * @hostcms-event field.onBeforeChangeVisible
	 * @hostcms-event field.onAfterChangeVisible
	 */
	public function changeVisible()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeVisible', $this);

		$this->visible = 1 - $this->visible;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeVisible', $this);

		return $this;
	}
}