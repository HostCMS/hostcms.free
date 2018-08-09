<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	private $_aItemBase_Properties;

	/**
	 * Base properties of item groups
	 * Основные свойства групп товаров
	 * @var array
	 */
	private $_aGroupBase_Properties;

	/**
	 * Special prices of item
	 * Основные свойства дополнительных цен товаров
	 * @var array
	 */
	private $_aSpecialPriceBase_Properties;

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
	 * Init
	 * @return self
	 */
	public function init()
	{
		if (!$this->exportOrders)
		{
			// Заполняем склады
			$this->_aShopWarehouses = Core_Entity::factory('Shop', $this->shopId)
				->Shop_Warehouses
				->findAll(FALSE);

			// Заполняем дополнительные свойства товара
			$this->exportItemExternalProperties
				&& $this->_aItem_Properties = Core_Entity::factory('Shop_Item_Property_List', $this->shopId)->Properties->findAll(FALSE);

			// Заполняем дополнительные свойства групп товаров
			$this->exportGroupExternalProperties
				&& $this->_aGroup_Properties = Core_Entity::factory('Shop_Group_Property_List', $this->shopId)->Properties->findAll(FALSE);

			// Название раздела - Порядок сортировки раздела
			$this->_aGroupBase_Properties = array(
				"","","","","","","","",""
			);

			// CML ID идентификатор товара - Ярлыки
			$this->_aItemBase_Properties = array(
				"", "", "", "", "", "", "", "", "", "",
				"", "", "", "", "", "", "", "", "", "",
				"", "", "", "", "", "", "", "", "", "",
				"", "", "", "", "", ""
			);

			$this->_aSpecialPriceBase_Properties = array(
				"", "", "", ""
			);

			$this->_iCurrentDataPosition = 0;

			$this->_aShopPrices = Core_Entity::factory('Shop', $this->shopId)->Shop_prices->findAll(FALSE);

			// 0-вая строка - заголовок CSV-файла
			$this->_aCurrentData[$this->_iCurrentDataPosition] = array(
				// 9 cells
				'"' . Core::_('Shop_Item_Export.category_name') . '"',
				'"' . Core::_('Shop_Item_Export.category_cml_id') . '"',
				'"' . Core::_('Shop_Item_Export.category_parent_cml_id') . '"',
				'"' . Core::_('Shop_Item_Export.category_meta_title') . '"',
				'"' . Core::_('Shop_Item_Export.category_meta_description') . '"',
				'"' . Core::_('Shop_Item_Export.category_meta_keywords') . '"',
				'"' . Core::_('Shop_Item_Export.category_description') . '"',
				'"' . Core::_('Shop_Item_Export.category_path') . '"',
				'"' . Core::_('Shop_Item_Export.category_sorting') . '"',
				// 36
				'"' . Core::_('Shop_Item_Export.item_cml_id') . '"',
				'"' . Core::_('Shop_Item_Export.item_id') . '"',
				'"' . Core::_('Shop_Item_Export.item_marking') . '"',
				'"' . Core::_('Shop_Item_Export.item_parent_marking') . '"',
				'"' . Core::_('Shop_Item_Export.item_name') . '"',
				'"' . Core::_('Shop_Item_Export.item_description') . '"',
				'"' . Core::_('Shop_Item_Export.item_text') . '"',
				'"' . Core::_('Shop_Item_Export.item_weight') . '"',
				'"' . Core::_('Shop_Item_Export.item_length') . '"',
				'"' . Core::_('Shop_Item_Export.item_width') . '"',
				'"' . Core::_('Shop_Item_Export.item_height') . '"',
				'"' . Core::_('Shop_Item_Export.item_type') . '"',
				'"' . Core::_('Shop_Item_Export.item_tags') . '"',
				'"' . Core::_('Shop_Item_Export.item_price') . '"',
				'"' . Core::_('Shop_Item_Export.item_activity') . '"',
				'"' . Core::_('Shop_Item_Export.item_sorting') . '"',
				'"' . Core::_('Shop_Item_Export.item_path') . '"',
				'"' . Core::_('Shop_Item_Export.item_tax_id') . '"',
				'"' . Core::_('Shop_Item_Export.item_currency_id') . '"',
				'"' . Core::_('Shop_Item_Export.item_seller_name') . '"',
				'"' . Core::_('Shop_Item_Export.item_producer_name') . '"',
				'"' . Core::_('Shop_Item_Export.item_measure_name') . '"',
				'"' . Core::_('Shop_Item_Export.item_meta_title') . '"',
				'"' . Core::_('Shop_Item_Export.item_meta_description') . '"',
				'"' . Core::_('Shop_Item_Export.item_meta_keywords') . '"',
				'"' . Core::_('Shop_Item_Export.item_indexing') . '"',
				'"' . Core::_('Shop_Item_Export.item_yandex_market') . '"',
				'"' . Core::_('Shop_Item_Export.item_yandex_market_bid') . '"',
				'"' . Core::_('Shop_Item_Export.item_yandex_market_cid') . '"',
				'"' . Core::_('Shop_Item_Export.item_date') . '"',
				'"' . Core::_('Shop_Item_Export.item_start_date') . '"',
				'"' . Core::_('Shop_Item_Export.item_end_date') . '"',
				'"' . Core::_('Shop_Item_Export.item_large_image') . '"',
				'"' . Core::_('Shop_Item_Export.item_small_image') . '"',
				'"' . Core::_('Shop_Item_Export.item_shortcuts') . '"',
				'"' . Core::_('Shop_Item_Export.item_siteuser_id') . '"',
				// 4
				'"' . Core::_('Shop_Item_Export.quantity_from') . '"',
				'"' . Core::_('Shop_Item_Export.quantity_to') . '"',
				'"' . Core::_('Shop_Item_Export.price_value') . '"',
				'"' . Core::_('Shop_Item_Export.price_percent') . '"',
			);

			// Добавляем в заголовок информацию о свойствах товара
			foreach ($this->_aItem_Properties as $oProperty)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString($oProperty->name));
				$this->_iItem_Properties_Count++;

				if ($oProperty->type == 2)
				{
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
	 * Get special prices data for item
	 * @param Shop_Item $oShopItem item
	 */
	private function getSpecialPriceData($oShopItem)
	{
		// Получаем список специальных цен товара
		$aShop_Specialprices = $oShopItem->Shop_Specialprices->findAll(FALSE);

		$aTmpArray = array_merge(
			$this->_aGroupBase_Properties,
			$this->_aItemBase_Properties,
			$this->_aSpecialPriceBase_Properties
		);

		// CML ID ТОВАРА
		$aTmpArray[9] = $oShopItem->guid;

		foreach ($aShop_Specialprices as $oShop_Specialprice)
		{
			$aTmpArray[41] = $oShop_Specialprice->min_quantity;
			$aTmpArray[42] = $oShop_Specialprice->max_quantity;
			$aTmpArray[43] = $oShop_Specialprice->price;
			$aTmpArray[44] = $oShop_Specialprice->percent;

			$this->_printRow($aTmpArray);

			$oShop_Specialprice->clear();
		}

		return $this;
	}

	/**
	 * Get item data
	 * @param int $oShopItem item
	 * @return array
	 */
	protected function _getItemData($oShopItem)
	{
		$aItemProperties = $aGroupProperties = $aWarehouses = $aShopPrices = array();

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
					? ($oProperty_Value->file_small == ''
						? ''
						: sprintf('"%s"', $oProperty_Value->getSmallFileHref())
					)
					: '';
			}

			$oProperty_Value && $oProperty_Value->clear();
		}

		for ($i = 0; $i < $this->_iGroup_Properties_Count; $i++)
		{
			$aGroupProperties[] = "";
		}

		foreach ($this->_aShopWarehouses as $oWarehouse)
		{
			$oShop_Warehouse_Item = $oShopItem->Shop_Warehouse_Items->getByWarehouseId($oWarehouse->id, FALSE);
			$aWarehouses[] = !is_null($oShop_Warehouse_Item) ? $oShop_Warehouse_Item->count : 0;
		}

		foreach ($this->_aShopPrices as $oShopPrice)
		{
			$oShop_Price = $oShopItem->Shop_Item_Prices->getByPriceId($oShopPrice->id, FALSE);
			$aShopPrices[] = !is_null($oShop_Price) ? $oShop_Price->value : 0;
		}

		$aTmpArray = $this->_aGroupBase_Properties;

		$oShop_Group = $oShopItem->shop_group_id
			? Core_Entity::factory('Shop_Group', $oShopItem->shop_group_id)
			: NULL;

		$aTmpArray[1] = is_null($oShop_Group)
			? 'ID00000000'
			: $oShop_Group->guid;

		if ($oShop_Group)
		{
			$aTmpArray[3] = sprintf('"%s"', $this->prepareString($oShop_Group->seo_title));
			$aTmpArray[4] = sprintf('"%s"', $this->prepareString($oShop_Group->seo_description));
			$aTmpArray[5] = sprintf('"%s"', $this->prepareString($oShop_Group->seo_keywords));
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
			$sTags = "";
		}

		return array_merge($aTmpArray,
			array(
				sprintf('"%s"', $this->prepareString($oShopItem->guid)),
				sprintf('"%s"', $oShopItem->id),
				sprintf('"%s"', $this->prepareString($oShopItem->marking)),
				sprintf('"%s"', $oShopItem->modification_id
					? $this->prepareString($oShopItem->Modification->marking)
					: ''),
				sprintf('"%s"', $this->prepareString($oShopItem->name)),
				sprintf('"%s"', $this->prepareString($oShopItem->description)),
				sprintf('"%s"', $this->prepareString($oShopItem->text)),
				sprintf('"%s"', $this->prepareFloat($oShopItem->weight)),
				sprintf('"%s"', $this->prepareFloat($oShopItem->length)),
				sprintf('"%s"', $this->prepareFloat($oShopItem->width)),
				sprintf('"%s"', $this->prepareFloat($oShopItem->height)),
				sprintf('"%s"', $oShopItem->type),
				sprintf('"%s"', $sTags),
				sprintf('"%s"', $this->prepareFloat($oShopItem->price)),
				sprintf('"%s"', $oShopItem->active),
				sprintf('"%s"', $oShopItem->sorting),
				sprintf('"%s"', $this->prepareString($oShopItem->path)),
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
				sprintf('"%s"', $oShopItem->siteuser_id)
			),
			$this->_aSpecialPriceBase_Properties,
			$aItemProperties,
			$aGroupProperties,
			$aWarehouses,
			$aShopPrices
		);
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
		return $list_item_id && Core::moduleIsActive('list')
			? (isset($this->_cacheGetListValue[$list_item_id])
				? $this->_cacheGetListValue[$list_item_id]
				: $this->_cacheGetListValue[$list_item_id] = Core_Entity::factory('List_Item', $list_item_id)->value
			)
			: '';
	}

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
			default:
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
		$this->init();

		$sFilename = 'CSV_' . date("Y_m_d_H_i_s") . '.csv';

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

			$aShopGroupsId = array_merge(array($this->parentGroup), $oShop_Groups->getGroupChildrenId(FALSE));

			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('CSV export, groups count = ' . count($aShopGroupsId));

			foreach ($aShopGroupsId as $iShopGroupId)
			{
				$aTmpArray = array();

				$oShopGroup = Core_Entity::factory('Shop_Group', $iShopGroupId);

				$oShopItems = $oShopGroup->Shop_Items;
				$oShopItems
					->queryBuilder()
					->where('modification_id', '=', 0)
					->where('shortcut_id', '=', 0);

				$this->producer
					&& $oShopItems->queryBuilder()->where('shop_producer_id', '=', $this->producer);

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
						sprintf('"%s"', $this->prepareString($oShopGroup->sorting))
					);

					// Пропускаем поля товара
					foreach ($this->_aItemBase_Properties as $sNullData)
					{
						$aTmpArray[] = $sNullData;
					}

					// Пропускаем поля специальных цен товара
					foreach ($this->_aSpecialPriceBase_Properties as $sNullData)
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
									? $aProperty_Values[0]->List_Item->value
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
									: $aProperty_Values[0]->setHref($oShopGroup->getGroupHref())->getLargeFileHref())
							)
							: '')
						);

						if ($oGroup_Property->type == 2)
						{
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

				$iPropertyFieldOffsetOriginal = count($this->_aGroupBase_Properties)
					+ count($this->_aItemBase_Properties)
					+ count($this->_aSpecialPriceBase_Properties);

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
						$iPropertyFieldOffset = $iPropertyFieldOffsetOriginal;

						// Кэш всех значений свойств товара
						$this->_cachePropertyValues[$oShopItem->id] = array();
						foreach ($this->_aItem_Properties as $oProperty)
						{
							$this->_cachePropertyValues[$oShopItem->id][$oProperty->id]
								= $oProperty->getValues($oShopItem->id, FALSE);
						}

						// Строка с основными данными о товаре
						$this->_printRow($this->_getItemData($oShopItem));

						$aCurrentPropertyLine = array_fill(0, $iPropertyFieldOffset, '""');

						// CML ID ТОВАРА
						$aCurrentPropertyLine[9] = $oShopItem->guid;

						foreach ($this->_aItem_Properties as $oProperty)
						{
							foreach ($this->_cachePropertyValues[$oShopItem->id][$oProperty->id] as $oProperty_Value)
							{
								$aCurrentPropertyLine[$iPropertyFieldOffset] = sprintf(
									'"%s"',
									$this->prepareString($this->_getPropertyValue($oProperty, $oProperty_Value, $oShopItem))
								);

								if ($oProperty->type == 2)
								{
									$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = sprintf('"%s"', $this->prepareString($oProperty_Value->setHref($oShopItem->getItemHref())->getSmallFileHref()));
								}

								$this->_printRow($aCurrentPropertyLine);
							}

							if ($oProperty->type == 2)
							{
								$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
								$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = '""';
								$iPropertyFieldOffset += 2;
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

								$this->_printRow($this->_getItemData($oModification));

								$aCurrentPropertyLine = array_fill(0, $iPropertyFieldOffset, '""');

								// CML ID МОДИФИКАЦИИ
								$aCurrentPropertyLine[9] = $oModification->guid;

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
											$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = sprintf('"%s"', $this->prepareString($oProperty_Value->setHref($oModification->getItemHref())->getSmallFileHref()));
										}

										$this->_printRow($aCurrentPropertyLine);
									}

									if ($oProperty->type == 2)
									{
										$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
										$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = '""';
										$iPropertyFieldOffset += 2;
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
		echo Shop_Item_Import_Csv_Controller::CorrectToEncoding(implode($this->separator, $aData) . "\n", $this->encoding);
		return $this;
	}
}