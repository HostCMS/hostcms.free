<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Import Csv Controller
 *
 * Доступные методы:
 *
 * - encoding('UTF-8') кодировка импорта
 * - file('my.csv') имя импортируемого CSV-файла, который ранее был загружен во временную директорию
 * - seek($int) позиция в файле импорта
 * - time(20) ограничение времени импорта за шаг, из конфигурационного файла, по умолчанию 20
 * - step(100) ограничение количества импортируемых за шаг, из конфигурационного файла, по умолчанию 100
 * - entriesLimit(500) ограничение количества проводок в документе, из конфигурационного файла, по умолчанию 5000
 * - separator($str) разделитель столбцов в CSV-файле, по умолчанию ';'
 * - limiter() ограничитель строки в CSV-файле, по умолчанию '"'
 * - firstlineheader(TRUE|FALSE) первая строка - название полей
 * - csv_fields(array) массив соответствий полей CSV элементам системы
 * - imagesPath($str) путь к импортируемым картинкам, конкатенируется с переденными в файле
 * - importAction($int) действие с существующими товарами: 1 - обновить существующие товары, 2 - не обновлять существующие товары, 3 - удалить содержимое магазина до импорта. По умолчанию 1
 * - searchIndexation(TRUE|FALSE) индексировать импортируемые данные, по умолчанию FALSE
 * - deletePropertyValues(TRUE|FALSE|array()) удалять существующие значения дополнительных свойств перед импортом новых, по умолчанию TRUE
 * - deleteFieldValues(TRUE|FALSE|array()) удалять существующие значения пользовательских полей перед импортом новых, по умолчанию TRUE
 * - deleteUnsentModificationsByProperties(TRUE|FALSE) удалять непереданные модификации, созданные по дополнительным свойствам, по умолчанию FALSE
 * - deleteImage(TRUE|FALSE) удалять основные изображения
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Item_Import_Csv_Controller extends Shop_Item_Import_Controller
{
	/**
	 * Array of inserted groups
	 * @var array
	 */
	protected $_aInsertedGroupIDs = array();

	/**
	 * Array of ID's and GUIDs of cleared item's properties
	 * @var array
	 */
	protected $_aClearedItemsPropertyValues = array();

	/**
	 * Array of ID's and GUIDs of cleared groups's properties
	 * @var array
	 */
	protected $_aClearedGroupsPropertyValues = array();

	/**
	 * Array of ID's and GUIDs of cleared item's properties
	 * @var array
	 */
	protected $_aClearedItemsFieldValues = array();

	/**
	 * Array of ID's and GUIDs of cleared groups's properties
	 * @var array
	 */
	protected $_aClearedGroupsFieldValues = array();

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
	 * List of goods' external properties
	 * @var array
	 */
	protected $_aExternalProperties = array();

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
	 * List of external fields
	 * @var array
	 */
	protected $_aExternalFields = array();

	/**
	 * List of small parts of external fields
	 * @var array
	 */
	protected $_aExternalFieldsSmall = array();

	/**
	 * List of descriptions of external fields
	 * @var array
	 */
	protected $_aExternalFieldsDesc = array();

	/**
	 * List of group's external properties
	 * @var array
	 */
	protected $_aGroupExternalProperties = array();

	/**
	 * List of group's external properties
	 * @var array
	 */
	protected $_aGroupExternalFields = array();

	/**
	 * List of modification by properties
	 * @var array
	 */
	protected $_aModificationsByProperties = array();

	/**
	 * List of additional group
	 * @var array
	 */
	protected $_aAdditionalGroups = array();

	/**
	 * List of barcodes
	 * @var array
	 */
	protected $_aBarcodes = array();

	/**
	 * List of items GUID in the set
	 * @var array
	 */
	protected $_aSets = array();

	/**
	 * List of item's tabs
	 * @var array
	 */
	protected $_aItemTabs = array();

	/**
	 * Path to the temprorary json file
	 * @var NULL|string
	 */
	protected $_jsonPath = NULL;

	protected $_aPropertiesTree = array();
	protected $_aPropertyDirsTree = array();

	protected $_aFieldsTree = array();
	protected $_aFieldDirsTree = array();

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'encoding',
		'file',
		'seek',
		'time',
		'step',
		'entriesLimit',
		'separator',
		'limiter',
		'firstlineheader',
		'csv_fields',
		'imagesPath',
		'importAction',
		'searchIndexation',
		'deletePropertyValues',
		'deleteFieldValues',
		'deleteUnsentModificationsByProperties',
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
	protected $_aCreatedItemIDs = array();

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

			$oParent_Group = Core_Entity::factory('Shop_Group', $iGroupId)->getParent();

			$oParent_Group
				&& $oParent_Group->incCountGroups();
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

			// see Shop_Item_Model::save()
			/*$oShop_Item = Core_Entity::factory('Shop_Item', $iItemId);

			$oShop_Item->shop_group_id
				&& $oShop_Item->Shop_Group->incCountItems();*/
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

		$this->_oCurrentOrder = $this->_oCurrentOrderItem = NULL;

		return $this;
	}

	/**
	 * Get $this->_oCurrentShop
	 * @return Shop_Model $oCurrentShop
	 */
	public function getCurrentShop()
	{
		return $this->_oCurrentShop;
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
	 * @return Shop_Item_Model
	 */
	public function getCurrentItem()
	{
		return $this->_oCurrentItem;
	}

	/**
	* Set $this->_oCurrentOrder
	* @param Shop_Order_Model $oCurrentOrder
	* @return self
	*/
	public function setCurrentOrder(Shop_Order_Model $oCurrentOrder)
	{
		$this->_oCurrentOrder = $oCurrentOrder;
		return $this;
	}

	/**
	 * Get $this->_oCurrentOrder
	 * @return Shop_Order_Model
	 */
	public function getCurrentOrder()
	{
		return $this->_oCurrentOrder;
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
			'entriesLimit' => 5000,
			'separator' => ';',
			'limiter' => '"',
			'itemSearchFields' => array('marking', 'path', 'cml_id', 'vendorcode')
		);

		$this->_iCurrentShopId = $iCurrentShopId;
		$this->_iCurrentGroupId = $iCurrentGroupId;

		$this->time = $this->_aConfig['maxTime'];
		$this->step = $this->_aConfig['maxCount'];
		$this->entriesLimit = $this->_aConfig['entriesLimit'];
		$this->separator = $this->_aConfig['separator'];
		$this->limiter = $this->_aConfig['limiter'];

		$this->encoding = 'UTF-8';
		$this->importAction = 1;

		$this->init();

		// Единожды в конструкторе, чтобы после __wakeup() не обнулялось
		$this->_jsonPath = CMS_FOLDER . TMP_DIR . 'csv_' . time() . '.json';
		$this->_InsertedItemsCount = $this->_UpdatedItemsCount = $this->_InsertedGroupsCount = $this->_UpdatedGroupsCount = $this->_posted = 0;

		//$this->_aCreatedItemIDs = array();

		$this->deletePropertyValues = $this->deleteFieldValues = TRUE;
		$this->searchIndexation = $this->deleteUnsentModificationsByProperties = FALSE;

		$oShop = Core_Entity::factory('Shop', $iCurrentShopId);

		$this->aEntities = array(
			'' => array(
				'caption' => Core::_('Shop_Exchange.!download'),
				'attr' => array('style' => 'background-color: #F5F5F5')
			),

			// groups
			'caption-shop_groups' => array(
				'caption' => Core::_('Shop_Group.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'group_id' => array(
				'caption' => Core::_('Shop_Exchange.group_id'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_name' => array(
				'caption' => Core::_('Shop_Exchange.group_name'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_path' => array(
				'caption' => Core::_('Shop_Exchange.group_path'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_sorting' => array(
				'caption' => Core::_('Shop_Exchange.group_sorting'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_description' => array(
				'caption' => Core::_('Shop_Exchange.group_description'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_active' => array(
				'caption' => Core::_('Shop_Exchange.group_active'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_seo_title' => array(
				'caption' => Core::_('Shop_Exchange.group_seo_title'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_seo_description' => array(
				'caption' => Core::_('Shop_Exchange.group_seo_description'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_seo_keywords' => array(
				'caption' => Core::_('Shop_Exchange.group_seo_keywords'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_image' => array(
				'caption' => Core::_('Shop_Exchange.group_image_large'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_small_image' => array(
				'caption' => Core::_('Shop_Exchange.group_image_small'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_cml_id' => array(
				'caption' => Core::_('Shop_Exchange.group_guid'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			),

			'group_parent_cml_id' => array(
				'caption' => Core::_('Shop_Exchange.parent_group_guid'),
				'attr' => array('style' => 'background-color: #DDE0B6')
			)
		);

		if (Core::moduleIsActive('property'))
		{
			// Свойства групп
			$aGroupProperties = Core_Entity::factory('Shop_Group_Property_List', $oShop->id)->Properties->findAll(FALSE);
			if (count($aGroupProperties))
			{
				// Общий заголовок
				$this->aEntities['caption-group-properties'] = array(
					'caption' => Core::_('Shop_Group.properties'),
					'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
				);

				$this->_aPropertiesTree = $this->_aPropertyDirsTree = array();
				foreach ($aGroupProperties as $oProperty)
				{
					$this->_aPropertiesTree[$oProperty->property_dir_id][] = $oProperty;
				}

				$aGroupPropertyDirs = Core_Entity::factory('Shop_Group_Property_List', $oShop->id)->Property_Dirs->findAll(FALSE);
				foreach ($aGroupPropertyDirs as $oProperty_Dir)
				{
					$this->_aPropertyDirsTree[$oProperty_Dir->parent_id][] = $oProperty_Dir;
				}

				$this->_addEntitiesOfProperties('prop_group', '#E0D5D5', 0);

				$this->_aPropertiesTree = $this->_aPropertyDirsTree = array();
				unset($aGroupProperties);
				unset($aGroupPropertyDirs);
			}
		}

		if (Core::moduleIsActive('field'))
		{
			$aGroupFields = Field_Controller::getFields('shop_group', $oShop->site_id);
			if (count($aGroupFields))
			{
				// Общий заголовок
				$this->aEntities['caption-group-fields'] = array(
					'caption' => Core::_('Shop_Group.fields'),
					'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
				);

				$this->_aFieldsTree = $this->_aFieldDirsTree = array();
				foreach ($aGroupFields as $oField)
				{
					$this->_aFieldsTree[$oField->field_dir_id][] = $oField;
				}

				$aGroupFieldDirs = Field_Controller::getFieldDirs('shop_group');
				foreach ($aGroupFieldDirs as $oField_Dir)
				{
					$this->_aFieldDirsTree[$oField_Dir->parent_id][] = $oField_Dir;
				}

				$this->_addEntitiesOfFields('field_group', '#BBCEF2', 0);

				$this->_aFieldsTree = $this->_aFieldDirsTree = array();
				unset($aGroupFields);
				unset($aGroupFieldDirs);
			}
		}

		$this->aEntities = array_merge($this->aEntities, array(
			// items
			'caption-shop_items' => array(
				'caption' => Core::_('Shop_Item.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'item_id' => array(
				'caption' => Core::_('Shop_Exchange.item_id'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_name' => array(
				'caption' => Core::_('Shop_Exchange.item_name'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_marking' => array(
				'caption' => Core::_('Shop_Exchange.item_marking'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'currency_id' => array(
				'caption' => Core::_('Shop_Exchange.currency_id'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'tax_id' => array(
				'caption' => Core::_('Shop_Exchange.tax_id'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_datetime' => array(
				'caption' => Core::_('Shop_Exchange.item_datetime'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_description' => array(
				'caption' => Core::_('Shop_Exchange.item_description'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_text' => array(
				'caption' => Core::_('Shop_Exchange.item_text'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_image' => array(
				'caption' => Core::_('Shop_Exchange.item_image_large'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_small_image' => array(
				'caption' => Core::_('Shop_Exchange.item_image_small'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_tags' => array(
				'caption' => Core::_('Shop_Exchange.item_tags'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_weight' => array(
				'caption' => Core::_('Shop_Exchange.item_weight'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_length' => array(
				'caption' => Core::_('Shop_Exchange.item_length'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_width' => array(
				'caption' => Core::_('Shop_Exchange.item_width'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_height' => array(
				'caption' => Core::_('Shop_Exchange.item_height'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),

			'item_weight_package' => array(
				'caption' => Core::_('Shop_Exchange.item_weight_package'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_length_package' => array(
				'caption' => Core::_('Shop_Exchange.item_length_package'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_width_package' => array(
				'caption' => Core::_('Shop_Exchange.item_width_package'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_height_package' => array(
				'caption' => Core::_('Shop_Exchange.item_height_package'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),

			'item_min_quantity' => array(
				'caption' => Core::_('Shop_Exchange.item_min_quantity'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_max_quantity' => array(
				'caption' => Core::_('Shop_Exchange.item_max_quantity'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_quantity_step' => array(
				'caption' => Core::_('Shop_Exchange.item_quantity_step'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_price' => array(
				'caption' => Core::_('Shop_Exchange.item_price'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_active' => array(
				'caption' => Core::_('Shop_Exchange.item_active'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_sorting' => array(
				'caption' => Core::_('Shop_Exchange.item_sorting'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_path' => array(
				'caption' => Core::_('Shop_Exchange.item_path'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_seo_title' => array(
				'caption' => Core::_('Shop_Exchange.item_seo_title'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_seo_description' => array(
				'caption' => Core::_('Shop_Exchange.item_seo_description'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_seo_keywords' => array(
				'caption' => Core::_('Shop_Exchange.item_seo_keywords'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_indexing' => array(
				'caption' => Core::_('Shop_Exchange.item_indexing'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_yandex_market_allow' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_yandex_market_bid' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market_bid'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_yandex_market_cid' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market_cid'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_manufacturer_warranty' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market_manufacturer_warranty'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_vendorcode' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market_vendorcode'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_country_of_origin' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market_country_of_origin'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_parent_marking' => array(
				'caption' => Core::_('Shop_Exchange.item_parent_marking'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_parent_guid' => array(
				'caption' => Core::_('Shop_Exchange.item_parent_guid'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_digital_name' => array(
				'caption' => Core::_('Shop_Exchange.digital_item_name'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_digital_text' => array(
				'caption' => Core::_('Shop_Exchange.digital_item_value'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_digital_file' => array(
				'caption' => Core::_('Shop_Exchange.digital_item_filename'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_digital_count' => array(
				'caption' => Core::_('Shop_Exchange.digital_item_count'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_end_datetime' => array(
				'caption' => Core::_('Shop_Exchange.item_end_datetime'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_start_datetime' => array(
				'caption' => Core::_('Shop_Exchange.item_start_datetime'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_type' => array(
				'caption' => Core::_('Shop_Exchange.item_type'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_siteuser_id' => array(
				'caption' => Core::_('Shop_Exchange.siteuser_id'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_yandex_market_sales_notes' => array(
				'caption' => Core::_('Shop_Exchange.item_yandex_market_sales_notes'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'additional_groups' => array(
				'caption' => Core::_('Shop_Exchange.item_additional_group'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'barcodes' => array(
				'caption' => Core::_('Shop_Exchange.item_barcode'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'sets_guid' => array(
				'caption' => Core::_('Shop_Exchange.item_sets_guid'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'sets_marking' => array(
				'caption' => Core::_('Shop_Exchange.item_sets_marking'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_tabs' => array(
				'caption' => Core::_('Shop_Exchange.item_tabs'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),
			'item_cml_id' => array(
				'caption' => Core::_('Shop_Exchange.item_guid'),
				'attr' => array('style' => 'background-color: #EEEDE7')
			),

			// item special prices
			'item_special_price_from' => array(
				'caption' => Core::_('Shop_Exchange.specialprices_min_quantity'),
				'attr' => array('style' => 'background-color: #EBE3E7')
			),
			'item_special_price_to' => array(
				'caption' => Core::_('Shop_Exchange.specialprices_max_quantity'),
				'attr' => array('style' => 'background-color: #EBE3E7')
			),
			'item_special_price_price' => array(
				'caption' => Core::_('Shop_Exchange.specialprices_price'),
				'attr' => array('style' => 'background-color: #EBE3E7')
			),
			'item_special_price_percent' => array(
				'caption' => Core::_('Shop_Exchange.specialprices_percent'),
				'attr' => array('style' => 'background-color: #EBE3E7')
			),

			// item associated
			'item_parent_associated' => array(
				'caption' => Core::_('Shop_Exchange.item_parent_associated'),
				'attr' => array('style' => 'background-color: #DACDD0')
			),
			'item_associated_markings' => array(
				'caption' => Core::_('Shop_Exchange.item_associated_markings'),
				'attr' => array('style' => 'background-color: #DACDD0')
			)
		));

		// Свойства товаров
		if (Core::moduleIsActive('property'))
		{
			$aItemProperties = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)->Properties->findAll(FALSE);
			if (count($aItemProperties))
			{
				$this->aEntities['caption-item-properties'] = array(
					'caption' => Core::_('Shop_Item.shops_add_form_link_properties'),
					'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
				);

				$this->_aPropertiesTree = $this->_aPropertyDirsTree = array();
				foreach ($aItemProperties as $oProperty)
				{
					$this->_aPropertiesTree[$oProperty->property_dir_id][] = $oProperty;
				}

				$aItemPropertyDirs = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)->Property_Dirs->findAll(FALSE);
				foreach ($aItemPropertyDirs as $oProperty_Dir)
				{
					$this->_aPropertyDirsTree[$oProperty_Dir->parent_id][] = $oProperty_Dir;
				}

				$this->_addEntitiesOfProperties('prop', '#CBE57B', 0);

				$this->_aPropertiesTree = $this->_aPropertyDirsTree = array();
				unset($aItemProperties);
				unset($aItemPropertyDirs);
			}
		}

		if (Core::moduleIsActive('field'))
		{
			// Пользовательские поля товаров
			$aItemFields = Field_Controller::getFields('shop_item', $oShop->site_id);
			if (count($aItemFields))
			{
				$this->aEntities['caption-item-fields'] = array(
					'caption' => Core::_('Shop_Item.shops_add_form_link_fields'),
					'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
				);

				$this->_aFieldsTree = $this->_aFieldDirsTree = array();
				foreach ($aItemFields as $oField)
				{
					$this->_aFieldsTree[$oField->field_dir_id][] = $oField;
				}

				$aItemFieldDirs = Field_Controller::getFieldDirs('shop_item');
				foreach ($aItemFieldDirs as $oField_Dir)
				{
					$this->_aFieldDirsTree[$oField_Dir->parent_id][] = $oField_Dir;
				}

				$this->_addEntitiesOfFields('field', '#BBCEF2', 0);

				$this->_aFieldsTree = $this->_aFieldDirsTree = array();
				unset($aItemFields);
				unset($aItemFieldDirs);
			}
		}

		// Цены для клиентов
		$aShop_Prices = $oShop->Shop_Prices->findAll(FALSE);
		if (count($aShop_Prices))
		{
			$this->aEntities['caption-shop_prices'] = array(
				'caption' => Core::_('Shop_Price.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			);

			foreach ($aShop_Prices as $oShop_Price)
			{
				$this->aEntities['price-' . $oShop_Price->id] = array(
					'caption' => $oShop_Price->name,
					'attr' => array('style' => 'background-color: #BDD8E8')
				);
			}
			unset($aShop_Prices);
		}

		// Выводим склады
		$aShop_Warehouses = $oShop->Shop_Warehouses->findAll(FALSE);
		if (count($aShop_Warehouses))
		{
			$this->aEntities['caption-warehouses'] = array(
				'caption' => Core::_('Shop_Warehouse.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			);

			foreach ($aShop_Warehouses as $oShopWarehouse)
			{
				$this->aEntities['warehouse-' . $oShopWarehouse->id] = array(
					'caption' => Core::_('Shop_Item.warehouse_import_field', $oShopWarehouse->name),
					'attr' => array('style' => 'background-color: #EAC3CF')
				);
			}
			unset($aShop_Warehouses);
		}

		// Создание модификаций по списочным свойствам
		if (Core::moduleIsActive('property'))
		{
			$aItemListProperties = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)->Properties->getAllByType(3, FALSE);
			if (count($aItemListProperties))
			{
				$this->aEntities['caption-modifications'] = array(
					'caption' => Core::_('Shop_Exchange.caption_modifications'),
					'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
				);

				foreach ($aItemListProperties as $oProperty)
				{
					$this->aEntities['modification-' . $oProperty->id] = array(
						'caption' => Core::_('Shop_Exchange.modification_by_property', $oProperty->name),
						'attr' => array('style' => 'background-color: #ebf1d0')
					);
				}
			}
		}

		$this->aEntities = array_merge($this->aEntities, array(
			// producer
			'caption-shop_producer' => array(
				'caption' => Core::_('Shop_Producer.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'producer_id' => array(
				'caption' => Core::_('Shop_Exchange.producer_id'),
				'attr' => array('style' => 'background-color: #F3E6BE')
			),
			'producer_name' => array(
				'caption' => Core::_('Shop_Exchange.producer_name'),
				'attr' => array('style' => 'background-color: #F3E6BE')
			),
			'producer_description' => array(
				'caption' => Core::_('Shop_Exchange.producer_description'),
				'attr' => array('style' => 'background-color: #F3E6BE')
			),
			'producer_active' => array(
				'caption' => Core::_('Shop_Exchange.producer_active'),
				'attr' => array('style' => 'background-color: #F3E6BE')
			),
			'producer_indexing' => array(
				'caption' => Core::_('Shop_Exchange.producer_indexing'),
				'attr' => array('style' => 'background-color: #F3E6BE')
			),
			'producer_sorting' => array(
				'caption' => Core::_('Shop_Exchange.producer_sorting'),
				'attr' => array('style' => 'background-color: #F3E6BE')
			),

			// seller
			'caption-shop_seller' => array(
				'caption' => Core::_('Shop_Seller.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'seller_id' => array(
				'caption' => Core::_('Shop_Exchange.seller_id'),
				'attr' => array('style' => 'background-color: #F5A883')
			),
			'seller_name' => array(
				'caption' => Core::_('Shop_Exchange.seller_name'),
				'attr' => array('style' => 'background-color: #F5A883')
			),

			// measure
			'caption-shop_measure' => array(
				'caption' => Core::_('Shop_Measure.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'mesure_id' => array(
				'caption' => Core::_('Shop_Exchange.measure_id'),
				'attr' => array('style' => 'background-color: #f4cfbe')
			),
			'mesure_name' => array(
				'caption' => Core::_('Shop_Exchange.measure_value'),
				'attr' => array('style' => 'background-color: #f4cfbe')
			),

			// order
			'caption-shop_orders' => array(
				'caption' => Core::_('Shop_Order.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'order_guid' => array(
				'caption' => Core::_('Shop_Exchange.order_guid'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_invoice' => array(
				'caption' => Core::_('Shop_Exchange.order_number'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_shop_country_id' => array(
				'caption' => Core::_('Shop_Exchange.order_country'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_shop_country_location_id' => array(
				'caption' => Core::_('Shop_Exchange.order_location'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_shop_country_location_city_id' => array(
				'caption' => Core::_('Shop_Exchange.order_city'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_shop_country_location_city_area_id' => array(
				'caption' => Core::_('Shop_Exchange.order_city_area'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_name' => array(
				'caption' => Core::_('Shop_Exchange.order_name'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_surname' => array(
				'caption' => Core::_('Shop_Exchange.order_surname'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_patronymic' => array(
				'caption' => Core::_('Shop_Exchange.order_patronymic'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_email' => array(
				'caption' => Core::_('Shop_Exchange.order_email'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_acceptance_report' => array(
				'caption' => Core::_('Shop_Exchange.order_akt'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_vat_invoice' => array(
				'caption' => Core::_('Shop_Exchange.order_schet_fak'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_company' => array(
				'caption' => Core::_('Shop_Exchange.order_company_name'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_tin' => array(
				'caption' => Core::_('Shop_Exchange.order_inn'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_kpp' => array(
				'caption' => Core::_('Shop_Exchange.order_kpp'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_phone' => array(
				'caption' => Core::_('Shop_Exchange.order_phone'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_fax' => array(
				'caption' => Core::_('Shop_Exchange.order_fax'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_address' => array(
				'caption' => Core::_('Shop_Exchange.order_address'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_shop_order_status_id' => array(
				'caption' => Core::_('Shop_Exchange.order_order_status'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_currency' => array(
				'caption' => Core::_('Shop_Exchange.order_currency'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_shop_payment_system_id' => array(
				'caption' => Core::_('Shop_Exchange.order_payment_system_id'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_datetime' => array(
				'caption' => Core::_('Shop_Exchange.order_date'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_paid' => array(
				'caption' => Core::_('Shop_Exchange.order_pay_status'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_payment_datetime' => array(
				'caption' => Core::_('Shop_Exchange.order_pay_date'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_description' => array(
				'caption' => Core::_('Shop_Exchange.order_description'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_system_information' => array(
				'caption' => Core::_('Shop_Exchange.order_info'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_canceled' => array(
				'caption' => Core::_('Shop_Exchange.order_canceled'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_status_datetime' => array(
				'caption' => Core::_('Shop_Exchange.order_pay_status_change_date'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),
			'order_delivery_information' => array(
				'caption' => Core::_('Shop_Exchange.order_delivery_info'),
				'attr' => array('style' => 'background-color: #E6BFDB')
			),

			// order items
			'caption-shop_order_items' => array(
				'caption' => Core::_('Shop_Order_Item.model_name'),
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
			),
			'order_item_marking' => array(
				'caption' => Core::_('Shop_Exchange.order_item_marking'),
				'attr' => array('style' => 'background-color: #E9E2EC')
			),
			'order_item_name' => array(
				'caption' => Core::_('Shop_Exchange.order_item_name'),
				'attr' => array('style' => 'background-color: #E9E2EC')
			),
			'order_item_quantity' => array(
				'caption' => Core::_('Shop_Exchange.order_item_quantity'),
				'attr' => array('style' => 'background-color: #E9E2EC')
			),
			'order_item_price' => array(
				'caption' => Core::_('Shop_Exchange.order_item_price'),
				'attr' => array('style' => 'background-color: #E9E2EC')
			),
			'order_item_rate' => array(
				'caption' => Core::_('Shop_Exchange.order_item_rate'),
				'attr' => array('style' => 'background-color: #E9E2EC')
			),
			'order_item_type' => array(
				'caption' => Core::_('Shop_Exchange.order_item_type'),
				'attr' => array('style' => 'background-color: #E9E2EC')
			)
		));

		Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterConstruct', $this);
	}

	/**
	 * Add entitites of properties
	 * @param string $prefix
	 * @param string $color
	 * @param integer $parent_id
	 * @param integer $level
	 */
	protected function _addEntitiesOfProperties($prefix, $color, $parent_id = 0, $level = 0)
	{
		if (isset($this->_aPropertiesTree[$parent_id]))
		{
			foreach ($this->_aPropertiesTree[$parent_id] as $oProperty)
			{
				$this->aEntities[$prefix . '-' . $oProperty->id] = array(
					'caption' => str_repeat('  ', $level) . $oProperty->name . " [{$oProperty->id}]",
					'attr' => array('style' => 'background-color: ' . $color)
				);

				if ($oProperty->type == 2)
				{
					$subcolor = Core_Str::hex2darker($color, 0.1);

					// Description
					$this->aEntities['propdesc-' . $oProperty->id] = array(
						'caption' => str_repeat('  ', $level) . Core::_('Shop_Item.import_file_description', $oProperty->name),
						'attr' => array('style' => 'background-color: ' . $subcolor)
					);

					// Small Image
					$this->aEntities['propsmall-' . $oProperty->id] = array(
						'caption' => str_repeat('  ', $level) . Core::_('Shop_Item.import_small_images', $oProperty->name),
						'attr' => array('style' => 'background-color: ' . $subcolor)
					);
				}
			}
		}

		if (isset($this->_aPropertyDirsTree[$parent_id]))
		{
			foreach ($this->_aPropertyDirsTree[$parent_id] as $oProperty_Dir)
			{
				// Заголовок выводим, если есть или элементы, или группы
				if (isset($this->_aPropertiesTree[$oProperty_Dir->id]) || isset($this->_aPropertyDirsTree[$oProperty_Dir->id]))
				{
					$this->aEntities['caption-group-dir-' . $oProperty_Dir->id] = array(
						'caption' => str_repeat('  ', $level + 1) . $oProperty_Dir->name,
						'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
					);

					$this->_addEntitiesOfProperties($prefix, $color, $oProperty_Dir->id, $level + 1);
				}
			}
		}
	}

	/**
	 * Add entitites of fields
	 * @param string $prefix
	 * @param string $color
	 * @param integer $parent_id
	 * @param integer $level
	 */
	protected function _addEntitiesOfFields($prefix, $color, $parent_id = 0, $level = 0)
	{
		if (isset($this->_aFieldsTree[$parent_id]))
		{
			foreach ($this->_aFieldsTree[$parent_id] as $oField)
			{
				$this->aEntities[$prefix . '-' . $oField->id] = array(
					'caption' => str_repeat('  ', $level) . $oField->name . " [{$oField->id}]",
					'attr' => array('style' => 'background-color: ' . $color)
				);

				if ($oField->type == 2)
				{
					$subcolor = Core_Str::hex2darker($color, 0.1);

					// Description
					$this->aEntities['fielddesc-' . $oField->id] = array(
						'caption' => str_repeat('  ', $level) . Core::_('Shop_Item.import_file_description', $oField->name),
						'attr' => array('style' => 'background-color: ' . $subcolor)
					);

					// Small Image
					$this->aEntities['fieldsmall-' . $oField->id] = array(
						'caption' => str_repeat('  ', $level) . Core::_('Shop_Item.import_small_images', $oField->name),
						'attr' => array('style' => 'background-color: ' . $subcolor)
					);
				}
			}
		}

		if (isset($this->_aFieldDirsTree[$parent_id]))
		{
			foreach ($this->_aFieldDirsTree[$parent_id] as $oField_Dir)
			{
				// Заголовок выводим, если есть или элементы, или группы
				if (isset($this->_aFieldsTree[$oField_Dir->id]) || isset($this->_aFieldDirsTree[$oField_Dir->id]))
				{
					$this->aEntities['caption-group-field-dir-' . $oField_Dir->id] = array(
						'caption' => str_repeat('  ', $level + 1) . $oField_Dir->name,
						'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold')
					);

					$this->_addEntitiesOfFields($prefix, $color, $oField_Dir->id, $level + 1);
				}
			}
		}
	}

	/**
	 * Add Field
	 * @param string $caption
	 * @param string $color color, e.g. #AAA
	 * @param string $entityName entity name, e.g. seller_name
	 */
	public function addField($caption, $color, $entityName)
	{
		$this->aEntities[$entityName] = array(
			'caption' => $caption,
			'attr' => array('style' => 'background-color: ' . $color)
		);

		return $this;
	}

	/**
	 * Add Field
	 * @param string $entityName entity name, e.g. seller_name
	 * @param array $aField entity option, e.g. array('caption' => 'Seller Name', 'attr' => array('style' => 'background-color: #AAA'))
	 */
	public function addFieldAsArray($entityName, array $aField)
	{
		$this->aEntities[$entityName] = $aField;

		return $this;
	}

	/**
	 * Save group
	 * @param Shop_Group_Model $oShop_Group group
	 * @return Shop_Group
	 */
	protected function _doSaveGroup(Shop_Group_Model $oShop_Group)
	{
		is_null($oShop_Group->path)
			&& $oShop_Group->path = '';

		$this->_incInsertedGroups($oShop_Group->save()->id);

		return $oShop_Group;
	}

	/**
	 * Get the full path of the CSV file
	 * @return string
	 */
	public function getFilePath()
	{
		return CMS_FOLDER . TMP_DIR . $this->file;
	}

	/**
	 * Delete uploaded CSV file
	 * @return boolean
	 */
	public function deleteUploadedFile()
	{
		$sTmpFileFullpath = $this->getFilePath();

		if (Core_File::isFile($sTmpFileFullpath))
		{
			Core_File::delete($sTmpFileFullpath);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Импорт CSV
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImport
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAfterImport
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeSwitch
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeFindByMarking
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAfterFindByMarking
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeFindSpecialprice
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeAdminUpload
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImportItemProperty
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeCaseDefault
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeAssociated
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAfterImportItem
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

		$fInputFile = fopen($this->getFilePath(), 'rb');

		if ($fInputFile === FALSE)
		{
			throw new Core_Exception('');
		}

		// Remove first BOM
		if ($this->seek == 0)
		{
			$BOM = fgets($fInputFile, 4); // length - 1 байт

			if ($BOM === "\xEF\xBB\xBF")
			{
				$this->seek = 3;
			}
			else
			{
				fseek($fInputFile, 0);
			}
		}
		else
		{
			fseek($fInputFile, $this->seek);
		}

		$iCounter = 0;

		$timeout = Core::getmicrotime();

		$aCsvLine = array();

		$bMarkingItemSearchFields = in_array('marking', $this->_aConfig['itemSearchFields']);
		$bPathItemSearchFields = in_array('path', $this->_aConfig['itemSearchFields']);
		$bCmlIdItemSearchFields = in_array('cml_id', $this->_aConfig['itemSearchFields']);
		$bVendorcodeItemSearchFields = in_array('vendorcode', $this->_aConfig['itemSearchFields']);

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

			$bGroupFound = FALSE;

			foreach ($aCsvLine as $iKey => $sData)
			{
				if (!isset($this->csv_fields[$iKey]))
				{
					continue;
				}

				$sData = trim($sData);

				if ($sData != '')
				{
					Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeSwitch', $this, array($this->csv_fields[$iKey], $sData));

					$sLastReturn = Core_Event::getLastReturn();
					!is_null($sLastReturn) && $sData = $sLastReturn;

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
								// $oShop_Order_Status = Core_Entity::factory('Shop_Order_Status')->getByName($sData);
								$oShop_Order_Status = $this->_oCurrentShop->Shop_Order_Statuses->getByName($sData);
								if (!is_null($oShop_Order_Status))
								{
									$this->_oCurrentOrder->shop_order_status_id = $oShop_Order_Status->id;
								}
							}
						break;
						case 'order_currency':
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

									$bGroupFound = TRUE;
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
										->where('shortcut_id', '=', 0)
										->limit(1);
								}
								else
								{
									$oTmpObject = $this->_oCurrentShop->Shop_Groups;
									$oTmpObject->queryBuilder()
										->where('parent_id', '=', intval($this->_oCurrentGroup->id))
										->where('name', '=', $sData)
										->where('shortcut_id', '=', 0)
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

							$bGroupFound = TRUE;

							!$this->_oCurrentItem->modification_id
								&& $this->_oCurrentItem->shop_group_id = $this->_oCurrentGroup->id;

						break;
						// Путь группы товаров
						case 'group_path':
							$oTmpObject = $this->_oCurrentShop->Shop_Groups;
							$oTmpObject
								->queryBuilder()
								->where('parent_id', '=', intval($this->_oCurrentGroup->id))
								->where('shortcut_id', '=', 0)
								->where('path', '=', $sData);

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

							$bGroupFound = TRUE;
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
							$sTmpFilePath = $this->imagesPath . (
								/*strtoupper($this->encoding) == 'UTF-8'
									? $sData
									: Core_File::convertfileNameFromLocalEncoding($sData)*/
								$sData
							);
							$sSourceFileBaseName = basename($sTmpFilePath, '');

							$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

							if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentGroup->createDir();

								if ($bHttp)
								{
									try {
										$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');
										$sSourceFile = NULL;
									}
								}
								else
								{
									$sSourceFile = CMS_FOLDER . $sTmpFilePath;
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
									Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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

								if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0)
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
							$sTmpFilePath = $this->imagesPath . (
								/*strtoupper($this->encoding) == 'UTF-8'
									? $sData
									: Core_File::convertfileNameFromLocalEncoding($sData)*/
								$sData
							);
							$sSourceFileBaseName = basename($sTmpFilePath, '');

							$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

							if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentGroup->createDir();

								if ($bHttp)
								{
									try {
										$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');
										$sSourceFile = NULL;
									}
								}
								else
								{
									$sSourceFile = CMS_FOLDER . $sTmpFilePath;
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
									Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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

								if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0)
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
									->where('shortcut_id', '=', 0)
									->limit(1);

								$oTmpObject = $oTmpObject->findAll(FALSE);
							}

							if (count($oTmpObject))
							{
								// группа найдена
								$this->_oCurrentGroup = $oTmpObject[0];

								!$this->_oCurrentItem->modification_id
									&& $this->_oCurrentItem->shop_group_id = $this->_oCurrentGroup->id;
							}
							else
							{
								// группа не найдена, присваиваем group_cml_id текущей группе
								$this->_oCurrentGroup->guid = $sData;
								$this->_oCurrentGroup->id && $this->_doSaveGroup($this->_oCurrentGroup);
							}

							$bGroupFound = TRUE;
						break;
						// GUID родительской группы товаров
						case 'group_parent_cml_id':
							$oTmpObject = $sData != 'ID00000000'
								? $this->_oCurrentShop->Shop_Groups->getByGuid($sData, FALSE)
								: Core_Entity::factory('Shop_Group', 0);

							if (!is_null($oTmpObject))
							{
								if ($oTmpObject->id != $this->_oCurrentGroup->id)
								{
									$this->_oCurrentGroup->parent_id = $oTmpObject->id;
									$this->_oCurrentGroup->id
										&& $this->_oCurrentGroup->save()
										&& $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								/*!$this->_oCurrentItem->modification_id
									&& $this->_oCurrentItem->shop_group_id = $oTmpObject->id;*/
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
							$oTmpObject = $this->_oCurrentShop->Shop_Producers->getById($sData, FALSE);

							$oTmpObject
								&& $this->_oCurrentItem->shop_producer_id = $oTmpObject->id;
						break;
						// название производителя
						case 'producer_name':
							$oTmpObject = $this->_oCurrentShop->Shop_Producers->getByName($sData, FALSE);

							$this->_oCurrentItem->shop_producer_id = $oTmpObject
								? $oTmpObject->id
								: Core_Entity::factory('Shop_Producer')
									->name($sData)
									->path(Core_Str::transliteration($sData))
									->shop_id($this->_oCurrentShop->id)
									->save()
									->id;
						break;
						// описание производителя
						case 'producer_description':
							if ($this->_oCurrentItem->shop_producer_id)
							{
								$oShop_Producer = $this->_oCurrentShop->Shop_Producers->getById($this->_oCurrentItem->shop_producer_id, FALSE);
								if (!is_null($oShop_Producer))
								{
									$oShop_Producer->description = $sData;
									$oShop_Producer->save();
								}
							}
						break;
						// активность производителя
						case 'producer_active':
							if ($this->_oCurrentItem->shop_producer_id)
							{
								$oShop_Producer = $this->_oCurrentShop->Shop_Producers->getById($this->_oCurrentItem->shop_producer_id, FALSE);
								if (!is_null($oShop_Producer))
								{
									$oShop_Producer->active = $sData;
									$oShop_Producer->save();
								}
							}
						break;
						// индексация производителя
						case 'producer_indexing':
							if ($this->_oCurrentItem->shop_producer_id)
							{
								$oShop_Producer = $this->_oCurrentShop->Shop_Producers->getById($this->_oCurrentItem->shop_producer_id, FALSE);
								if (!is_null($oShop_Producer))
								{
									$oShop_Producer->indexing = $sData;
									$oShop_Producer->save();
								}
							}
						break;
						// порядок сортировки производителя
						case 'producer_sorting':
							if ($this->_oCurrentItem->shop_producer_id)
							{
								$oShop_Producer = $this->_oCurrentShop->Shop_Producers->getById($this->_oCurrentItem->shop_producer_id, FALSE);
								if (!is_null($oShop_Producer))
								{
									$oShop_Producer->sorting = $sData;
									$oShop_Producer->save();
								}
							}
						break;
						// идентификатор продавца
						case 'seller_id':
							$oTmpObject = $this->_oCurrentShop->Shop_Sellers->getById($sData, FALSE);

							$oTmpObject
								&& $this->_oCurrentItem->shop_seller_id = $oTmpObject->id;
						break;
						// название продавца
						case 'seller_name':
							$oTmpObject = $this->_oCurrentShop->Shop_Sellers->getByName($sData, FALSE);

							$this->_oCurrentItem->shop_seller_id = $oTmpObject
								? $oTmpObject->id
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
						// название единицы измерения
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
							$aShortcuts = array_map('trim', $aShortcuts);
							$this->_aAdditionalGroups = array_merge($this->_aAdditionalGroups, $aShortcuts);
						break;
						// Штрихкоды, через запятую
						case 'barcodes':
							$sData = trim(str_replace(';', ',', $sData));
							$aBarcodes = explode(',', $sData);
							$aBarcodes = array_map('trim', $aBarcodes);
							$this->_aBarcodes = array_merge($this->_aBarcodes, $aBarcodes);
						break;
						// GUID товаров в комплекте, через запятую
						case 'sets_guid':
							$aSets = explode(',', trim($sData));
							foreach ($aSets as $sSet)
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Items->getByGuid(trim($sSet), FALSE);

								if (!is_null($oTmpObject))
								{
									$this->_aSets[] = $oTmpObject->id;
								}
							}
						break;
						// Артикулы товаров в комплекте, через запятую
						case 'sets_marking':
							$aSets = explode(',', trim($sData));
							foreach ($aSets as $sSet)
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Items->getByMarking(trim($sSet), FALSE);

								if (!is_null($oTmpObject))
								{
									$this->_aSets[] = $oTmpObject->id;
								}
							}
						break;
						// Названия вкладок товара
						case 'item_tabs':
							$aTabs = explode(',', trim($sData));
							foreach ($aTabs as $sTab)
							{
								$oTmpObject = $this->_oCurrentShop->Shop_Tabs->getByName(trim($sTab), FALSE);

								if (!is_null($oTmpObject))
								{
									$this->_aItemTabs[] = $oTmpObject->id;
								}
							}
						break;
						// Идентификатор товара
						case 'item_id':
							$oTmpObject = $this->_oCurrentShop->Shop_Items->getById($sData, FALSE);
							if (!is_null($oTmpObject))
							{
								// 2 - не обновлять существующие товары
								if ($this->importAction == 2
									&& !isset($this->_aCreatedItemIDs[$oTmpObject->id])
								)
								{
									$this->_clearWhileLoop();
									continue 3;
								}

								//$this->_oCurrentItem->id = $oTmpObject->id;
								$this->_oCurrentItem = $oTmpObject;
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
									->where('marking', '=', $sData) // NOT USE 'LIKE', markings with '_'
									->limit(1);

								$aTmpObject = $oTmpObject->findAll(FALSE);

								if (count($aTmpObject))
								{
									// 2 - не обновлять существующие товары
									if ($this->importAction == 2
										&& !isset($this->_aCreatedItemIDs[$aTmpObject[0]->id])
									)
									{
										$this->_clearWhileLoop();
										continue 3;
									}

									$this->_oCurrentItem = $aTmpObject[0];
								}
							}

							Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterFindByMarking', $this, array($this->_oCurrentShop, $this->_oCurrentItem));
						break;
						// дата добавления товара
						case 'item_datetime':
							$this->_oCurrentItem->datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
								? $sData
								: Core_Date::datetime2sql($sData);
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
						// описание товара
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
						// вес товара с упаковкой
						case 'item_weight_package':
							$this->_oCurrentItem->package_weight = Shop_Controller::instance()->convertPrice($sData);
						break;
						// длина упаковки
						case 'item_length_package':
							$this->_oCurrentItem->package_length = Shop_Controller::instance()->convertPrice($sData);
						break;
						// ширина упаковки
						case 'item_width_package':
							$this->_oCurrentItem->package_width = Shop_Controller::instance()->convertPrice($sData);
						break;
						// высота упаковки
						case 'item_height_package':
							$this->_oCurrentItem->package_height = Shop_Controller::instance()->convertPrice($sData);
						break;
						// минимальное количество
						case 'item_min_quantity':
							$this->_oCurrentItem->min_quantity = Shop_Controller::instance()->convertPrice($sData);
						break;
						// максимальное количество
						case 'item_max_quantity':
							$this->_oCurrentItem->max_quantity = Shop_Controller::instance()->convertPrice($sData);
						break;
						// шаг
						case 'item_quantity_step':
							$this->_oCurrentItem->quantity_step = Shop_Controller::instance()->convertPrice($sData);
						break;
						// цена товара
						case 'item_price':
							$this->_aExternalPrices[0] = Shop_Controller::instance()->convertPrice($sData);
						break;
						// активность товара
						case 'item_active':
							$this->_oCurrentItem->active = $this->_correctCheckbox($sData);
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
										->where('path', '=', $sData)
										->where('shop_group_id', '=', $this->_oCurrentGroup->id);

									$oTmpObject = $oTmpObject->findAll(FALSE);

									if (count($oTmpObject))
									{
										// 2 - не обновлять существующие товары
										if ($this->importAction == 2
											&& !isset($this->_aCreatedItemIDs[$oTmpObject[0]->id])
										)
										{
											$this->_clearWhileLoop();
											continue 3;
										}

										$this->_oCurrentItem = $oTmpObject[0];
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
							$this->_oCurrentItem->indexing = $this->_correctCheckbox($sData);
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
							Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeFindByVendorcode', $this, array($this->_oCurrentShop, $this->_oCurrentItem));

							// Значение 0 пропускаем
							if (!empty($sData))
							{
								$this->_oCurrentItem->vendorcode = $sData;

								if ($bVendorcodeItemSearchFields)
								{
									$oTmpObject = $this->_oCurrentShop->Shop_Items;
									$oTmpObject->queryBuilder()
										->where('vendorcode', '=', $sData) // NOT USE 'LIKE', markings with '_'
										->limit(1);

									$aTmpObject = $oTmpObject->findAll(FALSE);

									if (count($aTmpObject))
									{
										// 2 - не обновлять существующие товары
										if ($this->importAction == 2
											&& !isset($this->_aCreatedItemIDs[$aTmpObject[0]->id])
										)
										{
											$this->_clearWhileLoop();
											continue 3;
										}

										$this->_oCurrentItem = $aTmpObject[0];
									}
								}
							}

							Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterFindByVendorcode', $this, array($this->_oCurrentShop, $this->_oCurrentItem));

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
							$this->_oCurrentShopSpecialPrice->price = Shop_Controller::instance()->convertPrice($sData);
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
										// 2 - не обновлять существующие товары
										if ($this->importAction == 2
											&& !isset($this->_aCreatedItemIDs[$oTmpObject[0]->id])
										)
										{
											$this->_clearWhileLoop();
											continue 3;
										}

										$this->_oCurrentItem = $oTmpObject[0];
									}
								}
							}

							$this->_oCurrentItem->guid = $sData;
						break;
						default:
							$sFieldName = $this->csv_fields[$iKey];

							Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeCaseDefault', $this, array($sFieldName, $sData));

							// Дополнительная цена товара
							if (strpos($sFieldName, "price-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalPrices[$aTmpExplode[1]] = $sData;
							}

							// Дополнительная цена товара
							if (strpos($sFieldName, "warehouse-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aWarehouses[$aTmpExplode[1]] = $sData;
							}

							// Дополнительный файл дополнительного свойства/Малое изображение картинки дополнительного свойства
							if (strpos($sFieldName, "propsmall-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalPropertiesSmall[$aTmpExplode[1]][] = $sData;
							}

							// Описание дополнительного свойства
							if (strpos($sFieldName, "propdesc-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalPropertiesDesc[$aTmpExplode[1]][] = $sData;
							}

							// Основной файл дополнительного свойства/Большое изображение картинки дополнительного свойства
							if (strpos($sFieldName, "prop-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalProperties[$aTmpExplode[1]][] = $sData;
							}

							// Создание модификаций по свойствам
							if (strpos($sFieldName, "modification-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aModificationsByProperties[$aTmpExplode[1]] = $sData;
							}

							// Дополнительный файл пользовательского поля/Малое изображение картинки пользовательского поля
							if (strpos($sFieldName, "fieldsmall-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalFieldsSmall[$aTmpExplode[1]][] = $sData;
							}

							// Описание пользовательского поля
							if (strpos($sFieldName, "fielddesc-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalFieldsDesc[$aTmpExplode[1]][] = $sData;
							}

							// Основной файл пользовательского поля/Большое изображение картинки пользовательского поля
							if (strpos($sFieldName, "field-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aExternalFields[$aTmpExplode[1]][] = $sData;
							}

							// Дополнительное свойство группы товаров
							if (strpos($sFieldName, "prop_group-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aGroupExternalProperties[$aTmpExplode[1]][] = $sData;
							}

							// Пользовательские поля группы товаров
							if (strpos($sFieldName, "field_group-") === 0)
							{
								$aTmpExplode = explode('-', $sFieldName, 2);
								$this->_aGroupExternalFields[$aTmpExplode[1]][] = $sData;
							}
						break;
					}
				}
			}

			if ($this->_oCurrentGroup->id)
			{
				// Indexation
				$this->searchIndexation
					&& Core_Entity::factory('Shop_Group', $this->_oCurrentGroup->id)->index();

				// clearCache
				Core_Entity::factory('Shop_Group', $this->_oCurrentGroup->id)->clearCache();

				// Свойства групп
				if (count($this->_aGroupExternalProperties))
				{
					$this->_incUpdatedGroups($this->_oCurrentGroup->id);

					// Импорт доп. свойств с передачей вызова в метод _addGroupPropertyValue
					foreach ($this->_aGroupExternalProperties as $iPropertyID => $aPropertyValue)
					{
						$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

						foreach ($aPropertyValue as $key => $sPropertyValue)
						{
							Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportGroupProperty', $this, array($this->_oCurrentShop, $this->_oCurrentGroup, $oProperty, $sPropertyValue));

							$this->_addGroupPropertyValue($this->_oCurrentGroup, $oProperty, $sPropertyValue, $key);
						}
					}
				}

				if (count($this->_aGroupExternalFields))
				{
					$this->_incUpdatedGroups($this->_oCurrentGroup->id);

					// Импорт пользовательских полей с передачей вызова в метод _addGroupFieldValue
					foreach ($this->_aGroupExternalFields as $iFieldID => $aFieldValue)
					{
						$oField = Core_Entity::factory('Field')->find($iFieldID);

						foreach ($aFieldValue as $key => $sFieldValue)
						{
							Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportGroupField', $this, array($this->_oCurrentShop, $this->_oCurrentGroup, $oField, $sFieldValue));

							$this->_addGroupFieldValue($this->_oCurrentGroup, $oField, $sFieldValue, $key);
						}
					}
				}
			}

			// New Shop_Item
			$bNewItem = !$this->_oCurrentItem->id;

			if ($bNewItem)
			{
				is_null($this->_oCurrentItem->path)
					&& $this->_oCurrentItem->path = '';

				// Default shop_tax_id
				!$this->_oCurrentItem->shop_tax_id
					&& array_search('tax_id', $this->csv_fields) === FALSE
					&& $this->_oCurrentItem->shop_tax_id = $this->_oCurrentShop->shop_tax_id;

				// Default shop_currency_id
				!$this->_oCurrentItem->shop_currency_id
					&& $this->_oCurrentItem->shop_currency_id = $this->_oCurrentShop->shop_currency_id;
			}

			$this->_oCurrentItem->id
				&& $this->_oCurrentItem->id == $this->_oCurrentItem->modification_id
				&& $this->_oCurrentItem->modification_id = 0;

			if (!is_null($this->_oCurrentOrder))
			{
				$this->_oCurrentShop->add($this->_oCurrentOrder);
			}

			$this->_oCurrentItem->shop_id = $this->_oCurrentShop->id;

			// После установки shop_id
			if (!$this->_oCurrentItem->modification_id && $this->_oCurrentGroup->id)
			{
				// Если группа явно не была указана в CSV, то для существующих товаров её нельзя обновлять
				// в случае, если с передачей внешней группы обновляются цены по артикулу
				($bNewItem || $bGroupFound)
					&& $this->_oCurrentItem->shop_group_id = intval($this->_oCurrentGroup->id);
				//$this->_oCurrentItem->save();
			}

			if ($this->_oCurrentItem->id && $this->importAction == 2)
			{
				// если сказано - оставить без изменений, затираем все изменения
				$this->_oCurrentItem = Core_Entity::factory('Shop_Item')->find($this->_oCurrentItem->id);
				$this->_sBigImageFile = '';
				$this->_sSmallImageFile = '';
				$this->deleteImage = 0;
			}

			if ($this->_oCurrentItem->id
				//&& $this->importAction == 1
				&& !is_null($this->_oCurrentItem->name)
				&& $this->_oCurrentItem->save())
			{
				$this->_incUpdatedItems($this->_oCurrentItem->id);
			}
			elseif (!is_null($this->_oCurrentItem->name) && $this->_oCurrentItem->save())
			{
				$this->_incInsertedItems($this->_oCurrentItem->id);

				// Добавлем в список созданных товаров
				$this->_aCreatedItemIDs[$this->_oCurrentItem->id] = $this->_oCurrentItem->id;
			}

			$aTagsName = array();
			/*if (!$this->_oCurrentItem->id)
			{*/
			if (Core::moduleIsActive('tag'))
			{
				$aChangedColumns = $this->_oCurrentItem->getChangedData();
				// Вставка тэгов автоматически разрешена
				if ($this->_sCurrentTags == '' && $this->_oCurrentShop->apply_tags_automatically
					&& (isset($aChangedColumns['name']) || isset($aChangedColumns['description']) || isset($aChangedColumns['text']))
				)
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
				$this->_oCurrentItem->seo_keywords = implode(', ', $aTagsName);
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
						//$rest = $oShop_Warehouse->getRest($this->_oCurrentItem->id);
						$oShop_Warehouse_Items = $this->_oCurrentItem->Shop_Warehouse_Items->getByWarehouseId($oShop_Warehouse->id, FALSE);
						$rest = $oShop_Warehouse_Items ? $oShop_Warehouse_Items->count : NULL;

						$newRest = Shop_Controller::instance()->convertPrice($iWarehouseCount);

						if (is_null($rest) || $rest != $newRest)
						{
							$oShop_Warehouse_Inventory = $this->_getInventory($oShop_Warehouse->id);

							$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
							$oShop_Warehouse_Inventory_Item->shop_item_id = $this->_oCurrentItem->id;
							$oShop_Warehouse_Inventory_Item->count = $newRest;
							$oShop_Warehouse_Inventory->add($oShop_Warehouse_Inventory_Item);
						}
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
						//->where('price', '=', $this->_oCurrentShopSpecialPrice->price)
						//->where('percent', '=', $this->_oCurrentShopSpecialPrice->percent)
						->limit(1);

					Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeFindSpecialprice', $this, array($oTmpObject, $this->_oCurrentShopSpecialPrice));

					$aTmpObjecs = $oTmpObject->findAll(FALSE);

					// Добавляем специальную цену, если её ещё не существовало
					if (!count($aTmpObjecs))
					{
						$this->_oCurrentShopSpecialPrice->shop_item_id = $this->_oCurrentItem->id;
						$this->_oCurrentShopSpecialPrice->save();
					}
					// Обновляем цену и процент
					else
					{
						$oSpecialPrice = $aTmpObjecs[0];
						
						// В соответствии с формой редактирования товара спеццены без цены или процента удаляются
						if ($this->_oCurrentShopSpecialPrice->price || $this->_oCurrentShopSpecialPrice->percent)
						{
							$oSpecialPrice->price = $this->_oCurrentShopSpecialPrice->price;
							$oSpecialPrice->percent = $this->_oCurrentShopSpecialPrice->percent;
							$oSpecialPrice->save();
						}
						else
						{
							$oSpecialPrice->delete();
						}
					}
				}

				// Обрабатываем ярлыки
				if (count($this->_aAdditionalGroups))
				{
					$this->_aAdditionalGroups = array_map('trim', $this->_aAdditionalGroups);

					$aShopGroups = $this->_oCurrentShop->Shop_Groups;
					$aShopGroups
						->queryBuilder()
						->where('guid', 'IN', $this->_aAdditionalGroups)
						->where('shortcut_id', '=', 0);

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

				// Обрабатываем штрихкоды
				if (count($this->_aBarcodes))
				{
					foreach ($this->_aBarcodes as $value)
					{
						$oShop_Item_Barcode = $this->_oCurrentItem->Shop_Item_Barcodes->getByValue($value, FALSE);

						if (is_null($oShop_Item_Barcode))
						{
							$oShop_Item_Barcode = Core_Entity::factory('Shop_Item_Barcode');
							$oShop_Item_Barcode
								->value($value)
								->shop_item_id($this->_oCurrentItem->id)
								->setType()
								->save();
						}
					}
				}

				// Обрабатываем комплекты в товаре
				if (count($this->_aSets))
				{
					// Change to set
					$this->_oCurrentItem->type == 3;
					$this->_oCurrentItem->save();

					foreach ($this->_aSets as $iTmpId)
					{
						$iCount = $this->_oCurrentItem->Shop_Item_Sets->getCountByshop_item_set_id($iTmpId, FALSE);

						if (!$iCount)
						{
							$oShop_Item_Set = Core_Entity::factory('Shop_Item_Set');
							$oShop_Item_Set
								->shop_item_set_id($iTmpId)
								->shop_item_id($this->_oCurrentItem->id)
								->count(1)
								->save();
						}
					}
				}

				// Обрабатываем вкладки товара
				if (count($this->_aItemTabs))
				{
					foreach ($this->_aItemTabs as $value)
					{
						$oShop_Tab_Item = $this->_oCurrentItem->Shop_Tab_Items->getByshop_tab_id($value, FALSE);
						if (is_null($oShop_Tab_Item))
						{
							$oShop_Tab_Item = Core_Entity::factory('Shop_Tab_Item');
							$oShop_Tab_Item
								->shop_id($this->_oCurrentShop->id)
								->shop_item_id($this->_oCurrentItem->id)
								->shop_tab_id($value)
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

					if (Core_File::isFile($sSourceFile)
						&& Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']))
					{
						try
						{
							Core_File::copy($sSourceFile, $sTargetFile);
						} catch (Exception $e) {}
					}
				}

				if ($this->deleteImage)
				{
					$this->_oCurrentItem
						->deleteLargeImage()
						->deleteSmallImage();
				}

				if (/*!is_null($this->_sBigImageFile) && */$this->_sBigImageFile != ''/* && $this->importAction != 2*/)
				{
					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sTmpFilePath = $sOriginalSourceFile = $this->imagesPath . (
						/*strtoupper($this->encoding) == 'UTF-8'
							? $this->_sBigImageFile
							: Core_File::convertfileNameFromLocalEncoding($this->_sBigImageFile)*/
						$this->_sBigImageFile
					);
					$sSourceFileBaseName = basename($sTmpFilePath, '');

					$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

					if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Удаляем папку назначения вместе со всеми старыми файлами
						//Core_File::deleteDir($sDestinationFolder);

						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFile = NULL;
							}
						}
						else
						{
							$sSourceFile = CMS_FOLDER . trim(Core_File::pathCorrection($sTmpFilePath), DIRECTORY_SEPARATOR);
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

						$aPicturesParam['large_image_source'] = (string) $sSourceFile;
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
							Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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

						$this->_oCurrentItem->save();

						if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}
				}

				if ($this->_sSmallImageFile != '' || $this->_sBigImageFile != '')
				{
					$this->_sSmallImageFile == '' && $this->_sSmallImageFile = $this->_sBigImageFile;

					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sTmpFilePath = $this->imagesPath . (
						/*strtoupper($this->encoding) == 'UTF-8'
							? $this->_sSmallImageFile
							: Core_File::convertfileNameFromLocalEncoding($this->_sSmallImageFile)*/
						$this->_sSmallImageFile
					);

					$sSourceFileBaseName = basename($sTmpFilePath, '');

					$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

					if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFile = NULL;
							}
						}
						else
						{
							$sSourceFile = CMS_FOLDER . trim(Core_File::pathCorrection($sTmpFilePath), DIRECTORY_SEPARATOR);
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

						if (Core_File::isFile($sSourceFile) && filesize($sSourceFile))
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
								Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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

						if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0)
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

				//$aImportedFileProperties = array();

				// Импорт доп. свойств с передачей вызова в метод _addItemPropertyValue
				foreach ($this->_aExternalProperties as $iPropertyID => $aPropertyValue)
				{
					$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

					$group_id = $this->_oCurrentItem->modification_id == 0
						? $this->_oCurrentItem->shop_group_id
						: $this->_oCurrentItem->Modification->shop_group_id;

					// Разрешаем свойство для группы
					$this->_allowPropertyForGroup($oProperty, $group_id);

					foreach ($aPropertyValue as $key => $sPropertyValue)
					{
						Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportItemProperty', $this, array($this->_oCurrentShop, $this->_oCurrentItem, $oProperty, $sPropertyValue));

						/*$oProperty_Value = */
						$this->_addItemPropertyValue($this->_oCurrentItem, $oProperty, $sPropertyValue, $key);

						/*$oProperty->type == 2
							&& $aImportedFileProperties[$iPropertyID][] = $oProperty_Value;*/
					}
				}

				// Импорт пользовательских полей с передачей вызова в метод _addItemFieldValue
				foreach ($this->_aExternalFields as $iFieldID => $aFieldValue)
				{
					$oField = Core_Entity::factory('Field')->find($iFieldID);

					foreach ($aFieldValue as $key => $sFieldValue)
					{
						Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportItemField', $this, array($this->_oCurrentShop, $this->_oCurrentItem, $oField, $sFieldValue));

						$this->_addItemFieldValue($this->_oCurrentItem, $oField, $sFieldValue, $key);
					}
				}

				// Отдельный импорт малых изображений, когда большие не были проимпортированы
				foreach ($this->_aExternalPropertiesSmall as $iPropertyID => $aPropertyValue)
				{
					$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

					// Разрешаем свойство для группы
					$this->_allowPropertyForGroup($oProperty, $this->_oCurrentGroup->id);

					foreach ($aPropertyValue as $sPropertyValue)
					{

						// При отдельном импорте малых изображений, всегда создаются новые значения,
						// при совместном импорте с большими, малые изображения обрабатываются в _addItemPropertyValue()
						$oProperty_Value = $oProperty->createNewValue($this->_oCurrentItem->id);

						// Папка назначения
						$sDestinationFolder = $this->_oCurrentItem->getItemPath();

						// Файл-источник
						$sTmpFilePath = $this->imagesPath . $sPropertyValue;

						$sSourceFileBaseName = basename($sTmpFilePath, '');

						$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

						if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
						{
							// Создаем папку назначения
							$this->_oCurrentItem->createDir();

							if ($bHttp)
							{
								try {
									$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
								}
								catch (Exception $e)
								{
									Core_Message::show($e->getMessage(), 'error');
									$sSourceFile = NULL;
								}
							}
							else
							{
								$sSourceFile = CMS_FOLDER . $sTmpFilePath;
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
								Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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

							if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0)
							{
								// Файл временный, подлежит удалению
								Core_File::delete($sSourceFile);
							}
						}

						$oProperty_Value->save();
					}
				}

				// Отдельный импорт малых изображений, когда большие не были проимпортированы
				foreach ($this->_aExternalFieldsSmall as $iFieldID => $aFieldValue)
				{
					$oField = Core_Entity::factory('Field')->find($iFieldID);

					// Разрешаем свойство для группы
					// $this->_allowFieldForGroup($oField, $this->_oCurrentGroup->id);

					foreach ($aFieldValue as $sFieldValue)
					{
						/*$aFieldValues = $oField->getValues($this->_oCurrentItem->id, FALSE);

						$oField_Value = isset($aFieldValues[0])
							? $aFieldValues[0]
							: $oField->createNewValue($this->_oCurrentItem->id);*/

						// При отдельном импорте малых изображений, всегда создаются новые значения,
						// при совместном импорте с большими, малые изображения обрабатываются в _addItemFieldValue()
						$oField_Value = $oField->createNewValue($this->_oCurrentItem->id);

						// Папка назначения
						$sDestinationFolder = $this->_oCurrentItem->getItemPath();

						// Файл-источник
						$sTmpFilePath = $this->imagesPath . $sFieldValue;

						$sSourceFileBaseName = basename($sTmpFilePath, '');

						$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

						if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
						{
							// Создаем папку назначения
							$this->_oCurrentItem->createDir();

							if ($bHttp)
							{
								try {
									$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
								}
								catch (Exception $e)
								{
									Core_Message::show($e->getMessage(), 'error');
									$sSourceFile = NULL;
								}
							}
							else
							{
								$sSourceFile = CMS_FOLDER . $sTmpFilePath;
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

								$oField_Value->save();
								$sTargetFileName = "small_shop_field_file_{$this->_oCurrentItem->id}_{$oField_Value->id}{$sTargetFileExtension}";
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
							if ($oField_Value->file_small != '')
							{
								try
								{
									Core_File::delete($sDestinationFolder . $oField_Value->file_small);
								} catch (Exception $e) {}
							}

							try {
								Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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
								$oField_Value->file_small = $sTargetFileName;
								$oField_Value->file_small_name = '';
							}

							if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0)
							{
								// Файл временный, подлежит удалению
								Core_File::delete($sSourceFile);
							}
						}

						$oField_Value->save();
					}
				}

				// Устанавливаем цены из $this->_aExternalPrices
				$this->_setPrices($this->_oCurrentItem);

				// Модификации по свойствам
				if (count($this->_aModificationsByProperties))
				{
					$aToCombine = $aAffectedModifications = array();

					foreach ($this->_aModificationsByProperties as $iPropertyID => $sPropertyValue)
					{
						$oProperty = Core_Entity::factory('Property', $iPropertyID);

						// Свойство списочного типа
						if ($oProperty->type == 3)
						{
							// Разрешаем свойство для группы
							$this->_allowPropertyForGroup($oProperty, $this->_oCurrentItem->shop_group_id);

							$aPropertyValueExplode = explode(',', trim($sPropertyValue));
							$aPropertyValueExplode = array_map('trim', $aPropertyValueExplode);

							foreach ($aPropertyValueExplode as $tmpValue)
							{
								if ($tmpValue !== '')
								{
									$oList_Item = $oProperty->List->List_Items->getByValue($tmpValue, FALSE);

									if (is_null($oList_Item))
									{
										$oList_Item = Core_Entity::factory('List_Item');
										$oList_Item->list_id = $oProperty->list_id;
										$oList_Item->value = $tmpValue;

										// Apache %2F (/) is forbidden
										strpos($tmpValue, '/') !== FALSE
											&& $oList_Item->path = trim(str_replace('/', ' ', $tmpValue));

										$oList_Item->save();
									}

									$aToCombine[$iPropertyID][] = $oList_Item->id;
								}
							}
						}
					}

					if (count($aToCombine))
					{
						$aCombined = Core_Array::combine($aToCombine);
						if (count($aCombined))
						{
							$havingCount = count($aCombined[0]);

							foreach ($aCombined as $aCombinedValues)
							{
								$oModifications = $this->_oCurrentItem->Modifications;
								$oModifications->queryBuilder()
									->select('shop_items.*')
									->leftJoin('shop_item_properties', 'shop_items.shop_id', '=', 'shop_item_properties.shop_id')
									->leftJoin('property_value_ints', 'shop_items.id', '=', 'property_value_ints.entity_id',
										array(
											array('AND' => array('shop_item_properties.property_id', '=', Core_QueryBuilder::expression('property_value_ints.property_id')))
										)
									)
									->open();

								foreach ($aCombinedValues as $propertyId => $propertyValue)
								{
									$oModifications->queryBuilder()
										->where('shop_item_properties.property_id', '=', $propertyId)
										->where('property_value_ints.value', '=', $propertyValue)
										->setOr();
								}

								$oModifications->queryBuilder()
									->close()
									->groupBy('shop_items.id');

								$havingCount > 1
									&& $oModifications->queryBuilder()
										->having(Core_Querybuilder::expression('COUNT(DISTINCT `shop_item_properties`.`property_id`)'), '=', $havingCount);

								$aModifications = $oModifications->findAll(FALSE);

								// Создаем модификацию товара с заданными значениями
								if (!count($aModifications))
								{
									$oNew_Modification = Core_Entity::factory('Shop_Item');
									$oNew_Modification->name = $this->_oCurrentItem->name;
									$oNew_Modification->marking = $this->_oCurrentItem->marking;
									$oNew_Modification->shop_id = $this->_oCurrentShop->id;
									$oNew_Modification->modification_id = $this->_oCurrentItem->id;
									$oNew_Modification->shop_currency_id = $this->_oCurrentItem->shop_currency_id;
									$oNew_Modification->shop_measure_id = $this->_oCurrentItem->shop_measure_id;
									$oNew_Modification->shop_producer_id = $this->_oCurrentItem->shop_producer_id;
									$oNew_Modification->weight = $this->_oCurrentItem->weight;
									$oNew_Modification->min_quantity = $this->_oCurrentItem->min_quantity;
									$oNew_Modification->max_quantity = $this->_oCurrentItem->max_quantity;
									$oNew_Modification->quantity_step = $this->_oCurrentItem->quantity_step;

									$sorting = 0;

									foreach ($aCombinedValues as $propertyId => $propertyValue)
									{
										$oProperty = Core_Entity::factory('Property', $propertyId);
										$oList_Item = Core_Entity::factory('List_Item', $propertyValue);

										$oNew_Modification->marking .= '-' . $oList_Item->value;
										$oNew_Modification->name .= ', ' . $oProperty->name .' ' . $oList_Item->value;

										$sorting += $oList_Item->sorting;
									}

									$oNew_Modification->sorting = $sorting;
									$oNew_Modification->save();

									$aModifications[] = $oNew_Modification;

									$this->_incInsertedItems($oNew_Modification->id);

									// Свойства для заданной модификации
									foreach ($aCombinedValues as $propertyId => $propertyValue)
									{
										$oProperty = Core_Entity::factory('Property', $propertyId);

										$oProperty_Value = $oProperty->createNewValue($oNew_Modification->id);
										$oProperty_Value->value($propertyValue);
										$oProperty_Value->save();
									}
								}

								foreach ($aModifications as $oModification)
								{
									// Цены для создаваемых модификаций
									$this->_setPrices($oModification);

									// Fast filter
									if ($this->_oCurrentShop->filter)
									{
										$oShop_Filter_Controller = new Shop_Filter_Controller($this->_oCurrentShop);
										$oShop_Filter_Controller->fill($oModification);
									}

									$this->deleteUnsentModificationsByProperties
										&& $aAffectedModifications[] = $oModification->id;
								}
							}
						}
					}

					// Удалять модификации, не затронутые при создании по дополнительным свойствам
					if ($this->deleteUnsentModificationsByProperties)
					{
						$oModifications = $this->_oCurrentItem->Modifications;

						count($aAffectedModifications) && $oModifications->queryBuilder()
							->where('shop_items.id', 'NOT IN', $aAffectedModifications);

						$aModifications = $oModifications->findAll(FALSE);

						foreach ($aModifications as $oModification)
						{
							// Indexation
							$this->searchIndexation
								&& $oModification->unindex();

							$oModification->markDeleted();
						}
					}
				}

				if ($this->_oCurrentItem->id)
				{
					// Indexation
					$this->searchIndexation
						&& $this->_oCurrentItem->index();

					// clearCache
					$this->_oCurrentItem->clearCache();

					// Fast filter
					if ($this->_oCurrentShop->filter)
					{
						$oShop_Filter_Controller = new Shop_Filter_Controller($this->_oCurrentShop);
						$oShop_Filter_Controller->fill($this->_oCurrentItem);
					}
				}

				Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterImportItem', $this, array($this->_oCurrentShop, $this->_oCurrentItem, $aCsvLine));
			} // end fields

			if (!is_null($this->_oCurrentOrder) && !is_null($this->_oCurrentOrderItem))
			{
				$this->_oCurrentOrder->add($this->_oCurrentOrderItem);
			}

			$iCounter++;

			//$this->_oCurrentItem->clear();

			$this->_clearWhileLoop();
		} // end line

		$iCurrentSeekPosition = $aCsvLine === FALSE
			? FALSE
			: ftell($fInputFile);

		fclose($fInputFile);

		Core_Event::notify('Shop_Item_Import_Csv_Controller.onAfterImport', $this, array($this->_oCurrentShop, $iCurrentSeekPosition));

		return $iCurrentSeekPosition;
	}

	/**
	 * Allow Property For Group
	 * @param Property_Model $oProperty
	 * @param int $shop_group_id
	 * @return self
	 */
	protected function _allowPropertyForGroup(Property_Model $oProperty, $shop_group_id)
	{
		$iShop_Item_Property_Id = $oProperty->Shop_Item_Property->id;

		$shop_group_id = intval($shop_group_id);

		// Проверяем доступность дополнительного свойства для группы товаров
		if (is_null($this->_oCurrentShop->Shop_Item_Property_For_Groups->getByShopItemPropertyIdAndGroupId($iShop_Item_Property_Id, $shop_group_id)))
		{
			// Свойство не доступно текущей группе, делаем его доступным
			$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
			$oShop_Item_Property_For_Group->shop_group_id = $shop_group_id;
			$oShop_Item_Property_For_Group->shop_item_property_id = $iShop_Item_Property_Id;
			$oShop_Item_Property_For_Group->shop_id = $this->_oCurrentShop->id;
			$oShop_Item_Property_For_Group->save();
		}

		return $this;
	}

	/**
	 * Set Prices by $this->_aExternalPrices
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 */
	protected function _setPrices(Shop_Item_Model $oShop_Item)
	{
		foreach ($this->_aExternalPrices as $iPriceID => $sPriceValue)
		{
			$oShop_Item_Price = $iPriceID
				? $oShop_Item->Shop_Item_Prices->getByPriceId($iPriceID, FALSE)
				: NULL;

			$old_price = !is_null($oShop_Item_Price)
				? $oShop_Item_Price->value
				: $oShop_Item->price;

			$newPrice = Shop_Controller::instance()->convertPrice($sPriceValue);

			if (floatval($old_price) != floatval($newPrice))
			{
				$oShop_Price_Setting = $this->_getPriceSetting();

				$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
				$oShop_Price_Setting_Item->shop_price_id = $iPriceID;
				$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
				$oShop_Price_Setting_Item->old_price = $old_price;
				$oShop_Price_Setting_Item->new_price = $newPrice;
				$oShop_Price_Setting->add($oShop_Price_Setting_Item);

				/*if (is_null($oShop_Item_Price))
				{
					$oShop_Item_Price = Core_Entity::factory('Shop_Item_Price');
					$oShop_Item_Price->shop_item_id = $oShop_Item->id;
					$oShop_Item_Price->shop_price_id = $iPriceID;
				}

				$oShop_Item_Price->value($sPriceValue);
				$oShop_Item_Price->save();*/
			}
		}

		return $this;
	}

	protected function _clearWhileLoop()
	{
		$this->_oCurrentItem = Core_Entity::factory('Shop_Item');
		$this->_oCurrentGroup = Core_Entity::factory('Shop_Group', $this->_iCurrentGroupId);
		$this->_oCurrentGroup->shop_id = $this->_oCurrentShop->id;

		$this->_oCurrentItem->shop_group_id = $this->_oCurrentGroup->id;

		$this->_oCurrentOrder = $this->_oCurrentOrderItem = NULL;

		$this->_sBigImageFile = $this->_sSmallImageFile = '';

		// Очищаем временные массивы
		$this->_aExternalPrices =
			$this->_aWarehouses =
			$this->_aExternalPropertiesSmall =
			$this->_aExternalProperties =
			$this->_aExternalPropertiesDesc =
			$this->_aExternalFieldsSmall =
			$this->_aExternalFields =
			$this->_aExternalFieldsDesc =
			$this->_aModificationsByProperties =
			$this->_aGroupExternalProperties =
			$this->_aGroupExternalFields =
			$this->_aAdditionalGroups =
			$this->_aBarcodes =
			$this->_aItemTabs =
			$this->_aSets = array();

		// Список меток для текущего товара
		$this->_sCurrentTags = '';
		// Артикул родительского товара - признак того, что данный товар сопутствует товару с данным артикулом
		$this->_sAssociatedItemMark = '';
		// Текущий электронный товар
		$this->_oCurrentShopEItem->clear();
		// Текущая специальная цена для товара
		$this->_oCurrentShopSpecialPrice->clear();

		return $this;
	}

	/**
	 * Add property to item
	 * @param Shop_Item_Model $oShopItem
	 * @param Property_Model $oProperty
	 * @param string $sPropertyValue property value
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAddItemPropertyValueDefault
	 */
	protected function _addItemPropertyValue(Shop_Item_Model $oShopItem, Property_Model $oProperty, $sPropertyValue, $position = 0)
	{
		$aPropertyValues = $oProperty->getValues($oShopItem->id, FALSE);

		// Удалять ранее загруженные свойства или свойство в массиве у удалению перед загрузкой
		if ($this->deletePropertyValues === TRUE
			|| is_array($this->deletePropertyValues) && in_array($oProperty->id, $this->deletePropertyValues))
		{
			// Свойство для данного товара не было очищено
			if (!isset($this->_aClearedItemsPropertyValues[$oShopItem->id])
				|| !in_array($oProperty->id, $this->_aClearedItemsPropertyValues[$oShopItem->id]))
			{
				foreach ($aPropertyValues as $oPropertyValue)
				{
					$oProperty->type == 2
						&& $oPropertyValue->setDir($oShopItem->getItemPath());
					$oPropertyValue->delete();
				}

				$aPropertyValues = array();

				$this->_aClearedItemsPropertyValues[$oShopItem->id][] = $oProperty->id;
			}
		}

		switch ($oProperty->type)
		{
			case 0: // Int
				$changedValue = Shop_Controller::convertDecimal($sPropertyValue);
			break;
			case 2: // Файл
				$changedValue = $sPropertyValue;
			break;
			case 3: // Список
				if (Core::moduleIsActive('list'))
				{
					$oList_Item = $oProperty->List->List_Items->getByValue($sPropertyValue, FALSE);

					if ($oList_Item)
					{
						$changedValue = $oList_Item->id;
					}
					else
					{
						$oList_Item = Core_Entity::factory('List_Item')
							->list_id($oProperty->list_id)
							->value($sPropertyValue);

						// Apache %2F (/) is forbidden
						strpos($sPropertyValue, '/') !== FALSE
							&& $oList_Item->path = trim(str_replace('/', ' ', $sPropertyValue));

						$changedValue = $oList_Item->save()
							->id;
					}
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			case 5: // Informationsystem
				$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getByName($sPropertyValue);
				if ($oInformationsystem_Item)
				{
					$changedValue = $oInformationsystem_Item->id;
				}
				elseif (is_numeric($sPropertyValue))
				{
					$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getById($sPropertyValue);

					$changedValue = $oInformationsystem_Item
						? $oInformationsystem_Item->id
						: NULL;
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			case 7: // Checkbox
				$changedValue = $this->_correctCheckbox($sPropertyValue);
			break;
			case 8:
				$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sPropertyValue)
					? $sPropertyValue
					: Core_Date::datetime2sql($sPropertyValue);
			break;
			case 9:
				$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sPropertyValue)
					? $sPropertyValue
					: Core_Date::datetime2sql($sPropertyValue);
			break;
			case 11: // Float
				$changedValue = Shop_Controller::convertDecimal($sPropertyValue);
			break;
			case 12: // Shop
				// by Name
				$oShop_Item = $oProperty->Shop->Shop_Items->getByName($sPropertyValue);
				if ($oShop_Item)
				{
					$changedValue = $oShop_Item->id;
				}
				else
				{
					// by Marking
					$oShop_Item = $oProperty->Shop->Shop_Items->getByMarking($sPropertyValue);
					if ($oShop_Item)
					{
						$changedValue = $oShop_Item->id;
					}
					// by ID
					elseif (is_numeric($sPropertyValue))
					{
						$oShop_Item = $oProperty->Shop->Shop_Items->getById($sPropertyValue);

						$changedValue = $oShop_Item
							? $oShop_Item->id
							: NULL;
					}
					else
					{
						$changedValue = NULL;
					}
				}
			break;
			default:
				Core_Event::notify(get_class($this) . '.onAddItemPropertyValueDefault', $this, array($oShopItem, $oProperty, $sPropertyValue));

				$changedValue = is_null(Core_Event::getLastReturn())
					? $sPropertyValue
					: Core_Event::getLastReturn();
		}

		if (!is_null($changedValue))
		{
			if ($oProperty->multiple)
			{
				$bHttp = strpos(strtolower($changedValue), "http://") === 0 || strpos(strtolower($changedValue), "https://") === 0;

				foreach ($aPropertyValues as $oProperty_Value)
				{
					if ($oProperty->type == 2 && basename($oProperty_Value->file_name) == basename($changedValue)
						|| $oProperty->type != 2 && $oProperty_Value->value == $changedValue)
					{
						return $oProperty_Value;
					}
				}

				$oProperty_Value = $oProperty->createNewValue($oShopItem->id);
			}
			else
			{
				$oProperty_Value = isset($aPropertyValues[0])
					? $aPropertyValues[0]
					: $oProperty->createNewValue($oShopItem->id);
			}

			// File
			if ($oProperty->type == 2)
			{
				// Папка назначения
				$sDestinationFolder = $oShopItem->getItemPath();

				// Файл-источник
				$sTmpFilePath = $this->imagesPath . (
					/*strtoupper($this->encoding) == 'UTF-8'
						? $sPropertyValue
						: Core_File::convertfileNameFromLocalEncoding($sPropertyValue)*/
					$sPropertyValue
				);

				$sSourceFileBaseName = basename($sTmpFilePath, '');

				$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

				$sSourceFileName = $bHttp
					? basename($sPropertyValue)
					: $sSourceFileBaseName;

				if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
				{
					// Создаем папку назначения
					$oShopItem->createDir();

					if ($bHttp)
					{
						try {
							$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
						}
						catch (Exception $e)
						{
							Core_Message::show($e->getMessage(), 'error');
							$sSourceFile = NULL;
						}
					}
					else
					{
						$sSourceFile = CMS_FOLDER . ltrim($sTmpFilePath, '/\\');
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
						$sTargetFileName = "shop_property_file_{$oShopItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
						//$sTargetFileName = "shop_property_file_{$oShopItem->id}_{$oProperty->id}{$sTargetFileExtension}";
					}

					// Создаем массив параметров для загрузки картинок элементу
					$aPicturesParam = array();
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

					if (isset($this->_aExternalPropertiesSmall[$oProperty->id][$position]))
					{
						// Малое изображение передано
						$aPicturesParam['create_small_image_from_large'] = FALSE;

						// Файл-источник
						$sTmpFilePath = $this->imagesPath . $this->_aExternalPropertiesSmall[$oProperty->id][$position];

						$sSourceFileBaseNameSmall = basename($sTmpFilePath, '');

						$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

						if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
						{
							// Создаем папку назначения
							$oShopItem->createDir();

							if ($bHttp)
							{
								try {
									$sSourceFileSmall = $this->_downloadHttpFile($sTmpFilePath);
								}
								catch (Exception $e)
								{
									Core_Message::show($e->getMessage(), 'error');
									$sSourceFileSmall = NULL;
								}
							}
							else
							{
								$sSourceFileSmall = CMS_FOLDER . $sTmpFilePath;
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
								$sTargetFileNameSmall = "small_shop_property_file_{$oShopItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
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
						unset($this->_aExternalPropertiesSmall[$oProperty->id][$position]);
					}
					else
					{
						// Малое изображение не передано
						$aPicturesParam['create_small_image_from_large'] = TRUE;
						$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
						$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
						$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";

						$sSourceFileSmall = NULL;
						$sTargetFileNameSmall = "small_{$sTargetFileName}";
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
						Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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
						$oProperty_Value->file_name = $sSourceFileName;
					}

					if ($aResult['small_image'])
					{
						$oProperty_Value->file_small = $sTargetFileNameSmall;
						$oProperty_Value->file_small_name = '';
					}

					if (isset($this->_aExternalPropertiesDesc[$oProperty->id][$position]))
					{
						$oProperty_Value->file_description = $this->_aExternalPropertiesDesc[$oProperty->id][$position];
						unset($this->_aExternalPropertiesDesc[$oProperty->id][$position]);
					}

					$oProperty_Value->save();

					clearstatcache();

					if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0 && Core_File::isFile($sSourceFile))
					{
						// Файл временный, подлежит удалению
						Core_File::delete($sSourceFile);
					}

					if (!is_null($sSourceFileSmall) && strpos(basename($sSourceFileSmall), "CMS") === 0 && Core_File::isFile($sSourceFileSmall))
					{
						// Файл временный, подлежит удалению
						Core_File::delete($sSourceFileSmall);
					}
				}
			}
			else
			{
				$oProperty_Value->setValue($changedValue);
				$oProperty_Value->save();
			}

			return $oProperty_Value;
		}

		return FALSE;
	}

	/**
	 * Add field to item
	 * @param Shop_Item_Model $oShopItem
	 * @param Field_Model $oField
	 * @param string $sFieldValue field value
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAddItemFieldValueDefault
	 */
	protected function _addItemFieldValue(Shop_Item_Model $oShopItem, Field_Model $oField, $sFieldValue, $position = 0)
	{
		$aFieldValues = $oField->getValues($oShopItem->id, FALSE);

		// Удалять ранее загруженные свойства или свойство в массиве у удалению перед загрузкой
		if ($this->deleteFieldValues === TRUE
			|| is_array($this->deleteFieldValues) && in_array($oField->id, $this->deleteFieldValues))
		{
			// Свойство для данного товара не было очищено
			if (!isset($this->_aClearedItemsFieldValues[$oShopItem->id])
				|| !in_array($oField->id, $this->_aClearedItemsFieldValues[$oShopItem->id]))
			{
				foreach ($aFieldValues as $oFieldValue)
				{
					$oField->type == 2
						&& $oFieldValue->setDir($oShopItem->getItemPath());
					$oFieldValue->delete();
				}

				$aFieldValues = array();

				$this->_aClearedItemsFieldValues[$oShopItem->id][] = $oField->id;
			}
		}

		switch ($oField->type)
		{
			case 0: // Int
				$changedValue = Shop_Controller::convertDecimal($sFieldValue);
			break;
			case 2: // Файл
				$changedValue = $sFieldValue;
			break;
			case 3: // Список
				if (Core::moduleIsActive('list'))
				{
					$oList_Item = $oField->List->List_Items->getByValue($sFieldValue, FALSE);

					if ($oList_Item)
					{
						$changedValue = $oList_Item->id;
					}
					else
					{
						$oList_Item = Core_Entity::factory('List_Item')
							->list_id($oField->list_id)
							->value($sFieldValue);

						// Apache %2F (/) is forbidden
						strpos($sFieldValue, '/') !== FALSE
							&& $oList_Item->path = trim(str_replace('/', ' ', $sFieldValue));

						$changedValue = $oList_Item->save()
							->id;
					}
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			case 5: // Informationsystem
				$oInformationsystem_Item = $oField->Informationsystem->Informationsystem_Items->getByName($sFieldValue);
				if ($oInformationsystem_Item)
				{
					$changedValue = $oInformationsystem_Item->id;
				}
				elseif (is_numeric($sFieldValue))
				{
					$oInformationsystem_Item = $oField->Informationsystem->Informationsystem_Items->getById($sFieldValue);

					$changedValue = $oInformationsystem_Item
						? $oInformationsystem_Item->id
						: NULL;
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			case 7: // Checkbox
				$changedValue = $this->_correctCheckbox($sFieldValue);
			break;
			case 8:
				$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sFieldValue)
					? $sFieldValue
					: Core_Date::datetime2sql($sFieldValue);
			break;
			case 9:
				$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sFieldValue)
					? $sFieldValue
					: Core_Date::datetime2sql($sFieldValue);
			break;
			case 11: // Float
				$changedValue = Shop_Controller::convertDecimal($sFieldValue);
			break;
			case 12: // Shop
				// by Name
				$oShop_Item = $oField->Shop->Shop_Items->getByName($sFieldValue);
				if ($oShop_Item)
				{
					$changedValue = $oShop_Item->id;
				}
				else
				{
					// by Marking
					$oShop_Item = $oField->Shop->Shop_Items->getByMarking($sFieldValue);
					if ($oShop_Item)
					{
						$changedValue = $oShop_Item->id;
					}
					// by ID
					elseif (is_numeric($sFieldValue))
					{
						$oShop_Item = $oField->Shop->Shop_Items->getById($sFieldValue);

						$changedValue = $oShop_Item
							? $oShop_Item->id
							: NULL;
					}
					else
					{
						$changedValue = NULL;
					}
				}
			break;
			default:
				Core_Event::notify(get_class($this) . '.onAddItemFieldValueDefault', $this, array($oShopItem, $oField, $sFieldValue));

				$changedValue = is_null(Core_Event::getLastReturn())
					? $sFieldValue
					: Core_Event::getLastReturn();
		}

		if (!is_null($changedValue))
		{
			if ($oField->multiple)
			{
				$bHttp = strpos(strtolower($changedValue), "http://") === 0 || strpos(strtolower($changedValue), "https://") === 0;

				foreach ($aFieldValues as $oField_Value)
				{
					if ($oField->type == 2 && basename($oField_Value->file_name) == basename($changedValue)
						|| $oField->type != 2 && $oField_Value->value == $changedValue)
					{
						return $oField_Value;
					}
				}

				$oField_Value = $oField->createNewValue($oShopItem->id);
			}
			else
			{
				$oField_Value = isset($aFieldValues[0])
					? $aFieldValues[0]
					: $oField->createNewValue($oShopItem->id);
			}

			// File
			if ($oField->type == 2)
			{
				// Папка назначения
				$sDestinationFolder = $oShopItem->getItemPath();

				// Файл-источник
				$sTmpFilePath = $this->imagesPath . (
					/*strtoupper($this->encoding) == 'UTF-8'
						? $sFieldValue
						: Core_File::convertfileNameFromLocalEncoding($sFieldValue)*/
					$sFieldValue
				);

				$sSourceFileBaseName = basename($sTmpFilePath, '');

				$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

				$sSourceFileName = $bHttp
					? basename($sFieldValue)
					: $sSourceFileBaseName;

				if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
				{
					// Создаем папку назначения
					$oShopItem->createDir();

					if ($bHttp)
					{
						try {
							$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
						}
						catch (Exception $e)
						{
							Core_Message::show($e->getMessage(), 'error');
							$sSourceFile = NULL;
						}
					}
					else
					{
						$sSourceFile = CMS_FOLDER . ltrim($sTmpFilePath, '/\\');
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

						$oField_Value->save();
						$sTargetFileName = "shop_field_file_{$oShopItem->id}_{$oField_Value->id}{$sTargetFileExtension}";
						//$sTargetFileName = "shop_field_file_{$oShopItem->id}_{$oField->id}{$sTargetFileExtension}";
					}

					// Создаем массив параметров для загрузки картинок элементу
					$aPicturesParam = array();
					$aPicturesParam['large_image_source'] = $sSourceFile;
					$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
					$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
					$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
					$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
					$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
					$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;
					//$aPicturesParam['large_image_max_width'] = $this->_oCurrentShop->image_large_max_width;
					$aPicturesParam['large_image_max_width'] = $oField->image_large_max_width;
					//$aPicturesParam['large_image_max_height'] = $this->_oCurrentShop->image_large_max_height;
					$aPicturesParam['large_image_max_height'] = $oField->image_large_max_height;
					$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

					if (isset($this->_aExternalFieldsSmall[$oField->id][$position]))
					{
						// Малое изображение передано
						$aPicturesParam['create_small_image_from_large'] = FALSE;

						// Файл-источник
						$sTmpFilePath = $this->imagesPath . $this->_aExternalFieldsSmall[$oField->id][$position];

						$sSourceFileBaseNameSmall = basename($sTmpFilePath, '');

						$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

						if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
						{
							// Создаем папку назначения
							$oShopItem->createDir();

							if ($bHttp)
							{
								try {
									$sSourceFileSmall = $this->_downloadHttpFile($sTmpFilePath);
								}
								catch (Exception $e)
								{
									Core_Message::show($e->getMessage(), 'error');
									$sSourceFileSmall = NULL;
								}
							}
							else
							{
								$sSourceFileSmall = CMS_FOLDER . $sTmpFilePath;
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

								$oField_Value->save();
								$sTargetFileNameSmall = "small_shop_field_file_{$oShopItem->id}_{$oField_Value->id}{$sTargetFileExtension}";
							}

							$aPicturesParam['small_image_source'] = $sSourceFileSmall;
							$aPicturesParam['small_image_name'] = $sSourceFileBaseNameSmall;
							$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileNameSmall;

							// Удаляем старое малое изображение
							/*if ($oField_Value->file_small != '')
							{
								try
								{
									Core_File::delete($sDestinationFolder . $oField_Value->file_small);
								} catch (Exception $e) {}
							}*/
						}

						// ------------------------------------------
						// Исключаем из отдельного импорта малых изображений
						unset($this->_aExternalFieldsSmall[$oField->id][$position]);
					}
					else
					{
						// Малое изображение не передано
						$aPicturesParam['create_small_image_from_large'] = TRUE;
						$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
						$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
						$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";

						$sSourceFileSmall = NULL;
						$sTargetFileNameSmall = "small_{$sTargetFileName}";
					}

					$aPicturesParam['small_image_max_width'] = $oField->image_small_max_width;
					$aPicturesParam['small_image_max_height'] = $oField->image_small_max_height;
					$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
					$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];

					// Удаляем старое большое изображение
					if ($oField_Value->file != '')
					{
						if ($sDestinationFolder . $oField_Value->file != $sSourceFile)
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oField_Value->file);
							} catch (Exception $e) {}
						}
					}

					// Удаляем старое малое изображение
					if ($oField_Value->file_small != '')
					{
						if ($sDestinationFolder . $oField_Value->file_small != $sSourceFileSmall)
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oField_Value->file_small);
							} catch (Exception $e) {}
						}
					}

					try {
						Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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
						$oField_Value->file = $sTargetFileName;
						$oField_Value->file_name = $sSourceFileName;
					}

					if ($aResult['small_image'])
					{
						$oField_Value->file_small = $sTargetFileNameSmall;
						$oField_Value->file_small_name = '';
					}

					if (isset($this->_aExternalFieldsDesc[$oField->id][$position]))
					{
						$oField_Value->file_description = $this->_aExternalFieldsDesc[$oField->id][$position];
						unset($this->_aExternalFieldsDesc[$oField->id][$position]);
					}

					$oField_Value->save();

					clearstatcache();

					if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0 && Core_File::isFile($sSourceFile))
					{
						// Файл временный, подлежит удалению
						Core_File::delete($sSourceFile);
					}

					if (!is_null($sSourceFile) && strpos(basename($sSourceFileSmall), "CMS") === 0 && Core_File::isFile($sSourceFileSmall))
					{
						// Файл временный, подлежит удалению
						Core_File::delete($sSourceFileSmall);
					}
				}
			}
			else
			{
				$oField_Value->setValue($changedValue);
				$oField_Value->save();
			}

			return $oField_Value;
		}

		return FALSE;
	}

	/**
	 * Add property to group
	 * @param Shop_Group_Model $oShop_Group
	 * @param Property_Model $oProperty
	 * @param string $sPropertyValue property value
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImportGroupProperty
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAddGroupPropertyValueDefault
	 */
	protected function _addGroupPropertyValue(Shop_Group_Model $oShop_Group, Property_Model $oProperty, $sPropertyValue, $position = 0)
	{
		Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportGroupProperty', $this, array($this->_oCurrentShop, $oShop_Group, $oProperty, $sPropertyValue));

		$aPropertyValues = $oProperty->getValues($oShop_Group->id, FALSE);

		if ($this->deletePropertyValues === TRUE
			|| is_array($this->deletePropertyValues) && in_array($oProperty->id, $this->deletePropertyValues))
		{
			if (!isset($this->_aClearedGroupsPropertyValues[$oShop_Group->id])
				|| !in_array($oProperty->id, $this->_aClearedGroupsPropertyValues[$oShop_Group->id]))
			{
				foreach ($aPropertyValues as $oPropertyValue)
				{
					$oProperty->type == 2
						&& $oPropertyValue->setDir($oShop_Group->getGroupPath());
					$oPropertyValue->delete();
				}

				$aPropertyValues = array();

				$this->_aClearedGroupsPropertyValues[$oShop_Group->id][] = $oProperty->id;
			}
		}

		// File
		if ($oProperty->type == 2)
		{
			if ($oProperty->multiple)
			{
				$oProperty_Value = $oProperty->createNewValue($oShop_Group->id);
			}
			else
			{
				$oProperty_Value = isset($aPropertyValues[0])
					? $aPropertyValues[0]
					: $oProperty->createNewValue($oShop_Group->id);
			}

			// Папка назначения
			$sDestinationFolder = $oShop_Group->getGroupPath();

			// Файл-источник
			$sTmpFilePath = $this->imagesPath . (
				/*strtoupper($this->encoding) == 'UTF-8'
					? $sPropertyValue
					: Core_File::convertfileNameFromLocalEncoding($sPropertyValue)*/
				$sPropertyValue
			);

			$sSourceFileBaseName = basename($sTmpFilePath, '');

			$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

			if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
			{
				// Создаем папку назначения
				$oShop_Group->createDir();

				if ($bHttp)
				{
					try {
						$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
					}
					catch (Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
						$sSourceFile = NULL;
					}
				}
				else
				{
					$sSourceFile = CMS_FOLDER . ltrim($sTmpFilePath, '/\\');
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
					$sTargetFileName = "shop_property_file_{$oShop_Group->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
				}

				// Создаем массив параметров для загрузки картинок элементу
				$aPicturesParam = array();
				$aPicturesParam['large_image_source'] = $sSourceFile;
				$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
				$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
				$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
				$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
				$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
				$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;
				$aPicturesParam['large_image_max_width'] = $oProperty->image_large_max_width;
				$aPicturesParam['large_image_max_height'] = $oProperty->image_large_max_height;
				$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

				if (isset($this->_aExternalPropertiesSmall[$oProperty->id][$position]))
				{
					// Малое изображение передано
					$aPicturesParam['create_small_image_from_large'] = FALSE;

					// Файл-источник
					$sTmpFilePath = $this->imagesPath . $this->_aExternalPropertiesSmall[$oProperty->id][$position];

					$sSourceFileBaseNameSmall = basename($sTmpFilePath, '');

					$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

					if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$oShop_Group->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFileSmall = $this->_downloadHttpFile($sTmpFilePath);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFileSmall = NULL;
							}
						}
						else
						{
							$sSourceFileSmall = CMS_FOLDER . $sTmpFilePath;
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
							$sTargetFileNameSmall = "small_shop_property_file_{$oShop_Group->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
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
					unset($this->_aExternalPropertiesSmall[$oProperty->id][$position]);
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
					Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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

				// Для групп описания не передаются сейчас, только для товаров
				if (isset($this->_aExternalPropertiesDesc[$oProperty->id][$position]))
				{
					$oProperty_Value->file_description = $this->_aExternalPropertiesDesc[$oProperty->id][$position];
					unset($this->_aExternalPropertiesDesc[$oProperty->id][$position]);
				}

				$oProperty_Value->save();

				clearstatcache();

				if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0 && Core_File::isFile($sSourceFile))
				{
					// Файл временный, подлежит удалению
					Core_File::delete($sSourceFile);
				}

				if (!is_null($sSourceFile) && strpos(basename($sSourceFileSmall), "CMS") === 0 && Core_File::isFile($sSourceFileSmall))
				{
					// Файл временный, подлежит удалению
					Core_File::delete($sSourceFileSmall);
				}
			}
		}
		else
		{
			switch ($oProperty->type)
			{
				case 0: // Int
					$changedValue = Shop_Controller::convertDecimal($sPropertyValue);
				break;
				// Файл
				case 2:
					$changedValue = NULL;
				break;
				// Список
				case 3:
					if (Core::moduleIsActive('list'))
					{
						$oList_Item = $oProperty->List->List_Items->getByValue($sPropertyValue, FALSE);

						if ($oList_Item)
						{
							$changedValue = $oList_Item->id;
						}
						else
						{
							$oList_Item = Core_Entity::factory('List_Item')
								->list_id($oProperty->list_id)
								->value($sPropertyValue);

							// Apache %2F (/) is forbidden
							strpos($sPropertyValue, '/') !== FALSE
								&& $oList_Item->path = trim(str_replace('/', ' ', $sPropertyValue));

							$changedValue = $oList_Item->save()
								->id;
						}
					}
				break;
				case 5: // Informationsystem
					$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getByName($sPropertyValue);
					if ($oInformationsystem_Item)
					{
						$changedValue = $oInformationsystem_Item->id;
					}
					elseif (is_numeric($sPropertyValue))
					{
						$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getById($sPropertyValue);

						$changedValue = $oInformationsystem_Item
							? $oInformationsystem_Item->id
							: NULL;
					}
					else
					{
						$changedValue = NULL;
					}
				break;
				case 7: // Checkbox
					$changedValue = $this->_correctCheckbox($sPropertyValue);
				break;
				case 8:
					$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sPropertyValue)
						? $sPropertyValue
						: Core_Date::datetime2sql($sPropertyValue);
				break;
				case 9:
					$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sPropertyValue)
						? $sPropertyValue
						: Core_Date::datetime2sql($sPropertyValue);
				break;
				case 11: // Float
					$changedValue = Shop_Controller::convertDecimal($sPropertyValue);
				break;
				case 12: // Shop
					$oShop_Item = $oProperty->Shop->Shop_Items->getByName($sPropertyValue);
					if ($oShop_Item)
					{
						$changedValue = $oShop_Item->id;
					}
					elseif (is_numeric($sPropertyValue))
					{
						$oShop_Item = $oProperty->Shop->Shop_Items->getById($sPropertyValue);

						$changedValue = $oShop_Item
							? $oShop_Item->id
							: NULL;
					}
					else
					{
						$changedValue = NULL;
					}
				break;
				default:
					Core_Event::notify(get_class($this) . '.onAddGroupPropertyValueDefault', $this, array($oShop_Group, $oProperty, $sPropertyValue));

					$changedValue = is_null(Core_Event::getLastReturn())
						? $sPropertyValue
						: Core_Event::getLastReturn();
			}

			//$oProperty_Value->save();
			if (!is_null($changedValue))
			{
				//$aPropertyValues = $oProperty->getValues($oShop_Group->id, FALSE);
				if ($oProperty->multiple)
				{
					foreach ($aPropertyValues as $oProperty_Value)
					{
						if ($oProperty_Value->value == $changedValue)
						{
							return $this;
						}
					}

					$oProperty_Value = $oProperty->createNewValue($oShop_Group->id);
				}
				else
				{
					$oProperty_Value = isset($aPropertyValues[0])
						? $aPropertyValues[0]
						: $oProperty->createNewValue($oShop_Group->id);
				}

				$oProperty_Value->setValue($changedValue);
				$oProperty_Value->save();
			}
		}

		return $this;
	}

	/**
	 * Add field to group
	 * @param Shop_Group_Model $oShop_Group
	 * @param Field_Model $oField
	 * @param string $sFieldValue field value
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onBeforeImportGroupField
	 * @hostcms-event Shop_Item_Import_Csv_Controller.onAddGroupFieldValueDefault
	 */
	protected function _addGroupFieldValue(Shop_Group_Model $oShop_Group, Field_Model $oField, $sFieldValue, $position = 0)
	{
		Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeImportGroupField', $this, array($this->_oCurrentShop, $oShop_Group, $oField, $sFieldValue));

		$aFieldValues = $oField->getValues($oShop_Group->id, FALSE);

		if ($this->deleteFieldValues === TRUE
			|| is_array($this->deleteFieldValues) && in_array($oField->id, $this->deleteFieldValues))
		{
			if (!isset($this->_aClearedGroupsFieldValues[$oShop_Group->id])
				|| !in_array($oField->id, $this->_aClearedGroupsFieldValues[$oShop_Group->id]))
			{
				foreach ($aFieldValues as $oFieldValue)
				{
					$oField->type == 2
						&& $oFieldValue->setDir($oShop_Group->getGroupPath());
					$oFieldValue->delete();
				}

				$aFieldValues = array();

				$this->_aClearedGroupsFieldValues[$oShop_Group->id][] = $oField->id;
			}
		}

		// File
		if ($oField->type == 2)
		{
			if ($oField->multiple)
			{
				$oField_Value = $oField->createNewValue($oShop_Group->id);
			}
			else
			{
				$oField_Value = isset($aFieldValues[0])
					? $aFieldValues[0]
					: $oField->createNewValue($oShop_Group->id);
			}

			// Папка назначения
			$sDestinationFolder = $oShop_Group->getGroupPath();

			// Файл-источник
			$sTmpFilePath = $this->imagesPath . (
				/*strtoupper($this->encoding) == 'UTF-8'
					? $sFieldValue
					: Core_File::convertfileNameFromLocalEncoding($sFieldValue)*/
				$sFieldValue
			);

			$sSourceFileBaseName = basename($sTmpFilePath, '');

			$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

			if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
			{
				// Создаем папку назначения
				$oShop_Group->createDir();

				if ($bHttp)
				{
					try {
						$sSourceFile = $this->_downloadHttpFile($sTmpFilePath);
					}
					catch (Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
						$sSourceFile = NULL;
					}
				}
				else
				{
					$sSourceFile = CMS_FOLDER . ltrim($sTmpFilePath, '/\\');
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

					$oField_Value->save();
					$sTargetFileName = "shop_field_file_{$oShop_Group->id}_{$oField_Value->id}{$sTargetFileExtension}";
				}

				// Создаем массив параметров для загрузки картинок элементу
				$aPicturesParam = array();
				$aPicturesParam['large_image_source'] = $sSourceFile;
				$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
				$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
				$aPicturesParam['watermark_file_path'] = $this->_oCurrentShop->getWatermarkFilePath();
				$aPicturesParam['watermark_position_x'] = $this->_oCurrentShop->watermark_default_position_x;
				$aPicturesParam['watermark_position_y'] = $this->_oCurrentShop->watermark_default_position_y;
				$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentShop->preserve_aspect_ratio;
				$aPicturesParam['large_image_max_width'] = $oField->image_large_max_width;
				$aPicturesParam['large_image_max_height'] = $oField->image_large_max_height;
				$aPicturesParam['large_image_watermark'] = $this->_oCurrentShop->watermark_default_use_large_image;

				if (isset($this->_aExternalFieldsSmall[$oField->id][$position]))
				{
					// Малое изображение передано
					$aPicturesParam['create_small_image_from_large'] = FALSE;

					// Файл-источник
					$sTmpFilePath = $this->imagesPath . $this->_aExternalFieldsSmall[$oField->id][$position];

					$sSourceFileBaseNameSmall = basename($sTmpFilePath, '');

					$bHttp = strpos(strtolower($sTmpFilePath), "http://") === 0 || strpos(strtolower($sTmpFilePath), "https://") === 0;

					if (Core_File::isValidExtension($sTmpFilePath, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$oShop_Group->createDir();

						if ($bHttp)
						{
							try {
								$sSourceFileSmall = $this->_downloadHttpFile($sTmpFilePath);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
								$sSourceFileSmall = NULL;
							}
						}
						else
						{
							$sSourceFileSmall = CMS_FOLDER . $sTmpFilePath;
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

							$oField_Value->save();
							$sTargetFileNameSmall = "small_shop_field_file_{$oShop_Group->id}_{$oField_Value->id}{$sTargetFileExtension}";
						}

						$aPicturesParam['small_image_source'] = $sSourceFileSmall;
						$aPicturesParam['small_image_name'] = $sSourceFileBaseNameSmall;
						$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileNameSmall;

						// Удаляем старое малое изображение
						/*if ($oField_Value->file_small != '')
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oField_Value->file_small);
							} catch (Exception $e) {}
						}*/
					}

					// ------------------------------------------
					// Исключаем из отдельного импорта малых изображений
					unset($this->_aExternalFieldsSmall[$oField->id][$position]);
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

				$aPicturesParam['small_image_max_width'] = $oField->image_small_max_width;
				$aPicturesParam['small_image_max_height'] = $oField->image_small_max_height;
				$aPicturesParam['small_image_watermark'] = $this->_oCurrentShop->watermark_default_use_small_image;
				$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];

				// Удаляем старое большое изображение
				if ($oField_Value->file != '')
				{
					if ($sDestinationFolder . $oField_Value->file != $sSourceFile)
					{
						try
						{
							Core_File::delete($sDestinationFolder . $oField_Value->file);
						} catch (Exception $e) {}
					}
				}

				// Удаляем старое малое изображение
				if ($oField_Value->file_small != '')
				{
					if ($sDestinationFolder . $oField_Value->file_small != $sSourceFileSmall)
					{
						try
						{
							Core_File::delete($sDestinationFolder . $oField_Value->file_small);
						} catch (Exception $e) {}
					}
				}

				try {
					Core_Event::notify('Shop_Item_Import_Csv_Controller.onBeforeAdminUpload', $this, array($aPicturesParam));
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
					$oField_Value->file = $sTargetFileName;
					$oField_Value->file_name = '';
				}

				if ($aResult['small_image'])
				{
					$oField_Value->file_small = "small_{$sTargetFileName}";
					$oField_Value->file_small_name = '';
				}

				// Для групп описания не передаются сейчас, только для товаров
				if (isset($this->_aExternalFieldsDesc[$oField->id][$position]))
				{
					$oField_Value->file_description = $this->_aExternalFieldsDesc[$oField->id][$position];
					unset($this->_aExternalFieldsDesc[$oField->id][$position]);
				}

				$oField_Value->save();

				clearstatcache();

				if (!is_null($sSourceFile) && strpos(basename($sSourceFile), "CMS") === 0 && Core_File::isFile($sSourceFile))
				{
					// Файл временный, подлежит удалению
					Core_File::delete($sSourceFile);
				}

				if (!is_null($sSourceFile) && strpos(basename($sSourceFileSmall), "CMS") === 0 && Core_File::isFile($sSourceFileSmall))
				{
					// Файл временный, подлежит удалению
					Core_File::delete($sSourceFileSmall);
				}
			}
		}
		else
		{
			switch ($oField->type)
			{
				case 0: // Int
					$changedValue = Shop_Controller::convertDecimal($sFieldValue);
				break;
				// Файл
				case 2:
					$changedValue = NULL;
				break;
				// Список
				case 3:
					if (Core::moduleIsActive('list'))
					{
						$oList_Item = $oField->List->List_Items->getByValue($sFieldValue, FALSE);

						if ($oList_Item)
						{
							$changedValue = $oList_Item->id;
						}
						else
						{
							$oList_Item = Core_Entity::factory('List_Item')
								->list_id($oField->list_id)
								->value($sFieldValue);

							// Apache %2F (/) is forbidden
							strpos($sFieldValue, '/') !== FALSE
								&& $oList_Item->path = trim(str_replace('/', ' ', $sFieldValue));

							$changedValue = $oList_Item->save()
								->id;
						}
					}
				break;
				case 5: // Informationsystem
					$oInformationsystem_Item = $oField->Informationsystem->Informationsystem_Items->getByName($sFieldValue);
					if ($oInformationsystem_Item)
					{
						$changedValue = $oInformationsystem_Item->id;
					}
					elseif (is_numeric($sFieldValue))
					{
						$oInformationsystem_Item = $oField->Informationsystem->Informationsystem_Items->getById($sFieldValue);

						$changedValue = $oInformationsystem_Item
							? $oInformationsystem_Item->id
							: NULL;
					}
					else
					{
						$changedValue = NULL;
					}
				break;
				case 7: // Checkbox
					$changedValue = $this->_correctCheckbox($sFieldValue);
				break;
				case 8:
					$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sFieldValue)
						? $sFieldValue
						: Core_Date::datetime2sql($sFieldValue);
				break;
				case 9:
					$changedValue = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sFieldValue)
						? $sFieldValue
						: Core_Date::datetime2sql($sFieldValue);
				break;
				case 11: // Float
					$changedValue = Shop_Controller::convertDecimal($sFieldValue);
				break;
				case 12: // Shop
					$oShop_Item = $oField->Shop->Shop_Items->getByName($sFieldValue);
					if ($oShop_Item)
					{
						$changedValue = $oShop_Item->id;
					}
					elseif (is_numeric($sFieldValue))
					{
						$oShop_Item = $oField->Shop->Shop_Items->getById($sFieldValue);

						$changedValue = $oShop_Item
							? $oShop_Item->id
							: NULL;
					}
					else
					{
						$changedValue = NULL;
					}
				break;
				default:
					Core_Event::notify(get_class($this) . '.onAddGroupFieldValueDefault', $this, array($oShop_Group, $oField, $sFieldValue));

					$changedValue = is_null(Core_Event::getLastReturn())
						? $sFieldValue
						: Core_Event::getLastReturn();
			}

			//$oField_Value->save();
			if (!is_null($changedValue))
			{
				//$aFieldValues = $oField->getValues($oShop_Group->id, FALSE);
				if ($oField->multiple)
				{
					foreach ($aFieldValues as $oField_Value)
					{
						if ($oField_Value->value == $changedValue)
						{
							return $this;
						}
					}

					$oField_Value = $oField->createNewValue($oShop_Group->id);
				}
				else
				{
					$oField_Value = isset($aFieldValues[0])
						? $aFieldValues[0]
						: $oField->createNewValue($oShop_Group->id);
				}

				$oField_Value->setValue($changedValue);
				$oField_Value->save();
			}
		}

		return $this;
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
			$this->_aTags = array();

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

		$aCsvLine = @fgetcsv($fileDescriptor, 0, $this->separator, $this->limiter, '"');

		if ($aCsvLine === FALSE)
		{
			return $aCsvLine;
		}

		setlocale(LC_ALL, SITE_LOCAL);
		setlocale(LC_NUMERIC, 'POSIX');

		return Core_Str::iconv($this->encoding, 'UTF-8', $aCsvLine);
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_oCurrentShop = $this->_oCurrentGroup = $this->_oCurrentItem = $this->_oCurrentOrder
			= $this->_oCurrentOrderItem = $this->_oCurrentShopEItem = $this->_oCurrentShopSpecialPrice = NULL;

		$this->_aTags = NULL;

		$this->_aClearedItemsPropertyValues = $this->_aClearedGroupsPropertyValues = $this->_aClearedItemsFieldValues = $this->_aClearedGroupsFieldValues = array();

		// see __sleep()/__wakeup()
		$this->_aInsertedGroupIDs = $this->_aClearedItemsPropertyValues = $this->_aClearedGroupsPropertyValues = $this->_aClearedItemsFieldValues =
		$this->_aClearedGroupsFieldValues = $this->_aUpdatedGroupIDs = $this->_aInsertedItemIDs = $this->_aUpdatedItemIDs = $this->_aCreatedItemIDs = array();

		return $this;
	}

	/**
	 * Execute some routine before serialization
	 * @return array
	 */
	public function __sleep()
	{
		file_put_contents($this->_jsonPath, json_encode(
			array(
				'_aInsertedGroupIDs' => $this->_aInsertedGroupIDs,
				'_aClearedItemsPropertyValues' => $this->_aClearedItemsPropertyValues,
				'_aClearedGroupsPropertyValues' => $this->_aClearedGroupsPropertyValues,
				'_aClearedItemsFieldValues' => $this->_aClearedItemsFieldValues,
				'_aClearedGroupsFieldValues' => $this->_aClearedGroupsFieldValues,
				'_aUpdatedGroupIDs' => $this->_aUpdatedGroupIDs,
				'_aInsertedItemIDs' => $this->_aInsertedItemIDs,
				'_aUpdatedItemIDs' => $this->_aUpdatedItemIDs,
				'_aCreatedItemIDs' => $this->_aCreatedItemIDs
			)
		));

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

		if (Core_File::isFile($this->_jsonPath))
		{
			$aJSON = json_decode(Core_File::read($this->_jsonPath), TRUE);

			$this->_aInsertedGroupIDs = Core_Array::get($aJSON, '_aInsertedGroupIDs', array());
			$this->_aClearedItemsPropertyValues = Core_Array::get($aJSON, '_aClearedItemsPropertyValues', array());
			$this->_aClearedGroupsPropertyValues = Core_Array::get($aJSON, '_aClearedGroupsPropertyValues', array());
			$this->_aClearedItemsFieldValues = Core_Array::get($aJSON, '_aClearedItemsFieldValues', array());
			$this->_aClearedGroupsFieldValues = Core_Array::get($aJSON, '_aClearedGroupsFieldValues', array());
			$this->_aUpdatedGroupIDs = Core_Array::get($aJSON, '_aUpdatedGroupIDs', array());
			$this->_aInsertedItemIDs = Core_Array::get($aJSON, '_aInsertedItemIDs', array());
			$this->_aUpdatedItemIDs = Core_Array::get($aJSON, '_aUpdatedItemIDs', array());
			$this->_aCreatedItemIDs = Core_Array::get($aJSON, '_aCreatedItemIDs', array());
		}

		return $this;
	}

	public $aEntities = array();

	protected $_posted = 0;

	public function getPosted()
	{
		return $this->_posted;
	}

	protected $_aShop_Warehouse_Inventory_Ids = array();
	protected $_aShop_Warehouse_Inventory_Counts = array();
	protected $_aShop_Warehouse_Inventory_Previous_Ids = array();

	protected function _getInventory($shop_warehouse_id)
	{
		if (!isset($this->_aShop_Warehouse_Inventory_Counts[$shop_warehouse_id])
			|| $this->_aShop_Warehouse_Inventory_Counts[$shop_warehouse_id] >= $this->entriesLimit)
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory');
			$oShop_Warehouse_Inventory->shop_warehouse_id = $shop_warehouse_id;
			$oShop_Warehouse_Inventory->description = Core::_('Shop_Exchange.shop_warehouse_inventory');
			$oShop_Warehouse_Inventory->number = '';
			$oShop_Warehouse_Inventory->posted = 0;
			$oShop_Warehouse_Inventory->save();

			$oShop_Warehouse_Inventory->number = $oShop_Warehouse_Inventory->id;
			$oShop_Warehouse_Inventory->save();

			$this->_aShop_Warehouse_Inventory_Previous_Ids[]
				= $this->_aShop_Warehouse_Inventory_Ids[$shop_warehouse_id]
				= $oShop_Warehouse_Inventory->id;

			$this->_aShop_Warehouse_Inventory_Counts[$shop_warehouse_id] = 0;
		}

		$this->_aShop_Warehouse_Inventory_Counts[$shop_warehouse_id]++;

		return Core_Entity::factory('Shop_Warehouse_Inventory', $this->_aShop_Warehouse_Inventory_Ids[$shop_warehouse_id]);
	}

	protected $_oShop_Price_Setting_Id = NULL;
	protected $_oShop_Price_Setting_Count = NULL;
	protected $_oShop_Price_Setting_Previous_Ids = array();

	protected function _getPriceSetting()
	{
		if (is_null($this->_oShop_Price_Setting_Count)
			|| $this->_oShop_Price_Setting_Count >= $this->entriesLimit)
		{
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
			$oShop_Price_Setting->shop_id = $this->_oCurrentShop->id;
			$oShop_Price_Setting->number = '';
			$oShop_Price_Setting->posted = 0;
			$oShop_Price_Setting->description = Core::_('Shop_Exchange.shop_price_setting');
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			$this->_oShop_Price_Setting_Previous_Ids[]
				= $this->_oShop_Price_Setting_Id
				= $oShop_Price_Setting->id;

			$this->_oShop_Price_Setting_Count = 0;
		}

		$this->_oShop_Price_Setting_Count++;

		return Core_Entity::factory('Shop_Price_Setting', $this->_oShop_Price_Setting_Id);
	}

	public function postAll()
	{
		foreach ($this->_aShop_Warehouse_Inventory_Previous_Ids as $shop_warehouse_inventory_id)
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory', $shop_warehouse_inventory_id);
			$oShop_Warehouse_Inventory->post();
		}

		foreach ($this->_oShop_Price_Setting_Previous_Ids as $shop_price_setting_id)
		{
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting', $shop_price_setting_id);
			$oShop_Price_Setting->post();
		}

		if (Core_File::isFile($this->_jsonPath))
		{
			Core_File::delete($this->_jsonPath);
		}

		return $this;
	}

	public function postNext()
	{
		if (count($this->_aShop_Warehouse_Inventory_Previous_Ids))
		{
			$shop_warehouse_inventory_id = array_shift($this->_aShop_Warehouse_Inventory_Previous_Ids);
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory', $shop_warehouse_inventory_id);
			$oShop_Warehouse_Inventory->post();

			$this->_posted++;

			return TRUE;
		}

		if (count($this->_oShop_Price_Setting_Previous_Ids))
		{
			$shop_price_setting_id = array_shift($this->_oShop_Price_Setting_Previous_Ids);
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting', $shop_price_setting_id);
			$oShop_Price_Setting->post();

			$this->_posted++;

			return TRUE;
		}

		if (Core_File::isFile($this->_jsonPath))
		{
			Core_File::delete($this->_jsonPath);
		}

		return FALSE;
	}

	/**
	 * Correct checkbox value
	 * @param string $value
	 * @return bool
	 */
	protected function _correctCheckbox($value)
	{
		return $value == 1 || strtolower($value) === 'true' || strtolower($value) === 'да'
			? 1
			: 0;
	}

	/**
	 * Correct CSV-line encoding
	 * @param array $sLine current CSV-file line
	 * @param string $encodeTo detination encoding
	 * @param string $encodeFrom source encoding
	 * @return array
	 * @deprecated 6.9.7
	 */
	public static function CorrectToEncoding($sLine, $encodeTo, $encodeFrom = 'UTF-8')
	{
		return Core_Str::iconv($encodeFrom, $encodeTo, $sLine);
	}
}