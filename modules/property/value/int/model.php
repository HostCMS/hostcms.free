<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_Int_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Value_Int_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_int';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'property' => array(),
		'list_item' => array('foreign_key' => 'value'),
		'informationsystem_item' => array('foreign_key' => 'value'),
		'informationsystem_group' => array('foreign_key' => 'value'),
		'shop_item' => array('foreign_key' => 'value'),
		'shop_group' => array('foreign_key' => 'value'),
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'entity_id'
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	/*protected $_sorting = array(
		'property_value_ints.id' => 'ASC'
	);*/

	/**
	 * Set property value
	 * @param int $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = intval($value);
		return $this;
	}

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'property_value';

	/**
	 * Module config
	 */
	static public $aConfig = NULL;

	/**
	 * Constructor.
	 * @param string $primaryKey
	 */
	public function __construct($primaryKey = NULL)
	{
		parent::__construct($primaryKey);

		if (is_null(self::$aConfig))
		{
			self::$aConfig = Core_Config::instance()->get('property_config', array()) + array(
				'recursive_properties' => TRUE,
			);
		}
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event property_value_int.onBeforeRedeclaredGetXml
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
	 * @hostcms-event property_value_int.onBeforeRedeclaredGetStdObject
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
	 * @hostcms-event property_value_int.onBeforeAddListItem
	 * @hostcms-event property_value_int.onBeforeAddInformationsystemItem
	 * @hostcms-event property_value_int.onBeforeAddShopItem
	 * @hostcms-event property_value_int.onBeforeAddShopGroup
	 */
	protected function _prepareData()
	{
		$oProperty = $this->Property;

		// ---------------------------
		/*$this->_tagName = $this->Property->tag_name;
		return $this;*/
		// ---------------------------

		$this->clearXmlTags()
			->addXmlTag('property_dir_id', $this->Property->property_dir_id)
			->addXmlTag('tag_name', $oProperty->tag_name);

		// List
		if ($oProperty->type == 3 && Core::moduleIsActive('list'))
		{
			$this->addForbiddenTag('value');

			if ($this->value != 0)
			{
				$oList_Item = $this->List_Item;

				if ($oList_Item->id)
				{
					Core_Event::notify($this->_modelName . '.onBeforeAddListItem', $this, array($oList_Item));

					$this->addXmlTag('value', $oList_Item->value);

					$oList_Item->description != ''
						&& $this->addXmlTag('description', $oList_Item->description);

					$oList_Item->icon != ''
						&& $this->addXmlTag('icon', $oList_Item->icon);

					$oList_Item->color != ''
						&& $this->addXmlTag('color', $oList_Item->color);

					$this->addXmlTag('list_item_id', $oList_Item->id);

					$oParentListItem = $oList_Item->getParent();
					$oParentObject = $this;

					while ($oParentListItem)
					{
						$oParentObject->addEntity($oParentListItem->clearEntities());
						$oParentObject = $oParentListItem;
						$oParentListItem = $oParentListItem->getParent();
					}
				}
			}
		}

		// Informationsystem
		if ($oProperty->type == 5 && Core::moduleIsActive('informationsystem'))
		{
			$this->addForbiddenTag('value');

			if ($this->value != 0)
			{
				$oInformationsystem_Item = $this->Informationsystem_Item;

				if ($oInformationsystem_Item->id)
				{
					// Allow all kinds of properties except informationsystem
					$oInformationsystem_Item_Property_List = Core_Entity::factory('Informationsystem_Item_Property_List', $oInformationsystem_Item->informationsystem_id);

					$aTmp = array();
					$aItemProperties = $oInformationsystem_Item_Property_List->Properties->findAll();
					foreach ($aItemProperties as $oItemProperty)
					{
						// Зацикленность через Св-во типа ИЭ/Товар, у которого св-во ИЭ/Товар
						($oItemProperty->type != 5 && $oItemProperty->type != 12
							|| self::$aConfig['recursive_properties'] && $oItemProperty->informationsystem_id != $oProperty->informationsystem_id
						) && $aTmp[] = $oItemProperty->id;
					}

					$oInformationsystem_Item->shortcut_id && $oInformationsystem_Item = $oInformationsystem_Item->Informationsystem_Item;

					if ($oInformationsystem_Item->id)
					{
						$oNew_Informationsystem_Item = clone $oInformationsystem_Item;

						$oNew_Informationsystem_Item
							->id($oInformationsystem_Item->id)
							->clearEntities()
							->showXmlProperties(count($aTmp) ? $aTmp : FALSE);

						Core_Event::notify($this->_modelName . '.onBeforeAddInformationsystemItem', $this, array($oInformationsystem_Item));

						$oLastReturn = Core_Event::getLastReturn();

						if (!is_null($oLastReturn))
						{
							$oNew_Informationsystem_Item = $oLastReturn;
						}

						$this->addEntity($oNew_Informationsystem_Item);
					}
				}
			}
		}

		// Informationsystem group
		if ($oProperty->type == 13 && Core::moduleIsActive('informationsystem'))
		{
			$this->addForbiddenTag('value');

			if ($this->value != 0)
			{
				$oInformationsystem_Group = $this->Informationsystem_Group;

				if ($oInformationsystem_Group->id)
				{
					// Allow all kinds of properties except informationsystem
					$oInformationsystem_Group_Property_List = Core_Entity::factory('Informationsystem_Group_Property_List', $oInformationsystem_Group->informationsystem_id);

					$aTmp = array();
					$aGroupProperties = $oInformationsystem_Group_Property_List->Properties->findAll();
					foreach ($aGroupProperties as $oGroupProperty)
					{
						// Зацикленность через Св-во типа ИЭ/Группа, у которого св-во ИЭ/Группа
						($oGroupProperty->type != 13 && $oGroupProperty->type != 14
							|| self::$aConfig['recursive_properties'] && $oGroupProperty->informationsystem_id != $oProperty->informationsystem_id
						) && $aTmp[] = $oGroupProperty->id;
					}

					$oInformationsystem_Group->shortcut_id && $oInformationsystem_Group = $oInformationsystem_Group->Informationsystem_Group;

					if ($oInformationsystem_Group->id)
					{
						$oNew_Informationsystem_Group = clone $oInformationsystem_Group;

						$oNew_Informationsystem_Group
							->id($oInformationsystem_Group->id)
							->clearEntities()
							->showXmlProperties(count($aTmp) ? $aTmp : FALSE);

						Core_Event::notify($this->_modelName . '.onBeforeAddInformationsystemGroup', $this, array($oInformationsystem_Group));

						$oLastReturn = Core_Event::getLastReturn();

						if (!is_null($oLastReturn))
						{
							$oNew_Informationsystem_Group = $oLastReturn;
						}

						$this->addEntity($oNew_Informationsystem_Group);
					}
				}
			}
		}

		// Shop
		if ($oProperty->type == 12 && Core::moduleIsActive('shop'))
		{
			$this->addForbiddenTag('value');

			if ($this->value != 0)
			{
				$oShop_Item = $this->Shop_Item;

				// Shop_Item exists
				if ($oShop_Item->id)
				{
					// Allow all kinds of properties except shop
					$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop_Item->shop_id);

					$aTmp = array();

					//$aItemProperties = $oShop_Item_Property_List->Properties->findAll();
					$aItemProperties = $oShop_Item_Property_List->getPropertiesForGroup($oShop_Item->shop_group_id);
					foreach ($aItemProperties as $oItemProperty)
					{
						// Зацикленность через Св-во типа ИЭ/Товар, у которого св-во ИЭ/Товар
						if ($oItemProperty->type != 12 && $oItemProperty->type != 5
							|| self::$aConfig['recursive_properties'] && $oItemProperty->shop_id != $oProperty->shop_id
						)
						{
							$oShop_Item_Property = $oItemProperty->Shop_Item_Property;

							if ($oShop_Item_Property->show_in_item)
							{
								$aTmp[] = $oItemProperty->id;
							}
						}
					}

					$oShop_Item->shortcut_id && $oShop_Item = $oShop_Item->Shop_Item;

					if ($oShop_Item->id)
					{
						$oNew_Shop_Item = clone $oShop_Item;

						$oNew_Shop_Item
							->id($oShop_Item->id)
							->clearEntities()
							->showXmlProperties(count($aTmp) ? $aTmp : FALSE);

						Core_Event::notify($this->_modelName . '.onBeforeAddShopItem', $this, array($oShop_Item));

						$oLastReturn = Core_Event::getLastReturn();

						if (!is_null($oLastReturn))
						{
							$oNew_Shop_Item = $oLastReturn;
						}

						$this->addEntity($oNew_Shop_Item);
					}
				}
			}
		}

		// Shop group
		if ($oProperty->type == 14 && Core::moduleIsActive('shop'))
		{
			$this->addForbiddenTag('value');

			if ($this->value != 0)
			{
				$oShop_Group = $this->Shop_Group;

				// Shop_Group exists
				if ($oShop_Group->id)
				{
					// Allow all kinds of properties except shop
					$oShop_Group_Property_List = Core_Entity::factory('Shop_Group_Property_List', $oShop_Group->shop_id);

					$aTmp = array();

					$aGroupProperties = $oShop_Group_Property_List->Properties->findAll();
					foreach ($aGroupProperties as $oGroupProperty)
					{
						// Зацикленность через Св-во типа ИЭ/Товар, у которого св-во ИЭ/Товар
						($oGroupProperty->type != 13 && $oGroupProperty->type != 14
							|| self::$aConfig['recursive_properties'] && $oGroupProperty->shop_id != $oProperty->shop_id
						) && $aTmp[] = $oGroupProperty->id;
					}

					$oShop_Group->shortcut_id && $oShop_Group = $oShop_Group->Shop_Group;

					if ($oShop_Group->id)
					{
						$oNew_Shop_Group = clone $oShop_Group;

						$oNew_Shop_Group
							->id($oShop_Group->id)
							->clearEntities()
							->showXmlProperties(count($aTmp) ? $aTmp : FALSE);

						Core_Event::notify($this->_modelName . '.onBeforeAddShopGroup', $this, array($oShop_Group));

						$oLastReturn = Core_Event::getLastReturn();

						if (!is_null($oLastReturn))
						{
							$oNew_Shop_Group = $oLastReturn;
						}

						$this->addEntity($oNew_Shop_Group);
					}
				}
			}
		}

		return $this;
	}
}