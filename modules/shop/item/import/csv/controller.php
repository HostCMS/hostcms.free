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
class Shop_Item_Import_Csv_Controller extends Core_Servant_Properties
{
	/**
	 * Array of inserted groups
	 * @var array
	 */
	protected $_aInsertedGroupIDs = array();

	/**
	 * Array of property values
	 * @var array
	 */
	protected $_aClearedPropertyValues = array();

	/**
	 * Array of updated groups
	 * @var array
	 */
	protected $_aUpdatedGroupIDs = array();

	/**
	 * Array of inserted items
	 * @var array
	 */
	protected $_aInsertedItemIDs = array();

	/**
	 * Array of updated items
	 * @var array
	 */
	protected $_aUpdatedItemIDs = array();

	/**
	 * ID of current shop
	 * @var int
	 */
	protected $_iCurrentShopId = 0;

	/**
	 * ID of current group
	 * @var int
	 */
	protected $_iCurrentGroupId = 0;

	/**
	 * Current shop
	 * @var Shop_Model
	 */
	protected $_oCurrentShop;

	/**
	 * Current group
	 * @var Shop_Group_Model
	 */
	protected $_oCurrentGroup;

	/**
	 * Current item
	 * @var Shop_Item_Model
	 */
	protected $_oCurrentItem;

	/**
	 * Current order
	 * @var Shop_Item_Model
	 */
	protected $_oCurrentOrder;

	/**
	 * Current order item
	 * @var Shop_Order_Item_Model
	 */
	protected $_oCurrentOrderItem;

	/**
	 * Current tags
	 * @var string
	 */
	protected $_sCurrentTags;

	/**
	 * Mark of associated item
	 * Артикул родительского товара - признак того, что данный товар сопутствует товару с данным артикулом
	 * @var string
	 */
	protected $_sAssociatedItemMark;

	/**
	 * Current digital item
	 * Текущий электронный товар
	 * @var Shop_Item_Digital_Model
	 */
	protected $_oCurrentShopEItem;

	/**
	 * Current special price
	 * Текущая специальная цена для товара
	 * @var Shop_Specialprice_Model
	 */
	protected $_oCurrentShopSpecialPrice;

	/**
	 * List of external prices
	 * Вспомогательные массивы данных
	 * @var array
	 */
	protected $_aExternalPrices = array();

	/**
	 * List of warehouses
	 * @var array
	 */
	protected $_aWarehouses = array();

	/**
	 * List of small parts of external properties
	 * @var array
	 */
	protected $_aExternalPropertiesSmall = array();

	/**
	 * List of descriptions of external properties
	 * @var array
	 */
	protected $_aExternalPropertiesDesc = array();

	/**
	 * List of external properties
	 * @var array
	 */
	protected $_aExternalProperties = array();

	/**
	 * List of additional group
	 * @var array
	 */
	protected $_aAdditionalGroups = array();

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		// Кодировка импорта
		'encoding',
		// Файл импорта
		'file',
		// Позиция в файле импорта
		'seek',
		// Ограничение импорта по времени
		'time',
		// Ограничение импорта по количеству
		'step',
		// Настройка CSV: разделитель
		'separator',
		// Настройка CSV: ограничитель
		'limiter',
		// Настройка CSV: первая строка - название полей
		'firstlineheader',
		// Настройка CSV: массив соответствий полей CSV сущностям системы HostCMS
		'csv_fields',
		// Путь к картинкам
		'imagesPath',
		// Действие с существующими товарами:
		// 1 - обновить существующие товары
		// 2 - не обновлять существующие товары
		// 3 - удалить содержимое магазина до импорта
		'importAction',
		// Флаг, указывающий, включена ли индексация
		'searchIndexation',
		'deleteImage'
	);

	/**
	 * Count of inserted items
	 * @var int
	 */
	protected $_InsertedItemsCount;

	/**
	 * Count of updated items
	 * @var int
	 */
	protected $_UpdatedItemsCount;

	/**
	 * Count of inserted groups
	 * @var int
	 */
	protected $_InsertedGroupsCount;

	/**
	 * Count of updated groups
	 * @var int
	 */
	protected $_UpdatedGroupsCount;

	/**
	 * Path of the big image
	 * @var string
	 */
	protected $_sBigImageFile = '';

	/**
	 * Path of the small image
	 * @var string
	 */
	protected $_sSmallImageFile = '';

	/**
	 * IDs of created shop_items
	 */
	protected $_ShopItemCreatedIDs = array();

	/**
	 * Get inserted items count
	 * @return int
	 */
	public function getInsertedItemsCount()
	{
		return $this->_InsertedItemsCount;
	}

	/**
	 * Get inserted groups count
	 * @return int
	 */
	public function getInsertedGroupsCount()
	{
		return $this->_InsertedGroupsCount;
	}

	/**
	 * Get updated items count
	 * @return int
	 */
	public function getUpdatedItemsCount()
	{
		return $this->_UpdatedItemsCount;
	}

	/**
	 * Get updated groups count
	 * @return int
	 */
	public function getUpdatedGroupsCount()
	{
		return $this->_UpdatedGroupsCount;
	}

	/**
	 * Increment inserted groups
	 * @param int $iGroupId group ID
	 * @return self
	 */
	protected function _incInsertedGroups($iGroupId)
	{
		if (!in_array($iGroupId, $this->_aInsertedGroupIDs))
		{
			$this->_aInsertedGroupIDs[] = $iGroupId;
			$this->_InsertedGroupsCount++;
		}
		return $this;
	}

	/**
	 * Increment updated groups
	 * @param int $iGroupId group ID
	 * @return self
	 */
	protected function _incUpdatedGroups($iGroupId)
	{
		if (!in_array($iGroupId, $this->_aUpdatedGroupIDs))
		{
			$this->_aUpdatedGroupIDs[] = $iGroupId;
			$this->_UpdatedGroupsCount++;
		}
		return $this;
	}

	/**
	 * Increment inserted items
	 * @param int $iItemId item ID
	 * @return self
	 */
	protected function _incInsertedItems($iItemId)
	{
		if (!in_array($iItemId, $this->_aInsertedItemIDs))
		{
			$this->_aInsertedItemIDs[] = $iItemId;
			$this->_InsertedItemsCount++;
		}
		return $this;
	}

	/**
	 * Increment updated items
	 * @param int $iItemId item ID
	 * @return self
	 */
	protected function _incUpdatedItems($iItemId)
	{
		if (!in_array($iItemId, $this->_aUpdatedItemIDs))
		{
			$this->_aUpdatedItemIDs[] = $iItemId;
			$this->_UpdatedItemsCount++;
		}
		return $this;
	}

	/**
	* Set $this->_oCurrentItem
	* @param Shop_Item_Model $oCurrentItem
	* @return self
	*/
	public function setCurrentItem(Shop_Item_Model $oCurrentItem)
	{
		$this->_oCurrentItem = $oCurrentItem;
		return $this;
	}

	/**
	 * Get $this->_oCurrentItem
	 * @return Shop_Item_Model $oCurrentItem
	 */
	public function getCurrentItem()
	{
		return $this->_oCurrentItem;
	}

	/**
	 * Initialization
	 * @return self
	 */
	protected function init()
	{
		$this->_oCurrentShop = Core_Entity::factory('Shop')->find($this->_iCurrentShopId);

		// Инициализация текущей группы товаров
		$this->_oCurrentGroup = Core_Entity::factory('Shop_Group', $this->_iCurrentGroupId);
		$this->_oCurrentGroup->shop_id = $this->_oCurrentShop->id;

		// Инициализация текущего товара
		$this->_oCurrentItem = Core_Entity::factory('Shop_Item');
		$this->_oCurrentItem->shop_group_id = intval($this->_oCurrentGroup->id);

		// Инициализация текущего электронного товара
		$this->_oCurrentShopEItem = Core_Entity::factory('Shop_Item_Digital');

		// Инициализация текущей специальной цены для товара
		$this->_oCurrentShopSpecialPrice = Core_Entity::factory('Shop_Specialprice');

		$this->_oCurrentOrder = NULL;
		$this->_oCurrentOrderItem = NULL;

		return $this;
	}

	/**
	 * CSV config
	 * @var array
	 */
	protected $_aConfig = NULL;

	/**
	 * Constructor.
	 * @param int $iCurrentShopId shop ID
	 * @param int $iCurrentGroupId current group ID
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAfterConstruct
	 */
	public function __construct($iCurrentShopId, $iCurrentGroupId = 0)
	{
		parent::__construct();

		$this->_aConfig = Core_Config::instance()->get('shop_csv', array()) + array(
			'maxTime' => 20,
			'maxCount' => 100,
			'itemSearchFields' => array('marking', 'path', 'cml_id')
		);

		$this->_iCurrentShopId = $iCurrentShopId;
		$this->_iCurrentGroupId = $iCurrentGroupId;

		$this->time = $this->_aConfig['maxTime'];
		$this->step = $this->_aConfig['maxCount'];

		$this->init();

		// Единожды в конструкторе, чтобы после __wakeup() не обнулялось
		$this->_InsertedItemsCount = 0;
		$this->_UpdatedItemsCount = 0;
		$this->_InsertedGroupsCount = 0;
		$this->_UpdatedGroupsCount = 0;

		$this->_ShopItemCreatedIDs = array();

		$oShop = Core_Entity::factory('Shop', $iCurrentShopId);

		$this->aCaptions = array(
			Core::_('Shop_Exchange.!download'),

			// groups
			Core::_('Shop_Exchange.group_id'),
			Core::_('Shop_Exchange.group_name'),
			Core::_('Shop_Exchange.group_path'),
			Core::_('Shop_Exchange.group_sorting'),
			Core::_('Shop_Exchange.group_description'),
			Core::_('Shop_Exchange.group_active'),
			Core::_('Shop_Exchange.group_seo_title'),
			Core::_('Shop_Exchange.group_seo_description'),
			Core::_('Shop_Exchange.group_seo_keywords'),
			Core::_('Shop_Exchange.group_image_large'),
			Core::_('Shop_Exchange.group_image_small'),
			Core::_('Shop_Exchange.group_guid'),
			Core::_('Shop_Exchange.parent_group_guid'),

			// currency
			Core::_('Shop_Exchange.currency_id'),

			// tax
			Core::_('Shop_Exchange.tax_id'),

			// producer
			Core::_('Shop_Exchange.producer_id'),
			Core::_('Shop_Exchange.producer_name'),

			// seller
			Core::_('Shop_Exchange.seller_id'),
			Core::_('Shop_Exchange.seller_name'),

			// measure
			Core::_('Shop_Exchange.measure_id'),
			Core::_('Shop_Exchange.measure_value'),

			// items
			Core::_('Shop_Exchange.item_id'),
			Core::_('Shop_Exchange.item_name'),
			Core::_('Shop_Exchange.item_marking'),
			Core::_('Shop_Exchange.item_datetime'),
			Core::_('Shop_Exchange.item_description'),
			Core::_('Shop_Exchange.item_text'),
			Core::_('Shop_Exchange.item_image_large'),
			Core::_('Shop_Exchange.item_image_small'),
			Core::_('Shop_Exchange.item_tags'),
			Core::_('Shop_Exchange.item_weight'),
			Core::_('Shop_Exchange.item_length'),
			Core::_('Shop_Exchange.item_width'),
			Core::_('Shop_Exchange.item_height'),
			Core::_('Shop_Exchange.item_price'),
			Core::_('Shop_Exchange.item_active'),
			Core::_('Shop_Exchange.item_sorting'),
			Core::_('Shop_Exchange.item_path'),
			Core::_('Shop_Exchange.item_seo_title'),
			Core::_('Shop_Exchange.item_seo_description'),
			Core::_('Shop_Exchange.item_seo_keywords'),
			Core::_('Shop_Exchange.item_indexing'),
			Core::_('Shop_Exchange.item_yandex_market'),
			Core::_('Shop_Exchange.item_yandex_market_bid'),
			Core::_('Shop_Exchange.item_yandex_market_cid'),
			Core::_('Shop_Exchange.item_yandex_market_manufacturer_warranty'),
			Core::_('Shop_Exchange.item_yandex_market_vendorcode'),
			Core::_('Shop_Exchange.item_yandex_market_country_of_origin'),
			Core::_('Shop_Exchange.item_parent_marking'),
			Core::_('Shop_Exchange.item_parent_guid'),
			Core::_('Shop_Exchange.digital_item_name'),
			Core::_('Shop_Exchange.digital_item_value'),
			Core::_('Shop_Exchange.digital_item_filename'),
			Core::_('Shop_Exchange.digital_item_count'),
			Core::_('Shop_Exchange.item_end_datetime'),
			Core::_('Shop_Exchange.item_start_datetime'),
			Core::_('Shop_Exchange.item_type'),
			Core::_('Shop_Exchange.siteuser_id'),
			Core::_('Shop_Exchange.item_yandex_market_sales_notes'),
			Core::_('Shop_Exchange.item_additional_group'),
			Core::_('Shop_Exchange.item_guid'),

			// item special prices
			Core::_('Shop_Exchange.specialprices_min_quantity'),
			Core::_('Shop_Exchange.specialprices_max_quantity'),
			Core::_('Shop_Exchange.specialprices_price'),
			Core::_('Shop_Exchange.specialprices_percent'),

			// item associated
			Core::_('Shop_Exchange.item_parent_associated'),
			Core::_('Shop_Exchange.item_associated_markings'),

			// order
			Core::_('Shop_Exchange.order_guid'),
			Core::_('Shop_Exchange.order_number'),
			Core::_('Shop_Exchange.order_country'),
			Core::_('Shop_Exchange.order_location'),
			Core::_('Shop_Exchange.order_city'),
			Core::_('Shop_Exchange.order_city_area'),
			Core::_('Shop_Exchange.order_name'),
			Core::_('Shop_Exchange.order_surname'),
			Core::_('Shop_Exchange.order_patronymic'),
			Core::_('Shop_Exchange.order_email'),
			Core::_('Shop_Exchange.order_akt'),
			Core::_('Shop_Exchange.order_schet_fak'),
			Core::_('Shop_Exchange.order_company_name'),
			Core::_('Shop_Exchange.order_inn'),
			Core::_('Shop_Exchange.order_kpp'),
			Core::_('Shop_Exchange.order_phone'),
			Core::_('Shop_Exchange.order_fax'),
			Core::_('Shop_Exchange.order_address'),
			Core::_('Shop_Exchange.order_order_status'),
			Core::_('Shop_Exchange.order_currency'),
			Core::_('Shop_Exchange.order_payment_system_id'),
			Core::_('Shop_Exchange.order_date'),
			Core::_('Shop_Exchange.order_pay_status'),
			Core::_('Shop_Exchange.order_pay_date'),
			Core::_('Shop_Exchange.order_description'),
			Core::_('Shop_Exchange.order_info'),
			Core::_('Shop_Exchange.order_canceled'),
			Core::_('Shop_Exchange.order_pay_status_change_date'),
			Core::_('Shop_Exchange.order_delivery_info'),

			// order items
			Core::_('Shop_Exchange.order_item_marking'),
			Core::_('Shop_Exchange.order_item_name'),
			Core::_('Shop_Exchange.order_item_quantity'),
			Core::_('Shop_Exchange.order_item_price'),
			Core::_('Shop_Exchange.order_item_rate'),
			Core::_('Shop_Exchange.order_item_type')
		);

		$aGroupProperties = Core_Entity::factory('Shop_Group_Property_List', $oShop->id)->Properties->findAll();
		foreach ($aGroupProperties as $oGroupProperty)
		{
			$oPropertyDir = $oGroupProperty->Property_Dir;

			$this->aCaptions[] = $oGroupProperty->name . "&nbsp;[" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
			$this->aColors[] = "#E6EE9C";
			$this->aEntities[] = 'prop_group-' . $oGroupProperty->id;

			if ($oGroupProperty->type == 2)
			{
				// Description
				$this->aCaptions[] = Core::_('Shop_Item.import_file_description', $oGroupProperty->name)
					. " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
				$this->aColors[] = "#E1EA8E";
				$this->aEntities[] = 'propdesc-' . $oGroupProperty->id;

				// Small Image
				$this->aCaptions[] = Core::_('Shop_Item.import_small_images', $oGroupProperty->name)
					. " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
				$this->aColors[] = "#E1EA8E";
				$this->aEntities[] = 'propsmall-' . $oGroupProperty->id;
			}
		}

		$aItemProperties = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)->Properties->findAll();
		foreach ($aItemProperties as $oItemProperty)
		{
			$oPropertyDir = $oItemProperty->Property_Dir;

			$this->aCaptions[] = $oItemProperty->name . " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
			$this->aColors[] = "#FFD54F";
			$this->aEntities[] = 'prop-' . $oItemProperty->id;

			if ($oItemProperty->type == 2)
			{
				// Description
				$this->aCaptions[] = Core::_('Shop_Item.import_file_description', $oItemProperty->name)
					. " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
				$this->aColors[] = "#F3C83E";
				$this->aEntities[] = 'propdesc-' . $oItemProperty->id;

				// Small Image
				$this->aCaptions[] = Core::_('Shop_Item.import_small_images', $oItemProperty->name)
					. " [" . ($oPropertyDir->id ? $oPropertyDir->name : Core::_('Shop_item.root_folder')) . "]";
				$this->aColors[] = "#F3C83E";
				$this->aEntities[] = 'propsmall-' . $oItemProperty->id;
			}
		}

		$aShopPrices = $oShop->Shop_Prices->findAll();
		foreach ($aShopPrices as $oShopPrice)
		{
			$this->aCaptions[] = $oShopPrice->name;
			$this->aColors[] = "#B0BEC5";
			$this->aEntities[] = 'price-' . $oShopPrice->id;
		}

		// Выводим склады
		$aShopWarehouses = $oShop->Shop_Warehouses->findAll();
		foreach ($aShopWarehouses as $oShopWarehouse)
		{
			$this->aCaptions[] = Core::_('Shop_Item.warehouse_import_field', $oShopWarehouse->name);
			$this->aColors[] = "#F48FB1";
			$this->aEntities[] = 'warehouse-' . $oShopWarehouse->id;
		}

		Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterConstruct', $this);
	}

	public function addField($sCaption, $sColor, $sEntityName)
	{
		$this->aCaptions[] = $sCaption;
		$this->aColors[] = $sColor;
		$this->aEntities[] = $sEntityName;

		return $this;
	}

	/**
	 * Save group
	 * @param Shop_Group_Model $oShop_Group group
	 * @return Shop_Group
	 */
	protected function _doSaveGroup(Shop_Group_Model $oShop_Group)
	{
		is_null($oShop_Group->path) && $oShop_Group->path = '';
		$this->_incInsertedGroups($oShop_Group->save()->id);
		return $oShop_Group;
	}

	protected function _uploadHttpFile($sSourceFile)
	{
		$Core_Http = Core_Http::instance()
			->clear()
			->url($sSourceFile)
			->timeout(5)
			->addOption(CURLOPT_FOLLOWLOCATION, TRUE)
			->execute();

		$content = $Core_Http->getBody();

		$aHeaders = $Core_Http->parseHeaders();
		$sStatus = Core_Array::get($aHeaders, 'status');
		$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

		if ($iStatusCode != 200)
		{
			throw new Core_Exception("HTTP %code ERROR: %body.\nSource URL: %url",
				array('%code' => $iStatusCode, '%body' => strip_tags($content), '%url' => $sSourceFile));
		}

		// Файл из WEB'а, создаем временный файл
		$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");

		Core_File::write($sTempFileName, $content);

		return $sTempFileName;
	}

	/**
	 * Импорт CSV
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImport
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAfterImport
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeFindByMarking
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAfterFindByMarking
	 * @hostcms-event Shop_Item_Import_Csv_Controller.oBeforeAdminUpload
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImportGroupProperty
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImportItemProperty
	 * @hostcms-event Shop_Item_Import_Csv_Controller.oBeforeCaseDefault
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeAssociated
	 */
	public function import()
	{
		Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImport', $this, array($this->_oCurrentShop));

		// Clear Shop
		if ($this->importAction == 3)
		{
			Core_QueryBuilder::update('shop_groups')
				->set('deleted', 1)
				->where('shop_id', '=', $this->_oCurrentShop->id)
				->execute();

			Core_QueryBuilder::update('shop_items')
				->set('deleted', 1)
				->where('shop_id', '=', $this->_oCurrentShop->id)
				->execute();
		}

		$fInputFile = fopen($this->file, 'rb');

		if ($fInputFile === FALSE)
		{
			throw new Core_Exception("");
		}

		fseek($fInputFile, $this->seek);

		$iCounter = 0;

		$timeout = Core::getmicrotime();

		$aCsvLine = array();

		$bMarkingItemSearchFields = in_array('marking', $this->_aConfig['itemSearchFields']);
		$bPathItemSearchFields = in_array('path', $this->_aConfig['itemSearchFields']);
		$bCmlIdItemSearchFields = in_array('cml_id', $this->_aConfig['itemSearchFields']);

		// Позиция CML GROUP ID
		$sNeedKeyGroupCml = array_search('group_cml_id', $this->csv_fields);
		// Позиция названия группы
		$sNeedKeyGroupName = array_search('group_name', $this->csv_fields);
		// CML_ID родительской (!) группы товаров
		$sNeedKeyGroupParentCMLId = array_search('group_parent_cml_id', $this->csv_fields);

		while ((Core::getmicrotime() - $timeout + 3 < $this->time)
			&& $iCounter < $this->step
			&& ($aCsvLine = $this->getCSVLine($fInputFile)))
		{
			if (count($aCsvLine) == 1
			&& (is_null($aCsvLine[0]) || $aCsvLine[0] == ''))
			{
				continue;
			}

			foreach ($aCsvLine as $iKey => $sData)
			{
				if (!isset($this->csv_fields[$iKey]))
				{
					continue;
				}

				$sData = trim($sData);

				if ($sData != '')
				{
					switch ($this->csv_fields[$iKey])
					{
						//=================ЗАКАЗЫ=================//
						case 'order_guid':
							$this->_oCurrentOrder = $this->_oCurrentShop->Shop_Orders->getByGuid($sData, FALSE);

							if (is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder = Core_Entity::factory('Shop_Order');
								$this->_oCurrentOrder->guid = $sData;
							}
						break;
						case 'order_invoice':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->invoice = $sData;
							}
						break;
						case 'order_shop_country_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Country = Core_Entity::factory('Shop_Country')->getByName($sData);

								!is_null($oShop_Country)
									&& $this->_oCurrentOrder->shop_country_id = $oShop_Country->id;
							}
						break;
						case 'order_shop_country_location_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Country_Location = Core_Entity::factory('Shop_Country', $this->_oCurrentOrder->shop_country_id)
									->Shop_Country_Locations
									->getByName($sData);

								if (!is_null($oShop_Country_Location))
								{
									$this->_oCurrentOrder->shop_country_location_id = $oShop_Country_Location->id;
								}
							}
						break;
						case 'order_shop_country_location_city_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Country_Location_City = Core_Entity::factory('Shop_Country_Location', $this->_oCurrentOrder->shop_country_location_id)
									->Shop_Country_Location_Cities
									->getByName($sData);

								if (!is_null($oShop_Country_Location_City))
								{
									$this->_oCurrentOrder->shop_country_location_city_id = $oShop_Country_Location_City->id;
								}
							}
						break;
						case 'order_shop_country_location_city_area_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Country_Location_City_Area = Core_Entity::factory('Shop_Country_Location_City', $this->_oCurrentOrder->shop_country_location_city_id)
									->Shop_Country_Location_City_Areas
									->getByName($sData);

								if (!is_null($oShop_Country_Location_City_Area))
								{
									$this->_oCurrentOrder->shop_country_location_city_area_id = $oShop_Country_Location_City_Area->id;
								}
							}
						break;
						case 'order_name':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->name = $sData;
							}
						break;
						case 'order_surname':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->surname = $sData;
							}
						break;
						case 'order_patronymic':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->patronymic = $sData;
							}
						break;
						case 'order_email':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->email = $sData;
							}
						break;
						case 'order_acceptance_report':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->acceptance_report = $sData;
							}
						break;
						case 'order_vat_invoice':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->vat_invoice = $sData;
							}
						break;
						case 'order_company':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->company = $sData;
							}
						break;
						case 'order_tin':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->tin = $sData;
							}
						break;
						case 'order_kpp':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->kpp = $sData;
							}
						break;
						case 'order_phone':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->phone = $sData;
							}
						break;
						case 'order_fax':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->fax = $sData;
							}
						break;
						case 'order_address':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->address = $sData;
							}
						break;
						case 'order_shop_order_status_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Order_Status = Core_Entity::factory('Shop_Order_Status')->getByName($sData);
								if (!is_null($oShop_Order_Status))
								{
									$this->_oCurrentOrder->shop_order_status_id = $oShop_Order_Status->id;
								}
							}
						break;
						case 'order_shop_currency_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Currency = Core_Entity::factory('Shop_Currency')->getByName($sData);
								if (!is_null($oShop_Currency))
								{
									$this->_oCurrentOrder->shop_currency_id = $oShop_Currency->id;
								}
							}
						break;
						case 'order_shop_payment_system_id':
							if (!is_null($this->_oCurrentOrder))
							{
								$oShop_Payment_System = $this->_oCurrentShop->Shop_Payment_Systems->getById($sData);
								if (!is_null($oShop_Payment_System))
								{
									$this->_oCurrentOrder->shop_payment_system_id = $oShop_Payment_System->id;
								}
							}
						break;
						case 'order_datetime':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
									? $sData
									: Core_Date::datetime2sql($sData);
							}
						break;
						case 'order_paid':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->paid = ((bool)$sData) ? 1 : 0;
							}
						break;
						case 'order_payment_datetime':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->payment_datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
									? $sData
									: Core_Date::datetime2sql($sData);
							}
						break;
						case 'order_description':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->description = $sData;
							}
						break;
						case 'order_system_information':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->system_information = $sData;
							}
						break;
						case 'order_canceled':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->canceled = ((bool)$sData)?1:0;
							}
						break;
						case 'order_status_datetime':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->status_datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
									? $sData
									: Core_Date::datetime2sql($sData);
							}
						break;
						case 'order_delivery_information':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrder->delivery_information = $sData;
							}
						break;
						//============== order items ==============//
						case 'order_item_marking':
							if (!is_null($this->_oCurrentOrder))
							{
								$this->_oCurrentOrderItem = $this->_oCurrentOrder->Shop_Order_Items->getBymarking($sData, FALSE);

								if (is_null($this->_oCurrentOrderItem))
								{
									$this->_oCurrentOrderItem = Core_Entity::factory('Shop_Order_Item');
									$this->_oCurrentOrderItem->marking = $sData;
								}
							}
						break;
						case 'order_item_name':
							if (!is_null($this->_oCurrentOrderItem))
							{
								$this->_oCurrentOrderItem->name = $sData;
							}
						break;
						case 'order_item_quantity':
							if (!is_null($this->_oCurrentOrderItem))
							{
								$this->_oCurrentOrderItem->quantity = $sData;
							}
						break;
						case 'order_item_price':
							if (!is_null($this->_oCurrentOrderItem))
							{
								$this->_oCurrentOrderItem->price = $sData;
							}
						break;
						case 'order_item_rate':
							if (!is_null($this->_oCurrentOrderItem))
							{
								$this->_oCurrentOrderItem->rate = $sData;
							}
						break;
						case 'order_item_type':
							if (!is_null($this->_oCurrentOrderItem))
							{
								$this->_oCurrentOrderItem->type = $sData;
							}
						break;

						//=======================================//

						// Идентификатор группы товаров
						case 'group_id':
							if (intval($sData))
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Groups->getById($sData, FALSE);

								if (!is_null($oTmpObject))
								{
									$this->_oCurrentGroup = $oTmpObject;
								}
							}
						break;
						// Название группы товаров
						case 'group_name':
							// Группа была ранее найдена по CML GROUP ID и CML GROUP ID идет раньше,
							// чем название группы, тогда просто обновляем название группы
							if ($sNeedKeyGroupCml !== FALSE
								&& $sNeedKeyGroupCml < $sNeedKeyGroupName
								// Для новой группы "CML ID|Название группы", id будет пустым
								/*&& $this->_oCurrentGroup->id*/)
							{
								// Меняем название на переданное
								$this->_oCurrentGroup->name = $sData;
								$this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
							}
							else
							{
								if ($sNeedKeyGroupParentCMLId !== FALSE
									&& ($sCMLID = Core_Array::get($aCsvLine, $sNeedKeyGroupParentCMLId, '')) != '')
								{
									if ($sCMLID == 'ID00000000')
									{
										$oTmpParentObject = Core_Entity::factory('Shop_Group', 0);
									}
									else
									{
										$oTmpParentObject = $this->_oCurrentShop->Shop_Groups->getByGuid($sCMLID, FALSE);

										if (is_null($oTmpParentObject))
										{
											$oTmpParentObject = Core_Entity::factory('Shop_Group', 0);
										}
									}

									$oTmpObject = $this->_oCurrentShop->Shop_Groups;
									$oTmpObject->queryBuilder()
										->where('parent_id', '=', $oTmpParentObject->id)
										->where('name', '=', $sData)
										->limit(1);
								}
								else
								{
									$oTmpObject = $this->_oCurrentShop->Shop_Groups;
									$oTmpObject->queryBuilder()
										->where('parent_id', '=', intval($this->_oCurrentGroup->id))
										->where('name', '=', $sData)
										->limit(1);
								}

								$aTmpObject = $oTmpObject->findAll(FALSE);

								if (count($aTmpObject))
								{
									// Группа нашлась
									$this->_oCurrentGroup = $aTmpObject[0];
								}
								else
								{
									// Группа не нашлась
									$oTmpObject = Core_Entity::factory('Shop_Group');
									$oTmpObject->name = $sData;

									if ($sNeedKeyGroupParentCMLId !== FALSE
										// Если явно переданный CML Parent ID идет до названия
										&& $sNeedKeyGroupParentCMLId < $sNeedKeyGroupName)
									{
										$oTmpObject->parent_id = intval($this->_oCurrentGroup->parent_id);
									}
									else
									{
										$oTmpObject->parent_id = intval($this->_oCurrentGroup->id);
									}

									$oTmpObject->shop_id = $this->_oCurrentShop->id;

									// Переданные GUID для новой группы
									if ($sNeedKeyGroupCml !== FALSE
										// CML ID идет раньше названия группы, тогда он присваивается новой группе
										&& $sNeedKeyGroupCml < $sNeedKeyGroupName)
									{
										$oTmpObject->guid = strval(Core_Array::get($aCsvLine, $sNeedKeyGroupCml, ''));
									}

									$this->_oCurrentGroup = $this->_doSaveGroup($oTmpObject);
								}
							}

							$this->_oCurrentItem->shop_group_id = $this->_oCurrentGroup->id;

						break;
						// Путь группы товаров
						case 'group_path':
							$oTmpObject = $this->_oCurrentShop->Shop_Groups;
							$oTmpObject
								->queryBuilder()
								->where('parent_id', '=', intval($this->_oCurrentGroup->id))
								->where('path', 'LIKE', $sData);

							$oTmpObject = $oTmpObject->findAll(FALSE);

							if (count($oTmpObject))
							{
								// Группа найдена, делаем текущей
								$this->_oCurrentGroup = $oTmpObject[0];
							}
							else
							{
								// Группа не найдена, обновляем путь для текущей группы
								$this->_oCurrentGroup->path = $sData;
								$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
							}
						break;
						// Порядок сортировки группы товаров
						case 'group_sorting':
							$this->_oCurrentGroup->sorting = intval($sData);
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// Описание группы товаров
						case 'group_description':
							$this->_oCurrentGroup->description = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// SEO Title группы товаров
						case 'group_seo_title':
							$this->_oCurrentGroup->seo_title = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// SEO Description группы товаров
						case 'group_seo_description':
							$this->_oCurrentGroup->seo_description = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// SEO Keywords группы товаров
						case 'group_seo_keywords':
							$this->_oCurrentGroup->seo_keywords = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// Активность группы товаров
						case 'group_active':
							$this->_oCurrentGroup->active = intval($sData) >= 1 ? 1 : 0;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// Картинка группы товаров
						case 'group_image':
							// Для гарантии получения идентификатора группы
							$this->_oCurrentGroup->save();
							$this->_incUpdatedGroups($this->_oCurrentGroup->id);

							// Папка назначения
							$sDestinationFolder = $this->_oCurrentGroup->getGroupPath();

							// Файл-источник
							$sSourceFile = $this->imagesPath . (
								strtoupper($this->encoding) == 'UTF-8'
									? $sData
									: Core_File::convertfileNameToLocalEncoding($sData)
							);
							$sSourceFileBaseName = basename($sSourceFile, '');

							$bHttp = strpos(strtolower($sSourceFile), "http://") === 0
								|| strpos(strtolower($sSourceFile), "https://") === 0;

							if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentGroup->createDir();

								if ($bHttp)
								{
									try {
										$sSourceFile = $this->_uploadHttpFile($sSourceFile);
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');
										$sSourceFile = NULL;
									}
								}
								else
								{
									$sSourceFile = CMS_FOLDER . $sSourceFile;
								}

								if (!$this->_oCurrentShop->change_filename)
								{
									$sTargetFileName = $sSourceFileBaseName;
								}
								else
								{
									$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
									$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
										? '.jpg'
										: ".{$sTargetFileExtension}";

									$sTargetFileName = "shop_group_image{$this->_oCurrentGroup->id}{$sTargetFileExtension}";
								}

								// Создаем массив параметров для загрузки картинок элементу
								$aPicturesParam = array();
								$aPicturesParam['large_image_isset'] = TRUE;
								$aPicturesParam['large_image_source'] = $sSourceFile;
								$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
								$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;

								$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
								$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
								$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
								$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;

								// Проверяем, передали ли нам малое изображение
								$iSmallImageIndex = array_search('group_small_image', $this->csv_fields);

								$bCreateSmallImage = $iSmallImageIndex === FALSE || strval($this->csv_fields[$iSmallImageIndex]) == '';

								if ($bCreateSmallImage)
								{
									// Малое изображение не передано, создаем его из большого
									$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
									$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
									$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
									$aPicturesParam['create_small_image_from_large'] = TRUE;
									$aPicturesParam['small_image_max_width'] = $this->_oCurrentShop->group_image_small_max_width;
									$aPicturesParam['small_image_max_height'] = $this->_oCurrentShop->group_image_small_max_height;
									$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
									$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];
								}
								else
								{
									$aPicturesParam['create_small_image_from_large'] = FALSE;
								}

								$aPicturesParam['large_image_max_width'] = $this->_oCurrentShop->group_image_large_max_width;
								$aPicturesParam['large_image_max_height'] = $this->_oCurrentShop->group_image_large_max_height;
								$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

								// Удаляем старое большое изображение
								if ($this->_oCurrentGroup->image_large)
								{
									try
									{
										Core_File::delete($this->_oCurrentGroup->getLargeFilePath());
									} catch (Exception $e) {}
								}

								// Удаляем старое малое изображение
								if ($bCreateSmallImage && $this->_oCurrentGroup->image_small)
								{
									try
									{
										Core_File::delete($this->_oCurrentGroup->getSmallFilePath());
									} catch (Exception $e) {}
								}

								try {
									Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
									$aTmpReturn = Core_Event::getLastReturn();
									is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

									$result = Core_File::adminUpload($aPicturesParam);
								}
								catch (Exception $e)
								{
									Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
										? $e->getMessage()
										: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
									, 'error');

									$result = array('large_image' => FALSE, 'small_image' => FALSE);
								}

								if ($result['large_image'])
								{
									$this->_oCurrentGroup->image_large = $sTargetFileName;

									$this->_oCurrentGroup->id
										&& $this->_oCurrentGroup->setLargeImageSizes()
										&& $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								if ($result['small_image'])
								{
									$this->_oCurrentGroup->image_small = "small_{$sTargetFileName}";

									$this->_oCurrentGroup->id && $this->_oCurrentGroup->setSmallImageSizes() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								if (strpos(basename($sSourceFile), "CMS") === 0)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFile);
								}
							}
						break;
						// Малая картинка группы товаров
						case 'group_small_image':
							// Для гарантии получения идентификатора группы
							$this->_oCurrentGroup->save();
							$this->_incUpdatedGroups($this->_oCurrentGroup->id);

							// Папка назначения
							$sDestinationFolder = $this->_oCurrentGroup->getGroupPath();

							// Файл-источник
							$sSourceFile = $this->imagesPath . (
								strtoupper($this->encoding) == 'UTF-8'
									? $sData
									: Core_File::convertfileNameToLocalEncoding($sData)
							);
							$sSourceFileBaseName = basename($sSourceFile, '');

							$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

							if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentGroup->createDir();

								if ($bHttp)
								{
									try {
										$sSourceFile = $this->_uploadHttpFile($sSourceFile);
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');
										$sSourceFile = NULL;
									}
								}
								else
								{
									$sSourceFile = CMS_FOLDER . $sSourceFile;
								}

								if (!$this->_oCurrentShop->change_filename)
								{
									$sTargetFileName = "small_{$sSourceFileBaseName}";
								}
								else
								{
									$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
									$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
										? '.jpg'
										: ".{$sTargetFileExtension}";

									$sTargetFileName = "small_shop_group_image{$this->_oCurrentGroup->id}{$sTargetFileExtension}";
								}

								$aPicturesParam = array();
								$aPicturesParam['small_image_source'] = $sSourceFile;
								$aPicturesParam['small_image_name'] = $sSourceFileBaseName;
								$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileName;
								$aPicturesParam['create_small_image_from_large'] = FALSE;
								$aPicturesParam['small_image_max_width'] = $this->_oCurrentShop->group_image_small_max_width;
								$aPicturesParam['small_image_max_height'] = $this->_oCurrentShop->group_image_small_max_height;
								$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
								$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
								$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
								$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
								$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;

								// Удаляем старое малое изображение
								if ($this->_oCurrentGroup->image_small)
								{
									try
									{
										Core_File::delete($this->_oCurrentGroup->getSmallFilePath());
									} catch (Exception $e) {}
								}

								try {
									Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
									$aTmpReturn = Core_Event::getLastReturn();
									is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

									$result = Core_File::adminUpload($aPicturesParam);
								}
								catch (Exception $e)
								{
									Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
										? $e->getMessage()
										: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
									, 'error');

									$result = array('small_image' => FALSE);
								}

								if ($result['small_image'])
								{
									$this->_oCurrentGroup->image_small = $sTargetFileName;

									$this->_oCurrentGroup->id && $this->_oCurrentGroup->setSmallImageSizes() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								if (strpos(basename($sSourceFile), "CMS") === 0)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFile);
								}
							}
						break;
						// GUID группы товаров
						case 'group_cml_id':
							if ($sData == 'ID00000000')
							{
								$oTmpObject = array(Core_Entity::factory('Shop_Group', 0));
							}
							else
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Groups;
								$oTmpObject->queryBuilder()
									->where('guid', '=', $sData)
									->limit(1);

								$oTmpObject = $oTmpObject->findAll(FALSE);
							}

							if (count($oTmpObject))
							{
								// группа найдена
								$this->_oCurrentGroup = $oTmpObject[0];
								$this->_oCurrentItem->shop_group_id = $this->_oCurrentGroup->id;
							}
							else
							{
								// группа не найдена, присваиваем group_cml_id текущей группе
								$this->_oCurrentGroup->guid = $sData;
								$this->_oCurrentGroup->id && $this->_doSaveGroup($this->_oCurrentGroup);
							}
						break;
						// GUID родительской группы товаров
						case 'group_parent_cml_id':
							$oTmpObject = Core_Entity::factory('Shop_Group', 0);
							if ($sData != 'ID00000000')
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Groups->getByGuid($sData, FALSE);
							}
							if (!is_null($oTmpObject))
							{
								if ($oTmpObject->id != $this->_oCurrentGroup->id)
								{
									$this->_oCurrentGroup->parent_id = $oTmpObject->id;
									$this->_oCurrentGroup->id
										&& $this->_oCurrentGroup->save()
										&& $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								//$this->_oCurrentItem->shop_group_id = $oTmpObject->id;
							}
						break;
						// идентификатор валюты
						case 'currency_id':
							$oTmpObject = Core_Entity::factory('Shop_Currency')->find($sData);
							if (!is_null($oTmpObject->id))
							{
								$this->_oCurrentItem->shop_currency_id = $oTmpObject->id;
							}
						break;
						// идентификатор налога
						case 'tax_id':
							$oTmpObject = Core_Entity::factory('Shop_Tax')->find($sData);
							if (!is_null($oTmpObject->id))
							{
								$this->_oCurrentItem->shop_tax_id = $oTmpObject->id;
							}
						break;
						// идентификатор производителя
						case 'producer_id':
							$oTmpObject = Core_Entity::factory('Shop_Producer')->find($sData);
							if (!is_null($oTmpObject->id))
							{
								$this->_oCurrentItem->shop_producer_id = $oTmpObject->id;
							}
						break;
						// Передано название производителя
						case 'producer_name':
							$oTmpObject = $this->_oCurrentShop->Shop_Producers;
							$oTmpObject->queryBuilder()->where('name', '=', $sData);
							$oTmpObject = $oTmpObject->findAll(FALSE);
							if (count($oTmpObject))
							{
								$this->_oCurrentItem->shop_producer_id = $oTmpObject[0]->id;
							}
							else
							{
								$this->_oCurrentItem->shop_producer_id = Core_Entity::factory('Shop_Producer')
									->name($sData)
									->path(Core_Str::transliteration($sData))
									->shop_id($this->_oCurrentShop->id)
									->save()
									->id;
							}
						break;
						// идентификатор продавца
						case 'seller_id':
							$oTmpObject = $this->_oCurrentShop->Shop_Sellers;
							$oTmpObject->queryBuilder()->where('id', '=', $sData);
							$oTmpObject = $oTmpObject->findAll(FALSE);
							if (count($oTmpObject))
							{
								$this->_oCurrentItem->shop_seller_id = $oTmpObject[0]->id;
							}
						break;
						// Передано название продавца
						case 'seller_name':
							$oTmpObject = $this->_oCurrentShop->Shop_Sellers;
							$oTmpObject->queryBuilder()->where('name', '=', $sData);
							$oTmpObject = $oTmpObject->findAll(FALSE);

							$this->_oCurrentItem->shop_seller_id = count($oTmpObject)
								? $oTmpObject[0]->id
								: Core_Entity::factory('Shop_Seller')
									->name($sData)
									->path(Core_Str::transliteration($sData))
									->shop_id($this->_oCurrentShop->id)
									->save()
									->id;
						break;
						// Yandex Market Sales Notes
						case 'item_yandex_market_sales_notes':
							$this->_oCurrentItem->yandex_market_sales_notes = $sData;
						break;
						// единица измерения
						case 'mesure_id':
							$oTmpObject = Core_Entity::factory("Shop_Measure")->find($sData);
							if (!is_null($oTmpObject->id))
							{
								$this->_oCurrentItem->shop_measure_id = $oTmpObject->id;
							}
						break;
						// Передано название единицы измерения
						case 'mesure_name':
							$oShop_Measure = Core_Entity::factory('Shop_Measure')->getByName($sData);

							$this->_oCurrentItem->shop_measure_id = !is_null($oShop_Measure)
								? $oShop_Measure->id
								: Core_Entity::factory('Shop_Measure')
									->name($sData)
									->description($sData)
									->save()
									->id;
						break;
						// "Ярлыки GUID" - дополнительные группы для товара (CML_ID групп через запятую)
						case 'additional_groups':
							$aShortcuts = explode(',', $sData);
							$this->_aAdditionalGroups = array_merge($this->_aAdditionalGroups, $aShortcuts);
						break;
						// Идентификатор товара
						case 'item_id':
							$oTmpObject = $this->_oCurrentShop->Shop_Items->getById($sData, FALSE);
							if (!is_null($oTmpObject))
							{
								//$this->_oCurrentItem->id = $oTmpObject->id;
								$this->_oCurrentItem = $oTmpObject;

								// 2 - не обновлять существующие товары
								if ($this->importAction == 2
									&& !isset($this->_ShopItemCreatedIDs[$this->_oCurrentItem->id])
								)
								{
									continue 3;
								}
							}
						break;
						// Название товара
						case 'item_name':
							$this->_oCurrentItem->name = $sData;
						break;
						// артикул товара
						case 'item_marking':
							Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeFindByMarking', $this, array($this->_oCurrentShop, $this->_oCurrentItem));

							$this->_oCurrentItem->marking = $sData;

							if ($bMarkingItemSearchFields)
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Items;
								$oTmpObject->queryBuilder()
									->where('marking', 'LIKE', $sData)
									->limit(1);

								$aTmpObject = $oTmpObject->findAll(FALSE);

								if (count($aTmpObject))
								{
									$this->_oCurrentItem = $aTmpObject[0];

									// 2 - не обновлять существующие товары
									if ($this->importAction == 2
										&& !isset($this->_ShopItemCreatedIDs[$this->_oCurrentItem->id])
									)
									{
										continue 3;
									}
								}
							}

							Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterFindByMarking', $this, array($this->_oCurrentShop, $this->_oCurrentItem));
						break;
						// дата добавления товара
						case 'item_datetime':
							if (preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData))
							{
								$this->_oCurrentItem->datetime = $sData;
							}
							else
							{
								$this->_oCurrentItem->datetime = Core_Date::datetime2sql($sData);
							}
						break;
						// Передано описание товара
						case 'item_description':
							$this->_oCurrentItem->description = $sData;
						break;
						// текст товара
						case 'item_text':
							$this->_oCurrentItem->text = $sData;
						break;
						// большая картинка товара, обработка будет после вставки товара
						case 'item_image':
							/*if ($sData != '')
							{*/
							$this->_sBigImageFile = $sData;
							//}
						break;
						// малая картинка товара, обработка будет после вставки товара
						case 'item_small_image':
							/*if ($sData != '')
							{*/
							$this->_sSmallImageFile = $sData;
							//}
						break;
						// Переданы метки товара, обработка будет после вставки товара
						case 'item_tags':
							$this->_sCurrentTags = $sData;
						break;
						// вес товара
						case 'item_weight':
							$this->_oCurrentItem->weight = Shop_Controller::instance()->convertPrice($sData);
						break;
						// длина
						case 'item_length':
							$this->_oCurrentItem->length = Shop_Controller::instance()->convertPrice($sData);
						break;
						// ширина
						case 'item_width':
							$this->_oCurrentItem->width = Shop_Controller::instance()->convertPrice($sData);
						break;
						// высота
						case 'item_height':
							$this->_oCurrentItem->height = Shop_Controller::instance()->convertPrice($sData);
						break;
						// цена товара
						case 'item_price':
							$this->_oCurrentItem->price = Shop_Controller::instance()->convertPrice($sData);
						break;
						// активность товара
						case 'item_active':
							$this->_oCurrentItem->active = $sData;
						break;
						// порядок сортировки товара
						case 'item_sorting':
							$this->_oCurrentItem->sorting = $sData;
						break;
						// путь товара
						case 'item_path':
							if ($bPathItemSearchFields)
							{
								// Товар не был найден ранее, например, по артикулу
								if (!$this->_oCurrentItem->id)
								{
									$oTmpObject = $this->_oCurrentShop->Shop_Items;
									$oTmpObject->queryBuilder()
										->where('path', 'LIKE', $sData)
										->where('shop_group_id', '=', $this->_oCurrentGroup->id);

									$oTmpObject = $oTmpObject->findAll(FALSE);

									if (count($oTmpObject))
									{
										$this->_oCurrentItem = $oTmpObject[0];

										// 2 - не обновлять существующие товары
										if ($this->importAction == 2
											&& !isset($this->_ShopItemCreatedIDs[$this->_oCurrentItem->id])
										)
										{
											continue 3;
										}
									}
								}
							}

							$this->_oCurrentItem->path = $sData;
						break;
						// Seo Title для товара
						case 'item_seo_title':
							$this->_oCurrentItem->seo_title = $sData;
						break;
						// Seo Description для товара
						case 'item_seo_description':
							$this->_oCurrentItem->seo_description = $sData;
						break;
						// Seo Keywords для товара
						case 'item_seo_keywords':
							$this->_oCurrentItem->seo_keywords = $sData;
						break;
						// флаг индексации товара
						case 'item_indexing':
							$this->_oCurrentItem->indexing = $sData;
						break;
						// Yandex Market Allow
						case 'item_yandex_market_allow':
							$this->_oCurrentItem->yandex_market = $sData;
						break;
						// Yandex Market BID
						case 'item_yandex_market_bid':
							$this->_oCurrentItem->yandex_market_bid = $sData;
						break;
						// Yandex Market CID
						case 'item_yandex_market_cid':
							$this->_oCurrentItem->yandex_market_cid = $sData;
						break;
						// Гарантия производителя
						case 'item_manufacturer_warranty':
							$this->_oCurrentItem->manufacturer_warranty = ($sData == '1' ? 1 : 0);
						break;
						// vendorCode
						case 'item_vendorcode':
							$this->_oCurrentItem->vendorcode = $sData;
						break;
						// Страна производства
						case 'item_country_of_origin':
							$this->_oCurrentItem->country_of_origin = $sData;
						break;
						// артикул родительского товара (модификация)
						case 'item_parent_marking':
						// CML ID родительского товара (модификация)
						case 'item_parent_guid':
							$oTmpObject = $this->_oCurrentShop->Shop_Items;
							$oTmpObject->queryBuilder()->where(
								$this->csv_fields[$iKey] == 'item_parent_marking'
									? 'marking'
									: 'guid',
								'=',
								$sData
							);

							$oTmpObject = $oTmpObject->findAll(FALSE);

							if (count($oTmpObject) && $this->_oCurrentItem->id != $oTmpObject[0]->id)
							{
								$this->_oCurrentItem->shop_group_id = 0;
								$this->_oCurrentItem->modification_id = $oTmpObject[0]->id;
							}
						break;
						// идентификатор пользователя сайта
						case 'item_siteuser_id':
							$this->_oCurrentItem->siteuser_id = $sData;
						break;
						// артикул родительского товара для сопутствующего товара
						case 'item_parent_associated':
							$this->_sAssociatedItemMark = $sData;
						break;
						// артикулы сопутствующих товаров
						case 'item_associated_markings':
							$aTmp_Markings = explode(',', $sData);
							$aTmp_Markings = array_map('trim', $aTmp_Markings);

							foreach ($aTmp_Markings as $sAssociatedMarking)
							{
								if ($this->_oCurrentItem->id && $sAssociatedMarking != '')
								{
									$oTmp_Shop_Item = $this->_oCurrentShop
										->Shop_Items
										->getByMarking($sAssociatedMarking, FALSE);

									if (!is_null($oTmp_Shop_Item)
										// Ранее не было связи с ассоциированным
										&& is_null($this->_oCurrentItem->Shop_Item_Associateds->getByAssociatedId($oTmp_Shop_Item->id, FALSE))
									)
									{
										Core_Entity::factory('Shop_Item_Associated')
											->shop_item_id($this->_oCurrentItem->id) // Кому
											->shop_item_associated_id($oTmp_Shop_Item->id) // Кто
											->count(1)
											->save();
									}
								}
							}
						break;
						case 'item_digital_name':
							$this->_oCurrentShopEItem->name = $sData;
							$this->_oCurrentItem->type = 1;
						break;
						case 'item_digital_text':
							$this->_oCurrentShopEItem->value = $sData;
							$this->_oCurrentItem->type = 1;
						break;
						case 'item_digital_file':
							$this->_oCurrentShopEItem->filename = $sData;
							$this->_oCurrentItem->type = 1;
						break;
						case 'item_digital_count':
							$this->_oCurrentShopEItem->count = $sData;
							$this->_oCurrentItem->type = 1;
						break;
						case 'item_end_datetime':
							// дата завершения публикации, проверяем ее на соответствие стандарту времени MySQL
							$this->_oCurrentItem->end_datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
								? $sData
								: Core_Date::datetime2sql($sData);
						break;
						case 'item_start_datetime':
							// дата завершения публикации, проверяем ее на соответствие стандарту времени MySQL
							$this->_oCurrentItem->start_datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
								? $sData
								: Core_Date::datetime2sql($sData);
						break;
						case 'item_type':
							$this->_oCurrentItem->type = $sData;
						break;
						case 'item_special_price_from':
							$this->_oCurrentShopSpecialPrice->min_quantity = $sData;
						break;
						case 'item_special_price_to':
							$this->_oCurrentShopSpecialPrice->max_quantity = $sData;
						break;
						case 'item_special_price_price':
							$this->_oCurrentShopSpecialPrice->price = $sData;
						break;
						case 'item_special_price_percent':
							$this->_oCurrentShopSpecialPrice->percent = $sData;
						break;
						case 'item_cml_id':
							if ($bCmlIdItemSearchFields)
							{
								// Товар не был найден ранее, например, по артикулу
								if (!$this->_oCurrentItem->id)
								{
									$oTmpObject = $this->_oCurrentShop->Shop_Items;
									$oTmpObject->queryBuilder()
										->where('guid', '=', $sData)
										->limit(1);

									$oTmpObject = $oTmpObject->findAll(FALSE);

									if (count($oTmpObject))
									{
										$this->_oCurrentItem = $oTmpObject[0];

										// 2 - не обновлять существующие товары
										if ($this->importAction == 2
											&& !isset($this->_ShopItemCreatedIDs[$this->_oCurrentItem->id])
										)
										{
											continue 3;
										}
									}
								}
							}

							$this->_oCurrentItem->guid = $sData;
						break;
						default:
							$sFieldName = $this->csv_fields[$iKey];

							Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeCaseDefault', $this, array($sFieldName, $sData));

							if (strpos($sFieldName, "price-") === 0)
							{
								// Дополнительная цена товара
								$aTmpExplode = explode('-', $sFieldName);
								$this->_aExternalPrices[$aTmpExplode[1]] = $sData;
							}

							if (strpos($sFieldName, "warehouse-") === 0)
							{
								// Остаток на складе N
								$aTmpExplode = explode('-', $sFieldName);
								$this->_aWarehouses[$aTmpExplode[1]] = $sData;
							}

							if (strpos($sFieldName, "propsmall-") === 0)
							{
								// Дополнительный файл дополнительного свойства/Малое изображение картинки дополнительного свойства
								$aTmpExplode = explode('-', $sFieldName);
								$this->_aExternalPropertiesSmall[$aTmpExplode[1]] = $sData;
							}

							if (strpos($sFieldName, "propdesc-") === 0)
							{
								// Описание дополнительного свойства
								$aTmpExplode = explode('-', $sFieldName);
								$this->_aExternalPropertiesDesc[$aTmpExplode[1]] = $sData;
							}

							if (strpos($sFieldName, "prop-") === 0)
							{
								// Основной файл дополнительного свойства/Большое изображение картинки дополнительного свойства
								$aTmpExplode = explode('-', $sFieldName);
								$this->_aExternalProperties[$aTmpExplode[1]] = $sData;
							}

							if (strpos($sFieldName, "prop_group-") === 0)
							{
								// Дополнительное свойство группы товаров
								$aTmpExplode = explode('-', $sFieldName);
								$iPropertyId = $aTmpExplode[1];

								$oProperty = Core_Entity::factory('Property', $iPropertyId);

								Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportGroupProperty', $this, array($this->_oCurrentShop, $this->_oCurrentGroup, $oProperty, $sData));

								$aPropertyValues = $oProperty->getValues($this->_oCurrentGroup->id, FALSE);

								$oProperty_Value = isset($aPropertyValues[0])
									? $aPropertyValues[0]
									: $oProperty->createNewValue($this->_oCurrentGroup->id);

								switch ($oProperty->type)
								{
									// Файл
									case 2:
										// Для гарантии получения идентификатора группы
										$this->_oCurrentGroup->save();
										$this->_incUpdatedGroups($this->_oCurrentGroup->id);

										// Папка назначения
										$sDestinationFolder = $this->_oCurrentGroup->getGroupPath();

										// Файл-источник
										$sSourceFile = $this->imagesPath . (
											strtoupper($this->encoding) == 'UTF-8'
												? $sData
												: Core_File::convertfileNameToLocalEncoding($sData)
										);
										$sSourceFileBaseName = basename($sSourceFile, '');

										$bHttp = strpos(strtolower($sSourceFile), 'http://') === 0 || strpos(strtolower($sSourceFile), 'https://') === 0;

										if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
										{
											// Создаем папку назначения
											$this->_oCurrentGroup->createDir();

											if ($bHttp)
											{
												try {
													$sSourceFile = $this->_uploadHttpFile($sSourceFile);
												}
												catch (Exception $e)
												{
													Core_Message::show($e->getMessage(), 'error');
													$sSourceFile = NULL;
												}
											}
											else
											{
												$sSourceFile = CMS_FOLDER . ltrim($sSourceFile, '/\\');
											}

											if (!$this->_oCurrentShop->change_filename)
											{
												$sTargetFileName = $sSourceFileBaseName;
											}
											else
											{
												$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
												$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
													? '.jpg'
													: ".{$sTargetFileExtension}";

												$oProperty_Value->save();
												$sTargetFileName = "shop_property_file_{$this->_oCurrentGroup->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
											}

											// Создаем массив параметров для загрузки картинок элементу
											$aPicturesParam = array();
											$aPicturesParam['large_image_isset'] = TRUE;
											$aPicturesParam['large_image_source'] = $sSourceFile;
											$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
											$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;

											$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
											$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
											$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
											$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;

											// Малое изображение для дополнительных свойств создается всегда
											$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
											$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
											$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
											$aPicturesParam['create_small_image_from_large'] = TRUE;
											$aPicturesParam['small_image_max_width'] = $this->_oCurrentShop->group_image_small_max_width;
											$aPicturesParam['small_image_max_height'] = $this->_oCurrentShop->group_image_small_max_height;
											$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
											$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio_small;

											$aPicturesParam['large_image_max_width'] = $this->_oCurrentShop->group_image_large_max_width;
											$aPicturesParam['large_image_max_height'] = $this->_oCurrentShop->group_image_large_max_height;
											$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

											// Удаляем старое большое изображение
											if ($oProperty_Value->file != '')
											{
												try
												{
													Core_File::delete($sDestinationFolder . $oProperty_Value->file);
												} catch (Exception $e) {
												}
											}

											// Удаляем старое малое изображение
											if ($oProperty_Value->file_small != '')
											{
												try
												{
													Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
												} catch (Exception $e) {
												}
											}

											try {
												Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
												$aTmpReturn = Core_Event::getLastReturn();
												is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

												$result = Core_File::adminUpload($aPicturesParam);
											}
											catch (Exception $e)
											{
												Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
													? $e->getMessage()
													: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
												, 'error');

												$result = array('large_image' => FALSE, 'small_image' => FALSE);
											}

											if ($result['large_image'])
											{
												$oProperty_Value->file = $sTargetFileName;
												$oProperty_Value->file_name = '';
											}

											if ($result['small_image'])
											{
												$oProperty_Value->file_small = "small_{$sTargetFileName}";
												$oProperty_Value->file_small_name = '';
											}

											if (strpos(basename($sSourceFile), "CMS") === 0)
											{
												// Файл временный, подлежит удалению
												Core_File::delete($sSourceFile);
											}
										}
									break;
									// Список
									case 3:
										if (Core::moduleIsActive('list'))
										{
											$oListItem = Core_Entity::factory('List', $oProperty->list_id)
												->List_Items
												->getByValue($sData, FALSE);

											if (is_null($oListItem))
											{
												$oListItem = Core_Entity::factory('List_Item')
													->list_id($oProperty->list_id)
													->value($sData)
													->save();
											}

											$oProperty_Value->setValue($oListItem->id);
										}
									break;
									case 5: // Informationsystem
										$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getByName($sPropertyValue);
										if ($oInformationsystem_Item)
										{
											$oProperty_Value->setValue($oInformationsystem_Item->id);
										}
										elseif (is_numeric($sPropertyValue))
										{
											$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getById($sPropertyValue);

											$oInformationsystem_Item && $oProperty_Value->setValue($oInformationsystem_Item->id);
										}
									break;
									case 8:
										if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sData))
										{
											$sData = Core_Date::datetime2sql($sData);
										}

										$oProperty_Value->setValue($sData);
									break;
									case 9:
										if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData))
										{
											$sData = Core_Date::datetime2sql($sData);
										}

										$oProperty_Value->setValue($sData);
									break;
									case 11: // Float
										$sData = Shop_Controller::instance()->convertFloat($sData);
										$oProperty_Value->setValue($sData);
									break;
									case 12: // Shop
										$oShop_Item = $oProperty->Shop->Shop_Items->getByName($sPropertyValue);
										if ($oShop_Item)
										{
											$oProperty_Value->setValue($oShop_Item->id);
										}
										elseif (is_numeric($sPropertyValue))
										{
											$oShop_Item = $oProperty->Shop->Shop_Items->getById($sPropertyValue);

											$oShop_Item && $oProperty_Value->setValue($oShop_Item->id);
										}
									break;
									default:
										$oProperty_Value->setValue($sData);
									break;
								}

								$oProperty_Value->save();
							}
						break;
					}
				}
			}

			if ($this->searchIndexation && $this->_oCurrentGroup->id)
			{
				Core_Entity::factory('Shop_Group', $this->_oCurrentGroup->id)->index();
			}

			!$this->_oCurrentItem->modification_id
				&& !$this->_oCurrentItem->id
				&& $this->_oCurrentItem->shop_group_id = intval($this->_oCurrentGroup->id);

			!$this->_oCurrentItem->id
				&& is_null($this->_oCurrentItem->path)
				&& $this->_oCurrentItem->path = '';

			$this->_oCurrentItem->id
				&& $this->_oCurrentItem->id == $this->_oCurrentItem->modification_id
				&& $this->_oCurrentItem->modification_id = 0;

			if (!is_null($this->_oCurrentOrder))
			{
				$this->_oCurrentShop->add($this->_oCurrentOrder);
			}

			if ($this->_oCurrentItem->id && $this->importAction == 2)
			{
				// если сказано - оставить без изменений, затираем все изменения
				$this->_oCurrentItem = Core_Entity::factory('Shop_Item')->find($this->_oCurrentItem->id);
				$this->_sBigImageFile = '';
				$this->_sSmallImageFile = '';
				$this->deleteImage = 0;
			}

			// Обязательно после обработки тегов, т.к. иначе ORM сохранит товар косвенно.
			$this->_oCurrentItem->shop_id = $this->_oCurrentShop->id;

			if ($this->_oCurrentItem->id
			//&& $this->importAction == 1
			&& !is_null($this->_oCurrentItem->name)
			&& $this->_oCurrentItem->save())
			{
				$this->_incUpdatedItems($this->_oCurrentItem->id);
			}
			elseif (!is_null($this->_oCurrentItem->name) && $this->_oCurrentItem->save())
			{
				if (intval($this->_oCurrentItem->shop_currency_id) == 0)
				{
					$this->_oCurrentItem->shop_currency_id = $this->_oCurrentShop->shop_currency_id;
					$this->_oCurrentItem->save();
				}
				$this->_incInsertedItems($this->_oCurrentItem->id);

				// Добавлем в список созданных товаров
				$this->_ShopItemCreatedIDs[$this->_oCurrentItem->id] = $this->_oCurrentItem->id;
			}

			$aTagsName = array();
			/*if (!$this->_oCurrentItem->id)
			{*/
			if (Core::moduleIsActive('tag'))
			{
				// Вставка тэгов автоматически разрешена
				if ($this->_sCurrentTags == '' && $this->_oCurrentShop->apply_tags_automatically)
				{
					$sTmpString = '';
					$sTmpString .= $this->_oCurrentItem->name ? ' ' . $this->_oCurrentItem->name : '';
					$sTmpString .= $this->_oCurrentItem->description ? ' ' . $this->_oCurrentItem->description : '';
					$sTmpString .= $this->_oCurrentItem->text ? ' ' . $this->_oCurrentItem->text : '';

					// получаем хэш названия и описания группы
					$aText = Core_Str::getHashes($sTmpString, array ('hash_function' => 'crc32'));

					$aText = array_unique($aText);

					// Получаем список меток
					$aTags = $this->_getTags();

					if (count($aTags))
					{
						// Удаляем уже существующие связи с метками
						$this->_oCurrentItem->Tag_Shop_Items->deleteAll(FALSE);

						foreach ($aTags as $iTagId => $sTagName)
						{
							$aTmpTags = Core_Str::getHashes($sTagName, array ('hash_function' => 'crc32'));
							$aTmpTags = array_unique($aTmpTags);

							if (count($aText) >= count($aTmpTags))
							{
								// Расчитываем пересечение
								$iIntersect = count(array_intersect($aText, $aTmpTags));

								$iCoefficient = count($aTmpTags) != 0
									? $iIntersect / count($aTmpTags)
									: 0;

								// Найдено полное вхождение
								if ($iCoefficient == 1)
								{
									// Если тэг еще не учтен
									if (!in_array($sTagName, $aTmpTags))
									{
										// Добавляем в массив
										$aTagsName[] = $sTagName;

										// Add relation
										$this->_oCurrentItem->add(
											Core_Entity::factory('Tag', $iTagId)
										);
									}
								}
							}
						}
					}
				}
				elseif ($this->_sCurrentTags != '')
				{
					$this->_oCurrentItem->id && $this->_oCurrentItem->applyTags($this->_sCurrentTags);
				}
			}
			//}

			if ($this->_oCurrentItem->seo_keywords == '' && count($aTagsName))
			{
				$this->_oCurrentItem->seo_keywords = implode(", ", $aTagsName);
				$this->_oCurrentItem->save();
			}

			if ($this->_oCurrentItem->id)
			{
				Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAssociated', $this, array($this->_oCurrentShop, $this->_oCurrentItem, $aCsvLine));

				if ($this->_sAssociatedItemMark)
				{
					$oShop_Item = $this->_oCurrentShop->Shop_Items->getByMarking($this->_sAssociatedItemMark, FALSE);

					if (!is_null($oShop_Item)
						// Ранее не было связи с ассоциированным
						&& is_null($oShop_Item->Shop_Item_Associateds->getByAssociatedId($this->_oCurrentItem->id, FALSE))
					)
					{
						Core_Entity::factory('Shop_Item_Associated')
							->shop_item_id($oShop_Item->id) // Кому
							->shop_item_associated_id($this->_oCurrentItem->id) // Кто
							->count(1)
							->save();
					}
				}

				// Обрабатываем склады
				foreach ($this->_aWarehouses as $iWarehouseID => $iWarehouseCount)
				{
					$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse')->find($iWarehouseID);

					// Если склада не существует, связь не добавляется
					if (!is_null($oShop_Warehouse->id))
					{
						$oShop_Warehouse_Item = $oShop_Warehouse
							->Shop_Warehouse_Items
							->getByShopItemId($this->_oCurrentItem->id, FALSE);

						if (is_null($oShop_Warehouse_Item))
						{
							$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item');
							$oShop_Warehouse_Item->shop_warehouse_id = $iWarehouseID;
							$oShop_Warehouse_Item->shop_item_id = $this->_oCurrentItem->id;
						}
						$oShop_Warehouse_Item->count = Shop_Controller::instance()->convertPrice($iWarehouseCount);
						$oShop_Warehouse_Item->save();
					}
				}

				// Обрабатываем специальные цены
				if ($this->_oCurrentShopSpecialPrice->changed())
				{
					$oTmpObject = Core_Entity::factory('Shop_Specialprice');
					$oTmpObject->queryBuilder()
						->where('shop_item_id', '=', $this->_oCurrentItem->id)
						->where('min_quantity', '=', $this->_oCurrentShopSpecialPrice->min_quantity)
						->where('max_quantity', '=', $this->_oCurrentShopSpecialPrice->max_quantity)
						->where('price', '=', $this->_oCurrentShopSpecialPrice->price)
						->where('percent', '=', $this->_oCurrentShopSpecialPrice->percent)
					;

					// Добавляем специальную цену, если её ещё не существовало
					if ($oTmpObject->getCount(FALSE) == 0)
					{
						$this->_oCurrentShopSpecialPrice->shop_item_id = $this->_oCurrentItem->id;
						$this->_oCurrentShopSpecialPrice->save();
					}
				}

				// Обрабатываем ярлыки
				if (count($this->_aAdditionalGroups))
				{
					$this->_aAdditionalGroups = array_map('trim', $this->_aAdditionalGroups);

					$aShopGroups = $this->_oCurrentShop->Shop_Groups;
					$aShopGroups
						->queryBuilder()
						->where('guid', 'IN', $this->_aAdditionalGroups);

					$aShopGroups = $aShopGroups->findAll(FALSE);

					foreach ($aShopGroups as $oShopGroup)
					{
						$aShopItems = $this->_oCurrentShop->Shop_Items;
						$aShopItems->queryBuilder()
							->where('shortcut_id', '=', $this->_oCurrentItem->id)
							->where('shop_group_id', '=', $oShopGroup->id)
							->limit(1);

						$iCountShortcuts = $aShopItems->getCount(FALSE);

						if (!$iCountShortcuts)
						{
							Core_Entity::factory('Shop_Item')
								->shop_group_id($oShopGroup->id)
								->shortcut_id($this->_oCurrentItem->id)
								->shop_id($this->_oCurrentShop->id)
								->save();
						}
					}
				}

				// Обрабатываем электронные файлы электронного товара
				if ($this->_oCurrentItem->type == 1)
				{
					$this->_oCurrentShopEItem->shop_item_id = $this->_oCurrentItem->id;
					$sAdditionalPath = dirname($this->_oCurrentShopEItem->filename);
					$this->_oCurrentShopEItem->name = basename($this->_oCurrentShopEItem->filename);
					$this->_oCurrentShopEItem->filename = $this->_oCurrentShopEItem->name;
					$this->_oCurrentShopEItem->save();

					$sExtension = Core_File::getExtension($this->_oCurrentShopEItem->filename);

					$sSourceFile = CMS_FOLDER . $this->imagesPath . $sAdditionalPath . '/' . $this->_oCurrentShopEItem->filename;
					$sTargetFile = $this->_oCurrentShop->getPath() . '/eitems/item_catalog_' . $this->_oCurrentItem->id . '/' . $this->_oCurrentShopEItem->id . ($sExtension == '' ? '' : '.' . $sExtension);

					if (is_file($sSourceFile)
						&& Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']))
					{
						try
						{
							Core_File::copy($sSourceFile, $sTargetFile);
						} catch (Exception $e) {}
					}
				}

				if (/*!is_null($this->_sBigImageFile) && */$this->_sBigImageFile != ''/* && $this->importAction != 2*/)
				{
					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sSourceFile = $sOriginalSourceFile = $this->imagesPath . (
						strtoupper($this->encoding) == 'UTF-8'
							? $this->_sBigImageFile
							: Core_File::convertfileNameToLocalEncoding($this->_sBigImageFile)
					);
					$sSourceFileBaseName = basename($sSourceFile, '');

					$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

					if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension'])
						|| $bHttp)
					{
						// Удаляем папку назначения вместе со всеми старыми файлами
						//Core_File::deleteDir($sDestinationFolder);

						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFile = $this->_uploadHttpFile($sSourceFile);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFile = NULL;
							}
						}
						else
						{
							$sSourceFile = CMS_FOLDER . trim(Core_File::pathCorrection($sSourceFile), DIRECTORY_SEPARATOR);
						}

						if (!$this->_oCurrentShop->change_filename)
						{
							$sTargetFileName = $sSourceFileBaseName;
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
							$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
								? '.jpg'
								: ".{$sTargetFileExtension}";

							$sTargetFileName = "item_image{$this->_oCurrentItem->id}{$sTargetFileExtension}";
						}

						if ($this->_oCurrentItem->image_large != '')
						{
							if ($sDestinationFolder . $this->_oCurrentItem->image_large != $sSourceFile)
							{
								try
								{
									Core_File::delete($sDestinationFolder . $this->_oCurrentItem->image_large);
								} catch (Exception $e) {}
							}
						}

						// Создаем массив параметров для загрузки картинок элементу
						$aPicturesParam = array();
						$aPicturesParam['large_image_isset'] = TRUE;
						$aPicturesParam['large_image_source'] = $sSourceFile;
						$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
						$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
						$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
						$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
						$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
						$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;

						// Проверяем, передали ли нам малое изображение
						if (is_null($this->_oCurrentItem->image_small) || $this->_oCurrentItem->image_small == '')
						{
							// Малое изображение не передано, создаем его из большого
							$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
							$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
							$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
							$aPicturesParam['create_small_image_from_large'] = TRUE;
							$aPicturesParam['small_image_max_width'] = $this->_oCurrentShop->image_small_max_width;
							$aPicturesParam['small_image_max_height'] = $this->_oCurrentShop->image_small_max_height;
							$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
							$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio_small;
						}
						else
						{
							$aPicturesParam['create_small_image_from_large'] = FALSE;
						}

						$aPicturesParam['large_image_max_width'] = $this->_oCurrentShop->image_large_max_width;
						$aPicturesParam['large_image_max_height'] = $this->_oCurrentShop->image_large_max_height;
						$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

						try
						{
							Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
							$aTmpReturn = Core_Event::getLastReturn();
							is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

							$result = Core_File::adminUpload($aPicturesParam);
						}
						catch (Exception $e)
						{
							$sMessage = 'Source path: ' . $sOriginalSourceFile . PHP_EOL . $e->getMessage();

							Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
								? $sMessage
								: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $sMessage)
							, 'error');

							$result = array('large_image' => FALSE, 'small_image' => FALSE);
						}

						if ($result['large_image'])
						{
							$this->_oCurrentItem->image_large = $sTargetFileName;
							$this->_oCurrentItem->setLargeImageSizes();
						}

						if ($result['small_image'])
						{
							$this->_oCurrentItem->image_small = "small_{$sTargetFileName}";
							$this->_oCurrentItem->setSmallImageSizes();
						}

						if (strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}
				}
				elseif ($this->deleteImage)
				{
					// Удалить текущее большое изображение
					if ($this->_oCurrentItem->image_large != '')
					{
						try
						{
							Core_File::delete($this->_oCurrentItem->getItemPath() . $this->_oCurrentItem->image_large);
						} catch (Exception $e) {}
					}
				}

				if ($this->_sSmallImageFile != ''
				|| ($this->_sBigImageFile != ''
				&& !$this->deleteImage))
				{
					$this->_sSmallImageFile == '' && $this->_sSmallImageFile = $this->_sBigImageFile;

					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sSourceFile = $this->imagesPath . (
						strtoupper($this->encoding) == 'UTF-8'
							? $this->_sSmallImageFile
							: Core_File::convertfileNameToLocalEncoding($this->_sSmallImageFile)
					);

					$sSourceFileBaseName = basename($sSourceFile, '');

					$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

					if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFile = $this->_uploadHttpFile($sSourceFile);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFile = NULL;
							}
						}
						else
						{
							$sSourceFile = CMS_FOLDER . trim(Core_File::pathCorrection($sSourceFile), DIRECTORY_SEPARATOR);
						}

						if (!$this->_oCurrentShop->change_filename)
						{
							$sTargetFileName = "small_{$sSourceFileBaseName}";
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
							$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
								? '.jpg'
								: ".{$sTargetFileExtension}";

							$sTargetFileName = "small_item_image{$this->_oCurrentItem->id}{$sTargetFileExtension}";
						}

						if (is_file($sSourceFile) && filesize($sSourceFile))
						{
							// Удаляем старое малое изображение
							if ($this->_oCurrentItem->image_small != '')
							{
								if ($sDestinationFolder . $this->_oCurrentItem->image_small != $sSourceFile)
								{
									try
									{
										Core_File::delete($sDestinationFolder . $this->_oCurrentItem->image_small);
									} catch (Exception $e) {}
								}
							}

							$aPicturesParam = array();
							$aPicturesParam['small_image_source'] = $sSourceFile;
							$aPicturesParam['small_image_name'] = $sSourceFileBaseName;
							$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileName;
							$aPicturesParam['create_small_image_from_large'] = FALSE;
							$aPicturesParam['small_image_max_width'] = $this->_oCurrentShop->image_small_max_width;
							$aPicturesParam['small_image_max_height'] = $this->_oCurrentShop->image_small_max_height;
							$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
							$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
							$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
							$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
							$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio_small;

							try {
								Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
								$aTmpReturn = Core_Event::getLastReturn();
								is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

								$result = Core_File::adminUpload($aPicturesParam);
							}
							catch (Exception $e)
							{
								Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
									? $e->getMessage()
									: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
								, 'error');

								$result = array('small_image' => FALSE);
							}

							if ($result['small_image'])
							{
								$this->_oCurrentItem->image_small = $sTargetFileName;
								$this->_oCurrentItem->setSmallImageSizes();
							}
						}

						if (strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}

					$this->_sSmallImageFile = '';
				}
				elseif ($this->deleteImage)
				{
					if ($this->_oCurrentItem->image_small != '')
					{
						try
						{
							Core_File::delete($this->_oCurrentItem->getItemPath() . $this->_oCurrentItem->image_small);
						} catch (Exception $e) {}
					}
				}
				$this->_sBigImageFile = '';

				foreach ($this->_aExternalProperties as $iPropertyID => $sPropertyValue)
				{
					$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

					Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportItemProperty', $this, array($this->_oCurrentShop, $this->_oCurrentItem, $oProperty, $sPropertyValue));

					$iShop_Item_Property_Id = $oProperty->Shop_Item_Property->id;

					$group_id = $this->_oCurrentItem->modification_id == 0
						? $this->_oCurrentItem->shop_group_id
						: $this->_oCurrentItem->Modification->shop_group_id;

					// Проверяем доступность дополнительного свойства для группы товаров
					if (is_null(Core_Entity::factory('Shop', $this->_oCurrentShop->id)
						->Shop_Item_Property_For_Groups
						->getByShopItemPropertyIdAndGroupId($iShop_Item_Property_Id, $group_id)))
					{
						// Свойство не доступно текущей группе, делаем его доступным
						$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
						$oShop_Item_Property_For_Group->shop_group_id = intval($group_id);
						$oShop_Item_Property_For_Group->shop_item_property_id = $iShop_Item_Property_Id;
						$oShop_Item_Property_For_Group->shop_id = $this->_oCurrentShop->id;
						$oShop_Item_Property_For_Group->save();
					}

					$aPropertyValues = $oProperty->getValues($this->_oCurrentItem->id, FALSE);

					if (!isset($this->_aClearedPropertyValues[$this->_oCurrentItem->id]) || !in_array($oProperty->guid, $this->_aClearedPropertyValues[$this->_oCurrentItem->id]))
					{
						foreach ($aPropertyValues as $oPropertyValue)
						{
							$oProperty->type == 2
								&& $oPropertyValue->setDir($this->_oCurrentItem->getItemPath());
							$oPropertyValue->delete();
						}

						$aPropertyValues = array();

						$this->_aClearedPropertyValues[$this->_oCurrentItem->id][] = $oProperty->guid;
					}

					if ($oProperty->multiple)
					{
						$oProperty_Value = $oProperty->createNewValue($this->_oCurrentItem->id);
					}
					else
					{
						$oProperty_Value = isset($aPropertyValues[0])
							? $aPropertyValues[0]
							: $oProperty->createNewValue($this->_oCurrentItem->id);
					}

					switch ($oProperty->type)
					{
						// Файл
						case 2:
							// Папка назначения
							$sDestinationFolder = $this->_oCurrentItem->getItemPath();

							// Файл-источник
							$sSourceFile = $this->imagesPath . (
								strtoupper($this->encoding) == 'UTF-8'
									? $sPropertyValue
									: Core_File::convertfileNameToLocalEncoding($sPropertyValue)
							);

							$sSourceFileBaseName = basename($sSourceFile, '');

							$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

							if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentItem->createDir();

								if ($bHttp)
								{
									try {
										$sSourceFile = $this->_uploadHttpFile($sSourceFile);
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');
										$sSourceFile = NULL;
									}
								}
								else
								{
									$sSourceFile = CMS_FOLDER . ltrim($sSourceFile, '/\\');
								}

								if (!$this->_oCurrentShop->change_filename)
								{
									$sTargetFileName = $sSourceFileBaseName;
								}
								else
								{
									$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
									$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
										? '.jpg'
										: ".{$sTargetFileExtension}";

									$oProperty_Value->save();
									$sTargetFileName = "shop_property_file_{$this->_oCurrentItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
									//$sTargetFileName = "shop_property_file_{$this->_oCurrentItem->id}_{$oProperty->id}{$sTargetFileExtension}";
								}

								// Создаем массив параметров для загрузки картинок элементу
								$aPicturesParam = array();
								$aPicturesParam['large_image_isset'] = TRUE;
								$aPicturesParam['large_image_source'] = $sSourceFile;
								$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
								$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
								$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
								$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
								$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
								$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;
								//$aPicturesParam['large_image_max_width'] = $this->_oCurrentShop->image_large_max_width;
								$aPicturesParam['large_image_max_width'] = $oProperty->image_large_max_width;
								//$aPicturesParam['large_image_max_height'] = $this->_oCurrentShop->image_large_max_height;
								$aPicturesParam['large_image_max_height'] = $oProperty->image_large_max_height;
								$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

								if (isset($this->_aExternalPropertiesSmall[$iPropertyID]))
								{
									// Малое изображение передано
									$aPicturesParam['create_small_image_from_large'] = FALSE;

										// Файл-источник
									$sSourceFileSmall = $this->imagesPath . $this->_aExternalPropertiesSmall[$iPropertyID];

									$sSourceFileBaseNameSmall = basename($sSourceFileSmall, '');

									$bHttp = strpos(strtolower($sSourceFileSmall), "http://") === 0 || strpos(strtolower($sSourceFileSmall), "https://");

									if (Core_File::isValidExtension($sSourceFileSmall, Core::$mainConfig['availableExtension']) || $bHttp)
									{
										// Создаем папку назначения
										$this->_oCurrentItem->createDir();

										if ($bHttp)
										{
											try {
												$sSourceFileSmall = $this->_uploadHttpFile($sSourceFileSmall);
											}
											catch (Exception $e)
											{
												Core_Message::show($e->getMessage(), 'error');
												$sSourceFileSmall = NULL;
											}
										}
										else
										{
											$sSourceFileSmall = CMS_FOLDER . $sSourceFileSmall;
										}

										if (!$this->_oCurrentShop->change_filename)
										{
											$sTargetFileNameSmall = "small_{$sSourceFileBaseNameSmall}";
										}
										else
										{
											$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseNameSmall);
											$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
												? '.jpg'
												: ".{$sTargetFileExtension}";

											$oProperty_Value->save();
											$sTargetFileNameSmall = "small_shop_property_file_{$this->_oCurrentItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
										}

										$aPicturesParam['small_image_source'] = $sSourceFileSmall;
										$aPicturesParam['small_image_name'] = $sSourceFileBaseNameSmall;
										$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileNameSmall;

										// Удаляем старое малое изображение
										/*if ($oProperty_Value->file_small != '')
										{
											try
											{
												Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
											} catch (Exception $e) {}
										}*/
									}

									// ------------------------------------------
									// Исключаем из отдельного импорта малых изображений
									unset($this->_aExternalPropertiesSmall[$iPropertyID]);
								}
								else
								{
									// Малое изображение не передано
									$aPicturesParam['create_small_image_from_large'] = TRUE;
									$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
									$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
									$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";

									$sSourceFileSmall = NULL;
								}

								$aPicturesParam['small_image_max_width'] = $oProperty->image_small_max_width;
								$aPicturesParam['small_image_max_height'] = $oProperty->image_small_max_height;
								$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
								$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];

								// Удаляем старое большое изображение
								if ($oProperty_Value->file != '')
								{
									if ($sDestinationFolder . $oProperty_Value->file != $sSourceFile)
									{
										try
										{
											Core_File::delete($sDestinationFolder . $oProperty_Value->file);
										} catch (Exception $e) {}
									}
								}

								// Удаляем старое малое изображение
								if ($oProperty_Value->file_small != '')
								{
									if ($sDestinationFolder . $oProperty_Value->file_small != $sSourceFileSmall)
									{
										try
										{
											Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
										} catch (Exception $e) {}
									}
								}

								try {
									Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
									$aTmpReturn = Core_Event::getLastReturn();
									is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

									$aResult = Core_File::adminUpload($aPicturesParam);
								}
								catch (Exception $e)
								{
									Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
										? $e->getMessage()
										: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
									, 'error');

									$aResult = array('large_image' => FALSE, 'small_image' => FALSE);
								}

								if ($aResult['large_image'])
								{
									$oProperty_Value->file = $sTargetFileName;
									$oProperty_Value->file_name = '';
								}

								if ($aResult['small_image'])
								{
									$oProperty_Value->file_small = "small_{$sTargetFileName}";
									$oProperty_Value->file_small_name = '';
								}

								if (isset($this->_aExternalPropertiesDesc[$iPropertyID]))
								{
									$oProperty_Value->file_description = $this->_aExternalPropertiesDesc[$iPropertyID];
								}

								clearstatcache();

								if (strpos(basename($sSourceFile), "CMS") === 0
									&& is_file($sSourceFile)
								)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFile);
								}

								if (strpos(basename($sSourceFileSmall), "CMS") === 0
									&& is_file($sSourceFileSmall)
								)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFileSmall);
								}
							}
						break;
						// Список
						case 3:
							if (Core::moduleIsActive('list'))
							{
								$oListItem = Core_Entity::factory('List_Item');
								$oListItem
									->queryBuilder()
									->where('list_id', '=', $oProperty->list_id)
									->where('value', '=', $sPropertyValue)
								;
								$oListItem = $oListItem->findAll(FALSE);

								if (count($oListItem))
								{
									$oProperty_Value->setValue($oListItem[0]->id);
								}
								else
								{
									$oProperty_Value->setValue(Core_Entity::factory('List_Item')
										->list_id($oProperty->list_id)
										->value($sPropertyValue)
										->save()
										->id
									);
								}
							}
						break;
						case 5: // Informationsystem
							$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getByName($sPropertyValue);
							if ($oInformationsystem_Item)
							{
								$oProperty_Value->setValue($oInformationsystem_Item->id);
							}
							elseif (is_numeric($sPropertyValue))
							{
								$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getById($sPropertyValue);

								$oInformationsystem_Item && $oProperty_Value->setValue($oInformationsystem_Item->id);
							}
						break;
						case 8:
							if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sPropertyValue))
							{
							 $sPropertyValue = Core_Date::datetime2sql($sPropertyValue);
							}

							$oProperty_Value->setValue($sPropertyValue);
						break;
						case 9:
							if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sPropertyValue))
							{
							 $sPropertyValue = Core_Date::datetime2sql($sPropertyValue);
							}

							$oProperty_Value->setValue($sPropertyValue);
						break;
						case 11: // Float
							$sPropertyValue = Shop_Controller::instance()->convertFloat($sPropertyValue);
							$oProperty_Value->setValue($sPropertyValue);
						break;
						case 12: // Shop
							$oShop_Item = $oProperty->Shop->Shop_Items->getByName($sPropertyValue);
							if ($oShop_Item)
							{
								$oProperty_Value->setValue($oShop_Item->id);
							}
							elseif (is_numeric($sPropertyValue))
							{
								$oShop_Item = $oProperty->Shop->Shop_Items->getById($sPropertyValue);

								$oShop_Item && $oProperty_Value->setValue($oShop_Item->id);
							}
						break;
						default:
							$oProperty_Value->setValue($sPropertyValue);
						break;
					}

					$oProperty_Value->save();
				}

				foreach ($this->_aExternalPropertiesSmall as $iPropertyID => $sPropertyValue)
				{
					// Проверяем доступность дополнительного свойства для группы товаров
					if (Core_Entity::factory('Shop', $this->_oCurrentShop->id)
						->Shop_Item_Property_For_Groups
						->getByShopItemPropertyIdAndGroupId($iPropertyID, $this->_oCurrentGroup->id))
					{
						// Свойство не доступно текущей группе, делаем его доступным
						Core_Entity::factory('Shop_Item_Property_For_Group')
							->shop_group_id($this->_oCurrentGroup->id)
							->shop_item_property_id($iPropertyID)
							->shop_id($this->_oCurrentShop->id)
							->save();
					}

					$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

					$aPropertyValues = $oProperty->getValues($this->_oCurrentItem->id, FALSE);

					$oProperty_Value = isset($aPropertyValues[0])
						? $aPropertyValues[0]
						: $oProperty->createNewValue($this->_oCurrentItem->id);

					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sSourceFile = $this->imagesPath . $sPropertyValue;

					$sSourceFileBaseName = basename($sSourceFile, '');

					$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://");

					if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFile = $this->_uploadHttpFile($sSourceFile);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFile = NULL;
							}
						}
						else
						{
							$sSourceFile = CMS_FOLDER . $sSourceFile;
						}

						if (!$this->_oCurrentShop->change_filename)
						{
							$sTargetFileName = "small_{$sSourceFileBaseName}";
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
							$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
								? '.jpg'
								: ".{$sTargetFileExtension}";

							$oProperty_Value->save();
							$sTargetFileName = "small_shop_property_file_{$this->_oCurrentItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
						}

						$aPicturesParam = array();
						$aPicturesParam['small_image_source'] = $sSourceFile;
						$aPicturesParam['small_image_name'] = $sSourceFileBaseName;
						$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileName;
						$aPicturesParam['create_small_image_from_large'] = FALSE;
						$aPicturesParam['small_image_max_width'] = $this->_oCurrentShop->image_small_max_width;
						$aPicturesParam['small_image_max_height'] = $this->_oCurrentShop->image_small_max_height;
						$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
						$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
						$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
						$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
						$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;

						// Удаляем старое малое изображение
						if ($oProperty_Value->file_small != '')
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
							} catch (Exception $e) {}
						}

						try {
							Core_Event::notify('Shop_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
							$aTmpReturn = Core_Event::getLastReturn();
							is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

							$aResult = Core_File::adminUpload($aPicturesParam);
						}
						catch (Exception $e)
						{
							Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
								? $e->getMessage()
								: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
							, 'error');

							$aResult = array('large_image' => FALSE, 'small_image' => FALSE);
						}

						if ($aResult['small_image'])
						{
							$oProperty_Value->file_small = $sTargetFileName;
							$oProperty_Value->file_small_name = '';
						}

						if (strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}

					$oProperty_Value->save();
				}

				foreach ($this->_aExternalPrices as $iPriceID => $sPriceValue)
				{
					$oShop_Item_Price = Core_Entity::factory('Shop_Item', $this->_oCurrentItem->id)
						->Shop_Item_Prices
						->getByPriceId($iPriceID);

					if (is_null($oShop_Item_Price))
					{
						$oShop_Item_Price = Core_Entity::factory('Shop_Item_Price');
						$oShop_Item_Price->shop_item_id = $this->_oCurrentItem->id;
						$oShop_Item_Price->shop_price_id = $iPriceID;
					}

					$oShop_Item_Price->value($sPriceValue);
					$oShop_Item_Price->save();
				}

				if ($this->searchIndexation && $this->_oCurrentItem->id)
				{
					Core_Entity::factory('Shop_Item', $this->_oCurrentItem->id)->index();
				}
			} // end fields

			$iCounter++;

			//$this->_oCurrentItem->clear();
			$this->_oCurrentItem = Core_Entity::factory('Shop_Item');
			$this->_oCurrentGroup = Core_Entity::factory('Shop_Group', $this->_iCurrentGroupId);
			$this->_oCurrentGroup->shop_id = $this->_oCurrentShop->id;

			$this->_oCurrentItem->shop_group_id = $this->_oCurrentGroup->id;

			if (!is_null($this->_oCurrentOrderItem))
			{
				$this->_oCurrentOrder->add($this->_oCurrentOrderItem);
			}
			$this->_oCurrentOrder = NULL;
			$this->_oCurrentOrderItem = NULL;

			$this->_sBigImageFile = $this->_sSmallImageFile = '';

			// Очищаем временные массивы
			$this->_aExternalPrices =
				$this->_aWarehouses =
				$this->_aExternalPropertiesSmall =
				$this->_aExternalProperties =
				$this->_aAdditionalGroups = array();

			// Список меток для текущего товара
			$this->_sCurrentTags = '';
			// Артикул родительского товара - признак того, что данный товар сопутствует товару с данным артикулом
			$this->_sAssociatedItemMark = '';
			// Текущий электронный товар
			$this->_oCurrentShopEItem->clear();
			// Текущая специальная цена для товара
			$this->_oCurrentShopSpecialPrice->clear();
		} // end line

		$iCurrentSeekPosition = !$aCsvLine ? $aCsvLine : ftell($fInputFile);

		fclose($fInputFile);

		Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterImport', $this, array($this->_oCurrentShop, $iCurrentSeekPosition));

		return $iCurrentSeekPosition;
	}

	/**
	 * Array of cached tags
	 */
	protected $_aTags = NULL;

	/**
	 * Get cached tags of array
	 * @return array
	 */
	protected function _getTags()
	{
		if (is_null($this->_aTags))
		{
			$aTags = Core_Entity::factory('Tag')->findAll(FALSE);

			foreach ($aTags as $oTag)
			{
				$this->_aTags[$oTag->id] = $oTag->name;
			}
		}

		return $this->_aTags;
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		$aReturn = array();

		foreach ($this->_allowedProperties as $propertyName)
		{
			$aReturn[] = $propertyName . '=' . $this->$propertyName;
		}

		return implode(', ', $aReturn) . "<br/>";
	}

	/**
	 * Get CSV line from file
	 * @param handler file descriptor
	 * @return array
	 */
	public function getCSVLine($fileDescriptor)
	{
		if (strtoupper($this->encoding) != 'UTF-8' && defined('ALT_SITE_LOCALE'))
		{
			setlocale(LC_ALL, ALT_SITE_LOCALE);
		}

		$aCsvLine = PHP_VERSION_ID >= 50300
			? @fgetcsv($fileDescriptor, 0, $this->separator, $this->limiter, '"')
			: @fgetcsv($fileDescriptor, 0, $this->separator, $this->limiter);

		if ($aCsvLine === FALSE)
		{
			return $aCsvLine;
		}

		setlocale(LC_ALL, SITE_LOCAL);
		setlocale(LC_NUMERIC, 'POSIX');

		return self::CorrectToEncoding($aCsvLine, 'UTF-8', $this->encoding);
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_oCurrentShop =
		$this->_oCurrentGroup =
		$this->_oCurrentItem =
		$this->_oCurrentOrder =
		$this->_oCurrentOrderItem =
		$this->_oCurrentShopEItem =
		$this->_oCurrentShopSpecialPrice = NULL;

		$this->_aTags = NULL;

		$this->_aClearedPropertyValues = array();

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
	 * Reestablish any database connections that may have been lost during serialization and perform other reinitialization tasks
	 * @return self
	 */
	public function __wakeup()
	{
		date_default_timezone_set(Core::$mainConfig['timezone']);

		// Инициализация текущей группы товаров
		$this->_oCurrentGroup = Core_Entity::factory('Shop_Group', $this->_iCurrentGroupId
			? $this->_iCurrentGroupId
			: NULL);

		$this->init();

		$this->_oCurrentGroup->shop_id = $this->_oCurrentShop->id;

		// Инициализация текущего товара
		$this->_oCurrentItem = Core_Entity::factory('Shop_Item');
		$this->_oCurrentItem->shop_group_id = intval($this->_oCurrentGroup->id);

		// Инициализация текущего электронного товара
		$this->_oCurrentShopEItem = Core_Entity::factory('Shop_Item_Digital');

		// Инициализация текущей специальной цены для товара
		$this->_oCurrentShopSpecialPrice = Core_Entity::factory('Shop_Specialprice');

		return $this;
	}

	/**
	 * Correct CSV-line encoding
	 * @param array $sLine current CSV-file line
	 * @param string $encodeTo detination encoding
	 * @param string $encodeFrom source encoding
	 * @return array
	 */
	public static function CorrectToEncoding($sLine, $encodeTo, $encodeFrom = 'UTF-8')
	{
		if (is_array($sLine))
		{
			foreach ($sLine as $key => $value)
			{
				$sLine[$key] = self::CorrectToEncoding($value, $encodeTo, $encodeFrom);
			}
		}
		else
		{
			// Если кодировки не совпадают
			if (strtoupper($encodeTo) != strtoupper($encodeFrom))
			{
				// Перекодируем в указанную кодировку
				$sLine = @iconv($encodeFrom, $encodeTo . "//IGNORE//TRANSLIT", $sLine);
			}
		}

		return $sLine;
	}

	public $aCaptions = array();

	public $aColors = array(
		'#F5F5F5',

		// groups
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',
		'#E6EE9C',

		// currency
		'#80CBC4',

		// tax
		'#80DEEA',

		// producer
		'#9FA8DA',
		'#9FA8DA',

		// seller
		'#B39DDB',
		'#B39DDB',

		// measure
		'#F48FB1',
		'#F48FB1',

		// items
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',
		'#FFCC80',

		// item special prices
		'#FFB74D',
		'#FFB74D',
		'#FFB74D',
		'#FFB74D',

		// item associated
		'#B0BEC5',
		'#B0BEC5',

		// order
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',

		// order items
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7',
		'#A5D6A7');


	public $aEntities = array(
		'',

		'group_id',
		'group_name',
		'group_path',
		'group_sorting',
		'group_description',
		'group_active',
		'group_seo_title',
		'group_seo_description',
		'group_seo_keywords',
		'group_image',
		'group_small_image',
		'group_cml_id',
		'group_parent_cml_id',

		'currency_id',

		'tax_id',

		'producer_id',
		'producer_name',

		'seller_id',
		'seller_name',

		'mesure_id',
		'mesure_name',

		'item_id',
		'item_name',
		'item_marking',
		'item_datetime',
		'item_description',
		'item_text',
		'item_image',
		'item_small_image',
		'item_tags',
		'item_weight',
		'item_length',
		'item_width',
		'item_height',
		'item_price',
		'item_active',
		'item_sorting',
		'item_path',
		'item_seo_title',
		'item_seo_description',
		'item_seo_keywords',
		'item_indexing',
		'item_yandex_market_allow',
		'item_yandex_market_bid',
		'item_yandex_market_cid',
		'item_manufacturer_warranty',
		'item_vendorcode',
		'item_country_of_origin',
		'item_parent_marking',
		'item_parent_guid',
		'item_digital_name',
		'item_digital_text',
		'item_digital_file',
		'item_digital_count',
		'item_end_datetime',
		'item_start_datetime',
		'item_type',
		'item_siteuser_id',
		'item_yandex_market_sales_notes',
		'additional_groups',
		'item_cml_id',

		'item_special_price_from',
		'item_special_price_to',
		'item_special_price_price',
		'item_special_price_percent',

		'item_parent_associated',
		'item_associated_markings',

		'order_guid',
		'order_invoice',
		'order_shop_country_id',
		'order_shop_country_location_id',
		'order_shop_country_location_city_id',
		'order_shop_country_location_city_area_id',
		'order_name',
		'order_surname',
		'order_patronymic',
		'order_email',
		'order_acceptance_report',
		'order_vat_invoice',
		'order_company',
		'order_tin',
		'order_kpp',
		'order_phone',
		'order_fax',
		'order_address',
		'order_shop_order_status_id',
		'order_shop_currency_id',
		'order_shop_payment_system_id',
		'order_datetime',
		'order_paid',
		'order_payment_datetime',
		'order_description',
		'order_system_information',
		'order_canceled',
		'order_status_datetime',
		'order_delivery_information',

		'order_item_marking',
		'order_item_name',
		'order_item_quantity',
		'order_item_price',
		'order_item_rate',
		'order_item_type'
	);
}