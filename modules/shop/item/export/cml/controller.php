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

		$oShop_Items = Core_Entity::factory('Shop_Item');
		$oQueryBuilder = $oShop_Items->queryBuilder()
			->where('shop_id', '=', $this->shop->id)
			->where('modification_id', '=', 0)
			->clearOrderBy()
			->orderBy('id', 'ASC');

		if ($this->group->id)
		{
			$oShop_Items->queryBuilder()
				->where('shop_group_id', 'IN', $this->_groupsID);
		}

		$offset = 0;
		$limit = 100;

		do {
			$oShop_Items
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				$this->_addImportItem($oShop_Item, $xmlGoods);

				// Модификации
				if ($this->exportItemModifications)
				{
					$aModifications = $oShop_Item->Modifications->findAll(FALSE);
					foreach ($aModifications as $oModification)
					{
						$this->_addImportItem($oModification, $xmlGoods);
					}
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Items));

		return $this->_xml->asXML();
	}

	/**
	 * Add import-item
	 * @param Shop_Item_Model $oShop_Item
	 * @param object $parentNode Parent node
	 * @return self
	 */
	protected function _addImportItem(Shop_Item_Model $oShop_Item, $parentNode)
	{
		$sMod = $oShop_Item->modification_id == 0
			? ''
			: $oShop_Item->Modification->guid . '#';

		$xmlItem = $parentNode->addChild('Товар');
		$xmlItem->addChild('Ид', $sMod . $oShop_Item->guid);
		$xmlItem->addChild('Артикул', $oShop_Item->marking);
		$xmlItem->addChild('Наименование', $oShop_Item->name);
		$xmlItem->addChild('Описание', $oShop_Item->description);
		$xmlItem->addChild('БазоваяЕдиница', $oShop_Item->Shop_Measure->name)
			->addAttribute('НаименованиеПолное', $oShop_Item->Shop_Measure->description);

		if ($oShop_Item->modification_id && $oShop_Item->Modification->Shop_Group->id)
		{
			$xmlItem->addChild('Группы')->addChild('Ид', $oShop_Item->Modification->Shop_Group->guid);
		}
		elseif ($oShop_Item->Shop_Group->id)
		{
			$xmlItem->addChild('Группы')->addChild('Ид', $oShop_Item->Shop_Group->guid);
		}

		$oShop_Item->image_large
			&& $xmlItem->addChild('Картинка', $oShop_Item->getItemHref() . $oShop_Item->image_large);

		if ($oShop_Item->shop_producer_id)
		{
			$xmlProducer = $xmlItem->addChild('Изготовитель');
			$xmlProducer->addChild('Ид', $oShop_Item->Shop_Producer->id);
			$xmlProducer->addChild('Наименование', $oShop_Item->Shop_Producer->name);
		}

		// Обработка дополнительных свойств
		$aShopItemPropertyValues = $oShop_Item->getPropertyValues(FALSE);

		if (count($aShopItemPropertyValues))
		{
			$xmlProperyValues = $xmlItem->addChild('ЗначенияСвойств');

			foreach ($aShopItemPropertyValues as $oShop_ItemPropertyValue)
			{
				$xmlPropertyValue = $xmlProperyValues->addChild('ЗначенияСвойства');
				$xmlPropertyValue->addChild('Ид', $oShop_ItemPropertyValue->Property->guid);
				$xmlPropertyValue->addChild('Значение', $oShop_ItemPropertyValue->Property->type == 2
					? $oShop_ItemPropertyValue->getLargeFileHref()
					: $oShop_ItemPropertyValue->value);
			}
		}

		// СтавкиНалогов
		if ($oShop_Item->shop_tax_id)
		{
			$xmlTaxes = $xmlItem->addChild('СтавкиНалогов');
			
			$xmlTax = $xmlTaxes->addChild('СтавкаНалога');
			$xmlTax->addChild('Наименование', $oShop_Item->Shop_Tax->name);
			$xmlTax->addChild('Ставка', $oShop_Item->Shop_Tax->rate);
		}
		
		// ЗначенияРеквизитов
		if ($oShop_Item->weight)
		{
			$xmlProp = $xmlItem->addChild('ЗначенияРеквизитов');
			
			// Вес
			$xmlWeight = $xmlProp->addChild('ЗначениеРеквизита');
			$xmlWeight->addChild('Наименование', 'Вес');
			$xmlWeight->addChild('Значение', $oShop_Item->weight);
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

		$this->group->id
			&& $this->_groupsID[] = $this->group->id;

		$this->_setSimpleXML();

		$packageOfProposals = $this->_xml->addChild('ПакетПредложений');

		$packageOfProposals->addChild('Наименование', 'Пакет предложений');

		$prices = $packageOfProposals->addChild('ТипыЦен');

		$sCurrencyCode = $this->shop->Shop_Currency->code;

		$retailPrice = $prices->addChild('ТипЦены');
		$retailPrice->addChild('Ид', $this->_retailPriceGUID);
		$retailPrice->addChild('Наименование', 'Розничная');
		$retailPrice->addChild('Валюта', $sCurrencyCode);

		// Additional Prices
		if (Core::moduleIsActive('siteuser'))
		{
			$aShop_Prices = $this->shop->Shop_Prices->findAll();
			foreach ($aShop_Prices as $oShop_Price)
			{
				$retailPrice = $prices->addChild('ТипЦены');
				$retailPrice->addChild('Ид', $oShop_Price->guid);
				$retailPrice->addChild('Наименование', $oShop_Price->name);
				$retailPrice->addChild('Валюта', $sCurrencyCode);
			}
		}

		$oShop_Items = $this->shop->Shop_Items;
		$oShop_Items->queryBuilder()
			->where('modification_id', '=', 0)
			->clearOrderBy()
			->orderBy('id', 'ASC');

		if ($this->group->id)
		{
			$oShop_Items->queryBuilder()
				->where('shop_group_id', 'IN', $this->_groupsID);
		}

		$packageOfProposals = $packageOfProposals->addChild('Предложения');

		$offset = 0;
		$limit = 100;

		do {
			$oShop_Items
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			$aShop_Items = $oShop_Items->findAll(FALSE);
			foreach ($aShop_Items as $oShop_Item)
			{
				$this->_addOffersItem($oShop_Item, $packageOfProposals);

				// Модификации
				if ($this->exportItemModifications)
				{
					$aModifications = $oShop_Item->Modifications->findAll(FALSE);
					foreach ($aModifications as $oModification)
					{
						$this->_addOffersItem($oModification, $packageOfProposals);
					}
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Items));

		return $this->_xml->asXML();
	}

	/**
	 * Add offers-item
	 * @param Shop_Item_Model $oShop_Item
	 * @param object $parentNode Parent node
	 * @return self
	 */
	protected function _addOffersItem(Shop_Item_Model $oShop_Item, $parentNode)
	{
		$sMod = $oShop_Item->modification_id == 0
			? ''
			: $oShop_Item->Modification->guid . '#';

		$sShop_Measure_Name = $oShop_Item->Shop_Measure->name;

		$proposal = $parentNode->addChild('Предложение');
		$proposal->addChild('Ид', $sMod . $oShop_Item->guid);
		$proposal->addChild('Артикул', $oShop_Item->marking);
		$proposal->addChild('Наименование', $oShop_Item->name);
		$proposal->addChild('БазоваяЕдиница', $sShop_Measure_Name)
				->addAttribute('НаименованиеПолное', $oShop_Item->Shop_Measure->description);

		$prices = $proposal->addChild('Цены');

		$price = $prices->addChild('Цена');

		$price->addChild('ИдТипаЦены', $this->_retailPriceGUID);
		$price->addChild('ЦенаЗаЕдиницу', $oShop_Item->price);
		$price->addChild('Представление',
			sprintf('%s %s за %s',
					$oShop_Item->price,
					$oShop_Item->Shop_Currency->code,
					$sShop_Measure_Name));
		$price->addChild('Единица', $sShop_Measure_Name);

		// Additional Prices
		if (Core::moduleIsActive('siteuser'))
		{
			$aShop_Item_Prices = $oShop_Item->Shop_Item_Prices->findAll(FALSE);
			foreach($aShop_Item_Prices as $oShop_Item_Price)
			{
				$price = $prices->addChild('Цена');

				$price->addChild('ИдТипаЦены', $oShop_Item_Price->Shop_Price->guid);
				$price->addChild('ЦенаЗаЕдиницу', $oShop_Item_Price->value);
				$price->addChild('Представление',
					sprintf('%s %s за %s',
							$oShop_Item_Price->value,
							$oShop_Item->Shop_Currency->code,
							$sShop_Measure_Name));
				$price->addChild('Единица', $sShop_Measure_Name);
			}
		}

		$proposal->addChild('Количество', $oShop_Item->getRest());

		return $this;
	}
}