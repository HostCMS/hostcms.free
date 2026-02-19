<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Value_Int_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Field_Value_Int_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'field_value_int';

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
		'field' => array(),
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
	 * Set field value
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
	protected $_tagName = 'field_value';

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
			self::$aConfig = Core_Config::instance()->get('field_config', array()) + array(
				'recursive_fields' => TRUE,
			);
		}
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event field_value_int.onBeforeRedeclaredGetXml
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
	 * @hostcms-event field_value_int.onBeforeRedeclaredGetStdObject
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
	 * @hostcms-event field_value_int.onBeforeAddListItem
	 * @hostcms-event field_value_int.onBeforeAddInformationsystemItem
	 * @hostcms-event field_value_int.onBeforeAddShopItem
	 * @hostcms-event field_value_int.onBeforeAddShopGroup
	 */
	protected function _prepareData()
	{
		$oField = $this->Field;

		$this->clearXmlTags()
			->addXmlTag('field_dir_id', $this->Field->field_dir_id)
			->addXmlTag('tag_name', $oField->tag_name);

		// List
		if ($oField->type == 3 && Core::moduleIsActive('list'))
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
		if ($oField->type == 5 && Core::moduleIsActive('informationsystem'))
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
						($oItemProperty->type != 5 && $oItemProperty->type != 12 && $oItemProperty->type != 13 && $oItemProperty->type != 14
							|| self::$aConfig['recursive_fields'] && $oItemProperty->informationsystem_id != $oField->informationsystem_id
						) && $aTmp[] = $oItemProperty->id;
					}

					$oInformationsystem_Item->shortcut_id && $oInformationsystem_Item = $oInformationsystem_Item->Informationsystem_Item;

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

		// Informationsystem group
		if ($oField->type == 13 && Core::moduleIsActive('informationsystem'))
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
							|| self::$aConfig['recursive_fields'] && $oGroupProperty->informationsystem_id != $oField->informationsystem_id
						) && $aTmp[] = $oGroupProperty->id;
					}

					$oInformationsystem_Group->shortcut_id && $oInformationsystem_Group = $oInformationsystem_Group->Informationsystem_Group;

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

		// Shop
		if ($oField->type == 12 && Core::moduleIsActive('shop'))
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
							|| self::$aConfig['recursive_fields'] && $oItemProperty->shop_id != $oField->shop_id
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

		// Shop group
		if ($oField->type == 14 && Core::moduleIsActive('shop'))
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
							|| self::$aConfig['recursive_fields'] && $oGroupProperty->shop_id != $oField->shop_id
						) && $aTmp[] = $oGroupProperty->id;
					}

					$oShop_Group->shortcut_id && $oShop_Group = $oShop_Group->Shop_Group;

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

		return $this;
	}
}