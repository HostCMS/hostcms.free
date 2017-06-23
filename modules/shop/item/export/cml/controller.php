<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Export_Cml_Controller extends Core_Servant_Properties
{
	/**
	 * Backend property
	 * @var mixed
	 */
	private $_xml;

	/**
	 * Backend property
	 * @var array
	 */
	private $_groupsID = array();

	/**
	 * Backend property
	 * @var mixed
	 */
	private $_retailPriceGUID;

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'group',
		'shop',
		'exportItemExternalProperties',
		'exportItemModifications'
	);

	/**
	 * Generate CML for groups
	 * @param Shop_Group_Model $group start group
	 * @param SimpleXMLElement $xml target XML object
	 */
	private function getGroupsCML(Shop_Group_Model $group, $xml)
	{
		!in_array($group->id, $this->_groupsID) && $this->_groupsID[] = $group->id;

		if (intval($group->id) != 0)
		{
			$xml = $xml->addChild('Группа');
			$xml->addChild('Ид', $group->guid);
			$xml->addChild('Наименование', $group->name);

			$group->description != ''
				&& $xml->addChild('Описание', $group->description);

			$aShop_Groups = $group->Shop_Groups->findALL(FALSE);
		}
		else
		{
			$aShop_Groups = $this->shop->Shop_Groups->getAllByParent_id(0, FALSE);
		}

		if (count($aShop_Groups))
		{
			$xmlGroups = $xml->addChild('Группы');

			foreach ($aShop_Groups as $group)
			{
				$this->getGroupsCML($group, $xmlGroups);
			}
		}
	}

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct();

		$this->shop = $oShop;
		$this->group = NULL;
		$this->_retailPriceGUID = Core_Guid::get();
		$this->exportItemExternalProperties = TRUE;
		$this->exportItemModifications = TRUE;
	}

	/**
	 * Set SimpleXMLElement object
	 * @return self
	 */
	protected function _setSimpleXML()
	{
		$this->_xml = new Core_SimpleXMLElement(sprintf(
			'<?xml version="1.0" encoding="utf-8"?><КоммерческаяИнформация ВерсияСхемы="2.08" ДатаФормирования="%sT%s"></КоммерческаяИнформация>',
			date("Y-m-d"),
			date("H:i:s")));

		return $this;
	}

	/**
	 * Export import.xml
	 * @return string
	 */
	public function exportImport()
	{
		if ($this->group === NULL)
		{
			throw new Core_Exception('Parent group does not specified!');
		}

		$this->_groupsID[] = $this->group->id;

		$this->_setSimpleXML();

		$classifier = $this->_xml->addChild('Классификатор');

		// Группы товаров
		$this->getGroupsCML($this->group, $classifier);

		// Свойства товаров
		$aProperties = $this->exportItemExternalProperties
			? Core_Entity::factory('Shop_Item_Property_List', $this->shop->id)->Properties->findAll(FALSE)
			: array();

		if (count($aProperties))
		{
			$xmlProperties = $classifier->addChild('Свойства');

			foreach ($aProperties as $oProperty)
			{
				$xmlProperty = $xmlProperties->addChild('Свойство');
				$xmlProperty->addChild('Ид', $oProperty->guid);
				$xmlProperty->addChild('Наименование', $oProperty->name);

				if ($oProperty->type == 3 && Core::moduleIsActive('list'))
				{
					$xmlProperty->addChild('ТипЗначений', 'Справочник');

					$xmlValues = $xmlProperty->addChild('ВариантыЗначений');

					$aList_Items = $oProperty->List->List_Items->findAll(FALSE);

					foreach ($aList_Items as $oList_Item)
					{
						$xmlValue = $xmlValues->addChild('Справочник');
						$xmlValue->addChild('ИдЗначения', $oList_Item->id);
						$xmlValue->addChild('Значение', $oList_Item->value);
					}
				}
			}
		}

		// Товары
		$xmlCatalog = $this->_xml->addChild('Каталог');
		$xmlGoods = $xmlCatalog->addChild('Товары');

		$oShopItems = Core_Entity::factory('Shop_Item');
		$oQueryBuilder = $oShopItems->queryBuilder()
			->where('shop_group_id', 'IN', $this->_groupsID)
			->where('shop_id', '=', $this->shop->id)
			->where('modification_id', '=', 0)
			->clearOrderBy()
			->orderBy('id', 'ASC');

		$offset = 0;
		$limit = 100;

		do {
			$oShopItems
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			$aShopItems = $oShopItems->findAll(FALSE);

			foreach ($aShopItems as $oShopItem)
			{
				$this->_addImportItem($oShopItem, $xmlGoods);

				// Модификации
				if ($this->exportItemModifications)
				{
					$aModifications = $oShopItem->Modifications->findAll(FALSE);
					foreach ($aModifications as $oModification)
					{
						$this->_addImportItem($oModification, $xmlGoods);
					}
				}
			}

			$offset += $limit;
		}
		while (count($aShopItems));

		return $this->_xml->asXML();
	}

	/**
	 * Add import-item
	 * @param Shop_Item_Model $oShopItem
	 * @param object $parentNode Parent node
	 * @return self
	 */
	protected function _addImportItem(Shop_Item_Model $oShopItem, $parentNode)
	{
		$sMod = $oShopItem->modification_id == 0
			? ''
			: $oShopItem->Modification->guid . '#';

		$xmlItem = $parentNode->addChild('Товар');
		$xmlItem->addChild('Ид', $sMod . $oShopItem->guid);
		$xmlItem->addChild('Артикул', $oShopItem->marking);
		$xmlItem->addChild('Наименование', $oShopItem->name);
		$xmlItem->addChild('Описание', $oShopItem->description);
		$xmlItem->addChild('БазоваяЕдиница', $oShopItem->Shop_Measure->name)
			->addAttribute('НаименованиеПолное', $oShopItem->Shop_Measure->description);

		if ($oShopItem->modification_id && $oShopItem->Modification->Shop_Group->id)
		{
			$xmlItem->addChild('Группы')->addChild('Ид', $oShopItem->Modification->Shop_Group->guid);
		}
		elseif ($oShopItem->Shop_Group->id)
		{
			$xmlItem->addChild('Группы')->addChild('Ид', $oShopItem->Shop_Group->guid);
		}

		$oShopItem->image_large
			&& $xmlItem->addChild('Картинка', $oShopItem->getItemHref() . $oShopItem->image_large);

		if ($oShopItem->shop_producer_id)
		{
			$xmlProducer = $xmlItem->addChild('Изготовитель');
			$xmlProducer->addChild('Ид', $oShopItem->Shop_Producer->id);
			$xmlProducer->addChild('Наименование', $oShopItem->Shop_Producer->name);
		}

		// Обработка дополнительных свойств
		$aShopItemPropertyValues = $oShopItem->getPropertyValues(FALSE);

		if (count($aShopItemPropertyValues) > 0)
		{
			$xmlProperyValues = $xmlItem->addChild('ЗначенияСвойств');

			foreach ($aShopItemPropertyValues as $oShopItemPropertyValue)
			{
				$xmlPropertyValue = $xmlProperyValues->addChild('ЗначенияСвойства');
				$xmlPropertyValue->addChild('Ид', $oShopItemPropertyValue->Property->guid);
				$xmlPropertyValue->addChild('Значение', $oShopItemPropertyValue->Property->type == 2
					? $oShopItemPropertyValue->getLargeFileHref()
					: $oShopItemPropertyValue->value);
			}
		}

		return $this;
	}

	/**
	 * Export offers.xml
	 * @return string
	 */
	public function exportOffers()
	{
		if ($this->group === NULL)
		{
			throw new Core_Exception("Parent group does not specified!");
		}

		$this->_groupsID[] = $this->group->id;
		
		$this->_setSimpleXML();

		$packageOfProposals = $this->_xml->addChild('ПакетПредложений');

		$packageOfProposals->addChild('Наименование', 'Пакет предложений');

		$retailPrice = $packageOfProposals->addChild('ТипыЦен')->addChild('ТипЦены');
		$retailPrice->addChild('Ид', $this->_retailPriceGUID);
		$retailPrice->addChild('Наименование', 'Розничная');
		$retailPrice->addChild('Валюта', $this->shop->Shop_Currency->code);

		$oShopItems = $this->shop->Shop_Items;
		$oShopItems->queryBuilder()
			->where('shop_group_id', 'IN', $this->_groupsID)
			->where('modification_id', '=', 0);

		$packageOfProposals = $packageOfProposals->addChild('Предложения');

		$offset = 0;
		$limit = 100;

		do {
			$oShopItems->queryBuilder()->offset($offset)->limit($limit);
			$aShopItems = $oShopItems->findAll(FALSE);

			foreach ($aShopItems as $oShopItem)
			{
				$this->_addOffersItem($oShopItem, $packageOfProposals);

				// Модификации
				if ($this->exportItemModifications)
				{
					$aModifications = $oShopItem->Modifications->findAll(FALSE);
					foreach ($aModifications as $oModification)
					{
						$this->_addOffersItem($oModification, $packageOfProposals);
					}
				}
			}

			$offset += $limit;
		}
		while (count($aShopItems));

		return $this->_xml->asXML();
	}

	/**
	 * Add offers-item
	 * @param Shop_Item_Model $oShopItem
	 * @param object $parentNode Parent node
	 * @return self
	 */
	protected function _addOffersItem(Shop_Item_Model $oShopItem, $parentNode)
	{
		$sMod = $oShopItem->modification_id == 0
			? ''
			: $oShopItem->Modification->guid . '#';

		$proposal = $parentNode->addChild('Предложение');
		$proposal->addChild('Ид', $sMod . $oShopItem->guid);
		$proposal->addChild('Артикул', $oShopItem->marking);
		$proposal->addChild('Наименование', $oShopItem->name);
		$proposal->addChild('БазоваяЕдиница', $oShopItem->Shop_Measure->name)
				->addAttribute('НаименованиеПолное', $oShopItem->Shop_Measure->description);
		$price = $proposal->addChild('Цены')->addChild('Цена');

		$price->addChild('ИдТипаЦены', $this->_retailPriceGUID);
		$price->addChild('ЦенаЗаЕдиницу', $oShopItem->price);
		$price->addChild('Представление',
						sprintf('%s %s за %s',
								$oShopItem->price,
								$oShopItem->Shop_Currency->code,
								$oShopItem->Shop_Measure->name));
		$price->addChild('Единица', $oShopItem->Shop_Measure->name);
		$proposal->addChild('Количество', $oShopItem->getRest());

		return $this;
	}
}