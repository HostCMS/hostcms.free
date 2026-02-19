<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Export_Csv_Item_Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Item_Export_Csv_Item_Controller extends Shop_Item_Export_Csv_Controller
{
	/**
	 * User prices
	 * Цены групп пользователей
	 * @var array
	 */
	private $_aShopPrices = array();

	/**
	 * Warehouses
	 * Склады
	 * @var array
	 */
	private $_aShopWarehouses = array();

	/**
	 * Additional properties of items
	 * Дополнительные свойства товаров
	 * @var array
	 */
	private $_aItem_Properties	= array();

	/**
	 * Additional properties of item groups
	 * Дополнительные свойства групп товаров
	 * @var array
	 */
	private $_aGroup_Properties = array();

	/**
	 * Additional properties of items
	 * Пользовательские поля товаров
	 * @var array
	 */
	private $_aItem_Fields	= array();

	/**
	 * Additional properties of item groups
	 * Пользовательские поля групп товаров
	 * @var array
	 */
	private $_aGroup_Fields = array();

	/**
	 * Item properties count
	 * Требуется хранить количество свойств отдельно, т.к. количество полей файла CSV для свойств не равно количеству свойств (из-за файлов)
	 * @var int
	 */
	private $_iItem_Properties_Count;

	/**
	 * Item properties count
	 * Требуется хранить количество свойств отдельно, т.к. количество полей файла CSV для свойств не равно количеству свойств (из-за файлов)
	 * @var int
	 */
	private $_iItem_Fields_Count;

	/**
	 * Group properties count
	 * @var int
	 */
	private $_iGroup_Properties_Count;

	/**
	 * Group properties count
	 * @var int
	 */
	private $_iGroup_Fields_Count;

	/**
	 * Base properties of items
	 * Основные свойства товара
	 * @var array
	 */
	private $_aItemBaseProperties;

	/**
	 * Base properties of item groups
	 * Основные свойства групп товаров
	 * @var array
	 */
	private $_aGroupBaseProperties;

	/**
	 * Special prices of item
	 * Основные свойства дополнительных цен товаров
	 * @var array
	 */
	private $_aSpecialPriceBaseProperties;

	/**
	 * CSV data
	 * @var array
	 */
	private $_aCurrentData;

	/**
	 * Data pointer
	 * @var int
	 */
	private $_iCurrentDataPosition;

	/**
	 * Item CML ID Position
	 * @var int
	 */
	//private $_guidItemPosition = 0;

	protected $_shopUrl = NULL;

	/**
	 * Кэш значений доп. св-в
	 * @var array
	 */
	protected $_cachePropertyValues = array();

	/**
	 * Кэш значений доп. св-в
	 * @var array
	 */
	protected $_cacheFieldValues = array();

	/**
	 * Constructor.
	 * @param int $iShopId shop ID
	 */
	public function __construct($iShopId)
	{
		$this->_allowedProperties = array_merge($this->_allowedProperties, array(
			'guidItemPosition',
			'guidGroupPosition',
			'lastGroupId',
			'parentGroup',
			'producer',
			'seller',
			'startItemDate',
			'endItemDate',
			'exportItemExternalProperties',
			'exportGroupExternalProperties',
			'exportItemModifications',
			'exportItemShortcuts',
			'exportInStock',
			'exportItemFields',
			'exportGroupFields',
			'exportStocks',
			'exportPrices'
		));

		parent::__construct($iShopId);

		$this->clear();

		// Устанавливаем лимит времени выполнения в 1 час
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			if (Core::isFunctionEnable('set_time_limit') && ini_get('safe_mode') != 1 && ini_get('max_execution_time') < 3600)
			{
				@set_time_limit(3600);
			}
		}
	}

	/**
	 * Get Group Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetGroupTitles
	 */
	public function getGroupTitles()
	{
		$return = array(
			Core::_('Shop_Exchange.group_name'),
			Core::_('Shop_Exchange.group_guid'),
			Core::_('Shop_Exchange.parent_group_guid'),
			Core::_('Shop_Exchange.group_seo_title'),
			Core::_('Shop_Exchange.group_seo_description'),
			Core::_('Shop_Exchange.group_seo_keywords'),
			Core::_('Shop_Exchange.group_description'),
			Core::_('Shop_Exchange.group_path'),
			Core::_('Shop_Exchange.group_image_large'),
			Core::_('Shop_Exchange.group_image_small'),
			Core::_('Shop_Exchange.group_sorting'),

			Core::_('Shop_Exchange.group_seo_group_title_template'),
			Core::_('Shop_Exchange.group_seo_group_keywords_template'),
			Core::_('Shop_Exchange.group_seo_group_description_template'),

			Core::_('Shop_Exchange.group_seo_item_title_template'),
			Core::_('Shop_Exchange.group_seo_item_keywords_template'),
			Core::_('Shop_Exchange.group_seo_item_description_template')
		);

		$this->guidGroupPosition = 1;

		Core_Event::notify(get_class($this) . '.onGetGroupTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Get Item Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetItemTitles
	 */
	public function getItemTitles()
	{
		$return = array(
			Core::_('Shop_Exchange.item_guid'),
			Core::_('Shop_Exchange.item_id'),
			Core::_('Shop_Exchange.item_marking'),
			Core::_('Shop_Exchange.item_parent_marking'),
			Core::_('Shop_Exchange.item_parent_guid'),
			Core::_('Shop_Exchange.item_name'),
			Core::_('Shop_Exchange.item_description'),
			Core::_('Shop_Exchange.item_text'),
			Core::_('Shop_Exchange.item_weight'),
			Core::_('Shop_Exchange.item_length'),
			Core::_('Shop_Exchange.item_width'),
			Core::_('Shop_Exchange.item_height'),
			Core::_('Shop_Exchange.item_min_quantity'),
			Core::_('Shop_Exchange.item_max_quantity'),
			Core::_('Shop_Exchange.item_quantity_step'),
			Core::_('Shop_Exchange.item_type'),
			Core::_('Shop_Exchange.item_tags'),
			Core::_('Shop_Exchange.item_price'),
			Core::_('Shop_Exchange.item_active'),
			Core::_('Shop_Exchange.item_sorting'),
			Core::_('Shop_Exchange.item_path'),
			Core::_('Shop_Exchange.item_full_path'),
			Core::_('Shop_Exchange.tax_id'),
			Core::_('Shop_Exchange.currency_id'),
			Core::_('Shop_Exchange.seller_name'),
			Core::_('Shop_Exchange.producer_name'),
			Core::_('Shop_Exchange.measure_value'),
			Core::_('Shop_Exchange.item_seo_title'),
			Core::_('Shop_Exchange.item_seo_description'),
			Core::_('Shop_Exchange.item_seo_keywords'),
			Core::_('Shop_Exchange.item_indexing'),
			Core::_('Shop_Exchange.item_yandex_market'),
			Core::_('Shop_Exchange.item_yandex_market_bid'),
			Core::_('Shop_Exchange.item_yandex_market_cid'),
			Core::_('Shop_Exchange.item_yandex_vendorcode'),
			Core::_('Shop_Exchange.item_datetime'),
			Core::_('Shop_Exchange.item_start_datetime'),
			Core::_('Shop_Exchange.item_end_datetime'),
			Core::_('Shop_Exchange.item_image_large'),
			Core::_('Shop_Exchange.item_image_small'),
			Core::_('Shop_Exchange.item_additional_group'),
			Core::_('Shop_Exchange.item_barcode'),
			Core::_('Shop_Exchange.item_sets_guid'),
			Core::_('Shop_Exchange.item_tabs'),
			Core::_('Shop_Exchange.associateds'),
			Core::_('Shop_Exchange.siteuser_id'),
			Core::_('Shop_Exchange.item_yandex_market_sales_notes')
		);
		Core_Event::notify(get_class($this) . '.onGetItemTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Get Item's Special Prices Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetItemSpecialpricesTitles
	 */
	public function getItemSpecialpricesTitles()
	{
		$return = array(
			Core::_('Shop_Exchange.specialprices_min_quantity'),
			Core::_('Shop_Exchange.specialprices_max_quantity'),
			Core::_('Shop_Exchange.specialprices_price'),
			Core::_('Shop_Exchange.specialprices_percent')
		);

		Core_Event::notify(get_class($this) . '.onGetItemSpecialpricesTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_aShopWarehouses = $this->_aItem_Properties = $this->_aGroup_Properties = $this->_aItem_Fields = $this->_aGroup_Fields = $this->_aShopPrices
			= $this->_aGroupBaseProperties = $this->_aCurrentData = array();

		$this->_iCurrentDataPosition = $this->_iItem_Properties_Count = $this->_iGroup_Properties_Count = $this->_iItem_Fields_Count = $this->_iGroup_Fields_Count = 0;

		return $this;
	}

	/**
	 * Execute some routine before serialization
	 * @return array
	 */
	public function __sleep()
	{
		$this->clear();

		return array_keys(
			get_object_vars($this)
		);
	}

	/**
	 * Init
	 * @return self
	 */
	public function init()
	{
		$oShop = Core_Entity::factory('Shop', $this->shopId);

		$oSite_Alias = $oShop->Site->getCurrentAlias();
		$this->_shopUrl = $oSite_Alias
			? ($oShop->Site->https ? 'https://' : 'http://') . $oSite_Alias->name . $oShop->Structure->getPath()
			: '';

		// Заполняем склады
		$this->exportStocks
			&& $this->_aShopWarehouses = $oShop->Shop_Warehouses->findAll(FALSE);

		// Заполняем дополнительные свойства товара
		$this->exportItemExternalProperties
			&& $this->_aItem_Properties = Core_Entity::factory('Shop_Item_Property_List', $this->shopId)->Properties->findAll(FALSE);

		// Заполняем дополнительные свойства групп товаров
		$this->exportGroupExternalProperties
			&& $this->_aGroup_Properties = Core_Entity::factory('Shop_Group_Property_List', $this->shopId)->Properties->findAll(FALSE);

		// Заполняем пользовательские поля товаров
		$this->exportItemFields
			&& $this->_aItem_Fields = Field_Controller::getFields('shop_item', $oShop->site_id);

		// Заполняем пользовательские поля групп товаров
		$this->exportGroupFields
			&& $this->_aGroup_Fields = Field_Controller::getFields('shop_group', $oShop->site_id);

		// Заполняем цены
		$this->exportPrices
			&& $this->_aShopPrices = $oShop->Shop_Prices->findAll(FALSE);

		// Группы
		$aGroupTitles = array_map(array($this, 'prepareCell'), $this->getGroupTitles());

		$this->_aCurrentData[$this->_iCurrentDataPosition] = $aGroupTitles;

		// Название раздела - Порядок сортировки раздела
		$this->_aGroupBaseProperties = array_pad(array(), count($aGroupTitles), '');

		// Добавляем в заголовок информацию о свойствах группы товаров
		foreach ($this->_aGroup_Properties as $oGroup_Property)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oGroup_Property->name);
			$this->_iGroup_Properties_Count++;

			if ($oGroup_Property->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_file_description', $oGroup_Property->name));
				$this->_iGroup_Properties_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_small_images', $oGroup_Property->name));
				$this->_iGroup_Properties_Count++;
			}
		}

		foreach ($this->_aGroup_Fields as $oField)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oField->name);
			$this->_iGroup_Fields_Count++;

			if ($oField->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_file_description', $oField->name));
				$this->_iGroup_Fields_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_small_images', $oField->name));
				$this->_iGroup_Fields_Count++;
			}
		}

		// Товары
		$this->guidItemPosition = count($aGroupTitles) + $this->_iGroup_Properties_Count + $this->_iGroup_Fields_Count;

		$aItemTitles = array_map(array($this, 'prepareCell'), $this->getItemTitles());
		$aItemSpecialpricesTitles = array_map(array($this, 'prepareCell'), $this->getItemSpecialpricesTitles());

		// CML ID идентификатор товара - Ярлыки
		$this->_aItemBaseProperties = array_pad(array(), count($aItemTitles), '');

		$this->_aSpecialPriceBaseProperties = array_pad(array(), count($aItemSpecialpricesTitles), '');

		// 0-вая строка - заголовок CSV-файла
		$this->_aCurrentData[$this->_iCurrentDataPosition] = array_merge(
			$this->_aCurrentData[$this->_iCurrentDataPosition],
			$aItemTitles,
			$aItemSpecialpricesTitles
		);

		// Добавляем в заголовок информацию о свойствах товара
		foreach ($this->_aItem_Properties as $oProperty)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oProperty->name);
			$this->_iItem_Properties_Count++;

			if ($oProperty->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_file_description', $oProperty->name));
				$this->_iItem_Properties_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_small_images', $oProperty->name));
				$this->_iItem_Properties_Count++;
			}
		}

		foreach ($this->_aItem_Fields as $oField)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oField->name);
			$this->_iItem_Fields_Count++;

			if ($oField->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_file_description', $oField->name));
				$this->_iItem_Fields_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Shop_Item.import_small_images', $oField->name));
				$this->_iItem_Fields_Count++;
			}
		}

		// Добавляем в заголовок информацию о складах
		foreach ($this->_aShopWarehouses as $oWarehouse)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = Core::_('Shop_Item.warehouse_import_field', $this->prepareString($oWarehouse->name));
		}

		// Добавляем информацию о ценах на группы пользователя
		foreach ($this->_aShopPrices as $oShopPrice)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $oShopPrice->name;
		}

		return $this;
	}

	/**
	 * Get Full Shop Item Data
	 * @param object $oShopItem
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterGetItemData
	 */
	public function getItemData($oShopItem)
	{
		$aGroupData = $this->_aGroupBaseProperties;

		$oShop_Group = $oShopItem->shop_group_id
			? Core_Entity::factory('Shop_Group', $oShopItem->shop_group_id)
			: NULL;

		//!is_null($oShop_Group) && $aGroupData[0] = $oShop_Group->name;

		$aGroupData[1] = is_null($oShop_Group)
			? 'ID00000000'
			: $oShop_Group->guid;

		$result = array_merge(
			$aGroupData,
			array_pad(array(), $this->_iGroup_Properties_Count, ''),
			array_pad(array(), $this->_iGroup_Fields_Count, ''),
			$this->getItemBasicData($oShopItem),
			$this->_aSpecialPriceBaseProperties,
			$this->getPropertiesData($this->_aItem_Properties, $oShopItem),
			$this->getFieldsData($this->_aItem_Fields, $oShopItem),
			$this->getWarehouseItems($oShopItem),
			$this->getPrices($oShopItem)
		);

		Core_Event::notify(get_class($this) . '.onAfterGetItemData', $this, array($result, $oShopItem));

		if (!is_null(Core_Event::getLastReturn()))
		{
			$result = Core_Event::getLastReturn();
		}

		return $result;
	}

	/**
	 * Get Basic Item Data
	 * @param object $oShopItem
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterItemBasicData
	 */
	public function getItemBasicData($oShopItem)
	{
		// Метки
		if (Core::moduleIsActive('tag'))
		{
			$aTmpTags = array();

			$aTags = $oShopItem->Tags->findAll(FALSE);
			foreach ($aTags as $oTag)
			{
				$aTmpTags[] = $oTag->name;
			}

			$sTags = $this->prepareString(implode(',', $aTmpTags));
			unset($aTags);
			unset($aTmpTags);
		}
		else
		{
			$sTags = '';
		}

		// Ярлыки
		$aTmpShortcuts = array();

		if ($this->exportItemShortcuts)
		{
			$aShortcuts = $oShopItem->Shop_Items->findAll(FALSE);
			foreach ($aShortcuts as $oShortcut_Item)
			{
				$aTmpShortcuts[] = $oShortcut_Item->shop_group_id
					? $oShortcut_Item->Shop_Group->guid
					: 0;
				$oShortcut_Item->clear();
			}
			unset($aShortcuts);
		}

		// Штрихкоды
		$aTmpBarcodes = array();

		$aShop_Item_Barcodes = $oShopItem->Shop_Item_Barcodes->findAll(FALSE);
		foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
		{
			$aTmpBarcodes[] = $oShop_Item_Barcode->value;
			$oShop_Item_Barcode->clear();
		}
		unset($aShop_Item_Barcodes);

		// Наборы
		$aTmpSets = array();

		if ($oShopItem->type == 3)
		{
			$aShop_Item_Sets = $oShopItem->Shop_Item_Sets->findAll(FALSE);
			foreach ($aShop_Item_Sets as $oShop_Item_Set)
			{
				$aTmpSets[] = $oShop_Item_Set->Shop_Item->guid;
				$oShop_Item_Set->clear();
			}
			unset($aShop_Item_Sets);
		}

		// Вкладки
		$aTmpTabs = array();

		$aShop_Tab_Items = $oShopItem->Shop_Tab_Items->findAll(FALSE);
		foreach ($aShop_Tab_Items as $oShop_Tab_Item)
		{
			$aTmpTabs[] = $oShop_Tab_Item->Shop_Tab->name;
			$oShop_Tab_Item->clear();
		}
		unset($aShop_Tab_Items);

		$aTmpAssociateds = array();
		$aShop_Item_Associateds = $oShopItem->Item_Associateds->findAll(FALSE);
		foreach ($aShop_Item_Associateds as $oShop_Item_Associated_Original)
		{
			$oShop_Item_Associated_Original->shortcut_id
				&& $oShop_Item_Associated_Original = Core_Entity::factory('Shop_Item', $oShop_Item_Associated_Original->shortcut_id);

			if ($oShop_Item_Associated_Original->id != $oShopItem->id && $oShop_Item_Associated_Original->marking != '')
			{
				$aTmpAssociateds[] = $oShop_Item_Associated_Original->marking;
				$oShop_Item_Associated_Original->clear();
			}
		}
		unset($aShop_Item_Associateds);

		$result = array(
			$this->prepareCell($oShopItem->guid),
			sprintf('"%s"', $oShopItem->id),
			$this->prepareCell($oShopItem->marking),
			$oShopItem->modification_id
				? $this->prepareCell($oShopItem->Modification->marking)
				: '',
			$oShopItem->modification_id
				? $this->prepareCell($oShopItem->Modification->guid)
				: '',
			$this->prepareCell($oShopItem->name),
			$this->prepareCell($oShopItem->description),
			$this->prepareCell($oShopItem->text),
			sprintf('"%s"', $this->prepareFloat($oShopItem->weight)),
			sprintf('"%s"', $this->prepareFloat($oShopItem->length)),
			sprintf('"%s"', $this->prepareFloat($oShopItem->width)),
			sprintf('"%s"', $this->prepareFloat($oShopItem->height)),
			sprintf('"%s"', $this->prepareFloat($oShopItem->min_quantity)),
			sprintf('"%s"', $this->prepareFloat($oShopItem->max_quantity)),
			sprintf('"%s"', $this->prepareFloat($oShopItem->quantity_step)),
			sprintf('"%s"', $oShopItem->type),
			sprintf('"%s"', $sTags),
			sprintf('"%s"', $this->prepareFloat($oShopItem->price)),
			sprintf('"%s"', $oShopItem->active),
			sprintf('"%s"', $oShopItem->sorting),
			$this->prepareCell($oShopItem->path),
			$this->prepareCell($this->_shopUrl . $oShopItem->getPath()),
			sprintf('"%s"', $oShopItem->shop_tax_id),
			sprintf('"%s"', $oShopItem->shop_currency_id),
			$oShopItem->shop_seller_id
				? $this->prepareCell($oShopItem->Shop_Seller->name)
				: '',
			$oShopItem->shop_producer_id
				? $this->prepareCell($oShopItem->Shop_Producer->name)
				: '',
			$oShopItem->shop_measure_id
				? $this->prepareCell($oShopItem->Shop_Measure->name)
				: '',
			$this->prepareCell($oShopItem->seo_title),
			$this->prepareCell($oShopItem->seo_description),
			$this->prepareCell($oShopItem->seo_keywords),
			$this->prepareCell($oShopItem->indexing),
			sprintf('"%s"', $oShopItem->yandex_market),
			sprintf('"%s"', $oShopItem->yandex_market_bid),
			sprintf('"%s"', $oShopItem->yandex_market_cid),
			sprintf('"%s"', $oShopItem->vendorcode),
			sprintf('"%s"', $oShopItem->datetime == '0000-00-00 00:00:00'
				? '0000-00-00 00:00:00'
				: Core_Date::sql2datetime($oShopItem->datetime)
			),
			sprintf('"%s"', $oShopItem->start_datetime == '0000-00-00 00:00:00'
				? '0000-00-00 00:00:00'
				: Core_Date::sql2datetime($oShopItem->start_datetime)
			),
			sprintf('"%s"', $oShopItem->end_datetime == '0000-00-00 00:00:00'
				? '0000-00-00 00:00:00'
				: Core_Date::sql2datetime($oShopItem->end_datetime)
			),
			sprintf('"%s"', $oShopItem->image_large == '' ? '' : $oShopItem->getLargeFileHref()),
			sprintf('"%s"', $oShopItem->image_small == '' ? '' : $oShopItem->getSmallFileHref()),
			sprintf('"%s"', implode(',', $aTmpShortcuts)),
			sprintf('"%s"', implode(',', $aTmpBarcodes)),
			sprintf('"%s"', implode(',', $aTmpSets)),
			sprintf('"%s"', implode(',', $aTmpTabs)),
			sprintf('"%s"', implode(',', $aTmpAssociateds)),
			sprintf('"%s"', $oShopItem->siteuser_id),
			sprintf('"%s"', $oShopItem->yandex_market_sales_notes)
		);

		Core_Event::notify(get_class($this) . '.onAfterItemBasicData', $this, array($result, $oShopItem));

		if (!is_null(Core_Event::getLastReturn()))
		{
			$result = Core_Event::getLastReturn();
		}

		return $result;
	}

	/**
	 * Get block of Item/Group Property values
	 * @param array $aProperties
	 * @param object $object
	 * @return array
	 */
	public function getPropertiesData(array $aProperties, $object)
	{
		$aRow = array();

		foreach ($aProperties as $oProperty)
		{
			$oProperty_Value = isset($this->_cachePropertyValues[$object->id][$oProperty->id]) && is_array($this->_cachePropertyValues[$object->id][$oProperty->id])
				? array_shift($this->_cachePropertyValues[$object->id][$oProperty->id])
				: NULL;

			$aRow[] = $this->prepareCell(
				$oProperty_Value
					? $this->_getPropertyValue($oProperty, $oProperty_Value, $object)
					: ''
			);

			if ($oProperty->type == 2)
			{
				$aRow[] = $oProperty_Value
					? $this->prepareCell($oProperty_Value->file_description)
					: '';

				$aRow[] = $oProperty_Value
					? ($oProperty_Value->file_small == ''
						? ''
						: $this->prepareCell($oProperty_Value->getSmallFileHref())
					)
					: '';
			}

			$oProperty_Value && $oProperty_Value->clear();

			// Удаляем пустой массив для свойств, чтобы определить, что значения закончились
			if (isset($this->_cachePropertyValues[$object->id][$oProperty->id]) && !count($this->_cachePropertyValues[$object->id][$oProperty->id]))
			{
				unset($this->_cachePropertyValues[$object->id][$oProperty->id]);
			}
		}

		return $aRow;
	}

	/**
	 * Get block of Item/Group Property values
	 * @param array $aFields
	 * @param object $object
	 * @return array
	 */
	public function getFieldsData(array $aFields, $object)
	{
		$aRow = array();

		foreach ($aFields as $oField)
		{
			$oField_Value = isset($this->_cacheFieldValues[$object->id][$oField->id]) && is_array($this->_cacheFieldValues[$object->id][$oField->id])
				? array_shift($this->_cacheFieldValues[$object->id][$oField->id])
				: NULL;

			$aRow[] = $this->prepareCell(
				$oField_Value
					? $this->_getFieldValue($oField, $oField_Value, $object)
					: ''
			);

			if ($oField->type == 2)
			{
				$aRow[] = $oField_Value
					? $this->prepareCell($oField_Value->file_description)
					: '';

				$aRow[] = $oField_Value
					? ($oField_Value->file_small == ''
						? ''
						: $this->prepareCell($oField_Value->getSmallFileHref())
					)
					: '';
			}

			$oField_Value && $oField_Value->clear();

			// Удаляем пустой массив для свойств, чтобы определить, что значения закончились
			if (isset($this->_cacheFieldValues[$object->id][$oField->id]) && !count($this->_cacheFieldValues[$object->id][$oField->id]))
			{
				unset($this->_cacheFieldValues[$object->id][$oField->id]);
			}
		}

		return $aRow;
	}

	/**
	 * Get waregouses
	 * @param Shop_Item_Model $oShop_Item
	 * @return array
	 */
	public function getWarehouseItems($oShop_Item)
	{
		$aWarehouses = array();

		foreach ($this->_aShopWarehouses as $oWarehouse)
		{
			$oShop_Warehouse_Item = $oShop_Item->Shop_Warehouse_Items->getByWarehouseId($oWarehouse->id, FALSE);
			$aWarehouses[] = !is_null($oShop_Warehouse_Item) ? $oShop_Warehouse_Item->count : 0;
		}

		return $aWarehouses;
	}

	/**
	 * Get prices
	 * @param Shop_Item_Model $oShop_Item
	 * @return array
	 */
	public function getPrices($oShop_Item)
	{
		$aShopPrices = array();

		foreach ($this->_aShopPrices as $oShopPrice)
		{
			$oShop_Price = $oShop_Item->Shop_Item_Prices->getByPriceId($oShopPrice->id, FALSE);
			$aShopPrices[] = !is_null($oShop_Price) ? $oShop_Price->value : 0;
		}

		return $aShopPrices;
	}

	/**
	 * Export special prices data for item
	 * @param Shop_Item $oShopItem item
	 */
	public function exportSpecialPriceData($oShopItem)
	{
		// Получаем список специальных цен товара
		$aShop_Specialprices = $oShopItem->Shop_Specialprices->findAll(FALSE);

		$aTmpArray = array_merge(
			$this->_aGroupBaseProperties,
			array_pad(array(), $this->_iGroup_Properties_Count, ''),
			array_pad(array(), $this->_iGroup_Fields_Count, ''),
			$this->_aItemBaseProperties
		);

		// CML ID ТОВАРА
		$aTmpArray[$this->guidItemPosition] = $oShopItem->guid;

		foreach ($aShop_Specialprices as $oShop_Specialprice)
		{
			$this->_printRow(
				array_merge($aTmpArray, array(
					$oShop_Specialprice->min_quantity,
					$oShop_Specialprice->max_quantity,
					$oShop_Specialprice->price,
					$oShop_Specialprice->percent
				))
			);

			$oShop_Specialprice->clear();
		}

		return $this;
	}

	/**
	 * Array of titile line
	 * @var array
	 */
	protected $_aCurrentRow = array();

	/**
	 * Get Current Row
	 * @return array
	 */
	public function getCurrentRow()
	{
		return $this->_aCurrentRow;
	}

	/**
	 * Set Current Row
	 * @param array $array
	 * @return self
	 */
	public function setCurrentRow(array $array)
	{
		$this->_aCurrentRow = $array;
		return $this;
	}

	protected $_shopGroups = NULL;

	/**
	 * Get Basic Group Data
	 * @param object $oShopGroup
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterGroupBasicData
	 */
	public function getGroupBasicData($oShopGroup)
	{
		$result = array(
			$this->prepareCell($oShopGroup->name),
			$this->prepareCell($oShopGroup->guid),
			$this->prepareCell(is_null($oShopGroup->Shop_Group->id) ? 'ID00000000' : $oShopGroup->Shop_Group->guid),
			$this->prepareCell($oShopGroup->seo_title),
			$this->prepareCell($oShopGroup->seo_description),
			$this->prepareCell($oShopGroup->seo_keywords),
			$this->prepareCell($oShopGroup->description),
			$this->prepareCell($oShopGroup->path),
			sprintf('"%s"', ($oShopGroup->image_large == '') ? '' : $oShopGroup->getLargeFileHref()),
			sprintf('"%s"', ($oShopGroup->image_small == '') ? '' : $oShopGroup->getSmallFileHref()),
			$this->prepareCell($oShopGroup->sorting),

			$this->prepareCell($oShopGroup->seo_group_title_template),
			$this->prepareCell($oShopGroup->seo_group_keywords_template),
			$this->prepareCell($oShopGroup->seo_group_description_template),

			$this->prepareCell($oShopGroup->seo_item_title_template),
			$this->prepareCell($oShopGroup->seo_item_keywords_template),
			$this->prepareCell($oShopGroup->seo_item_description_template)
		);

		Core_Event::notify(get_class($this) . '.onAfterGroupBasicData', $this, array($result, $oShopGroup));

		if (!is_null(Core_Event::getLastReturn()))
		{
			$result = Core_Event::getLastReturn();
		}

		return $result;
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		$this->init();

		// Stop buffering
		//ob_get_clean();
		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		header('Cache-Control: no-cache, must-revalidate');

		// Disable Nginx cache
		header('X-Accel-Buffering: no');

		//header('Content-Type: application/force-download');
		header('Content-Encoding: identity'); // вместо none
		header('Content-Type: text/csv; charset=utf-8');

		// Автоматический сброс буфера при каждом выводе
		ob_implicit_flush(TRUE);

		$oShop = Core_Entity::factory('Shop', $this->shopId);

		if (is_null($this->lastGroupId))
		{
			// Выбор групп для показа
			if ($this->parentGroup == 0)
			{
				$oShop_Groups = $oShop->Shop_Groups;
				$oShop_Groups->queryBuilder()
					->where('shop_groups.parent_id', '=', 0);
			}
			else
			{
				$oShop_Groups = Core_Entity::factory('Shop_Group', $this->parentGroup)->Shop_Groups;
			}

			$oShop_Groups->queryBuilder()
				->where('shop_groups.shortcut_id', '=', 0);

			$aShopGroupsId = $this->_shopGroups = array_merge(array($this->parentGroup), $oShop_Groups->getGroupChildrenId(FALSE));
		}
		// Пошаговый экспорт в файл на сервере
		else
		{
			// Skip first row
			$this->_aCurrentData = array();

			$key = array_search($this->lastGroupId, $this->_shopGroups);
			//echo 'Key:', $key, ', total groups: '. count($this->_shopGroups);

			if ($key !== FALSE)
			{
				$aShopGroupsId = array_slice($this->_shopGroups, $key + 1);
			}
			else
			{
				echo "Wrong lastGroupId!";
				die();
			}
		}

		if (!$this->exportToFile)
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/force-download');
			header('Content-Transfer-Encoding: binary');
			header("Content-Disposition: attachment; filename = {$this->fileName};");

			// Дополнительные настройки для потоковой передачи
			if (function_exists('apache_setenv'))
			{
				@apache_setenv('no-gzip', 1);
			}
			@ini_set('output_buffering', 'off');
			@ini_set('zlib.output_compression', 0);
			@ini_set('implicit_flush', 1);
		}
		
		if (is_null($this->lastGroupId))
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write("Upload to file {$this->fileName} started");

			foreach ($this->_aCurrentData as $aData)
			{
				$this->_printRow($aData);
			}
		}

		foreach ($aShopGroupsId as $iShopGroupId)
		{
			$oShopGroup = Core_Entity::factory('Shop_Group', $iShopGroupId);

			$oShop_Items = $oShopGroup->Shop_Items;
			$oShop_Items
				->queryBuilder()
				->where('shop_items.shop_id', '=', $this->shopId)
				->where('shop_items.modification_id', '=', 0)
				->where('shop_items.shortcut_id', '=', 0)
				->clearOrderBy()
				->orderBy('shop_items.id', 'ASC');

			$this->producer
				&& $oShop_Items->queryBuilder()->where('shop_items.shop_producer_id', '=', $this->producer);

			$this->seller
				&& $oShop_Items->queryBuilder()->where('shop_items.shop_seller_id', '=', $this->seller);

			if ($this->exportInStock)
			{
				$this->applyInStockConditions($oShop_Items);
			}

			if ($iShopGroupId != 0)
			{
				// Кэш всех значений свойств группы
				$this->_cachePropertyValues[$oShopGroup->id] = array();
				foreach ($this->_aGroup_Properties as $oProperty)
				{
					$this->_cachePropertyValues[$oShopGroup->id][$oProperty->id] = $oProperty->getValues($oShopGroup->id, FALSE);
				}

				$this->_cacheFieldValues[$oShopGroup->id] = array();
				foreach ($this->_aGroup_Fields as $oField)
				{
					$this->_cacheFieldValues[$oShopGroup->id][$oField->id] = $oField->getValues($oShopGroup->id, FALSE);
				}

				$aBasicGroupData = $this->getGroupBasicData($oShopGroup);

				$aTmpArray = array_merge(
					$aBasicGroupData,
					$this->getPropertiesData($this->_aGroup_Properties, $oShopGroup),
					$this->getFieldsData($this->_aGroup_Fields, $oShopGroup),
					$this->_aItemBaseProperties,
					$this->_aSpecialPriceBaseProperties
				);

				// Пропускаем поля дополнительных свойств товара
				for ($i = 0; $i < $this->_iItem_Properties_Count; $i++)
				{
					$aTmpArray[] = "";
				}

				// Пропускаем поля пользовательских полей товара
				for ($i = 0; $i < $this->_iItem_Fields_Count; $i++)
				{
					$aTmpArray[] = "";
				}

				$this->_printRow($aTmpArray);

				$iPropertyOffset = count($aBasicGroupData);

				// Оставшиеся множественные значения свойств
				while (count($this->_cachePropertyValues[$oShopGroup->id]))
				{
					$aCurrentPropertyLine = array_fill(0, $iPropertyOffset, '""');

					// CML ID группы
					$aCurrentPropertyLine[$this->guidGroupPosition] = $oShopGroup->guid;

					$aCurrentPropertyLine = array_merge($aCurrentPropertyLine, $this->getPropertiesData($this->_aGroup_Properties, $oShopGroup));
					$this->_printRow($aCurrentPropertyLine);
				}
				unset($this->_cachePropertyValues[$oShopGroup->id]);

				// Оставшиеся множественные значения полей
				while (count($this->_cacheFieldValues[$oShopGroup->id]))
				{
					$aCurrentFieldLine = array_fill(0, $iPropertyOffset + $this->_iGroup_Properties_Count, '""');

					// CML ID группы
					$aCurrentFieldLine[$this->guidGroupPosition] = $oShopGroup->guid;

					$aCurrentFieldLine = array_merge($aCurrentFieldLine, $this->getFieldsData($this->_aGroup_Fields, $oShopGroup));
					$this->_printRow($aCurrentFieldLine);
				}
				unset($this->_cacheFieldValues[$oShopGroup->id]);
			}

			$iPropertyOffset = count($this->_aGroupBaseProperties)
				+ $this->_iGroup_Properties_Count
				+ $this->_iGroup_Fields_Count
				+ count($this->_aItemBaseProperties)
				+ count($this->_aSpecialPriceBaseProperties);

			$offset = 0;
			$limit = 500;

			if (strlen($this->startItemDate) && strlen($this->endItemDate))
			{
				$sStartDate = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($this->startItemDate . " 00:00:00"));
				$sEndDate = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($this->endItemDate . " 23:59:59"));
			}
			else
			{
				$sStartDate = $sEndDate = NULL;
			}

			do {
				if (!is_null($sStartDate) && !is_null($sEndDate))
				{
					$oShop_Items
						->queryBuilder()
						->where('shop_items.datetime', 'BETWEEN', array($sStartDate, $sEndDate));
				}

				$oShop_Items
					->queryBuilder()
					->offset($offset)
					->limit($limit);

				$aShop_Items = $oShop_Items->findAll(FALSE);

				foreach ($aShop_Items as $key => $oShopItem)
				{
					// Set GUID
					if ($oShopItem->guid == '')
					{
						$oShopItem->guid = Core_Guid::get();
						$oShopItem->save();
					}

					// Кэш всех значений свойств товара
					$this->_cachePropertyValues[$oShopItem->id] = array();
					foreach ($this->_aItem_Properties as $oProperty)
					{
						$this->_cachePropertyValues[$oShopItem->id][$oProperty->id] = $oProperty->getValues($oShopItem->id, FALSE);
					}

					$this->_cacheFieldValues[$oShopItem->id] = array();
					foreach ($this->_aItem_Fields as $oField)
					{
						$this->_cacheFieldValues[$oShopItem->id][$oField->id] = $oField->getValues($oShopItem->id, FALSE);
					}

					// Строка с основными данными о товаре
					$this->_printRow($this->getItemData($oShopItem));

					// Оставшиеся множественные значения свойств
					while (count($this->_cachePropertyValues[$oShopItem->id]))
					{
						$aCurrentPropertyLine = array_fill(0, $iPropertyOffset, '""');

						// CML ID ТОВАРА
						$aCurrentPropertyLine[$this->guidItemPosition] = $oShopItem->guid;

						$aCurrentPropertyLine = array_merge($aCurrentPropertyLine, $this->getPropertiesData($this->_aItem_Properties, $oShopItem));
						$this->_printRow($aCurrentPropertyLine);
					}
					unset($this->_cachePropertyValues[$oShopItem->id]);

					// Оставшиеся множественные значения полей
					while (count($this->_cacheFieldValues[$oShopItem->id]))
					{
						$aCurrentFieldLine = array_fill(0, $iPropertyOffset + $this->_iItem_Properties_Count, '""');

						// CML ID ТОВАРА
						$aCurrentFieldLine[$this->guidItemPosition] = $oShopItem->guid;

						$aCurrentFieldLine = array_merge($aCurrentFieldLine, $this->getFieldsData($this->_aItem_Fields, $oShopItem));
						$this->_printRow($aCurrentFieldLine);
					}
					unset($this->_cacheFieldValues[$oShopItem->id]);

					// Выгружается отдельными строками
					$this->exportSpecialPriceData($oShopItem);

					// Получаем список всех модификаций
					if ($this->exportItemModifications)
					{
						$oModifications = Core_Entity::factory('Shop_Item');
						$oModifications->queryBuilder()
							->where('shop_items.modification_id', '=', $oShopItem->id);

						$this->producer
							&& $oModifications->queryBuilder()->where('shop_items.shop_producer_id', '=', $this->producer);

						$this->seller
							&& $oModifications->queryBuilder()->where('shop_items.shop_seller_id', '=', $this->seller);

						if ($this->exportInStock)
						{
							$this->applyInStockConditions($oModifications);
						}

						$aModifications = $oModifications->findAll(FALSE);

						// Добавляем информацию о модификациях
						foreach ($aModifications as $oModification)
						{
							// Кэш всех значений свойств товара
							$this->_cachePropertyValues[$oModification->id] = array();
							foreach ($this->_aItem_Properties as $oProperty)
							{
								$this->_cachePropertyValues[$oModification->id][$oProperty->id] = $oProperty->getValues($oModification->id, FALSE);
							}

							$this->_cacheFieldValues[$oModification->id] = array();
							foreach ($this->_aItem_Fields as $oField)
							{
								$this->_cacheFieldValues[$oModification->id][$oField->id] = $oField->getValues($oModification->id, FALSE);
							}

							// Строка с основными данными о модификаций
							$this->_printRow($this->getItemData($oModification));

							// Оставшиеся множественные значения свойств
							while (count($this->_cachePropertyValues[$oModification->id]))
							{
								$aCurrentPropertyLine = array_fill(0, $iPropertyOffset, '""');

								// CML ID ТОВАРА
								$aCurrentPropertyLine[$this->guidItemPosition] = $oModification->guid;

								$aCurrentPropertyLine = array_merge($aCurrentPropertyLine, $this->getPropertiesData($this->_aItem_Properties, $oModification));
								$this->_printRow($aCurrentPropertyLine);
							}
							unset($this->_cachePropertyValues[$oModification->id]);

							// Оставшиеся множественные значения полей
							while (count($this->_cacheFieldValues[$oModification->id]))
							{
								$aCurrentFieldLine = array_fill(0, $iPropertyOffset + $this->_iItem_Properties_Count, '""');

								// CML ID ТОВАРА
								$aCurrentFieldLine[$this->guidItemPosition] = $oModification->guid;

								$aCurrentFieldLine = array_merge($aCurrentFieldLine, $this->getFieldsData($this->_aItem_Fields, $oModification));
								$this->_printRow($aCurrentFieldLine);
							}
							unset($this->_cacheFieldValues[$oModification->id]);

							$oModification->clear();
						}

						unset($aModifications);
					}

					$oShopItem->clear();
				}

				Core_ObjectWatcher::instance()->reduce();

				$offset += $limit;
			}
			while (count($aShop_Items));

			if ($this->exportToFile)
			{
				$this->lastGroupId = $iShopGroupId;
				break;
			}
		}

		$this->_finish();

		// Выгрузка в браузер или выгрузка в файл на сервер и группы закончились
		if (!$this->exportToFile || count($aShopGroupsId) == 1)
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write("Upload to csv-file {$this->fileName} completed");
		}

		// Выгрузка в файл на сервер
		if ($this->exportToFile)
		{
			// Экспорт не завершен
			if (count($aShopGroupsId) == 1)
			{
				@chmod(CMS_FOLDER . TMP_DIR . $this->fileName, CHMOD_FILE);

				// Останавливаем редиректы
				$this->lastGroupId = NULL;
			}
		}
	}

	/**
	 * Apply in stock conditions
	 * @param object $oShop_Items
	 * @return self
	 */
	public function applyInStockConditions($oShop_Items)
	{
		$oShop_Items
			->queryBuilder()
			->select('shop_items.*')
			->join('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
			->join('shop_warehouses', 'shop_warehouses.id', '=', 'shop_warehouse_items.shop_warehouse_id')
			->where('shop_warehouses.active', '=', 1)
			->where('shop_warehouses.deleted', '=', 0)
			->groupBy('shop_items.id')
			->having(Core_QueryBuilder::expression('SUM(shop_warehouse_items.count)'), '>', 0);

		return $this;
	}
}