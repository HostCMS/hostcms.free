<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Model
 *
 * Goods types: 1 - Digital, 2 - Divisible, 3 - Set, 4 - Certificate
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Item_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop_item';

	/**
	 * Callback property_id
	 * @var int
	 */
	public $related = 1;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $modifications = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $discounts = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $reviews = 1;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $key = NULL;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $count = NULL;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $absolute_price = NULL;
	public $price_absolute = NULL;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $adminRest = NULL;

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'shop_item_certificate' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_cart' => array(),
		'comment' => array('through' => 'comment_shop_item'),
		'shop_bonus' => array('through' => 'shop_item_bonus'),
		'shop_discount' => array('through' => 'shop_item_discount'),
		'shop_item_bonus' => array(),
		'shop_item_discount' => array(),
		'shop_item_digital' => array(),
		'shop_item' => array('foreign_key' => 'shortcut_id'),
		'modification' => array('model' => 'Shop_Item', 'foreign_key' => 'modification_id'),
		'media_shop_item' => array(),
		'media_item' => array('through' => 'media_shop_item'),
		'shop_price' => array('through' => 'shop_item_price'),
		'shop_item_price' => array(),
		'item_associated' => array('through' => 'shop_item_associated', 'through_table_name' => 'shop_item_associated', 'model' => 'Shop_Item', 'dependent_key' => 'shop_item_associated_id'),
		'shop_item_associated' => array(),
		'shop_item_associated_second' => array('model' => 'Shop_Item_Associated', 'foreign_key' => 'shop_item_associated_id'),
		'shop_item_set' => array(),
		'shop_item_set_second' => array('model' => 'Shop_Item_Set', 'foreign_key' => 'shop_item_set_id'),
		'shop_specialprice' => array(),
		'shop_item_reserved' => array(),
		'tag' => array('through' => 'tag_shop_item'),
		'tag_shop_item' => array(),
		'shop_warehouse' => array('through' => 'shop_warehouse_item'),
		'shop_warehouse_item' => array(),
		'vote' => array('through' => 'vote_shop_item'),
		'shop_item_delivery_option' => array(),
		'shop_favorite' => array(),
		'shop_item_barcode' => array(),
		'shop_warehouse_entry' => array(),
		'shop_warehouse_incoming_item' => array(),
		'shop_warehouse_inventory_item' => array(),
		'shop_warehouse_writeoff_item' => array(),
		'shop_warehouse_regrade_incoming_item' => array('model' => 'Shop_Warehouse_Regrade_Item', 'foreign_key' => 'incoming_shop_item_id'),
		'shop_warehouse_regrade_writeoff_item' => array('model' => 'Shop_Warehouse_Regrade_Item', 'foreign_key' => 'writeoff_shop_item_id'),
		'shop_price_entry' => array(),
		'shop_price_setting_item' => array(),
		'shop_warehouse_cell_item' => array(),
		'shop_tab' => array('through' => 'shop_tab_item'),
		'shop_tab_item' => array(),
		'shop_item_certificate' => array(),
		'lead_shop_item' => array(),
		'deal_shop_item' => array(),
		'shop_warehouse_purchaseorder_item' => array(),
		'shop_warehouse_invoice_item' => array(),
		'shop_warehouse_supply_item' => array(),
		'production_process_stage_material' => array(),
		'production_process_stage_manufacture' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'shortcut_id' => 0,
		'siteuser_id' => 0,
		'weight' => 0,
		'price' => 0,
		'sorting' => 0,
		'image_small_height' => 0,
		'image_small_width' => 0,
		'image_large_height' => 0,
		'image_large_width' => 0,
		'yandex_market' => 1,
		'yandex_market_bid' => 0,
		'yandex_market_cid' => 0,
		'active' => 1,
		'indexing' => 1,
		'modification_id' => 0,
		'shop_measure_id' => 0,
		'length' => 0,
		'width' => 0,
		'height' => 0,
		'apply_purchase_discount' => 1,
		'showed' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_measure' => array(),
		'shop_tax' => array(),
		'siteuser_group' => array(),
		'shop_seller' => array(),
		'shop_group' => array(),
		'shop_currency' => array(),
		'shop' => array(),
		'shop_producer' => array(),
		'siteuser' => array(),
		'shop_item_type' => array(),
		'shop_item' => array('foreign_key' => 'shortcut_id'),
		'modification' => array('model' => 'Shop_Item', 'foreign_key' => 'modification_id'),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'price',
		'datetime',
		'start_datetime',
		'end_datetime',
		'yandex_market',
		'yandex_market_bid',
		'yandex_market_cid',
		'yandex_market_sales_notes',
		'apply_purchase_discount',
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description',
		'text'
	);

	/**
	 * Has revisions
	 *
	 * @param boolean
	 */
	protected $_hasRevisions = TRUE;

	/**
	 * Inc items'count in group during creating item
	 * @var boolean
	 */
	protected $_incCountByCreate = TRUE;

	/**
	 * Inc items'count in group during creating item
	 * @param boolean $value
	 * @return self
	 */
	public function incCountByCreate($value = TRUE)
	{
		$this->_incCountByCreate = $value;

		return $this;
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		return strtolower($property) == 'adminprice'
			? TRUE
			: parent::__isset($property);
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function __set($property, $value)
	{
		if ($property == 'adminPrice')
		{
			$this->adminPrice($value);
			return $this;
		}

		/*$tmpProperty = $property == 'adminPrice'
			? 'price'
			: $property;*/

		return parent::__set($property, $value);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		return strtolower($property) == 'adminprice'
			? ($this->shortcut_id
				? Core_Entity::factory('Shop_Item', $this->shortcut_id)->price
				: $this->price)
			: parent::__get($property);
	}

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
			$this->_preloadValues['guid'] = Core_Guid::get();
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['delivery'] = $this->_preloadValues['pickup'] = 1;
		}
	}

	/**
	 * Apply tags for item
	 * @param string $sTags string of tags, separated by comma
	 * @return self
	 */
	public function applyTags($sTags)
	{
		$aTags = explode(',', $sTags);

		return $this->applyTagsArray($aTags);
	}

	/**
	 * Apply array tags for item
	 * @param array $aTags array of tags
	 * @return self
	 * @hostcms-event shop_item.onAfterCreateTag
	 */
	public function applyTagsArray(array $aTags)
	{
		// Удаляем связь метками
		$this->Tag_Shop_Items->deleteAll(FALSE);

		foreach ($aTags as $tag_name)
		{
			$tag_name = trim($tag_name);

			if ($tag_name != '')
			{
				$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

				if (is_null($oTag))
				{
					$oTag = Core_Entity::factory('Tag');
					$oTag->name = $oTag->path = $tag_name;

					if ($this->Shop->url_type == 1)
					{
						try {
							Core::$mainConfig['translate'] && $sTranslated = Core_Str::translate($tag_name);

							$oTag->path = Core::$mainConfig['translate'] && strlen((string) $sTranslated)
								? $sTranslated
								: $tag_name;

							$oTag->path = Core_Str::transliteration($oTag->path);
						} catch (Exception $e) {
							$oTag->path = Core_Str::transliteration($tag_name);
						}
					}

					$oTag->save();

					Core_Event::notify($this->_modelName . '.onAfterCreateTag', $this, array($oTag));
				}

				$this->add($oTag);
			}
		}

		return $this;
	}

	/**
	 * Values of all properties of item
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Values of all properties of item
	 * Значения всех свойств товара
	 * @param boolean $bCache cache mode status
	 * @param array $aPropertiesId array of properties' IDs
	 * @param boolean $bSorting sort results, default FALSE
	 * @return array Property_Value
	 */
	public function getPropertyValues($bCache = TRUE, $aPropertiesId = array(), $bSorting = FALSE)
	{
		if ($bCache && !is_null($this->_propertyValues))
		{
			return $this->_propertyValues;
		}

		if (!is_array($aPropertiesId) || !count($aPropertiesId))
		{
			$aProperties = Core_Entity::factory('Shop_Item_Property_List', $this->shop_id)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aReturn = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache, $bSorting);

		// setHref()
		foreach ($aReturn as $oProperty_Value)
		{
			$this->_preparePropertyValue($oProperty_Value);
		}

		$bCache && $this->_propertyValues = $aReturn;

		return $aReturn;
	}

	/**
	 * Prepare Property Value
	 * @param Property_Value_Model $oProperty_Value
	 */
	protected function _preparePropertyValue($oProperty_Value)
	{
		switch ($oProperty_Value->Property->type)
		{
			case 2:
				$oProperty_Value
					->setHref($this->getItemHref())
					->setDir($this->getItemPath());
			break;
			case 5: // Элемент информационной системы
			case 12: // Товар интернет-магазина
			case 13: // Группа информационной системы
			case 14: // Группа интернет-магазина
				$oProperty_Value->showXmlMedia($this->_showXmlMedia);
			break;
			case 8:
				$oProperty_Value->dateFormat($this->Shop->format_date);
			break;
			case 9:
				$oProperty_Value->dateTimeFormat($this->Shop->format_datetime);
			break;
		}
	}

	/**
	 * @var Shop_Item_Controller|NULL
	 */
	protected $_Shop_Item_Controller = NULL;

	/**
	 * Set $this->_Shop_Item_Controller
	 * @return self
	 */
	public function setShop_Item_Controller()
	{
		if (is_null($this->_Shop_Item_Controller))
		{
			$this->_Shop_Item_Controller = new Shop_Item_Controller();
			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
				$oSiteuser && $this->_Shop_Item_Controller->siteuser($oSiteuser);
			}
		}

		$this->_Shop_Item_Controller->count($this->_cartQuantity);

		return $this;
	}

	/**
	 * Get Prices
	 * @param boolean $bCache cache mode status
	 * @return array
	 */
	public function getPrices($bCache = TRUE)
	{
		$this->setShop_Item_Controller();

		return $this->_Shop_Item_Controller->getPrices($this, TRUE, $bCache);
	}

	/**
	 * Get Bonuses
	 * @return array
	 */
	public function getBonuses($aPrices)
	{
		$this->setShop_Item_Controller();

		$aBonuses = $this->_Shop_Item_Controller->getBonuses($this, $aPrices['price_discount']);

		return $aBonuses;
	}

	/**
	 * Cache for getRest()
	 * @var mixed
	 */
	protected $_rest = NULL;

	/**
	 * Get the quantity in the active warehouses
	 * @param boolean $bCache cache mode status
	 * @return float
	 * @hostcms-event shop_item.onBeforeGetRest
	 */
	public function getRest($bCache = TRUE)
	{
		if ($bCache && !is_null($this->_rest))
		{
			return $this->_rest;
		}

		$queryBuilder = Core_QueryBuilder::select(array('SUM(count)', 'count'))
			->from('shop_warehouse_items')
			->join('shop_warehouses', 'shop_warehouses.id', '=', 'shop_warehouse_items.shop_warehouse_id')
			->where('shop_warehouse_items.shop_item_id', '=', !$this->shortcut_id ? $this->id : $this->shortcut_id)
			->where('shop_warehouses.active', '=', 1)
			->where('shop_warehouses.deleted', '=', 0);

		Core_Event::notify($this->_modelName . '.onBeforeGetRest', $this, array($queryBuilder));

		$oCore_DataBase = $queryBuilder->execute();

		$aResult = $oCore_DataBase->asAssoc()->current();

		$oCore_DataBase->free();

		$this->_rest = $aResult['count'];

		return $this->_rest;
	}

	public function reservedBackend()
	{
		return $this->getReserved();
	}

	/**
	 * Cache for getRest()
	 * @var mixed
	 */
	protected $_reserved = NULL;

	/**
	 * Get the quantity of reserved items
	 * @param boolean $bCache cache mode status
	 * @return float
	 */
	public function getReserved($bCache = TRUE)
	{
		if ($bCache && !is_null($this->_reserved))
		{
			return $this->_reserved;
		}

		$oShop_Item = !$this->shortcut_id
			? $this
			: Core_Entity::factory('Shop_Item', $this->shortcut_id);

		$oShop_Item_Reserveds = $oShop_Item->Shop_Item_Reserveds;
		$oShop_Item_Reserveds->queryBuilder()
			->where('shop_item_reserved.datetime', '>', Core_Date::timestamp2sql(time() - $oShop_Item->Shop->reserve_hours * 60 * 60));

		$aShop_Item_Reserveds = $oShop_Item_Reserveds->findAll();

		$this->_reserved = 0;
		foreach ($aShop_Item_Reserveds as $oShop_Item_Reserved)
		{
			$this->_reserved += $oShop_Item_Reserved->count;
		}

		return $this->_reserved;
	}

	/**
	 * Backend callback method
	 * @param mixed $value value
	 * @return float
	 */
	public function adminRest($value = NULL)
	{
		// Get value
		if (is_null($value) || is_object($value))
		{
			//return $this->getRest();
			return Core_Str::hideZeros($this->adminRest);
		}

		// Save value for default warehouse
		$oDefault_Warehouse = $this->Shop->Shop_Warehouses->getDefault();

		if (!is_null($oDefault_Warehouse))
		{
			$oShop_Warehouse_Item = $oDefault_Warehouse->Shop_Warehouse_Items->getByShopItemId($this->id);

			if (is_null($oShop_Warehouse_Item))
			{
				$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item');
				$oShop_Warehouse_Item->shop_warehouse_id = $oDefault_Warehouse->id;
				$oShop_Warehouse_Item->shop_item_id = $this->id;
			}

			$oShop_Warehouse_Item->count = $value;
			$oShop_Warehouse_Item->save();
		}
		else
		{
			throw new Core_Exception('Default warehouse does not exist.', array(), 0, FALSE);
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @param object $value value
	 * @return string
	 * @see Shop_Item_Controller_Apply
	 */
	public function adminPrice($value = NULL)
	{
		// Get value
		if (is_null($value) || is_object($value))
		{
			$oShop_Item = $this->shortcut_id
				? Core_Entity::factory('Shop_Item', $this->shortcut_id)
				: $this;

			return Core_Str::hideZeros($oShop_Item->price);
		}

		/*if ($this->price != $value)
		{
			Core_Event::notify($this->_modelName . '.onBeforeAdminPrice', $this);

			$this->price = $value;
			//$this->save();
			$this->clearCache();

			// Fast filter
			if ($this->Shop->filter)
			{
				$oShop_Filter_Controller = new Shop_Filter_Controller($this->Shop);
				$oShop_Filter_Controller->fill($this);
			}

			Core_Event::notify($this->_modelName . '.onAfterAdminPrice', $this);
		}*/

		return $this;
	}

	/**
	 * Get item's currency
	 * @return string
	 */
	public function adminCurrency()
	{
		$oShop_Item = $this->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->shortcut_id)
			: $this;

		return $oShop_Item->shop_currency_id
			? htmlspecialchars((string) $oShop_Item->Shop_Currency->sign)
			: '';
	}

	/**
	 * Get item's measure
	 * @return string
	 */
	public function adminMeasure()
	{
		$oShop_Item = $this->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->shortcut_id)
			: $this;

		return htmlspecialchars((string) $oShop_Item->Shop_Measure->name);
	}

	/**
	 * Get currency name
	 * @return string
	 */
	public function currencyName()
	{
		return $this->shop_currency_id
			? $this->Shop_Currency->sign
			: '';
	}

	/**
	 * Get price with currency for SEO-templates
	 * @param string $format
	 * @return string
	 */
	public function priceWithCurrency($format = '%s %s')
	{
		$aPrices = $this->getPrices();

		return floatval($aPrices['price_discount']) > 0
			? sprintf($format, $aPrices['price_discount'], $this->Shop->Shop_Currency->sign)
			: '';
	}

	/**
	 * Get producer name
	 * @return string
	 */
	public function producerName()
	{
		return $this->shop_producer_id
			? $this->Shop_Producer->name
			: '';
	}

	/**
	 * Get seller name
	 * @return string
	 */
	public function sellerName()
	{
		return $this->shop_seller_id
			? $this->Shop_Seller->name
			: '';
	}

	/**
	 * Get item path include CMS_FOLDER
	 * @return string
	 */
	public function getItemPath()
	{
		return $this->Shop->getPath() . '/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/item_' . $this->id . '/';
	}

	/**
	 * Get href to the item dir
	 * @return string
	 */
	public function getItemHref()
	{
		return '/' . $this->Shop->getHref() . '/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/item_' . $this->id . '/';
	}

	/**
	 * Get item small image path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getItemPath() . $this->image_small;
	}

	/**
	 * Get item small image href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->getItemHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get item large image path
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getItemPath() . $this->image_large;
	}

	/**
	 * Get item large image href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		return $this->getItemHref() . rawurlencode($this->image_large);
	}

	/**
	 * Set large image sizes
	 * @return self
	 */
	public function setLargeImageSizes()
	{
		$path = $this->getLargeFilePath();

		if (Core_File::isFile($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_large_width = $aSizes['width'];
				$this->image_large_height = $aSizes['height'];
				$this->save();
			}
		}
		return $this;
	}

	/**
	 * Specify large image for item
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveLargeImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		$this->image_large = $fileName;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getItemPath() . $fileName);
		$this->setLargeImageSizes();
		return $this;
	}

	/**
	 * Set small image sizes
	 * @return self
	 */
	public function setSmallImageSizes()
	{
		$path = $this->getSmallFilePath();

		if (Core_File::isFile($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_small_width = $aSizes['width'];
				$this->image_small_height = $aSizes['height'];
				$this->save();
			}
		}
		return $this;
	}

	/**
	 * Specify small image for item
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveSmallImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		$this->image_small = $fileName;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getItemPath() . $fileName);
		$this->setSmallImageSizes();
		return $this;
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 * @hostcms-event shop_item.onAfterCheckDuplicatePath
	 */
	public function checkDuplicatePath()
	{
		$oShop = $this->Shop;

		if (!$this->modification_id)
		{
			if (strlen($this->path))
			{
				// Search the same item or group
				$oSameShopItem = $oShop->Shop_Items->getByGroupIdAndPath($this->shop_group_id, $this->path);

				if (!is_null($oSameShopItem) && $oSameShopItem->id != $this->id)
				{
					$this->path = Core_Guid::get();
				}

				$oSameShopGroup = $oShop->Shop_Groups->getByParentIdAndPath($this->shop_group_id, $this->path);
				if (!is_null($oSameShopGroup))
				{
					$this->path = Core_Guid::get();
				}
			}
			else
			{
				$this->path = Core_Guid::get();
			}
		}
		else
		{
			$oParentItem = $this->Modification;

			$aSameItems = $oParentItem->Modifications->getAllByPath($this->path, FALSE);
			foreach ($aSameItems as $oSameItem)
			{
				if ($oSameItem->id != $this->id)
				{
					$this->path = Core_Guid::get();
					break;
				}
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterCheckDuplicatePath', $this);

		return $this;
	}

	/**
	 * Make url path
	 * @return self
	 * @hostcms-event shop_item.onAfterMakePath
	 */
	public function makePath()
	{
		switch ($this->Shop->url_type)
		{
			case 0:
			default:
				!is_null($this->id) && $this->path = $this->id;
			break;
			case 1:
				try {
					Core::$mainConfig['translate'] && $sTranslated = Core_Str::translate($this->name);

					$this->path = Core::$mainConfig['translate'] && strlen((string) $sTranslated)
						? $sTranslated
						: $this->name;

					$this->path = Core_Str::transliteration($this->path);

				} catch (Exception $e) {
					$this->path = Core_Str::transliteration($this->name);
				}

				$this->checkDuplicatePath();
			break;
			case 2:
				if ($this->Shop->path_date_format != '' && strpos($this->Shop->path_date_format, '/') === FALSE && $this->datetime != '')
				{
					$date_path = date($this->Shop->path_date_format, Core_Date::sql2timestamp($this->datetime));

					$oExist_Items = $this->Shop->Shop_Items;
					$oExist_Items->queryBuilder()
						->where('shop_items.path', 'LIKE', $date_path . '%');

					$exist_count = $oExist_Items->getCount(FALSE);

					$this->path = !$exist_count
						? $date_path
						: $date_path . '-' . ($exist_count + 1);
				}
				else
				{
					!is_null($this->id) && $this->path = $this->id;
				}
			break;
		}

		Core_Event::notify($this->_modelName . '.onAfterMakePath', $this);

		return $this;
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if (!$this->shortcut_id)
		{
			if (is_null($this->path) || $this->path === '')
			{
				$this->makePath();
			}
			elseif (in_array('path', $this->_changedColumns))
			{
				$this->checkDuplicatePath();
			}
		}

		parent::save();

		if (!$this->shortcut_id && $this->path == '' && !$this->deleted && $this->makePath())
		{
			$this->path != '' && $this->save();
		}

		return $this;
	}

	/**
	 * Create directory for item
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!Core_File::isDir($this->getItemPath()))
		{
			try
			{
				Core_File::mkdir($this->getItemPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_item.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->path = '';
		$newObject->showed = 0;
		$newObject->guid = Core_Guid::get();
		$newObject->save();

		if (Core_File::isFile($this->getLargeFilePath()))
		{
			try
			{
				$newObject->createDir();
				Core_File::copy($this->getLargeFilePath(), $newObject->getLargeFilePath());
			}
			catch (Exception $e) {}
		}

		if (Core_File::isFile($this->getSmallFilePath()))
		{
			try
			{
				$newObject->createDir();
				Core_File::copy($this->getSmallFilePath(), $newObject->getSmallFilePath());
			}
			catch (Exception $e) {}
		}

		$aShop_Warehouse_Items = $this->Shop_Warehouse_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Items as $oShop_Warehouse_Item)
		{
			if ($oShop_Warehouse_Item->count != '0.00')
			{
				$oShop_Warehouse_Incoming = $oShop_Warehouse_Item->Shop_Warehouse->createShopWarehouseIncoming();

				$oShop_Warehouse_Incoming_Item = Core_Entity::factory('Shop_Warehouse_Incoming_Item');
				$oShop_Warehouse_Incoming_Item->shop_item_id = $newObject->id;
				$oShop_Warehouse_Incoming_Item->price = $newObject->price;
				$oShop_Warehouse_Incoming_Item->count = $oShop_Warehouse_Item->count;
				$oShop_Warehouse_Incoming->add($oShop_Warehouse_Incoming_Item);

				$oShop_Warehouse_Incoming->post();
			}
		}

		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oNewPropertyValue = clone $oPropertyValue;
			$oNewPropertyValue->entity_id = $newObject->id;
			$oNewPropertyValue->save();

			if ($oNewPropertyValue->Property->type == 2)
			{
				$oPropertyValue->setDir($this->getItemPath());
				$oNewPropertyValue->setDir($newObject->getItemPath());

				if (Core_File::isFile($oPropertyValue->getLargeFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getLargeFilePath(), $oNewPropertyValue->getLargeFilePath());
					} catch (Exception $e) {}
				}

				if (Core_File::isFile($oPropertyValue->getSmallFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getSmallFilePath(), $oNewPropertyValue->getSmallFilePath());
					} catch (Exception $e) {}
				}
			}
		}

		// Получаем список цен для копируемого товара
		$aShop_Item_Prices = $this->Shop_Item_Prices->findAll(FALSE);
		foreach ($aShop_Item_Prices as $oShop_Item_Price)
		{
			$newObject->add(clone $oShop_Item_Price);
		}

		// Получаем список специальных цен для копируемого товара
		$aShop_Specialprices = $this->Shop_Specialprices->findAll(FALSE);
		foreach ($aShop_Specialprices as $oShop_Specialprice)
		{
			$newObject->add(clone $oShop_Specialprice);
		}

		// Список модификаций товара
		$aModifications = $this->Modifications->findAll(FALSE);
		foreach ($aModifications as $oModification)
		{
			$oNewModification = $oModification->copy();
			$newObject->add($oNewModification, 'modifications');
		}

		// Список сопутствующих товаров копируемому товару
		$aShop_Item_Associateds = $this->Shop_Item_Associateds->findAll(FALSE);
		foreach ($aShop_Item_Associateds as $oShop_Item_Associated)
		{
			$newObject->add(clone $oShop_Item_Associated);
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll(FALSE);
			foreach ($aTags as $oTag)
			{
				$newObject->add($oTag);
			}
		}

		$aShop_Tab_Items = $this->Shop_Tab_Items->findAll(FALSE);
		foreach ($aShop_Tab_Items as $oShop_Tab_Item)
		{
			$newObject->add(clone $oShop_Tab_Item);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move item to another group
	 * @param int $iShopGroupId target group id
	 * @return Core_Entity
	 * @hostcms-event shop_item.onBeforeMove
	 * @hostcms-event shop_item.onAfterMove
	 */
	public function move($iShopGroupId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iShopGroupId));

		$oShop_Group = Core_Entity::factory('Shop_Group', $iShopGroupId);

		if ($this->shortcut_id)
		{
			$oShop_Item = $oShop_Group->Shop_Items->getByShortcut_id($this->shortcut_id);

			if (!is_null($oShop_Item))
			{
				return $this;
			}
		}

		$this->shop_group_id && $this->Shop_Group->decCountItems();

		$this->shop_group_id = $iShopGroupId;
		//$this->path = $this->path; // add path to the _changedColumns array
		$this->checkDuplicatePath()->save()->clearCache();

		// Fast filter
		if ($this->Shop->filter)
		{
			$oShop_Filter_Controller = new Shop_Filter_Controller($this->Shop);
			$oShop_Filter_Controller->fill($this);
		}

		$iShopGroupId && $oShop_Group->incCountItems();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Change item status
	 * @return self
	 * @hostcms-event shop_item.onBeforeChangeActive
	 * @hostcms-event shop_item.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		$this->active
			? $this->index()
			: $this->unindex();

		$this->clearCache();

		if ($this->Shop->filter)
		{
			$oShop_Filter_Controller = new Shop_Filter_Controller($this->Shop);
			$oShop_Filter_Controller->fill($this);

			// Fast filter for modifications
			$aModifications = $this->Modifications->findAll(FALSE);
			foreach ($aModifications as $oModification)
			{
				$this->active
					? $oShop_Filter_Controller->fill($oModification)
					: $oShop_Filter_Controller->remove($oModification);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Add item into search index
	 * @return self
	 */
	public function index()
	{
		if (Core::moduleIsActive('search')
			&& $this->indexing && $this->active
			&& ($this->start_datetime == '0000-00-00 00:00:00'
				|| Core_Date::sql2timestamp($this->start_datetime) <= time())
			&& ($this->end_datetime == '0000-00-00 00:00:00'
				|| Core_Date::sql2timestamp($this->end_datetime) > time())
		)
		{
			Search_Controller::indexingSearchPages(array($this->indexing()));
		}

		return $this;
	}

	/**
	 * Remove item from search index
	 * @return self
	 */
	public function unindex()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::deleteSearchPage($this->Shop->site_id, 3, 2, $this->id);
		}

		return $this;
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$this->clearCache();

		return parent::markDeleted();
	}

	/**
	 * Change indexation mode
	 *	@return self
	 */
	public function changeIndexation()
	{
		$this->indexing = 1 - $this->indexing;

		$this->active && $this->indexing
			? $this->index()
			: $this->unindex();

		return $this->save();
	}

	/**
	 * Create shortcut and move into group $group_id
	 * @param int $group_id group id
	 * @return Shop_Item_Model Shortcut
	 */
	public function shortcut($group_id = NULL)
	{
		$oShop_ItemShortcut = Core_Entity::factory('Shop_Item');

		$object = $this->shortcut_id
			? $this->Shop_Item
			: $this;

		$oShop_ItemShortcut->shop_id = $object->shop_id;
		$oShop_ItemShortcut->shortcut_id = $object->id;
		$oShop_ItemShortcut->datetime = $object->datetime;
		$oShop_ItemShortcut->name = ''/*$object->name*/;
		$oShop_ItemShortcut->type = 0; //$object->type;
		$oShop_ItemShortcut->path = '';
		$oShop_ItemShortcut->indexing = 0;

		$oShop_ItemShortcut->shop_group_id =
			is_null($group_id)
			? $object->shop_group_id
			: $group_id;

		return $oShop_ItemShortcut->save()->clearCache();
	}

	/**
	 * Get item's path
	 * @return string
	 * @hostcms-event shop_item.onBeforeGetPath
	 */
	public function getPath()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetPath', $this);

		$sPath = Core_Event::getLastReturn();

		if (is_null($sPath))
		{
			$sPath = ($this->path == ''
				? $this->id
				: rawurlencode($this->path)) . '/';

			if ($this->modification_id == 0)
			{
				if ($this->shop_group_id)
				{
					$sPath = $this->Shop_Group->getPath() . $sPath;
				}
			}
			else
			{
				$sPath = $this->Modification->getPath() . $sPath;
			}
		}

		return $sPath;
	}

	/**
	 * Delete item's large image
	 * @return self
	 * @hostcms-event shop_item.onAfterDeleteLargeImage
	 */
	public function deleteLargeImage()
	{
		$fileName = $this->getLargeFilePath();
		if ($this->image_large != '' && Core_File::isFile($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}

			Core_Event::notify($this->_modelName . '.onAfterDeleteLargeImage', $this);

			$this->image_large = '';
			$this->save();
		}
		return $this;
	}

	/**
	 * Delete item's small image
	 * @return self
	 * @hostcms-event shop_item.onAfterDeleteSmallImage
	 */
	public function deleteSmallImage()
	{
		$fileName = $this->getSmallFilePath();
		if ($this->image_small != '' && Core_File::isFile($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}

			Core_Event::notify($this->_modelName . '.onAfterDeleteSmallImage', $this);

			$this->image_small = '';
			$this->save();
		}
		return $this;
	}

	/**
	 * Get the ID of the user group
	 * @return int
	 */
	public function getSiteuserGroupId()
	{
		// как у родителя
		if ($this->siteuser_group_id == -1)
		{
			$result = $this->shop_group_id
				? $this->Shop_Group->getSiteuserGroupId()
				: $this->Shop->siteuser_group_id;
		}
		else
		{
			$result = $this->siteuser_group_id;
		}

		return intval($result);
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event shop_item.onBeforeIndexing
	 * @hostcms-event shop_item.onAfterIndexing
	 */
	public function indexing()
	{
		$oSearch_Page = new stdClass();

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		$oSearch_Page->text = $this->text . ' ' . $this->description . ' ' . htmlspecialchars((string) $this->name) . ' ' . $this->id . ' ' . htmlspecialchars((string) $this->seo_title) . ' ' . htmlspecialchars((string) $this->seo_description) . ' ' . htmlspecialchars((string) $this->seo_keywords) . ' ' . htmlspecialchars((string) $this->path) . ' ' . $this->price . ' ' . htmlspecialchars((string) $this->vendorcode) . ' ' . htmlspecialchars((string) $this->marking) . ' ';

		$oSearch_Page->title = (string) $this->name;

		// Set
		if ($this->type == 3)
		{
			$aShop_Item_Sets = $this->Shop_Item_Sets->findAll(FALSE);

			foreach ($aShop_Item_Sets as $oShop_Item_Set)
			{
				$oSearch_Page->text .= htmlspecialchars((string) $oShop_Item_Set->Shop_Item->name) . ' ' . $oShop_Item_Set->Shop_Item->marking . ' ';
			}
		}

		// Комментарии к товару
		if (Core::moduleIsActive('comment'))
		{
			$aComments = $this->Comments->getAllByActive(1, FALSE);
			foreach ($aComments as $oComment)
			{
				$oSearch_Page->text .= htmlspecialchars((string) $oComment->author) . ' ' . $oComment->text . ' ';
			}
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll(FALSE);
			foreach ($aTags as $oTag)
			{
				$oSearch_Page->text .= htmlspecialchars((string) $oTag->name) . ' ';
			}
		}

		// Barcodes
		$aShop_Item_Barcodes = $this->Shop_Item_Barcodes->findAll(FALSE);
		foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
		{
			$oSearch_Page->text .= htmlspecialchars((string) $oShop_Item_Barcode->value) . ' ';
		}

		if (Core::moduleIsActive('property'))
		{
			$aPropertyValues = $this->getPropertyValues(FALSE);
			foreach ($aPropertyValues as $oPropertyValue)
			{
				if ($oPropertyValue->Property->indexing)
				{
					// List
					if ($oPropertyValue->Property->type == 3 && Core::moduleIsActive('list'))
					{
						if ($oPropertyValue->value != 0)
						{
							$oList_Item = $oPropertyValue->List_Item;
							$oList_Item->id && $oSearch_Page->text .= htmlspecialchars((string) $oList_Item->value) . ' ' . htmlspecialchars((string) $oList_Item->description) . ' ';
						}
					}
					// Informationsystem
					elseif ($oPropertyValue->Property->type == 5 && Core::moduleIsActive('informationsystem'))
					{
						if ($oPropertyValue->value != 0)
						{
							$oInformationsystem_Item = $oPropertyValue->Informationsystem_Item;
							if ($oInformationsystem_Item->id)
							{
								$oSearch_Page->text .= htmlspecialchars((string) $oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
							}
						}
					}
					// Shop
					elseif ($oPropertyValue->Property->type == 12 && Core::moduleIsActive('shop'))
					{
						if ($oPropertyValue->value != 0)
						{
							$oShop_Item = $oPropertyValue->Shop_Item;
							if ($oShop_Item->id)
							{
								$oSearch_Page->text .= htmlspecialchars((string) $oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
							}
						}
					}
					// Wysiwyg
					elseif ($oPropertyValue->Property->type == 6)
					{
						$oSearch_Page->text .= htmlspecialchars(strip_tags((string) $oPropertyValue->value)) . ' ';
					}
					// Other type
					elseif ($oPropertyValue->Property->type != 2)
					{
						$oSearch_Page->text .= htmlspecialchars((string) $oPropertyValue->value) . ' ';
					}
				}
			}
		}

		if (Core::moduleIsActive('field'))
		{
			$aField_Values = Field_Controller_Value::getFieldsValues($this->getFieldIDs(), $this->id);
			foreach ($aField_Values as $oField_Value)
			{
				// List
				if ($oField_Value->Field->type == 3 && Core::moduleIsActive('list'))
				{
					if ($oField_Value->value != 0)
					{
						$oList_Item = $oField_Value->List_Item;
						$oList_Item->id && $oSearch_Page->text .= htmlspecialchars((string) $oList_Item->value) . ' ' . htmlspecialchars((string) $oList_Item->description) . ' ';
					}
				}
				// Informationsystem
				elseif ($oField_Value->Field->type == 5 && Core::moduleIsActive('informationsystem'))
				{
					if ($oField_Value->value != 0)
					{
						$oInformationsystem_Item = $oField_Value->Informationsystem_Item;
						if ($oInformationsystem_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars((string) $oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
						}
					}
				}
				// Shop
				elseif ($oField_Value->Field->type == 12 && Core::moduleIsActive('shop'))
				{
					if ($oField_Value->value != 0)
					{
						$oShop_Item = $oField_Value->Shop_Item;
						if ($oShop_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars((string) $oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
						}
					}
				}
				// Wysiwyg
				elseif ($oField_Value->Field->type == 6)
				{
					$oSearch_Page->text .= htmlspecialchars(strip_tags((string) $oField_Value->value)) . ' ';
				}
				// Other type
				elseif ($oField_Value->Field->type != 2)
				{
					$oSearch_Page->text .= htmlspecialchars((string) $oField_Value->value) . ' ';
				}
			}
		}

		// Производитель
		$oShop_Producer = $this->Shop_Producer;
		if ($oShop_Producer->id)
		{
			$oSearch_Page->text .= htmlspecialchars((string) $oShop_Producer->name) . ' ';
		}

		// Продавец
		$oShop_Seller = $this->Shop_Seller;
		if ($oShop_Seller->id)
		{
			$oSearch_Page->text .= htmlspecialchars((string) $oShop_Seller->name) . ' ';
		}

		$oSiteAlias = $this->Shop->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$oSearch_Page->url = ($this->Shop->Structure->https ? 'https://' : 'http://')
				. $oSiteAlias->name
				. $this->Shop->Structure->getPath()
				. $this->getPath();
		}
		else
		{
			return NULL;
		}

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = $this->Shop->site_id;
		$oSearch_Page->datetime = !is_null($this->datetime) && $this->datetime != '0000-00-00 00:00:00'
			? $this->datetime
			: date('Y-m-d H:i:s');
		$oSearch_Page->module = 3;
		$oSearch_Page->module_id = $this->shop_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 2; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array($this->getSiteuserGroupId());

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function adminStatus($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$iShopItemId = intval(Core_Array::getGet('shop_item_id', 0));

		$queryBuilder = Core_QueryBuilder::select(array('COUNT(*)', 'count'))
			->from('shop_item_associated')
			->where('shop_item_id', '=', $iShopItemId)
			->where('shop_item_associated_id', '=', $this->id);

		$aItemAssociatedCount = $queryBuilder->execute()->asAssoc()->current();

		$iCount = $aItemAssociatedCount['count'];

		$window_id = $oAdmin_Form_Controller->getWindowId();

		Core_Html_Entity::factory('A')
			->add(
				Core_Html_Entity::factory('I')
					->class('fa fa-lightbulb-o ' . ($iCount ? 'fa-active' : 'fa-inactive'))
			)
			->href($oAdmin_Form_Controller->getAdminActionLoadHref("/{admin}/shop/item/associated/index.php", 'adminChangeAssociated', NULL, 1, intval($this->id)))
			->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax("/{admin}/shop/item/associated/index.php", 'adminChangeAssociated', NULL, 1, intval($this->id)))
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminSetAssociated()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShopAssociatedItem = $oShopItem
		->Shop_Item_Associateds
		->getByAssociatedId($this->id);

		if (is_null($oShopAssociatedItem))
		{
			$oShopAssociatedItem = Core_Entity::factory('Shop_Item_Associated');
			$oShopAssociatedItem->shop_item_associated_id = $this->id;
			$oShopAssociatedItem->count = intval(Core_Array::getPost("apply_check_1_{$this->id}_fv_887", 0));
			$oShopItem->add($oShopAssociatedItem);
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminUnsetAssociated()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShopAssociatedItem = $oShopItem
		->Shop_Item_Associateds
		->getByAssociatedId($this->id);

		if (!is_null($oShopAssociatedItem))
		{
			$oShopAssociatedItem->delete();
		}
		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminChangeAssociated()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShopItem->clearCache();

		$oShopAssociatedItem = $oShopItem
			->Shop_Item_Associateds
			->getByAssociatedId($this->id);

		!is_null($oShopAssociatedItem)
			? $this->adminUnsetAssociated()
			: $this->adminSetAssociated();
	}

	/**
	 * Delete item directory
	 * @return self
	 */
	public function deleteDir()
	{
		// Удаляем файл большого изображения элемента
		$this->deleteLargeImage();

		// Удаляем файл малого изображения элемента
		$this->deleteSmallImage();

		if (Core_File::isDir($this->getItemPath()))
		{
			try
			{
				Core_File::deleteDir($this->getItemPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_item.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		// Удаляем значения доп. свойств
		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oPropertyValue->Property->type == 2 && $oPropertyValue->setDir($this->getItemPath());
			$oPropertyValue->delete();
		}

		$this->Shop_Carts->deleteAll(FALSE);
		$this->Shop_Favorites->deleteAll(FALSE);

		if (Core::moduleIsActive('comment'))
		{
			// Удаляем комментарии
			$this->Comments->deleteAll(FALSE);
		}

		// Удаляем связи с бонусами
		$this->Shop_Item_Bonuses->deleteAll(FALSE);

		// Удаляем связи со скидками
		$this->Shop_Item_Discounts->deleteAll(FALSE);

		// Электронные товары
		$this->Shop_Item_Digitals->deleteAll(FALSE);

		// Удаляем ярлыки
		$this->Shop_Items->deleteAll(FALSE);

		// Удаляем все модификации товара
		$this->Modifications->deleteAll(FALSE);

		// Удаляем значения дополнительных цен
		$this->Shop_Item_Prices->deleteAll(FALSE);

		// Удаляем связи с ассоциированными товарами, прямая связь
		$this->Shop_Item_Associateds->deleteAll(FALSE);

		// Удаляем связи с ассоциированными товарами, обратная связь
		$this->Shop_Item_Associated_Seconds->deleteAll(FALSE);

		// Удаляем специальные цены
		$this->Shop_Specialprices->deleteAll(FALSE);

		if (Core::moduleIsActive('tag'))
		{
			// Удаляем метки
			$this->Tag_Shop_Items->deleteAll(FALSE);
		}

		// Удаляем значения из складов
		$this->Shop_Warehouse_Items->deleteAll(FALSE);

		// Удаляем связи с зарезервированными, прямая связь
		$this->Shop_Item_Reserveds->deleteAll(FALSE);

		// Удаляем данные по доставке товаров (Яндекс.Маркет)
		$this->Shop_Item_Delivery_Options->deleteAll(FALSE);

		// Удаляем данные о комплекте товаров
		$this->Shop_Item_Sets->deleteAll(FALSE);

		// Удаляем связи с комплектом товаров, обратная связь
		$this->Shop_Item_Set_Seconds->deleteAll(FALSE);

		// Удаляем штрихкоды
		$this->Shop_Item_Barcodes->deleteAll(FALSE);

		$this->Shop_Warehouse_Incoming_Items->deleteAll(FALSE);
		$this->Shop_Warehouse_Inventory_Items->deleteAll(FALSE);
		$this->Shop_Warehouse_Writeoff_Items->deleteAll(FALSE);
		$this->Shop_Warehouse_Regrade_Incoming_Items->deleteAll(FALSE);
		$this->Shop_Warehouse_Regrade_Writeoff_Items->deleteAll(FALSE);
		$this->Shop_Warehouse_Entries->deleteAll(FALSE);

		$this->Shop_Warehouse_Cell_Items->deleteAll(FALSE);

		$this->Shop_Price_Setting_Items->deleteAll(FALSE);
		$this->Shop_Price_Entries->deleteAll(FALSE);
		$this->Shop_Tab_Items->deleteAll(FALSE);

		if (Core::moduleIsActive('deal'))
		{
			$this->Deal_Shop_Items->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Shop_Items->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('media'))
		{
			$this->Media_Shop_Items->deleteAll(FALSE);
		}

		$this->Shop_Item_Certificate->delete();

		// Fast filter
		if ($this->Shop->filter && $this->shop_id)
		{
			Core_DataBase::instance()->query("DELETE FROM `shop_filter" . intval($this->shop_id) . "` WHERE `shop_item_id` = " . intval($this->id));
		}

		// Свойства "Товар"
		$oProperties = Core_Entity::factory('Property');
		$oProperties->queryBuilder()
			->where('shop_id', '=', $this->shop_id)
			->where('type', '=', 12);

		$aProperties = $oProperties->findAll(FALSE);
		foreach ($aProperties as $oProperty)
		{
			Core_QueryBuilder::delete('property_value_ints')
				->where('property_id', '=', $oProperty->id)
				->where('value', '=', $this->id)
				->execute();
		}

		// Удаляем директорию товара
		$this->deleteDir();

		if (!is_null($this->Shop_Group->id))
		{
			// Уменьшение количества элементов в группе
			$this->Shop_Group->decCountItems();
		}

		// Remove from search index
		$this->unindex();

		return parent::delete($primaryKey);
	}

	/**
	 * Get item by group id
	 * @param int $group_id group id
	 * @return array
	 */
	public function getByGroupId($group_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_items.shop_group_id', '=', $group_id)
			->where('shop_items.shortcut_id', '=', 0);

		return $this->findAll();
	}

	/**
	 * Get item by group id and path
	 * @param int $group_id group id
	 * @param string $path path
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getByGroupIdAndPath($group_id, $path)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_items.path', 'LIKE', Core_DataBase::instance()->escapeLike($path))
			->where('shop_items.shop_group_id', '=', $group_id)
			->where('shop_items.shortcut_id', '=', 0)
			->clearOrderBy()
			->limit(1);

		$aShop_Items = $this->findAll(FALSE);

		return isset($aShop_Items[0])
			? $aShop_Items[0]
			: NULL;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		$object = $this->shortcut_id
			? $this->Shop_Item
			: $this;

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
			->class('d-flex align-items-center');

		if ($this->closed)
		{
			$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('I')
					->class('fa fa-lock darkorange locked-item order-first')
					->title(Core::_('Shop_Item.closed'))
			);
		}

		if (is_null(Core_Array::getGet('shop_item_id')) && $object->modification_id)
		{
			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('I')->class('fa fa-code-fork margin-right-5 order-first')
				);
		}

		$oCore_Html_Entity_Div->value(
			htmlspecialchars((string) $object->name)
		);

		if ($object->modification_id)
		{
			$oCore_Html_Entity_Div->value .= '<span class="small darkgray margin-left-5"> → ' . htmlspecialchars($object->Modification->name) . '</span>';
		}
		elseif ($object->shortcut_id)
		{
			$oCore_Html_Entity_Div->value .= '<span class="small darkgray margin-left-5"> → ' . htmlspecialchars($object->Shop_Item->name) . '</span>';
		}

		// Barcodes
		$aShop_Item_Barcodes = $this->Shop_Item_Barcodes->findAll(FALSE);
		foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('Span')
					// ->class('label label-sm darkgray bordered-1 bordered-gray margin-left-5')
					->class('badge badge-gray inverted margin-left-5')
					->value($oShop_Item_Barcode->value)
			);
		}

		$bRightTime = ($this->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($this->start_datetime))
			&& ($this->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($this->end_datetime));

		!$bRightTime && $oCore_Html_Entity_Div->class('wrongTime');

		// Зачеркнут в зависимости от статуса родительского товара или своего статуса
		if (!$object->active || !$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}
		elseif ($bRightTime)
		{
			$oCurrentAlias = $object->Shop->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = ($object->Shop->Structure->https ? 'https://' : 'http://')
					. $oCurrentAlias->name
					. $object->Shop->Structure->getPath()
					. $object->getPath();

				$oCore_Html_Entity_Div->add(
					Core_Html_Entity::factory('A')
						->href($href)
						->target('_blank')
						->add(
							Core_Html_Entity::factory('I')->class('fa fa-external-link margin-left-5')
						)
				);
			}
		}
		elseif (!$bRightTime)
		{
			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('I')->class('fa fa-clock-o black margin-left-5')
				);
		}

		$oCore_Html_Entity_Div->execute();

		$this->showSetBadges();
	}

	/**
	 * Show set badges
	 * @return string
	 */
	public function showSetBadges()
	{
		if ($this->type == 3)
		{
			$aSets = array();

			$aShop_Item_Sets = $this->Shop_Item_Sets->findAll(FALSE);
			foreach ($aShop_Item_Sets as $oShop_Item_Set)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item', $oShop_Item_Set->shop_item_set_id);

				if (!is_null($oShop_Item->id))
				{
					$aSets[] = htmlspecialchars($oShop_Item->name);
				}
			}

			if (count($aSets))
			{
				$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
					->class('d-flex align-items-center small darkgray margin-top-5')
					->style('flex-wrap: wrap; gap: 2px;');

				$oCore_Html_Entity_Div->add(
					Core_Html_Entity::factory('Span')
						->class('badge badge-sky inverted')
						->value('<i class="fa fa-archive fa-fw no-margin"></i>')
				);

				foreach ($aSets as $name)
				{
					$oCore_Html_Entity_Div->add(
						Core_Html_Entity::factory('Span')
							->class('badge badge-sky inverted')
							->value($name)
					);
				}

				$oCore_Html_Entity_Div->execute();
			}
		}
	}

	/**
	 * Show bonuses in XML
	 * @var boolean
	 */
	protected $_showXmlBonuses = FALSE;

	/**
	 * Add bonuses XML to item
	 * @param boolean $showXmlBonuses mode
	 * @return self
	 */
	public function showXmlBonuses($showXmlBonuses = TRUE)
	{
		$this->_showXmlBonuses = $showXmlBonuses;
		return $this;
	}

	/**
	 * Show barcodes in XML
	 * @var boolean
	 */
	protected $_showXmlBarcodes = FALSE;

	/**
	 * Add barcodes XML to item
	 * @param boolean $showXmlBarcodes mode
	 * @return self
	 */
	public function showXmlBarcodes($showXmlBarcodes = TRUE)
	{
		$this->_showXmlBarcodes = $showXmlBarcodes;
		return $this;
	}

	/**
	 * Show comments data in XML
	 * @var boolean
	 */
	protected $_showXmlComments = FALSE;

	/**
	 * Add comments XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlComments($showXmlComments = TRUE)
	{
		$this->_showXmlComments = $showXmlComments;
		return $this;
	}

	/**
	 * Show comments rating data in XML
	 * @var boolean
	 */
	protected $_showXmlCommentsRating = FALSE;

	/**
	 * Add Comments Rating XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlCommentsRating($showXmlCommentsRating = TRUE)
	{
		$this->_showXmlCommentsRating = $showXmlCommentsRating;
		return $this;
	}

	/**
	 * What comments show in XML? (active|inactive|all)
	 * @var string
	 */
	protected $_commentsActivity = 'active';

	/**
	 * Set comments filter rule
	 * @param string $commentsActivity (active|inactive|all)
	 * @return self
	 */
	public function commentsActivity($commentsActivity = 'active')
	{
		$this->_commentsActivity = $commentsActivity;
		return $this;
	}

	/**
	 * Show tabs data in XML
	 * @var boolean
	 */
	protected $_showXmlTabs = FALSE;

	/**
	 * Add tabs XML to item
	 * @param boolean $showXmlTabs mode
	 * @return self
	 */
	public function showXmlTabs($showXmlTabs = TRUE)
	{
		$this->_showXmlTabs = $showXmlTabs;
		return $this;
	}

	/**
	 * Show associated items data in XML
	 * @var boolean
	 */
	protected $_showXmlAssociatedItems = FALSE;

	/**
	 * Add associated items XML to item
	 * @param boolean $showXmlAssociatedItems mode
	 * @return self
	 */
	public function showXmlAssociatedItems($showXmlAssociatedItems = TRUE)
	{
		$this->_showXmlAssociatedItems = $showXmlAssociatedItems;
		return $this;
	}

	/**
	 * Show modifications data in XML
	 * @var boolean
	 */
	protected $_showXmlModifications = FALSE;

	/**
	 * Add modifications XML to item
	 * @param boolean $showXmlModifications mode
	 * @return self
	 */
	public function showXmlModifications($showXmlModifications = TRUE)
	{
		$this->_showXmlModifications = $showXmlModifications;
		return $this;
	}

	/**
	 * Show special prices data in XML
	 * @var boolean
	 */
	protected $_showXmlSpecialprices = FALSE;

	/**
	 * Add special prices XML to item
	 * @param boolean $showXmlSpecialprices mode
	 * @return self
	 */
	public function showXmlSpecialprices($showXmlSpecialprices = TRUE)
	{
		$this->_showXmlSpecialprices = $showXmlSpecialprices;
		return $this;
	}

	/**
	 * Show tags data in XML
	 * @var boolean
	 */
	protected $_showXmlTags = FALSE;

	/**
	 * Add tags XML to item
	 * @param boolean $showXmlTags mode
	 * @return self
	 */
	public function showXmlTags($showXmlTags = TRUE)
	{
		$this->_showXmlTags = $showXmlTags;
		return $this;
	}

	/**
	 * Show items count data in XML
	 * @var boolean
	 */
	protected $_showXmlWarehousesItems = FALSE;

	/**
	 * Add warehouse information to XML
	 * @param boolean $showXmlWarehousesItems show status
	 * @return self
	 */
	public function showXmlWarehousesItems($showXmlWarehousesItems = TRUE)
	{
		$this->_showXmlWarehousesItems = $showXmlWarehousesItems;
		return $this;
	}

	/**
	 * Show user data in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuser = FALSE;

	/**
	 * Add site user information to XML
	 * @param boolean $showXmlSiteuser show status
	 * @return self
	 */
	public function showXmlSiteuser($showXmlSiteuser = TRUE)
	{
		$this->_showXmlSiteuser = $showXmlSiteuser;
		return $this;
	}

	/**
	 * Show votes in XML
	 * @var boolean
	 */
	protected $_showXmlVotes = FALSE;

	/**
	 * Add votes XML to item
	 * @param boolean $showXmlSiteuser mode
	 * @return self
	 */
	public function showXmlVotes($showXmlVotes = TRUE)
	{
		$this->_showXmlVotes = $showXmlVotes;
		return $this;
	}

	/**
	 * Show sets in XML
	 * @var boolean
	 */
	protected $_showXmlSets = TRUE;

	/**
	 * Add XML of sets to item
	 * @param boolean $showXmlSets mode
	 * @return self
	 */
	public function showXmlSets($showXmlSets = TRUE)
	{
		$this->_showXmlSets = $showXmlSets;
		return $this;
	}

	/**
	 * Show siteuser properties in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuserProperties = FALSE;

	/**
	 * Show siteuser properties in XML
	 * @param boolean $showXmlSiteuserProperties mode
	 * @return self
	 */
	public function showXmlSiteuserProperties($showXmlSiteuserProperties = TRUE)
	{
		$this->_showXmlSiteuserProperties = $showXmlSiteuserProperties;
		return $this;
	}

	/**
	 * Show siteuser properties in XML
	 * @var boolean
	 */
	protected $_showXmlCommentProperties = FALSE;

	/**
	 * Show siteuser properties in XML
	 * @param boolean $showXmlCommentProperties mode
	 * @return self
	 */
	public function showXmlCommentProperties($showXmlCommentProperties = TRUE)
	{
		$this->_showXmlCommentProperties = is_array($showXmlCommentProperties)
			? array_combine($showXmlCommentProperties, $showXmlCommentProperties)
			: $showXmlCommentProperties;

		return $this;
	}

	/**
	 * Show properties in XML
	 * @var mixed
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Sort properties values in XML
	 * @var mixed
	 */
	protected $_xmlSortPropertiesValues = TRUE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Show media in XML
	 * @var boolean
	 */
	protected $_showXmlMedia = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlMedia($showXmlMedia = TRUE)
	{
		$this->_showXmlMedia = $showXmlMedia;

		return $this;
	}

	/**
	 * Show media in XML
	 * @var string
	 */
	protected $_itemsActivity = 'active';

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function itemsActivity($itemsActivity)
	{
		$this->_itemsActivity = strtolower($itemsActivity);

		return $this;
	}

	/**
	 * Количество товара в корзине
	 */
	protected $_cartQuantity = 1;

	/**
	 * Set item quantity into cart
	 * @param int $cartQuantity quantity of item in the cart
	 * @return self
	 */
	public function cartQuantity($cartQuantity)
	{
		$this->_cartQuantity = $cartQuantity;
		return $this;
	}

	/**
	 * Get item quantity into cart
	 * @return int
	 */
	public function getCartQuantity()
	{
		return $this->_cartQuantity;
	}

	/**
	 * Array of comments, [parent_id] => array(comments)
	 * @var array
	 */
	protected $_aComments = array();

	/**
	 * Set array of comments for getXml()
	 * @param array $aComments
	 * @return self
	 */
	public function setComments(array $aComments)
	{
		$this->_aComments = $aComments;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_item.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_item.onBeforeRedeclaredGetStdObject
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
	 * @hostcms-event shop_item.onBeforeShowXmlModifications
	 * @hostcms-event shop_item.onBeforeSelectModifications
	 * @hostcms-event shop_item.onBeforeAddModification
	 * @hostcms-event shop_item.onBeforeSelectAssociatedItems
	 * @hostcms-event shop_item.onBeforeAddAssociatedEntity
	 * @hostcms-event shop_item.onBeforeSelectComments
	 * @hostcms-event shop_item.onBeforeSelectShopWarehouseItems
	 * @hostcms-event shop_item.onAfterAddSetEntity
	 * @hostcms-event shop_item.onBeforeAddPropertyValues
	 */
	protected function _prepareData()
	{
		$oShop = $this->Shop;

		$this->clearXmlTags();

		$this->_isTagAvailable('url')
			&& $this->addXmlTag('url', $this->Shop->Structure->getPath() . $this->getPath());

		$this->_isTagAvailable('date')
			&& $this->addXmlTag('date', Core_Date::strftime($oShop->format_date, Core_Date::sql2timestamp($this->datetime)));

		/*$this->_isTagAvailable('datetime')
			&& */$this->addXmlTag('datetime', Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->datetime)));

		/*$this->_isTagAvailable('start_datetime')
			&& */$this->addXmlTag('start_datetime', $this->start_datetime == '0000-00-00 00:00:00'
				? $this->start_datetime
				: Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->start_datetime)));

		/*$this->_isTagAvailable('end_datetime')
			&& */$this->addXmlTag('end_datetime', $this->end_datetime == '0000-00-00 00:00:00'
				? $this->end_datetime
				: Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->end_datetime)));

		$this->_isTagAvailable('dir')
			&& $this->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getItemHref());

		if ($this->_showXmlVotes && Core::moduleIsActive('siteuser'))
		{
			$aRate = Vote_Controller::instance()->getRateByObject($this);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('rate')
					->value($aRate['rate'])
					->addAttribute('likes', $aRate['likes'])
					->addAttribute('dislikes', $aRate['dislikes'])
			);

			if (!is_null($oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent()))
			{
				$oVote = $this->Votes->getBySiteuser_Id($oCurrentSiteuser->id);
				!is_null($oVote) && $this->addEntity($oVote);
			}
		}

		if ($this->_showXmlSiteuser && $this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser->showXmlProperties($this->_showXmlSiteuserProperties, $this->_xmlSortPropertiesValues);
			$this->addEntity($this->Siteuser);
		}

		$this->_showXmlTags && Core::moduleIsActive('tag') && $this->addEntities($this->Tags->findAll());

		if ($this->_showXmlWarehousesItems)
		{
			$oShop_Warehouse_Items = $this->Shop_Warehouse_Items;

			Core_Event::notify($this->_modelName . '.onBeforeSelectShopWarehouseItems', $this, array($oShop_Warehouse_Items));

			$this->addEntities($oShop_Warehouse_Items->findAll());
		}

		// Digital item
		if ($this->type == 1)
		{
			$this->addXmlTag('digitals', $this->Shop_Item_Digitals->getCountDigitalItems());
		}
		// Sets
		elseif ($this->type == 3)
		{
			if ($this->_showXmlSets)
			{
				$oSetEntity = Core::factory('Core_Xml_Entity')->name('set');

				$this->addEntity($oSetEntity);

				$aForbiddenTags = $this->getForbiddenTags();

				$aShop_Item_Sets = $this->Shop_Item_Sets->findAll();
				foreach ($aShop_Item_Sets as $oShop_Item_Set)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item', $oShop_Item_Set->shop_item_set_id);

					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					if (!is_null($oShop_Item->id))
					{
						$oTmp_Shop_Item = clone $oShop_Item->clearEntities();

						// Apply forbidden tags for sets
						foreach ($aForbiddenTags as $tagName)
						{
							$oTmp_Shop_Item->addForbiddenTag($tagName);
						}

						$oSetEntity->addEntity(
							$oTmp_Shop_Item
								->id($oShop_Item->id)
								->showXmlModifications(FALSE)
								->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
								->showXmlAssociatedItems(FALSE)
								->showXmlSpecialprices($this->_showXmlSpecialprices)
								->addEntity(
									Core::factory('Core_Xml_Entity')
										->name('count')
										->value($oShop_Item_Set->count)
								)
						);

						// Parent item for modification
						if ($oShop_Item->modification_id)
						{
							$oModification = Core_Entity::factory('Shop_Item')->find($oShop_Item->modification_id);
							if (!is_null($oModification->id))
							{
								$oTmp_Modification = clone $oModification;
								$oTmp_Shop_Item->addEntity(
									$oTmp_Modification
										->id($oModification->id)
										->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
										->showXmlAssociatedItems($this->_showXmlAssociatedItems)
										->cartQuantity(1)
								);
							}
						}

						Core_Event::notify($this->_modelName . '.onAfterAddSetEntity', $this, array($oTmp_Shop_Item, $oSetEntity));
					}
					else
					{
						// Delete broken set
						$oShop_Item_Set->delete();
					}
				}
			}
		}

		// Warehouses rest
		$this->_isTagAvailable('rest') && $this->addXmlTag('rest', $this->getRest());

		// Reserved
		$this->_isTagAvailable('reserved') && $this->addXmlTag('reserved', $oShop->reserve
			? $this->getReserved()
			: 0);

		if ($this->_isTagAvailable('getPrices'))
		{
			// Prices
			$aPrices = $this->getPrices();

			if ($this->shop_currency_id)
			{
				$oShopCurrency = $this->Shop->Shop_Currency;

				$this->addXmlTag('original_price', $aPrices['price'], array(
					'formatted' => $oShopCurrency->format($aPrices['price']),
					'formattedWithCurrency' => $oShopCurrency->formatWithCurrency($aPrices['price']))
				);

				// Будет совпадать с ценой вместе с налогом
				$this->addXmlTag('price', $aPrices['price_discount'], array(
					'formatted' => $oShopCurrency->format($aPrices['price_discount']),
					'formattedWithCurrency' => $oShopCurrency->formatWithCurrency($aPrices['price_discount']))
				);
				$this->_isTagAvailable('discount') && $this->addXmlTag('discount', $aPrices['discount'], array(
					'formatted' => $oShopCurrency->format($aPrices['discount']),
					'formattedWithCurrency' => $oShopCurrency->formatWithCurrency($aPrices['discount']))
				);
				$this->_isTagAvailable('tax') && $this->addXmlTag('tax', $aPrices['tax'], array(
					'formatted' => $oShopCurrency->format($aPrices['tax']),
					'formattedWithCurrency' => $oShopCurrency->formatWithCurrency($aPrices['tax']))
				);
				$this->_isTagAvailable('price_tax') && $this->addXmlTag('price_tax', $aPrices['price_tax'], array(
					'formatted' => $oShopCurrency->format($aPrices['price_tax']),
					'formattedWithCurrency' => $oShopCurrency->formatWithCurrency($aPrices['price_tax']))
				);

				$this->_isTagAvailable('shop_discount') && count($aPrices['discounts'])
					&& $this->addEntities($aPrices['discounts']);

				// Валюта от магазина
				$this->addXmlTag('currency', $oShopCurrency->sign);
			}

			// Бонусы
			if ($this->_showXmlBonuses && Core::moduleIsActive('siteuser'))
			{
				$aBonuses = $this->getBonuses($aPrices);

				if ($aBonuses['total'])
				{
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('shop_bonuses')
							->addEntities($aBonuses['bonuses'])
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('total')
									->value($aBonuses['total'])
							)
					);
				}
			}
		}

		$this->shop_seller_id && $this->_isTagAvailable('shop_seller') && $this->addEntity($this->Shop_Seller->clearEntities());
		$this->shop_producer_id && $this->_isTagAvailable('shop_producer') && $this->addEntity($this->Shop_Producer->clearEntities());
		$this->shop_measure_id && $this->_isTagAvailable('shop_measure') && $this->addEntity($this->Shop_Measure->clearEntities());

		// Barcodes
		$this->_showXmlBarcodes && $this->addEntities($this->Shop_Item_Barcodes->findAll());

		// Modifications
		if ($this->_showXmlModifications && $this->_isTagAvailable('modifications'))
		{
			$oShop_Items_Modifications = $this->Modifications;

			Core_Event::notify($this->_modelName . '.onBeforeShowXmlModifications', $this, array($oShop_Items_Modifications));

			switch ($oShop->items_sorting_direction)
			{
				case 1:
					$items_sorting_direction = 'DESC';
				break;
				case 0:
				default:
					$items_sorting_direction = 'ASC';
			}

			// Определяем поле сортировки товаров
			switch ($oShop->items_sorting_field)
			{
				case 1:
					$oShop_Items_Modifications
						->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_items.name', $items_sorting_direction)
						->orderBy('shop_items.sorting', $items_sorting_direction);
					break;
				case 2:
					$oShop_Items_Modifications
						->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_items.sorting', $items_sorting_direction)
						->orderBy('shop_items.name', $items_sorting_direction);
					break;
				case 0:
				default:
					$oShop_Items_Modifications
						->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_items.datetime', $items_sorting_direction)
						->orderBy('shop_items.sorting', $items_sorting_direction);
			}

			if ($this->_itemsActivity != 'all')
			{
				$oShop_Items_Modifications
					->queryBuilder()
					->where('shop_items.active', '=', $this->_itemsActivity == 'inactive' ? 0 : 1);
			}

			$dateTime = Core_Date::timestamp2sql(time());
			$oShop_Items_Modifications
				->queryBuilder()
				->open()
					->where('shop_items.start_datetime', '<', $dateTime)
					->setOr()
					->where('shop_items.start_datetime', '=', '0000-00-00 00:00:00')
				->close()
				->setAnd()
				->open()
					->where('shop_items.end_datetime', '>', $dateTime)
					->setOr()
					->where('shop_items.end_datetime', '=', '0000-00-00 00:00:00')
				->close();

			Core_Event::notify($this->_modelName . '.onBeforeSelectModifications', $this, array($oShop_Items_Modifications));

			$aShop_Items_Modifications = $oShop_Items_Modifications->findAll();

			if (count($aShop_Items_Modifications))
			{
				$oModificationEntity = Core::factory('Core_Xml_Entity')
					->name('modifications');

				$this->addEntity($oModificationEntity);

				$aForbiddenTags = $this->getForbiddenTags();

				foreach ($aShop_Items_Modifications as $oShop_Items_Modification)
				{
					$oTmp_Shop_Items_Modification = clone $oShop_Items_Modification;

					$oTmp_Shop_Items_Modification
						->id($oShop_Items_Modification->id)
						->clearEntities();

					// Apply forbidden tags
					foreach ($aForbiddenTags as $tagName)
					{
						$oTmp_Shop_Items_Modification->addForbiddenTag($tagName);
					}

					$oTmp_Shop_Items_Modification
						->showXmlComments($this->_showXmlComments)
						->showXmlAssociatedItems(FALSE)
						->showXmlModifications(FALSE)
						->showXmlSpecialprices($this->_showXmlSpecialprices)
						->showXmlTags($this->_showXmlTags)
						->showXmlWarehousesItems($this->_showXmlWarehousesItems)
						->showXmlBonuses($this->_showXmlBonuses)
						->showXmlSiteuser($this->_showXmlSiteuser)
						->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
						->showXmlMedia($this->_showXmlMedia)
						->cartQuantity(1);

					Core_Event::notify($this->_modelName . '.onBeforeAddModification', $this, array(
						$oTmp_Shop_Items_Modification
					));

					$oModificationEntity->addEntity(
						$oTmp_Shop_Items_Modification
					);
				}
			}
		}

		$this->_showXmlSpecialprices && $this->addEntities($this->Shop_Specialprices->findAll());

		// Associated items
		if ($this->_showXmlAssociatedItems)
		{
			$oShop_Item_Associateds = $this->Item_Associateds;
			$oShop_Item_Associateds
				->queryBuilder()
				->where('shop_items.active', '=', 1);

			Core_Event::notify($this->_modelName . '.onBeforeSelectAssociatedItems', $this, array($oShop_Item_Associateds));

			$aShop_Item_Associateds = $oShop_Item_Associateds->findAll();
			if (count($aShop_Item_Associateds))
			{
				$oAssociatedEntity = Core::factory('Core_Xml_Entity')
					->name('associated');

				$this->addEntity($oAssociatedEntity);

				$aForbiddenTags = $this->getForbiddenTags();

				foreach ($aShop_Item_Associateds as $oShop_Item_Associated_Original)
				{
					$oShop_Item_Associated_Original->shortcut_id
						&& $oShop_Item_Associated_Original = Core_Entity::factory('Shop_Item', $oShop_Item_Associated_Original->shortcut_id);

					if ($oShop_Item_Associated_Original->id != $this->id)
					{
						// Сопутствующий товар может быть в списке, соответственное его модификации не выведутся из-за запрета на вывод модификаций для сопутствующих
						$oShop_Item_Associated = clone $oShop_Item_Associated_Original;
						$oShop_Item_Associated->id = $oShop_Item_Associated_Original->id;
						$oShop_Item_Associated->clearEntities();

						// Apply forbidden tags
						foreach ($aForbiddenTags as $tagName)
						{
							$oShop_Item_Associated->addForbiddenTag($tagName);
						}

						$oShop_Item_Associated
							->showXmlComments($this->_showXmlComments)
							->showXmlCommentsRating($this->_showXmlCommentsRating)
							->showXmlAssociatedItems(FALSE)
							->showXmlModifications(FALSE)
							->showXmlSets(FALSE)
							->showXmlSpecialprices($this->_showXmlSpecialprices)
							->showXmlTags($this->_showXmlTags)
							->showXmlWarehousesItems($this->_showXmlWarehousesItems)
							->showXmlSiteuser($this->_showXmlSiteuser)
							->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
							->showXmlMedia($this->_showXmlMedia)
							->cartQuantity(1);

						Core_Event::notify($this->_modelName . '.onBeforeAddAssociatedEntity', $this, array($oShop_Item_Associated));

						$oAssociatedEntity->addEntity(
							$oShop_Item_Associated
						);
					}
				}
			}
		}

		if (($this->_showXmlComments || $this->_showXmlCommentsRating) && Core::moduleIsActive('comment'))
		{
			$this->_aComments = array();

			$gradeSum = $gradeCount = 0;

			$oComments = $this->Comments;
			$oComments->queryBuilder()
				->orderBy('datetime', 'DESC');

			// учитываем заданную активность комментариев
			$this->_commentsActivity = strtolower($this->_commentsActivity);
			if ($this->_commentsActivity != 'all')
			{
				$oComments->queryBuilder()
					->where('active', '=', $this->_commentsActivity == 'inactive' ? 0 : 1);
			}

			Core_Event::notify($this->_modelName . '.onBeforeSelectComments', $this, array($oComments));

			$aComments = $oComments->findAll();
			foreach ($aComments as $oComment)
			{
				if ($oComment->grade > 0)
				{
					$gradeSum += $oComment->grade;
					$gradeCount++;
				}

				$this->_showXmlComments
					&& $this->_aComments[$oComment->parent_id][] = $oComment;
			}

			// Средняя оценка
			$avgGrade = $gradeCount > 0
				? round($gradeSum / $gradeCount, 2)
				: 0;

			$fractionalPart = $avgGrade - floor($avgGrade);
			$avgGradeRounded = floor($avgGrade);

			if ($fractionalPart >= 0.25 && $fractionalPart < 0.75)
			{
				$avgGradeRounded += 0.5;
			}
			elseif ($fractionalPart >= 0.75)
			{
				$avgGradeRounded += 1;
			}

			$this->_isTagAvailable('comments_count') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_count')
					->value(count($aComments))
			);

			$this->_isTagAvailable('comments_grade_sum') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_sum')
					->value($gradeSum)
			);

			$this->_isTagAvailable('comments_grade_count') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_count')
					->value($gradeCount)
			);

			$this->_isTagAvailable('comments_average_grade') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_average_grade')
					->addAttribute('value', $avgGrade)
					->value($avgGradeRounded)
			);

			$this->_showXmlComments
				&& $this->_addComments(0, $this);

			$this->_aComments = array();
		}

		if ($this->_showXmlTabs)
		{
			$oShop_Tab_Groups = Core_Entity::factory('Shop_Tab');
			$oShop_Tab_Groups
				->queryBuilder()
				->join('shop_tab_groups', 'shop_tabs.id', '=', 'shop_tab_groups.shop_tab_id')
				->where('shop_tab_groups.shop_id', '=', $this->shop_id)
				->where('shop_tab_groups.shop_group_id', '=', $this->shop_group_id);

			$aShop_Tab_Groups = $oShop_Tab_Groups->findAll();

			$oShop_Tab_Producers = Core_Entity::factory('Shop_Tab');
			$oShop_Tab_Producers
				->queryBuilder()
				->join('shop_tab_producers', 'shop_tabs.id', '=', 'shop_tab_producers.shop_tab_id')
				->where('shop_tab_producers.shop_id', '=', $this->shop_id)
				->where('shop_tab_producers.shop_producer_id', '=', $this->shop_producer_id);

			$aShop_Tab_Producers = $oShop_Tab_Producers->findAll();

			$aShop_Tabs = array_unique(array_merge($aShop_Tab_Groups, $aShop_Tab_Producers, $this->Shop_Tabs->findAll()));

			if (count($aShop_Tabs))
			{
				$oTabEntity = Core::factory('Core_Xml_Entity')
					->name('shop_tabs')
					->addEntities($aShop_Tabs);

				$this->addEntity($oTabEntity);
			}
		}

		if ($this->_showXmlProperties)
		{
			if (is_array($this->_showXmlProperties))
			{
				$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id, FALSE, $this->_xmlSortPropertiesValues);
				foreach ($aProperty_Values as $oProperty_Value)
				{
					$this->_preparePropertyValue($oProperty_Value);
				}
			}
			else
			{
				$aProperty_Values = $this->getPropertyValues(TRUE, array(), $this->_xmlSortPropertiesValues);
			}

			Core_Event::notify($this->_modelName . '.onBeforeAddPropertyValues', $this, array($aProperty_Values));

			$aListIDs = array();

			foreach ($aProperty_Values as $oProperty_Value)
			{
				// List_Items
				if ($oProperty_Value->Property->type == 3)
				{
					$aListIDs[] = $oProperty_Value->value;
				}

				$this->addEntity($oProperty_Value);
			}

			if (Core::moduleIsActive('list'))
			{
				// Cache necessary List_Items
				if (count($aListIDs))
				{
					$oList_Items = Core_Entity::factory('List_Item');
					$oList_Items->queryBuilder()
						->where('id', 'IN', $aListIDs)
						->clearOrderBy();

					$oList_Items->findAll();
				}
			}
		}

		if ($this->_showXmlMedia && Core::moduleIsActive('media'))
		{
			$aEntities = Media_Item_Controller::getValues($this);
			foreach ($aEntities as $oEntity)
			{
				$oMedia_Item = $oEntity->Media_Item;
				$this->addEntity($oMedia_Item->setCDN(Core_Page::instance()->shopCDN));
			}
		}

		return $this;
	}

	/**
	 * Add comments into object XML
	 * @param int $parent_id parent comment id
	 * @param Core_Entity $parentObject object
	 * @return self
	 * @hostcms-event shop_item.onBeforeAddComments
	 * @hostcms-event shop_item.onAfterAddComments
	 */
	protected function _addComments($parent_id, $parentObject)
	{
		Core_Event::notify($this->_modelName . '.onBeforeAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		if (isset($this->_aComments[$parent_id]))
		{
			foreach ($this->_aComments[$parent_id] as $oComment)
			{
				$parentObject->addEntity($oComment
					->clearEntities()
					->showXmlProperties($this->_showXmlCommentProperties, $this->_xmlSortPropertiesValues)
					->showXmlSiteuserProperties($this->_showXmlSiteuserProperties)
					->showXmlVotes($this->_showXmlVotes)
					->dateFormat($this->Shop->format_date)
					->dateTimeFormat($this->Shop->format_datetime)
				);

				$this->_addComments($oComment->id, $oComment);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		return $this;
	}

	/**
	 * Create item
	 * @return self
	 */
	public function create()
	{
		$return = parent::create();

		if ($this->_incCountByCreate && !is_null($this->Shop_Group->id))
		{
			// Увеличение количества элементов в группе
			$this->Shop_Group->incCountItems();
		}

		return $return;
	}

	/**
	 * Clear tagged cache
	 * @return self
	 */
	public function clearCache()
	{
		if (Core::moduleIsActive('cache'))
		{
			// Clear item's cache
			Core_Cache::instance(Core::$mainConfig['defaultCache'])
				->deleteByTag('shop_item_' . $this->id);

			// Clear group's cache
			$this->shop_group_id
				? $this->Shop_Group->clearCache()
				: Core_Cache::instance(Core::$mainConfig['defaultCache'])
					->deleteByTag('shop_group_0');

			$this->modification_id && $this->Modification->clearCache();

			// Static cache
			$oSite = $this->Shop->Site;
			if ($oSite->html_cache_use)
			{
				$oSiteAlias = $oSite->getCurrentAlias();
				if ($oSiteAlias)
				{
					if ($this->shop_group_id)
					{
						$url = $oSiteAlias->name
							. $this->Shop->Structure->getPath()
							. $this->Shop_Group->getPath();
					}
					else
					{
						$url = $oSiteAlias->name
							. $this->Shop->Structure->getPath();
							//. $this->getPath();
					}

					$oCache_Static = Core_Cache::instance('static');
					$oCache_Static->delete($url);
				}
			}
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function adminCurrencyBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oShop_Item = $this->shortcut_id
			? Core_Entity::factory('Shop_Item', $this->shortcut_id)
			: $this;

		!$oShop_Item->shop_currency_id && Core_Html_Entity::factory('I')
			->class('fa fa-exclamation-triangle darkorange')
			->title(Core::_('Shop_Item.shop_item_not_currency'))
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function relatedBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Item_Associateds->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-azure white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function modificationsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Modifications->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-darkorange white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function discountsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oShop_Item_Discounts = $this->Shop_Item_Discounts;
		$oShop_Item_Discounts->queryBuilder()
			->join('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id')
			->where('shop_discounts.deleted', '=', 0);
		$countDiscount = $oShop_Item_Discounts->getCountBySiteuser_id(0);

		$oShop_Item_Bonuses = $this->Shop_Item_Bonuses;
		$oShop_Item_Bonuses->queryBuilder()
			->join('shop_bonuses', 'shop_item_bonuses.shop_bonus_id', '=', 'shop_bonuses.id')
			->where('shop_bonuses.deleted', '=', 0);
		$countBonuses = $oShop_Item_Bonuses->getCount();

		$count = $countDiscount + $countBonuses;

		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-palegreen white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function reviewsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('comment'))
		{
			$count = $this->Comments->getCount();
			$count && Core_Html_Entity::factory('Span')
				->class('badge badge-ico white')
				->value($count < 100 ? $count : '∞')
				->title($count)
				->execute();
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function adminPriceBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		switch ($this->type)
		{
			case 1:
				// Digital
				Core_Html_Entity::factory('Span')
					->class('badge badge-ico badge-danger white')
					->style('padding-left: 1px;')
					->value('<i class="fa fa-table fa-fw"></i>')
					->title(Core::_('Shop_Item.item_type_selection_group_buttons_name_electronic'))
					->execute();
			break;
			case 2:
				// Divisible
				Core_Html_Entity::factory('Span')
					->class('badge badge-ico badge-warning white')
					->style('padding-left: 2px;')
					->value('<i class="fa fa-puzzle-piece fa-fw"></i>')
					->title(Core::_('Shop_Item.item_type_selection_group_buttons_name_divisible'))
					->execute();
			break;
			case 3:
				// Set
				Core_Html_Entity::factory('Span')
					->class('badge badge-ico badge-sky white')
					->style('padding-left: 1px;')
					->value('<i class="fa fa-archive fa-fw"></i>')
					->title(Core::_('Shop_Item.item_type_selection_group_buttons_name_set'))
					->execute();
			break;
			case 4:
				// Certificate
				Core_Html_Entity::factory('Span')
					->class('badge badge-ico badge-maroon white')
					->style('padding-left: 1px;')
					->value('<i class="fa fa-certificate fa-fw"></i>')
					->title(Core::_('Shop_Item.item_type_selection_group_buttons_name_certificate'))
					->execute();
			break;
		}
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'name' => $this->name,
				'shop_group_id' => $this->shop_group_id,
				'modification_id' => $this->modification_id,
				'datetime' => $this->datetime,
				'start_datetime' => $this->start_datetime,
				'end_datetime' => $this->end_datetime,
				'showed' => $this->showed,
				'marking' => $this->marking,
				'weight' => $this->weight,
				'shop_measure_id' => $this->shop_measure_id,
				'path' => $this->path,
				'sorting' => $this->sorting,
				'shop_producer_id' => $this->shop_producer_id,
				'shop_seller_id' => $this->shop_seller_id,
				'active' => $this->active,
				'indexing' => $this->indexing,
				'length' => $this->length,
				'width' => $this->width,
				'height' => $this->height,
				'price' => $this->price,
				'shop_currency_id' => $this->shop_currency_id,
				'shop_tax_id' => $this->shop_tax_id,
				'siteuser_id' => $this->siteuser_id,
				'shortcut_id' => $this->shortcut_id,
				'description' => $this->description,
				'text' => $this->text,
				'guid' => $this->guid,
				'yandex_market' => $this->yandex_market,
				'seo_title' => $this->seo_title,
				'seo_description' => $this->seo_description,
				'seo_keywords' => $this->seo_keywords,
				'siteuser_group_id' => $this->siteuser_group_id,
				'apply_purchase_discount' => $this->apply_purchase_discount,
				'shop_id' => $this->shop_id,
				'user_id' => $this->user_id
			);

			if (Core::moduleIsActive('siteuser'))
			{
				$aBackup['shop_item_prices'] = array();

				$aShop_Item_Prices = $this->Shop_Item_Prices->findAll(FALSE);
				foreach ($aShop_Item_Prices as $oShop_Item_Price)
				{
					$aBackup['shop_item_prices'][$oShop_Item_Price->shop_price_id] = $oShop_Item_Price->value;
				}
			}

			$aShop_Specialprices = $this->Shop_Specialprices->findAll(FALSE);

			if (count($aShop_Specialprices))
			{
				$aBackup['shop_specialprices'] = array();
				foreach ($aShop_Specialprices as $oShop_Specialprice)
				{
					$aBackup['shop_specialprices'][] = array(
						'min_quantity' => $oShop_Specialprice->min_quantity,
						'max_quantity' => $oShop_Specialprice->max_quantity,
						'price' => $oShop_Specialprice->price,
						'percent' => $oShop_Specialprice->percent,
					);
				}
			}

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->name = Core_Array::get($aBackup, 'name');
				$this->shop_group_id = Core_Array::get($aBackup, 'shop_group_id');
				$this->modification_id = Core_Array::get($aBackup, 'modification_id');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->start_datetime = Core_Array::get($aBackup, 'start_datetime');
				$this->end_datetime = Core_Array::get($aBackup, 'end_datetime');
				$this->showed = Core_Array::get($aBackup, 'showed');
				$this->marking = Core_Array::get($aBackup, 'marking');
				$this->weight = Core_Array::get($aBackup, 'weight');
				$this->shop_measure_id = Core_Array::get($aBackup, 'shop_measure_id');
				$this->path = Core_Array::get($aBackup, 'path');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->shop_producer_id = Core_Array::get($aBackup, 'shop_producer_id');
				$this->shop_seller_id = Core_Array::get($aBackup, 'shop_seller_id');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->indexing = Core_Array::get($aBackup, 'indexing');
				$this->length = Core_Array::get($aBackup, 'length');
				$this->width = Core_Array::get($aBackup, 'width');
				$this->height = Core_Array::get($aBackup, 'height');
				$this->price = Core_Array::get($aBackup, 'price');
				$this->shop_currency_id = Core_Array::get($aBackup, 'shop_currency_id');
				$this->shop_tax_id = Core_Array::get($aBackup, 'shop_tax_id');
				$this->siteuser_id = Core_Array::get($aBackup, 'siteuser_id');
				$this->shortcut_id = Core_Array::get($aBackup, 'shortcut_id');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->text = Core_Array::get($aBackup, 'text');
				$this->guid = Core_Array::get($aBackup, 'guid');
				$this->yandex_market = Core_Array::get($aBackup, 'yandex_market');
				$this->seo_title = Core_Array::get($aBackup, 'seo_title');
				$this->seo_description = Core_Array::get($aBackup, 'seo_description');
				$this->seo_keywords = Core_Array::get($aBackup, 'seo_keywords');
				$this->siteuser_group_id = Core_Array::get($aBackup, 'siteuser_group_id');
				$this->apply_purchase_discount = Core_Array::get($aBackup, 'apply_purchase_discount');
				$this->shop_id = Core_Array::get($aBackup, 'shop_id');
				$this->user_id = Core_Array::get($aBackup, 'user_id');

				if (isset($aBackup['shop_item_prices']) && Core::moduleIsActive('siteuser'))
				{
					foreach ($aBackup['shop_item_prices'] as $shop_price_id => $value)
					{
						$oShop_Item_Price = $this->Shop_Item_Prices->getByShop_price_id($shop_price_id);
						if (is_null($oShop_Item_Price))
						{
							$oShop_Item_Price = Core_Entity::factory('Shop_Item_Price');
							$oShop_Item_Price->shop_item_id = $this->id;
							$oShop_Item_Price->shop_price_id = $shop_price_id;
						}
						$oShop_Item_Price->value = $value;
						$oShop_Item_Price->save();
					}
				}

				if (isset($aBackup['shop_specialprices']))
				{
					$aShop_Specialprices = $this->Shop_Specialprices->findAll(FALSE);

					foreach ($aBackup['shop_specialprices'] as $aTmp)
					{
						if (count($aShop_Specialprices))
						{
							$oShop_Specialprice = array_shift($aShop_Specialprices);
						}
						else
						{
							$oShop_Specialprice = Core_Entity::factory('Shop_Specialprice');
							$oShop_Specialprice->shop_item_id = $this->id;
						}

						$oShop_Specialprice->min_quantity = $aTmp['min_quantity'];
						$oShop_Specialprice->max_quantity = $aTmp['max_quantity'];
						$oShop_Specialprice->price = $aTmp['price'];
						$oShop_Specialprice->percent = $aTmp['percent'];
						$oShop_Specialprice->save();
					}

					foreach ($aShop_Specialprices as $oShop_Specialprice)
					{
						$oShop_Specialprice->delete();
					}
				}

				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		if ($this->shortcut_id)
		{
			return '<i class="fa-solid fa-link"></i>';
		}
		elseif ($this->image_small != '' || $this->image_large != '')
		{
			$srcImg = htmlspecialchars($this->image_small != ''
				? $this->getSmallFileHref()
				: $this->getLargeFileHref()
			);

			$dataContent = '<img class="backend-preview" src="' . $srcImg . '" />';

			return '<img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="' . htmlspecialchars($dataContent) . '" class="backend-thumbnail" src="' . $srcImg . '" />';
		}
		else
		{
			return '<i class="fa-regular fa-image"></i>';
		}
	}

	/**
	 * Get price of set
	 * @return decimal
	 * @hostcms-event shop_item.onAfterGetSetPrice
	 */
	public function getSetPrice()
	{
		$amount = 0;

		if ($this->shop_currency_id)
		{
			$aShop_Item_Sets = $this->Shop_Item_Sets->findAll(FALSE);

			$oShop_Item_Controller = new Shop_Item_Controller();

			foreach ($aShop_Item_Sets as $oShop_Item_Set)
			{
				$oTmp_Shop_Item = Core_Entity::factory('Shop_Item', $oShop_Item_Set->shop_item_set_id);

				$oTmp_Shop_Item = $this->shortcut_id
					? $oTmp_Shop_Item->Shop_Item
					: $oTmp_Shop_Item;

				if ($oTmp_Shop_Item->shop_currency_id)
				{
					$fCurrencyCoefficient = $oTmp_Shop_Item->Shop_Currency->id > 0 && $this->Shop_Currency->id > 0
						? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
							$oTmp_Shop_Item->Shop_Currency, $this->Shop_Currency
						)
						: 0;

					$price = $oShop_Item_Controller->getSpecialprice($oTmp_Shop_Item->price, $oTmp_Shop_Item, FALSE);

					$aPrice = $oShop_Item_Controller->calculatePriceInItemCurrency($price, $oTmp_Shop_Item);

					$amount += $aPrice['price_discount'] * $fCurrencyCoefficient * $oShop_Item_Set->count;
				}
			}
		}
		else
		{
			throw new Core_Exception(Core::_('Shop_Item.shop_item_set_not_currency', $this->id, $this->name));
		}

		Core_Event::notify($this->_modelName . '.onAfterGetSetPrice', $this, array($amount));

		return $amount;
	}

	/**
	 * Get property value for SEO-templates
	 * @param int $property_id Property ID
	 * @param string $format string format, e.g. '%s: %s'. %1$s - Property Name, %2$s - List of Values
	 * @param string $separator separator
	 * @return string
	 */
	public function propertyValue($property_id, $format = '%2$s', $separator = ', ')
	{
		$oProperty = Core_Entity::factory('Property', $property_id);
		$aProperty_Values = $oProperty->getValues($this->id, FALSE);

		if (count($aProperty_Values))
		{
			$aTmp = array();

			foreach ($aProperty_Values as $oProperty_Value)
			{
				switch ($oProperty->type)
				{
					case 0: // Int
					case 1: // String
					case 4: // Textarea
					case 6: // Wysiwyg
					case 11: // Float
						$aTmp[] = $oProperty_Value->value;
					break;
					case 8: // Date
						$aTmp[] = Core_Date::strftime($this->Shop->format_date, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 9: // Datetime
						$aTmp[] = Core_Date::strftime($this->Shop->format_datetime, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 3: // List
						if ($oProperty_Value->value)
						{
							$oList_Item = $oProperty->List->List_Items->getById(
								$oProperty_Value->value, FALSE
							);

							!is_null($oList_Item) && $aTmp[] = $oList_Item->value;
						}
					break;
					case 7: // Checkbox
					break;
					case 5: // Informationsystem
						if ($oProperty_Value->value)
						{
							$aTmp[] = $oProperty_Value->Informationsystem_Item->name;
						}
					break;
					case 12: // Shop
						if ($oProperty_Value->value)
						{
							$aTmp[] = $oProperty_Value->Shop_Item->name;
						}
					break;
					case 2: // File
					case 10: // Hidden field
					default:
					break;
				}
			}

			if (count($aTmp))
			{
				return sprintf($format, $oProperty->name, implode($separator, $aTmp));
			}
		}

		return NULL;
	}

	public function loadPrice($shop_price_id)
	{
		if ($shop_price_id)
		{
			$price = '0.00';

			$oShop_Item_Price = $this->Shop_Item_Prices->getByShop_price_id($shop_price_id);

			if (!is_null($oShop_Item_Price))
			{
				$price = $oShop_Item_Price->value;
			}
		}
		else
		{
			$price = $this->price;
		}

		return $price;
	}

	/**
	 * Check activity of item and parent groups
	 * @return bool
	 */
	public function isActive()
	{
		if ($this->modification_id && !$this->Modification->active)
		{
			return FALSE;
		}

		if (!$this->active)
		{
			return FALSE;
		}

		$oTmpItem = $this->modification_id
			? $this->Modification
			: $this;

		if ($oTmpItem->shop_group_id)
		{
			$oTmpGroup = $oTmpItem->Shop_Group;

			// Все директории от текущей до родителя.
			do {
				if (!$oTmpGroup->active)
				{
					return FALSE;
				}
			} while ($oTmpGroup = $oTmpGroup->getParent());
		}

		return TRUE;
	}

	/**
	 * RestApi Upload Large Image from $_FILES['image']
	 * @retrun string|NULL Uploaded image path
	 */
	public function uploadLargeImage()
	{
		if (isset($_FILES['image']['tmp_name']))
		{
			$file_name = $_FILES['image']['name'];

			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($file_name, Core::$mainConfig['availableExtension']))
			{
				$oShop = $this->Shop;

				// Удаление файла большого изображения
				$this->image_large && $this->deleteLargeImage();

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					$fileName = $file_name;
				}
				else
				{
					$aConfig = Shop_Controller::getConfig();

					// Определяем расширение файла
					$ext = Core_File::getExtension($file_name);

					$fileName = sprintf($aConfig['itemLargeImage'], $this->id, $ext);
				}

				$this->saveLargeImageFile($_FILES['image']['tmp_name'], $fileName);

				if ($this->image_small == '' && $oShop->create_small_image)
				{
					$this->uploadSmallImage();
				}

				return $this->getLargeFileHref();
			}
		}
	}

	/**
	 * RestApi Upload Small Image from $_FILES['image']
	 * @retrun string|NULL Uploaded image path
	 */
	public function uploadSmallImage()
	{
		if (isset($_FILES['image']['tmp_name']))
		{
			$file_name = $_FILES['image']['name'];

			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($file_name, Core::$mainConfig['availableExtension']))
			{
				$oShop = $this->Shop;

				// Удаление файла малого изображения
				$this->image_small && $this->deleteSmallImage();

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					$fileName = $file_name;
				}
				else
				{
					$aConfig = Shop_Controller::getConfig();

					// Определяем расширение файла
					$ext = Core_File::getExtension($file_name);

					$fileName = sprintf($aConfig['itemSmallImage'], $this->id, $ext);
				}

				$this->saveSmallImageFile($_FILES['image']['tmp_name'], $fileName);

				return $this->getSmallFileHref();
			}
		}
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item.onBeforeGetRelatedSite
	 * @hostcms-event shop_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}