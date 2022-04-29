<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Export_Csv_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'separator',
		'encoding',
		'parentGroup',
		'exportItemExternalProperties',
		'exportGroupExternalProperties',
		'exportItemModifications',
		'exportItemShortcuts',
		'exportOrders',
		'producer',
		'seller',
		'shopId',
		'startOrderDate',
		'endOrderDate',
		'startItemDate',
		'endItemDate'
	);

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
	 * Item properties count
	 * Требуется хранить количество свойств отдельно, т.к. количество полей файла CSV для свойств не равно количеству свойств (из-за файлов)
	 * @var int
	 */
	private $_iItem_Properties_Count;

	/**
	 * Group properties count
	 * @var int
	 */
	private $_iGroup_Properties_Count;

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

	protected $_shopUrl = NULL;

	/**
	 * Constructor.
	 * @param int $iShopId shop ID
	 */
	public function __construct($iShopId)
	{
		parent::__construct();

		$this->shopId = $iShopId;

		$this->_iItem_Properties_Count = $this->_iGroup_Properties_Count = 0;

		// Устанавливаем лимит времени выполнения в 1 час
		(!defined('DENY_INI_SET') || !DENY_INI_SET)
			&& function_exists('set_time_limit') && ini_get('safe_mode') != 1 && @set_time_limit(3600);
	}

	/**
	 * Get Group Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetGroupTitles
	 */
	public function getGroupTitles()
	{
		$return = array(
			'"' . Core::_('Shop_Exchange.group_name') . '"',
			'"' . Core::_('Shop_Exchange.group_guid') . '"',
			'"' . Core::_('Shop_Exchange.parent_group_guid') . '"',
			'"' . Core::_('Shop_Exchange.group_seo_title') . '"',
			'"' . Core::_('Shop_Exchange.group_seo_description') . '"',
			'"' . Core::_('Shop_Exchange.group_seo_keywords') . '"',
			'"' . Core::_('Shop_Exchange.group_description') . '"',
			'"' . Core::_('Shop_Exchange.group_path') . '"',
			'"' . Core::_('Shop_Exchange.group_image_large') . '"',
			'"' . Core::_('Shop_Exchange.group_image_small') . '"',
			'"' . Core::_('Shop_Exchange.group_sorting') . '"'
		);

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
			'"' . Core::_('Shop_Exchange.item_guid') . '"',
			'"' . Core::_('Shop_Exchange.item_id') . '"',
			'"' . Core::_('Shop_Exchange.item_marking') . '"',
			'"' . Core::_('Shop_Exchange.item_parent_marking') . '"',
			'"' . Core::_('Shop_Exchange.item_parent_guid') . '"',
			'"' . Core::_('Shop_Exchange.item_name') . '"',
			'"' . Core::_('Shop_Exchange.item_description') . '"',
			'"' . Core::_('Shop_Exchange.item_text') . '"',
			'"' . Core::_('Shop_Exchange.item_weight') . '"',
			'"' . Core::_('Shop_Exchange.item_length') . '"',
			'"' . Core::_('Shop_Exchange.item_width') . '"',
			'"' . Core::_('Shop_Exchange.item_height') . '"',
			'"' . Core::_('Shop_Exchange.item_min_quantity') . '"',
			'"' . Core::_('Shop_Exchange.item_max_quantity') . '"',
			'"' . Core::_('Shop_Exchange.item_quantity_step') . '"',
			'"' . Core::_('Shop_Exchange.item_type') . '"',
			'"' . Core::_('Shop_Exchange.item_tags') . '"',
			'"' . Core::_('Shop_Exchange.item_price') . '"',
			'"' . Core::_('Shop_Exchange.item_active') . '"',
			'"' . Core::_('Shop_Exchange.item_sorting') . '"',
			'"' . Core::_('Shop_Exchange.item_path') . '"',
			'"' . Core::_('Shop_Exchange.item_full_path') . '"',
			'"' . Core::_('Shop_Exchange.tax_id') . '"',
			'"' . Core::_('Shop_Exchange.currency_id') . '"',
			'"' . Core::_('Shop_Exchange.seller_name') . '"',
			'"' . Core::_('Shop_Exchange.producer_name') . '"',
			'"' . Core::_('Shop_Exchange.measure_value') . '"',
			'"' . Core::_('Shop_Exchange.item_seo_title') . '"',
			'"' . Core::_('Shop_Exchange.item_seo_description') . '"',
			'"' . Core::_('Shop_Exchange.item_seo_keywords') . '"',
			'"' . Core::_('Shop_Exchange.item_indexing') . '"',
			'"' . Core::_('Shop_Exchange.item_yandex_market') . '"',
			'"' . Core::_('Shop_Exchange.item_yandex_market_bid') . '"',
			'"' . Core::_('Shop_Exchange.item_yandex_market_cid') . '"',
			'"' . Core::_('Shop_Exchange.item_yandex_vendorcode') . '"',
			'"' . Core::_('Shop_Exchange.item_datetime') . '"',
			'"' . Core::_('Shop_Exchange.item_start_datetime') . '"',
			'"' . Core::_('Shop_Exchange.item_end_datetime') . '"',
			'"' . Core::_('Shop_Exchange.item_image_large') . '"',
			'"' . Core::_('Shop_Exchange.item_image_small') . '"',
			'"' . Core::_('Shop_Exchange.item_additional_group') . '"',
			'"' . Core::_('Shop_Exchange.item_barcode') . '"',
			'"' . Core::_('Shop_Exchange.item_sets_guid') . '"',
			'"' . Core::_('Shop_Exchange.item_tabs') . '"',
			'"' . Core::_('Shop_Exchange.siteuser_id') . '"',
			'"' . Core::_('Shop_Exchange.item_yandex_market_sales_notes') . '"',
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
			'"' . Core::_('Shop_Exchange.specialprices_min_quantity') . '"',
			'"' . Core::_('Shop_Exchange.specialprices_max_quantity') . '"',
			'"' . Core::_('Shop_Exchange.specialprices_price') . '"',
			'"' . Core::_('Shop_Exchange.specialprices_percent') . '"',
		);

		Core_Event::notify(get_class($this) . '.onGetItemSpecialpricesTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Init
	 * @return self
	 */
	public function init()
	{
		if (!$this->exportOrders)
		{
			$oShop = Core_Entity::factory('Shop', $this->shopId);

			$oSite_Alias = $oShop->Site->getCurrentAlias();
			$this->_shopUrl = $oSite_Alias
				? ($oShop->Site->https ? 'https://' : 'http://') . $oSite_Alias->name . $oShop->Structure->getPath()
				: '';

			// Заполняем склады
			$this->_aShopWarehouses = $oShop->Shop_Warehouses->findAll(FALSE);

			// Заполняем дополнительные свойства товара
			$this->exportItemExternalProperties
				&& $this->_aItem_Properties = Core_Entity::factory('Shop_Item_Property_List', $this->shopId)->Properties->findAll(FALSE);

			// Заполняем дополнительные свойства групп товаров
			$this->exportGroupExternalProperties
				&& $this->_aGroup_Properties = Core_Entity::factory('Shop_Group_Property_List', $this->shopId)->Properties->findAll(FALSE);

			$this->_aShopPrices = $oShop->Shop_prices->findAll(FALSE);

			$this->_iCurrentDataPosition = 0;

			$aGroupTitles = $this->getGroupTitles();
			$aItemTitles = $this->getItemTitles();
			$aItemSpecialpricesTitles = $this->getItemSpecialpricesTitles();

			// Название раздела - Порядок сортировки раздела
			$this->_aGroupBaseProperties = array_pad(array(), count($aGroupTitles), '');

			// CML ID идентификатор товара - Ярлыки
			$this->_aItemBaseProperties = array_pad(array(), count($aItemTitles), '');

			$this->_aSpecialPriceBaseProperties = array_pad(array(), count($aItemSpecialpricesTitles), '');

			// 0-вая строка - заголовок CSV-файла
			$this->_aCurrentData[$this->_iCurrentDataPosition] = array_merge(
				$aGroupTitles,
				$aItemTitles,
				$aItemSpecialpricesTitles
			);

			// Добавляем в заголовок информацию о свойствах товара
			foreach ($this->_aItem_Properties as $oProperty)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString($oProperty->name));
				$this->_iItem_Properties_Count++;

				if ($oProperty->type == 2)
				{
					$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Shop_Item.import_file_description', $oProperty->name)));
					$this->_iItem_Properties_Count++;

					$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Shop_Item.import_small_images', $oProperty->name)));
					$this->_iItem_Properties_Count++;
				}
			}

			// Добавляем в заголовок информацию о свойствах группы товаров
			foreach ($this->_aGroup_Properties as $oGroup_Property)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString($oGroup_Property->name));
				$this->_iGroup_Properties_Count++;

				if ($oGroup_Property->type == 2)
				{
					$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Shop_Item.import_file_description', $oGroup_Property->name)));
					$this->_iGroup_Properties_Count++;

					$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Shop_Item.import_small_images', $oGroup_Property->name)));
					$this->_iGroup_Properties_Count++;
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

		$aGroupData[1] = is_null($oShop_Group)
			? 'ID00000000'
			: $oShop_Group->guid;

		// У товара нет необходимости дублировать данные о группе
		/*if ($oShop_Group)
		{
			$aGroupData[3] = sprintf('"%s"', $this->prepareString($oShop_Group->seo_title));
			$aGroupData[4] = sprintf('"%s"', $this->prepareString($oShop_Group->seo_description));
			$aGroupData[5] = sprintf('"%s"', $this->prepareString($oShop_Group->seo_keywords));
		}*/

		$result = array_merge($aGroupData,
			$this->getItemBasicData($oShopItem),
			$this->_aSpecialPriceBaseProperties,
			$this->getItemProperties($oShopItem),
			array_pad(array(), $this->_iGroup_Properties_Count, ''),
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

			$sTags = $this->prepareString(implode(",", $aTmpTags));
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
			}
			unset($aShop_Item_Sets);
		}

		// Вкладки
		$aTmpTabs = array();

		$aShop_Tab_Items = $oShopItem->Shop_Tab_Items->findAll(FALSE);
		foreach ($aShop_Tab_Items as $oShop_Tab_Item)
		{
			$aTmpTabs[] = $oShop_Tab_Item->Shop_Tab->name;
		}
		unset($aShop_Item_Tabs);

		$result = array(
			sprintf('"%s"', $this->prepareString($oShopItem->guid)),
			sprintf('"%s"', $oShopItem->id),
			sprintf('"%s"', $this->prepareString($oShopItem->marking)),
			sprintf('"%s"', $oShopItem->modification_id
				? $this->prepareString($oShopItem->Modification->marking)
				: ''),
			sprintf('"%s"', $oShopItem->modification_id
				? $this->prepareString($oShopItem->Modification->guid)
				: ''),
			sprintf('"%s"', $this->prepareString($oShopItem->name)),
			sprintf('"%s"', $this->prepareString($oShopItem->description)),
			sprintf('"%s"', $this->prepareString($oShopItem->text)),
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
			sprintf('"%s"', $this->prepareString($oShopItem->path)),
			sprintf('"%s"', $this->prepareString($this->_shopUrl . $oShopItem->getPath())),
			sprintf('"%s"', $oShopItem->shop_tax_id),
			sprintf('"%s"', $oShopItem->shop_currency_id),
			sprintf('"%s"', $oShopItem->shop_seller_id
				? $this->prepareString($oShopItem->Shop_Seller->name)
				: ''),
			sprintf('"%s"', $oShopItem->shop_producer_id
				? $this->prepareString($oShopItem->Shop_Producer->name)
				: ''),
			sprintf('"%s"', $oShopItem->shop_measure_id
				? $this->prepareString($oShopItem->Shop_Measure->name)
				: ''),
			sprintf('"%s"', $this->prepareString($oShopItem->seo_title)),
			sprintf('"%s"', $this->prepareString($oShopItem->seo_description)),
			sprintf('"%s"', $this->prepareString($oShopItem->seo_keywords)),
			sprintf('"%s"', $this->prepareString($oShopItem->indexing)),
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
			sprintf('"%s"', ($oShopItem->image_large == '') ? '' : $oShopItem->getLargeFileHref()),
			sprintf('"%s"', ($oShopItem->image_small == '') ? '' : $oShopItem->getSmallFileHref()),
			sprintf('"%s"', implode(',', $aTmpShortcuts)),
			sprintf('"%s"', implode(',', $aTmpBarcodes)),
			sprintf('"%s"', implode(',', $aTmpSets)),
			sprintf('"%s"', implode(',', $aTmpTabs)),
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

	public function getItemProperties($oShopItem)
	{
		$aItemProperties = array();

		foreach ($this->_aItem_Properties as $oProperty)
		{
			$oProperty_Value = is_array($this->_cachePropertyValues[$oShopItem->id][$oProperty->id])
				? array_shift($this->_cachePropertyValues[$oShopItem->id][$oProperty->id])
				: NULL;

			$aItemProperties[] = sprintf('"%s"', $this->prepareString(
				$oProperty_Value
					? $this->_getPropertyValue($oProperty, $oProperty_Value, $oShopItem)
					: ''
			));

			if ($oProperty->type == 2)
			{
				$aItemProperties[] = $oProperty_Value
					? sprintf('"%s"', $oProperty_Value->file_description)
					: '';

				$aItemProperties[] = $oProperty_Value
					? ($oProperty_Value->file_small == ''
						? ''
						: sprintf('"%s"', $oProperty_Value->getSmallFileHref())
					)
					: '';
			}

			$oProperty_Value && $oProperty_Value->clear();
		}

		return $aItemProperties;
	}

	public function getWarehouseItems($oShopItem)
	{
		$aWarehouses = array();

		foreach ($this->_aShopWarehouses as $oWarehouse)
		{
			$oShop_Warehouse_Item = $oShopItem->Shop_Warehouse_Items->getByWarehouseId($oWarehouse->id, FALSE);
			$aWarehouses[] = !is_null($oShop_Warehouse_Item) ? $oShop_Warehouse_Item->count : 0;
		}

		return $aWarehouses;
	}

	public function getPrices($oShopItem)
	{
		$aShopPrices = array();

		foreach ($this->_aShopPrices as $oShopPrice)
		{
			$oShop_Price = $oShopItem->Shop_Item_Prices->getByPriceId($oShopPrice->id, FALSE);
			$aShopPrices[] = !is_null($oShop_Price) ? $oShop_Price->value : 0;
		}

		return $aShopPrices;
	}

	/**
	 * Get special prices data for item
	 * @param Shop_Item $oShopItem item
	 */
	public function getSpecialPriceData($oShopItem)
	{
		// Получаем список специальных цен товара
		$aShop_Specialprices = $oShopItem->Shop_Specialprices->findAll(FALSE);

		$aTmpArray = array_merge(
			$this->_aGroupBaseProperties,
			$this->_aItemBaseProperties
		);

		// CML ID ТОВАРА
		$aTmpArray[11] = $oShopItem->guid;

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

	protected $_cacheGetListValue = array();

	protected function _getListValue($list_item_id)
	{
		if ($list_item_id && Core::moduleIsActive('list'))
		{
			if (!isset($this->_cacheGetListValue[$list_item_id]))
			{
				$oList_Item = Core_Entity::factory('List_Item')->getByid($list_item_id);

				$this->_cacheGetListValue[$list_item_id] = $oList_Item ? $oList_Item->value : '';
			}

			return $this->_cacheGetListValue[$list_item_id];
		}

		return '';
	}

	/**
	 * Get value of Property_Value
	 * @param Property_Model $oProperty
	 * @param mixed $oProperty_Value
	 * @param mixed $object
	 * @return string
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetPropertyValueDefault
	 */
	protected function _getPropertyValue($oProperty, $oProperty_Value, $object)
	{
		switch ($oProperty->type)
		{
			case 0: // Int
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
			case 7: // Checkbox
			case 10: // Hidden field
			case 11: // Float
				$result = $oProperty_Value->value;
			break;
			case 2: // File
				$result = $oProperty_Value->file == ''
					? ''
					: $oProperty_Value->setHref($object->getItemHref())->getLargeFileHref();
			break;
			case 3: // List
				$result = $this->_getListValue($oProperty_Value->value);
			break;
			case 5: // Informationsystem
				$result = $oProperty_Value->value
					? $oProperty_Value->Informationsystem_Item->name
					: '';
			break;
			case 8: // Date
				$result = Core_Date::sql2date($oProperty_Value->value);
			break;
			case 9: // Datetime
				$result = Core_Date::sql2datetime($oProperty_Value->value);
			break;
			case 12: // Shop
				$result = $oProperty_Value->value
					? $oProperty_Value->Shop_Item->name
					: '';
			break;
			default:
				$result = $oProperty_Value->value;

				Core_Event::notify(get_class($this) . '.onGetPropertyValueDefault', $this, array($oProperty, $oProperty_Value, $object));

				if (!is_null(Core_Event::getLastReturn()))
				{
					$result = Core_Event::getLastReturn();
				}
		}

		return $result;
	}

	/**
	 * Кэш значений доп. св-в
	 * @var array
	 */
	protected $_cachePropertyValues = array();

	/**
	 * Executes the business logic.
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onBeforeExportOrdersTitleProperties
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterExportOrdersTitleProperties
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onBeforeExportOrderProperties
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterExportOrderProperties
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if ($oUser->only_access_my_own)
		{
			return FALSE;
		}

		$this->init();

		$sFilename = 'CSV_' . date("Y_m_d_H_i_s") . '.csv';

		// Stop buffering
		ob_get_clean();

		header("Cache-Control: no-cache, must-revalidate");
		header('X-Accel-Buffering: no');

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . $sFilename . ";");
		header("Content-Transfer-Encoding: binary");

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Begin CSV export ' . $sFilename);

		$oShop = Core_Entity::factory('Shop', $this->shopId);

		if (!$this->exportOrders)
		{
			foreach ($this->_aCurrentData as $aData)
			{
				$this->_printRow($aData);
			}
			$this->_aCurrentData = array();

			if ($this->parentGroup == 0)
			{
				$oShop_Groups = $oShop->Shop_Groups;
				$oShop_Groups->queryBuilder()
					->where('parent_id', '=', 0);
			}
			else
			{
				$oShop_Groups = Core_Entity::factory('Shop_Group', $this->parentGroup)->Shop_Groups;
			}

			$oShop_Groups->queryBuilder()
				->where('shortcut_id', '=', 0);

			$aShopGroupsId = array_merge(array($this->parentGroup), $oShop_Groups->getGroupChildrenId(FALSE));

			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('CSV export, groups count = ' . count($aShopGroupsId));

			foreach ($aShopGroupsId as $iShopGroupId)
			{
				$oShopGroup = Core_Entity::factory('Shop_Group', $iShopGroupId);

				$oShopItems = $oShopGroup->Shop_Items;
				$oShopItems
					->queryBuilder()
					->where('modification_id', '=', 0)
					->where('shortcut_id', '=', 0)
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$this->producer
					&& $oShopItems->queryBuilder()->where('shop_producer_id', '=', $this->producer);

				$this->seller
					&& $oShopItems->queryBuilder()->where('shop_seller_id', '=', $this->seller);

				if ($iShopGroupId != 0)
				{
					$aTmpArray = array(
						sprintf('"%s"', $this->prepareString($oShopGroup->name)),
						sprintf('"%s"', $this->prepareString($oShopGroup->guid)),
						sprintf('"%s"', $this->prepareString(is_null($oShopGroup->Shop_Group->id) ? 'ID00000000' : $oShopGroup->Shop_Group->guid)),
						sprintf('"%s"', $this->prepareString($oShopGroup->seo_title)),
						sprintf('"%s"', $this->prepareString($oShopGroup->seo_description)),
						sprintf('"%s"', $this->prepareString($oShopGroup->seo_keywords)),
						sprintf('"%s"', $this->prepareString($oShopGroup->description)),
						sprintf('"%s"', $this->prepareString($oShopGroup->path)),
						sprintf('"%s"', ($oShopGroup->image_large == '') ? '' : $oShopGroup->getLargeFileHref()),
						sprintf('"%s"', ($oShopGroup->image_small == '') ? '' : $oShopGroup->getSmallFileHref()),
						sprintf('"%s"', $this->prepareString($oShopGroup->sorting))
					);

					// Пропускаем поля товара
					foreach ($this->_aItemBaseProperties as $sNullData)
					{
						$aTmpArray[] = $sNullData;
					}

					// Пропускаем поля специальных цен товара
					foreach ($this->_aSpecialPriceBaseProperties as $sNullData)
					{
						$aTmpArray[] = $sNullData;
					}

					// Пропускаем поля дополнительных свойств товара
					for ($i = 0; $i < $this->_iItem_Properties_Count; $i++)
					{
						$aTmpArray[] = "";
					}

					// Выводим данные о дополнительных свойствах групп
					foreach ($this->_aGroup_Properties as $oGroup_Property)
					{
						$aProperty_Values = $oGroup_Property->getValues($oShopGroup->id, FALSE);
						$iProperty_Values_Count = count($aProperty_Values);

						$aTmpArray[] = sprintf('"%s"', $this->prepareString($iProperty_Values_Count > 0
							? ($oGroup_Property->type != 2
								? ($oGroup_Property->type == 3 && $aProperty_Values[0]->value != 0 && Core::moduleIsActive('list')
									? $this->_getListValue($aProperty_Values[0]->value)
									: (
										$oGroup_Property->type == 8
											? Core_Date::sql2date($aProperty_Values[0]->value)
											: (
												$oGroup_Property->type == 9
													? Core_Date::sql2datetime($aProperty_Values[0]->value)
													: $aProperty_Values[0]->value
												)
											)
									)
								: ($aProperty_Values[0]->file == ''
									? ''
									: $aProperty_Values[0]->setHref($oShopGroup->getGroupHref())->getLargeFileHref()
								)
							)
							: '')
						);

						if ($oGroup_Property->type == 2)
						{
							$aTmpArray[] = $iProperty_Values_Count
								? sprintf('"%s"', $aProperty_Values[0]->file_description)
								: '';

							$aTmpArray[] = $iProperty_Values_Count
								? ($aProperty_Values[0]->file_small == ''
									? ''
									: $aProperty_Values[0]->setHref($oShopGroup->getGroupHref())->getSmallFileHref()
								)
								: '';
						}

						isset($aProperty_Values[0]) && $aProperty_Values[0]->clear();
					}

					$this->_printRow($aTmpArray);
				}
				else
				{
					$oShopItems->queryBuilder()->where('shop_id', '=', $this->shopId);
				}

				$iPropertyFieldOffsetOriginal = count($this->_aGroupBaseProperties)
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
						$oShopItems
							->queryBuilder()
							->where('datetime', 'BETWEEN', array($sStartDate, $sEndDate));
					}

					$oShopItems
						->queryBuilder()
						->offset($offset)
						->limit($limit);

					$aShopItems = $oShopItems->findAll(FALSE);

					foreach ($aShopItems as $oShopItem)
					{
						// Set GUID
						if ($oShopItem->guid == '')
						{
							$oShopItem->guid = Core_Guid::get();
							$oShopItem->save();
						}

						$iPropertyFieldOffset = $iPropertyFieldOffsetOriginal;

						// Кэш всех значений свойств товара
						$this->_cachePropertyValues[$oShopItem->id] = array();
						foreach ($this->_aItem_Properties as $oProperty)
						{
							$this->_cachePropertyValues[$oShopItem->id][$oProperty->id]
								= $oProperty->getValues($oShopItem->id, FALSE);
						}

						// Строка с основными данными о товаре
						$this->_printRow($this->getItemData($oShopItem));

						$aCurrentPropertyLine = array_fill(0, $iPropertyFieldOffset, '""');

						// CML ID ТОВАРА
						$aCurrentPropertyLine[11] = $oShopItem->guid;

						foreach ($this->_aItem_Properties as $oProperty)
						{
							foreach ($this->_cachePropertyValues[$oShopItem->id][$oProperty->id] as $oProperty_Value)
							{
								$aCurrentPropertyLine[$iPropertyFieldOffset] = sprintf(
									'"%s"', $this->prepareString($this->_getPropertyValue($oProperty, $oProperty_Value, $oShopItem))
								);

								if ($oProperty->type == 2)
								{
									$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = sprintf('"%s"', $this->prepareString($oProperty_Value->file_description));

									$aCurrentPropertyLine[$iPropertyFieldOffset + 2] = sprintf('"%s"', $this->prepareString($oProperty_Value->file_small == ''
										? ''
										: $oProperty_Value->setHref($oShopItem->getItemHref())->getSmallFileHref())
									);
								}

								$this->_printRow($aCurrentPropertyLine);
							}

							if ($oProperty->type == 2)
							{
								// File
								$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
								$iPropertyFieldOffset++;

								// Description
								$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
								$iPropertyFieldOffset++;

								// Small File
								$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
								$iPropertyFieldOffset++;
							}
							else
							{
								$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
								$iPropertyFieldOffset++;
							}

							unset($this->_cachePropertyValues[$oShopItem->id][$oProperty->id]);
						}
						unset($this->_cachePropertyValues[$oShopItem->id]);

						$this->getSpecialPriceData($oShopItem);

						// Получаем список всех модификаций
						if ($this->exportItemModifications)
						{
							$oModifications = Core_Entity::factory('Shop_Item');
							$oModifications->queryBuilder()
								->where('modification_id', '=', $oShopItem->id);
								
							$this->producer
								&& $oModifications->queryBuilder()->where('shop_producer_id', '=', $this->producer);

							$this->seller
								&& $oModifications->queryBuilder()->where('shop_seller_id', '=', $this->seller);

							$aModifications = $oModifications->findAll(FALSE);

							// Добавляем информацию о модификациях
							foreach ($aModifications as $oModification)
							{
								$iPropertyFieldOffset = $iPropertyFieldOffsetOriginal;

								// Кэш всех значений свойств товара
								$this->_cachePropertyValues[$oModification->id] = array();
								foreach ($this->_aItem_Properties as $oProperty)
								{
									$this->_cachePropertyValues[$oModification->id][$oProperty->id]
										= $oProperty->getValues($oModification->id, FALSE);
								}

								$this->_printRow($this->getItemData($oModification));

								$aCurrentPropertyLine = array_fill(0, $iPropertyFieldOffset, '""');

								// CML ID МОДИФИКАЦИИ
								$aCurrentPropertyLine[11] = $oModification->guid;

								foreach ($this->_aItem_Properties as $oProperty)
								{
									foreach ($this->_cachePropertyValues[$oModification->id][$oProperty->id] as $oProperty_Value)
									{
										$aCurrentPropertyLine[$iPropertyFieldOffset] = sprintf(
											'"%s"',
											$this->prepareString($this->_getPropertyValue($oProperty, $oProperty_Value, $oModification))
										);

										if ($oProperty->type == 2)
										{
											$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = sprintf('"%s"', $this->prepareString($oProperty_Value->file_description));

											$aCurrentPropertyLine[$iPropertyFieldOffset + 2] = sprintf('"%s"', $this->prepareString($oProperty_Value->setHref($oModification->getItemHref())->getSmallFileHref()));
										}

										$this->_printRow($aCurrentPropertyLine);
									}

									if ($oProperty->type == 2)
									{
										// File
										$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
										$iPropertyFieldOffset++;

										// Description
										$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
										$iPropertyFieldOffset++;

										// Small File
										$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
										$iPropertyFieldOffset++;
									}
									else
									{
										$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
										$iPropertyFieldOffset++;
									}

									unset($this->_cachePropertyValues[$oModification->id][$oProperty->id]);
								}
								unset($this->_cachePropertyValues[$oShopItem->id]);

								$oModification->clear();
							}

							unset($aModifications);
						}

						$oShopItem->clear();
					}
					$offset += $limit;
				}
				while (count($aShopItems));
			}
		}
		else
		{
			$this->_aCurrentRow = array(
				'"' . Core::_('Shop_Item_Export.order_guid') . '"',
				'"' . Core::_('Shop_Item_Export.order_invoice') . '"',
				'"' . Core::_('Shop_Item_Export.order_country') . '"',
				'"' . Core::_('Shop_Item_Export.order_location') . '"',
				'"' . Core::_('Shop_Item_Export.order_city') . '"',
				'"' . Core::_('Shop_Item_Export.order_city_area') . '"',
				'"' . Core::_('Shop_Item_Export.order_name') . '"',
				'"' . Core::_('Shop_Item_Export.order_surname') . '"',
				'"' . Core::_('Shop_Item_Export.order_patronymic') . '"',
				'"' . Core::_('Shop_Item_Export.order_email') . '"',
				'"' . Core::_('Shop_Item_Export.order_acceptance_report_form') . '"',
				'"' . Core::_('Shop_Item_Export.order_acceptance_report_invoice') . '"',
				'"' . Core::_('Shop_Item_Export.order_company_name') . '"',
				'"' . Core::_('Shop_Item_Export.order_tin') . '"',
				'"' . Core::_('Shop_Item_Export.order_phone') . '"',
				'"' . Core::_('Shop_Item_Export.order_fax') . '"',
				'"' . Core::_('Shop_Item_Export.order_address') . '"',
				'"' . Core::_('Shop_Item_Export.order_status') . '"',
				'"' . Core::_('Shop_Item_Export.order_currency') . '"',
				'"' . Core::_('Shop_Item_Export.order_paymentsystem') . '"',
				'"' . Core::_('Shop_Item_Export.order_delivery') . '"',
				'"' . Core::_('Shop_Item_Export.order_date') . '"',
				'"' . Core::_('Shop_Item_Export.order_paid') . '"',
				'"' . Core::_('Shop_Item_Export.order_paid_date') . '"',
				'"' . Core::_('Shop_Item_Export.order_description') . '"',
				'"' . Core::_('Shop_Item_Export.order_info') . '"',
				'"' . Core::_('Shop_Item_Export.order_canceled') . '"',
				'"' . Core::_('Shop_Item_Export.order_status_date') . '"',
				'"' . Core::_('Shop_Item_Export.order_delivery_info') . '"',
				'"' . Core::_('Shop_Item_Export.order_item_marking') . '"',
				'"' . Core::_('Shop_Item_Export.order_item_name') . '"',
			);

			Core_Event::notify(get_class($this) . '.onBeforeExportOrdersTitleProperties', $this, array($oShop));

			$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->shopId);
			$aProperties = $linkedObject->Properties->findAll(FALSE);

			$aCheckedProperties = array();

			foreach ($aProperties as $oProperty)
			{
				if (Core_Array::getPost('property_' . $oProperty->id))
				{
					$this->_aCurrentRow[] = '"' . $oProperty->name . '"';
					$aCheckedProperties[] = $oProperty;
				}
			}

			Core_Event::notify(get_class($this) . '.onAfterExportOrdersTitleProperties', $this, array($oShop));

			$this->_aCurrentRow = array_merge($this->_aCurrentRow, array(
				'"' . Core::_('Shop_Item_Export.order_item_quantity') . '"',
				'"' . Core::_('Shop_Item_Export.order_item_price') . '"',
				'"' . Core::_('Shop_Item_Export.order_item_tax') . '"',
				'"' . Core::_('Shop_Item_Export.order_item_type') . '"'
				)
			);

			$this->_printRow($this->_aCurrentRow);

			$offset = 0;
			$limit = 100;

			if (!is_null($this->startOrderDate) && !is_null($this->endOrderDate))
			{
				$sStartDate = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($this->startOrderDate . " 00:00:00"));
				$sEndDate = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($this->endOrderDate . " 23:59:59"));
			}
			else
			{
				$sStartDate = $sEndDate = NULL;
			}

			do {
				$oShop_Orders = $oShop->Shop_Orders;

				if (!is_null($sStartDate) && !is_null($sEndDate))
				{
					$oShop_Orders
						->queryBuilder()
						->where('datetime', 'BETWEEN', array($sStartDate, $sEndDate));
				}

				$oShop_Orders
					->queryBuilder()
					->orderBy('id', 'ASC')
					->offset($offset)->limit($limit);

				$aShop_Orders = $oShop_Orders->findAll(FALSE);

				foreach ($aShop_Orders as $oShop_Order)
				{
					$this->_printRow(array(
						sprintf('"%s"', $this->prepareString($oShop_Order->guid)),
						sprintf('"%s"', $this->prepareString($oShop_Order->invoice)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Country->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Country_Location->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Country_Location_City->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Country_Location_City_Area->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->surname)),
						sprintf('"%s"', $this->prepareString($oShop_Order->patronymic)),
						sprintf('"%s"', $this->prepareString($oShop_Order->email)),
						sprintf('"%s"', $this->prepareString($oShop_Order->acceptance_report)),
						sprintf('"%s"', $this->prepareString($oShop_Order->vat_invoice)),
						sprintf('"%s"', $this->prepareString($oShop_Order->company)),
						sprintf('"%s"', $this->prepareString($oShop_Order->tin)),
						sprintf('"%s"', $this->prepareString($oShop_Order->phone)),
						sprintf('"%s"', $this->prepareString($oShop_Order->fax)),
						sprintf('"%s"', $this->prepareString($oShop_Order->address)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Order_Status->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Currency->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Payment_System->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->Shop_Delivery->name)),
						sprintf('"%s"', $this->prepareString($oShop_Order->datetime)),
						sprintf('"%s"', $this->prepareString($oShop_Order->paid)),
						sprintf('"%s"', $this->prepareString($oShop_Order->payment_datetime)),
						sprintf('"%s"', $this->prepareString($oShop_Order->description)),
						sprintf('"%s"', $this->prepareString($oShop_Order->system_information)),
						sprintf('"%s"', $this->prepareString($oShop_Order->canceled)),
						sprintf('"%s"', $this->prepareString($oShop_Order->status_datetime)),
						sprintf('"%s"', $this->prepareString($oShop_Order->delivery_information))
					));

					// Получаем все товары заказа
					$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll(FALSE);
					foreach ($aShop_Order_Items as $oShop_Order_Item)
					{
						$this->_aCurrentRow = array(
							sprintf('"%s"', $this->prepareString($oShop_Order->guid)),
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							'""',
							sprintf('"%s"', $this->prepareString($oShop_Order_Item->marking)),
							sprintf('"%s"', $this->prepareString($oShop_Order_Item->name))
						);

						Core_Event::notify(get_class($this) . '.onBeforeExportOrderProperties', $this, array($oShop, $oShop_Order_Item));

						foreach ($aCheckedProperties as $oProperty)
						{
							$oShop_Item = $oShop_Order_Item->Shop_Item;
							$aPropertyValues = $oProperty->getValues($oShop_Item->id, FALSE);

							if (count($aPropertyValues))
							{
								$oProperty_Value = $aPropertyValues[0];

								$this->_aCurrentRow[] = sprintf('"%s"', $this->prepareString($this->_getPropertyValue($oProperty, $oProperty_Value, $oShop_Item)));
							}
							else
							{
								$this->_aCurrentRow[] = '""';
							}
						}

						Core_Event::notify(get_class($this) . '.onAfterExportOrderProperties', $this, array($oShop, $oShop_Order_Item));

						$this->_aCurrentRow = array_merge($this->_aCurrentRow, array(
							sprintf('"%s"', $this->prepareFloat($oShop_Order_Item->quantity)),
							sprintf('"%s"', $this->prepareFloat($oShop_Order_Item->price)),
							sprintf('"%s"', $this->prepareFloat($oShop_Order_Item->rate)),
							sprintf('"%s"', $oShop_Order_Item->type)
						));

						$this->_printRow($this->_aCurrentRow);
					}
				}
				$offset += $limit;
			}
			while (count($aShop_Orders));
		}

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('End CSV export ' . $sFilename);

		exit();
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	public function prepareString($string)
	{
		return str_replace('"', '""', trim($string));
	}

	/**
	 * Prepare float
	 * @param mixed $string
	 * @return string
	 */
	public function prepareFloat($string)
	{
		return str_replace('.', ',', $string);
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo Core_Str::iconv('UTF-8', $this->encoding, implode($this->separator, $aData) . "\n");
		return $this;
	}
}