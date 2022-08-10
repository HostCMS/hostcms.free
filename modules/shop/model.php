<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop';

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var string
	 */
	public $img_transactions = 0;

	/**
	 * Backend property
	 * @var string
	 */
	public $currency_name = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'affiliate_plan' => array('through' => 'shop_affiliate_plan'),
		'shop_affiliate_plan' => array(),
		'shop_cart' => array(),
		'shop_favorite' => array(),
		'shop_compare' => array(),
		'shop_delivery' => array(),
		'shop_bonus' => array(),
		'shop_discount' => array(),
		'shop_group' => array(),
		'shop_group_property' => array(),
		'shop_group_property_dir' => array(),
		'shop_item' => array(),
		'shop_item_property' => array(),
		'shop_item_property_dir' => array(),
		'shop_order' => array(),
		'shop_order_property' => array(),
		'shop_order_property_dir' => array(),
		'shop_payment_system' => array(),
		'shop_print_form' => array(),
		'shop_price' => array(),
		'shop_producer' => array(),
		'shop_producer_dir' => array(),
		'shop_purchase_discount' => array(),
		'shop_seller' => array(),
		'shop_siteuser_transaction' => array(),
		'shop_warehouse' => array(),
		'shop_item_property_for_group' => array(),
		'shop_item_delivery_option' => array(),
		'deal' => array(),
		'shop_discountcard' => array(),
		'shop_discountcard_level' => array(),
		'shop_discountcard_bonus_type' => array(),
		'shop_price_setting' => array(),
		'shop_filter_seo' => array(),
		'shop_filter_seo_dir' => array(),
		'shop_tab_dir' => array(),
		'shop_tab' => array(),
		'shop_comment_property' => array(),
		'shop_comment_property_dir' => array(),
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'use_captcha' => 1,
		'image_small_max_width' => 100,
		'image_large_max_width' => 800,
		'image_small_max_height' => 100,
		'image_large_max_height' => 800,
		'group_image_small_max_width' => 100,
		'group_image_large_max_width' => 800,
		'group_image_small_max_height' => 100,
		'group_image_large_max_height' => 800,
		'items_sorting_field' => 0,
		'items_sorting_direction' => 0,
		'groups_sorting_field' => 0,
		'groups_sorting_direction' => 0,
		'url_type' => 0,
		'apply_tags_automatically' => 0,
		'write_off_paid_items' => 0,
		'comment_active' => 0,
		'format_date' => '%d.%m.%Y',
		'format_datetime' => '%d.%m.%Y %H:%M:%S',
		'typograph_default_items' => 1,
		'typograph_default_groups' => 1,
		'watermark_default_position_x' => '50%',
		'watermark_default_position_y' => '100%',
		'preserve_aspect_ratio' => 1,
		'items_on_page' => 10,
		'reserve' => 0,
		'reserve_hours' => 24,
		'watermark_file' => '',
		'producer_image_small_max_width' => 100,
		'producer_image_large_max_width' => 800,
		'producer_image_small_max_height' => 100,
		'producer_image_large_max_height' => 800,
		'discountcard_template' => '{this.id}',
		'invoice_template' => '{this.id}'
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_dir' => array(),
		'site' => array(),
		'structure' => array(),
		'shop_country' => array(),
		'shop_currency' => array(),
		'shop_order_status' => array(),
		'shop_codetype' => array(),
		'shop_measure' => array(), // weight
		'default_shop_measure' => array('model' => 'Shop_Measure', 'foreign_key' => 'default_shop_measure_id'), // item's default measure
		'user' => array(),
		'siteuser_group' => array(),
		'shop_company' => array(), // old relation
		'company' => array('foreign_key' => 'shop_company_id') // new relation
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description'
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 *
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'size_measure',
		'yandex_market_name',
		'items_sorting_direction',
		'items_sorting_field',
		'groups_sorting_direction',
		'groups_sorting_field',
		'image_large_max_width',
		'image_large_max_height',
		'image_small_max_width',
		'image_small_max_height',
		'siteuser_group_id',
		'watermark_file',
		'watermark_default_use_large_image',
		'watermark_default_use_small_image',
		'watermark_default_position_x',
		'watermark_default_position_y',
		'create_small_image',
		'typograph_default_items',
		'typograph_default_groups',
		'apply_tags_automatically',
		'change_filename',
		'apply_keywords_automatically',
		'group_image_small_max_width',
		'group_image_large_max_width',
		'group_image_small_max_height',
		'group_image_large_max_height',
		'producer_image_small_max_width',
		'producer_image_large_max_width',
		'producer_image_small_max_height',
		'producer_image_large_max_height',
		'preserve_aspect_ratio',
		'preserve_aspect_ratio_small',
		'preserve_aspect_ratio_group',
		'preserve_aspect_ratio_group_small',
		'seo_group_title_template',
		'seo_group_keywords_template',
		'seo_group_description_template',
		'seo_item_title_template',
		'seo_item_keywords_template',
		'seo_item_description_template',
		'order_admin_subject',
		'order_user_subject',
		'confirm_admin_subject',
		'confirm_user_subject',
		'cancel_admin_subject',
		'cancel_user_subject',
		'shop_order_status_id',
		'send_order_email_admin',
		'send_order_email_user',
		'guid',
		'yandex_market_sales_notes_default',
		'filter'
	);

	/**
	 * Tree of groups
	 * @var array
	 */
	protected $_groupsTree = array();

	/**
	 * Cache of groups
	 * @var array
	 */
	protected $_cacheGroups = array();

	/**
	 * Cache of items
	 * @var array
	 */
	protected $_cacheItems = array();

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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Calculate counts
	 * @var boolean
	 */
	protected $_showXmlCounts = TRUE;

	/**
	 * Add comments XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlCounts($showXmlCounts = TRUE)
	{
		$this->_showXmlCounts = $showXmlCounts;
		return $this;
	}

	/**
	 * Get shop by structure id.
	 * @param int $structure_id
	 * @return self|NULL
	 */
	public function getByStructureId($structure_id)
	{
		$this->queryBuilder()
			->clear()
			->where('structure_id', '=', $structure_id)
			->limit(1);

		$aShops = $this->findAll();

		return isset($aShops[0]) ? $aShops[0] : NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// Fix bug with 'deleted' relations
		$this->deleted = 0;
		$this->save();

		// Доп. свойства товаров
		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $this->id);
		$oShop_Item_Property_List->Properties->deleteAll(FALSE);
		$oShop_Item_Property_List->Property_Dirs->deleteAll(FALSE);

		// Доп. свойства групп
		$oShop_Group_Property_List = Core_Entity::factory('Shop_Group_Property_List', $this->id);
		$oShop_Group_Property_List->Properties->deleteAll(FALSE);
		$oShop_Group_Property_List->Property_Dirs->deleteAll(FALSE);

		// Доп. свойства заказов
		$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $this->id);
		$oShop_Order_Property_List->Properties->deleteAll(FALSE);
		$oShop_Order_Property_List->Property_Dirs->deleteAll(FALSE);

		// Доп. свойства комменариев
		$oShop_Comment_Property_List = Core_Entity::factory('Shop_Comment_Property_List', $this->id);
		$oShop_Comment_Property_List->Properties->deleteAll(FALSE);
		$oShop_Comment_Property_List->Property_Dirs->deleteAll(FALSE);

		$this->Shop_Item_Property_Dirs->deleteAll(FALSE);
		$this->Shop_Item_Properties->deleteAll(FALSE);
		$this->Shop_Group_Property_Dirs->deleteAll(FALSE);
		$this->Shop_Group_Properties->deleteAll(FALSE);
		$this->Shop_Order_Property_Dirs->deleteAll(FALSE);
		$this->Shop_Order_Properties->deleteAll(FALSE);
		$this->Shop_Comment_Property_Dirs->deleteAll(FALSE);
		$this->Shop_Comment_Properties->deleteAll(FALSE);

		$this->Shop_Affiliate_Plans->deleteAll(FALSE);
		$this->Shop_Carts->deleteAll(FALSE);
		$this->Shop_Favorites->deleteAll(FALSE);
		$this->Shop_Compares->deleteAll(FALSE);
		$this->Shop_Deliveries->deleteAll(FALSE);
		$this->Shop_Bonuses->deleteAll(FALSE);
		$this->Shop_Discounts->deleteAll(FALSE);
		$this->Shop_Groups->deleteAll(FALSE);
		$this->Shop_Items->deleteAll(FALSE);
		$this->Shop_Orders->deleteAll(FALSE);
		$this->Shop_Payment_Systems->deleteAll(FALSE);
		$this->Shop_Prices->deleteAll(FALSE);
		$this->Shop_Producers->deleteAll(FALSE);
		$this->Shop_Purchase_Discounts->deleteAll(FALSE);
		$this->Shop_Sellers->deleteAll(FALSE);
		$this->Shop_Siteuser_Transactions->deleteAll(FALSE);
		$this->Shop_Warehouses->deleteAll(FALSE);
		$this->Shop_Item_Property_For_Groups->deleteAll(FALSE);
		$this->Shop_Item_Delivery_Options->deleteAll(FALSE);
		$this->Shop_Discountcards->deleteAll(FALSE);
		$this->Shop_Discountcard_Levels->deleteAll(FALSE);
		$this->Shop_Discountcard_Bonus_Types->deleteAll(FALSE);
		$this->Shop_Price_Settings->deleteAll(FALSE);
		$this->Shop_Filter_Seo_Dirs->deleteAll(FALSE);
		$this->Shop_Filter_Seos->deleteAll(FALSE);
		$this->Shop_Tab_Dirs->deleteAll(FALSE);
		$this->Shop_Tabs->deleteAll(FALSE);

		// Shop dir
		Core_File::deleteDir($this->getPath());

		return parent::delete($primaryKey);
	}

	/**
	 * Get watermark file path
	 * @return string|NULL
	 */
	public function getWatermarkFilePath()
	{
		return $this->watermark_file != ''
			? $this->getPath() . '/watermarks/' . $this->watermark_file
			: NULL;
	}

	/**
	 * Get watermark file href
	 * @return string
	 */
	public function getWatermarkFileHref()
	{
		return '/' . $this->getHref() . '/watermarks/' . $this->watermark_file;
	}

	/**
	 * Get shop path include CMS_FOLDER
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Get shop href
	 * @return string
	 */
	public function getHref()
	{
		return $this->Site->uploaddir . "shop_" . intval($this->id);
	}

	/**
	 * Save watermark file
	 * @param string $fileSourcePath file to upload
	 */
	public function saveWatermarkFile($fileSourcePath)
	{
		$this->watermark_file = 'shop_watermark_' . $this->id . '.png';
		$this->save();
		Core_File::upload($fileSourcePath, $this->getWatermarkFilePath());
	}

	/**
	 * Save object. Use self::update() or self::create()
	 * @return self
	 */
	public function save()
	{
		parent::save();

		// Создание директории для Watermark
		$sWatermarkDirPath = $this->getPath() . '/watermarks';

		if (!is_dir($sWatermarkDirPath))
		{
			try
			{
				Core_File::mkdir($sWatermarkDirPath, CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete watermark file
	 */
	public function deleteWatermarkFile()
	{
		try
		{
			Core_File::delete($this->getWatermarkFilePath());
		} catch (Exception $e) {}

		$this->watermark_file = '';
		$this->save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->filter = 0;
		$newObject->save();

		try
		{
			is_file($this->getWatermarkFilePath()) && Core_File::copy($this->getWatermarkFilePath(), $newObject->getWatermarkFilePath());
		} catch (Exception $e) {}

		// Копирование доп. свойств и разделов доп. свойств товаров
		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $this->id);

		// Linked object for new shop
		$oNewObject_Shop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $newObject->id);

		$oProperty_Dir = $oShop_Item_Property_List->Property_Dirs;
		//$oProperty_Dir->queryBuilder()->where('parent_id', '=', 0);
		$aProperty_Dirs = $oProperty_Dir->findAll();

		$aMatchProperty_Dirs = array();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			//$oNewProperty_Dir = $oProperty_Dir->copy();
			$oNewProperty_Dir = clone $oProperty_Dir;
			$oNewObject_Shop_Item_Property_List->add($oNewProperty_Dir);

			$aMatchProperty_Dirs[$oProperty_Dir->id] = $oNewProperty_Dir;
		}

		$oNewProperty_Dirs = $oNewObject_Shop_Item_Property_List->Property_Dirs->findAll();

		foreach ($oNewProperty_Dirs as $oNewProperty_Dir)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]))
			{
				$oNewProperty_Dir->parent_id = $aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]->id;
				$oNewProperty_Dir->save();
			}
		}

		$oProperty = $oShop_Item_Property_List->Properties;
		//$oProperty->queryBuilder()->where('property_dir_id', '=', 0);
		$aProperties = $oProperty->findAll();

		foreach ($aProperties as $oProperty)
		{
			//$oNewProperty = $oProperty->copy(FALSE);
			$oNewProperty = clone $oProperty;
			$oNewObject_Shop_Item_Property_List->add($oNewProperty);
		}

		$oNewProperties = $oNewObject_Shop_Item_Property_List->Properties->findAll();
		foreach ($oNewProperties as $oNewProperty)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty->property_dir_id]))
			{
				$oNewProperty->property_dir_id = $aMatchProperty_Dirs[$oNewProperty->property_dir_id]->id;
				$oNewProperty->save();
			}
		}

		// Копирование доп. свойств и разделов доп. свойств групп товаров
		$oShop_Group_Property_List = Core_Entity::factory('Shop_Group_Property_List', $this->id);
		$oNewObject_Shop_Group_Property_List = Core_Entity::factory('Shop_Group_Property_List', $newObject->id);

		$oProperty_Dir = $oShop_Group_Property_List->Property_Dirs;
		//$oProperty_Dir->queryBuilder()->where('parent_id', '=', 0);
		$aProperty_Dirs = $oProperty_Dir->findAll();

		$aMatchProperty_Dirs = array();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$oNewProperty_Dir = clone $oProperty_Dir;

			$oNewObject_Shop_Group_Property_List->add($oNewProperty_Dir);

			$aMatchProperty_Dirs[$oProperty_Dir->id] = $oNewProperty_Dir;
			/*
			$oNewObject_Shop_Group_Property_List->add(
				$oProperty_Dir->copy()
			);
			*/
		}

		$oNewProperty_Dirs = $oNewObject_Shop_Group_Property_List->Property_Dirs->findAll();

		foreach ($oNewProperty_Dirs as $oNewProperty_Dir)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]))
			{
				$oNewProperty_Dir->parent_id = $aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]->id;
				$oNewProperty_Dir->save();
			}
		}

		$oProperty = $oShop_Group_Property_List->Properties;
		//$oProperty->queryBuilder()->where('property_dir_id', '=', 0);
		$aProperties = $oProperty->findAll();

		foreach ($aProperties as $oProperty)
		{
			$oNewProperty = clone $oProperty;

			$oNewObject_Shop_Group_Property_List->add($oNewProperty);
			/*
			$oNewObject_Shop_Group_Property_List->add(
				$oProperty->copy(FALSE)
			);
			*/
		}

		$oNewProperties = $oNewObject_Shop_Group_Property_List->Properties->findAll();
		foreach ($oNewProperties as $oNewProperty)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty->property_dir_id]))
			{
				$oNewProperty->property_dir_id = $aMatchProperty_Dirs[$oNewProperty->property_dir_id]->id;
				$oNewProperty->save();
			}
		}

		// Копирование доп. свойств и разделов доп. свойств заказов
		$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $this->id);
		$oNewObject_Shop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $newObject->id);

		$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();

		$aMatchProperty_Dirs = array();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$oNewProperty_Dir = clone $oProperty_Dir;
			$oNewObject_Shop_Order_Property_List->add($oNewProperty_Dir);
			$aMatchProperty_Dirs[$oProperty_Dir->id] = $oNewProperty_Dir;
		}

		$oNewProperty_Dirs = $oNewObject_Shop_Order_Property_List->Property_Dirs->findAll();
		foreach ($oNewProperty_Dirs as $oNewProperty_Dir)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]))
			{
				$oNewProperty_Dir->parent_id = $aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]->id;
				$oNewProperty_Dir->save();
			}
		}

		$aProperties = $oShop_Order_Property_List->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			$oNewProperty = clone $oProperty;
			$oNewObject_Shop_Order_Property_List->add($oNewProperty);
		}

		$oNewProperties = $oNewObject_Shop_Order_Property_List->Properties->findAll();
		foreach ($oNewProperties as $oNewProperty)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty->property_dir_id]))
			{
				$oNewProperty->property_dir_id = $aMatchProperty_Dirs[$oNewProperty->property_dir_id]->id;
				$oNewProperty->save();
			}
		}

		// Копирование связи (!) с партнерскими программами
		$aAffiliate_Plans = $this->Affiliate_Plans->findAll();
		foreach ($aAffiliate_Plans as $oAffiliate_Plan)
		{
			$newObject->add($oAffiliate_Plan);
		}

		// Копирование типов и условий доставки
		$aShop_Deliveries = $this->Shop_Deliveries->findAll();
		foreach ($aShop_Deliveries as $oShop_Delivery)
		{
			$newObject->add(
				$oShop_Delivery->copy()
			);
		}

		// Копирование бонусов
		$aShop_Bonuses = $this->Shop_Bonuses->findAll();
		foreach ($aShop_Bonuses as $oShop_Bonus)
		{
			$newObject->add(
				$oShop_Bonus->copy()
			);
		}

		$aShop_Discountcard_Levels = $this->Shop_Discountcard_Levels->findAll(FALSE);
		foreach ($aShop_Discountcard_Levels as $oShop_Discountcard_Level)
		{
			$newObject->add(
				$oShop_Discountcard_Level->copy()
			);
		}

		$aShop_Discountcard_Bonus_Types = $this->Shop_Discountcard_Bonus_Types->findAll(FALSE);
		foreach ($aShop_Discountcard_Bonus_Types as $oShop_Discountcard_Bonus_Type)
		{
			$newObject->add(
				$oShop_Discountcard_Bonus_Type->copy()
			);
		}

		// Копирование скидок на товары
		$aShop_Discounts = $this->Shop_Discounts->findAll();
		foreach ($aShop_Discounts as $oShop_Discount)
		{
			$newObject->add(
				$oShop_Discount->copy()
			);
		}

		// Копирование платежных систем
		$aShop_Payment_Systems = $this->Shop_Payment_Systems->findAll();
		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$newObject->add($oShop_Payment_System->copy());
		}

		// Копирование цен
		$aShop_Prices = $this->Shop_Prices->findAll();
		foreach ($aShop_Prices as $Shop_Price)
		{
			$newObject->add($Shop_Price->copy());
		}

		// Копирование производителей
		$aShop_Producers = $this->Shop_Producers->findAll();
		foreach ($aShop_Producers as $oShop_Producer)
		{
			$newObject->add($oShop_Producer->copy());
		}

		// Копирование скидок от суммы заказа
		$aShop_Purchase_Discounts = $this->Shop_Purchase_Discounts->findAll();
		foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
		{
			$newObject->add($oShop_Purchase_Discount->copy());
		}

		// Копирование продавцов
		$aShop_Sellers = $this->Shop_Sellers->findAll();
		foreach ($aShop_Sellers as $oShop_Seller)
		{
			$newObject->add($oShop_Seller->copy());
		}

		// Копирование складов
		$aShop_Warehouses = $this->Shop_Warehouses->findAll();
		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$newObject->add($oShop_Warehouse->copy());
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Recount items and subgroups
	 * @return self
	 * @hostcms-event shop.onBeforeRecount
	 * @hostcms-event shop.onAfterRecount
	 * @hostcms-event shop.onBeforeSelectCountGroupsInRecount
	 * @hostcms-event shop.onBeforeSelectCountItemsInRecount
	 */
	public function recount()
	{
		$shop_id = $this->id;

		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			Core::isFunctionEnable('set_time_limit') && @set_time_limit(1200);
			@ini_set('max_execution_time', '1200');
		}

		Core_Event::notify($this->_modelName . '.onBeforeRecount', $this);

		$this->_groupsTree = array();
		$queryBuilder = Core_QueryBuilder::select('id', 'parent_id')
			->from('shop_groups')
			->where('shop_groups.shop_id', '=', $shop_id)
			//->where('shop_groups.active', '=', 1) // Пресчитываем для всех групп, включая отключенные
			->where('shop_groups.deleted', '=', 0);

		$aShop_Groups = $queryBuilder->execute()->asAssoc()->result();

		foreach ($aShop_Groups as $aShop_Group)
		{
			$this->_groupsTree[$aShop_Group['parent_id']][] = $aShop_Group['id'];
		}

		$this->_cacheGroups = array();

		$queryBuilder = Core_QueryBuilder::select('parent_id', array('COUNT(id)', 'count'))
			->from('shop_groups')
			->where('shop_groups.shop_id', '=', $shop_id)
			->where('shop_groups.active', '=', 1)
			->where('shop_groups.deleted', '=', 0)
			->groupBy('parent_id');

		Core_Event::notify($this->_modelName . '.onBeforeSelectCountGroupsInRecount', $this, array($queryBuilder));

		$aShop_Groups = $queryBuilder->execute()->asAssoc()->result();

		foreach ($aShop_Groups as $aShop_Group)
		{
			$this->_cacheGroups[$aShop_Group['parent_id']] = $aShop_Group['count'];
		}

		$this->_cacheItems = array();

		$current_date = date('Y-m-d H:i:s');

		$queryBuilder->clear()
			->select('shop_group_id', array('COUNT(id)', 'count'))
			->from('shop_items')
			->where('shop_items.shop_id', '=', $shop_id)
			->where('shop_items.active', '=', 1)
			->where('shop_items.start_datetime', '<=', $current_date)
			->open()
				->where('shop_items.end_datetime', '>=', $current_date)
				->setOr()
				->where('shop_items.end_datetime', '=', '0000-00-00 00:00:00')
			->close()
			->where('shop_items.deleted', '=', 0)
			->groupBy('shop_group_id');

		Core_Event::notify($this->_modelName . '.onBeforeSelectCountItemsInRecount', $this, array($queryBuilder));

		$aShop_Items = $queryBuilder->execute()->asAssoc()->result();
		foreach ($aShop_Items as $Shop_Item)
		{
			$this->_cacheItems[$Shop_Item['shop_group_id']] = $Shop_Item['count'];
		}

		// DISABLE KEYS
		Core_DataBase::instance()->setQueryType(5)->query("ALTER TABLE `shop_groups` DISABLE KEYS");

		$this->_callSubgroup();

		// ENABLE KEYS
		Core_DataBase::instance()->setQueryType(5)->query("ALTER TABLE `shop_groups` ENABLE KEYS");

		$this->_groupsTree = $this->_cacheGroups = $this->_cacheItems = array();

		Core_Event::notify($this->_modelName . '.onAfterRecount', $this);

		return $this;
	}

	/**
	 * Recount subgroups
	 * @param int $parent_id parent group ID
	 * @return array
	 */
	protected function _callSubgroup($parent_id = 0)
	{
		$return = array(
			'subgroups' => 0,
			'subgroups_total' => 0,
			'items' => 0,
			'items_total' => 0
		);

		if (isset($this->_groupsTree[$parent_id]))
		{
			foreach ($this->_groupsTree[$parent_id] as $groupId)
			{
				$aTmp = $this->_callSubgroup($groupId);
				$return['subgroups_total'] += $aTmp['subgroups_total'];
				$return['items_total'] += $aTmp['items_total'];
			}
		}

		if (isset($this->_cacheGroups[$parent_id]))
		{
			$return['subgroups'] = $this->_cacheGroups[$parent_id];
			$return['subgroups_total'] += $return['subgroups'];
		}

		if (isset($this->_cacheItems[$parent_id]))
		{
			$return['items'] = $this->_cacheItems[$parent_id];
			$return['items_total'] += $return['items'];
		}

		if ($parent_id)
		{
			$oShop_Group = Core_Entity::factory('Shop_Group', $parent_id);
			$oShop_Group->subgroups_count = $return['subgroups'];
			$oShop_Group->subgroups_total_count = $return['subgroups_total'];
			$oShop_Group->items_count = $return['items'];
			$oShop_Group->items_total_count = $return['items_total'];
			$oShop_Group->setCheck(FALSE)->save();
		}

		return $return;
	}

	/**
	 * Recount sets
	 * @return self
	 * @hostcms-event shop.onBeforeRecountSets
	 * @hostcms-event shop.onAfterRecountSets
	 */
	public function recountSets()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRecountSets', $this);

		$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
		$oShop_Price_Setting->shop_id = $this->id;
		$oShop_Price_Setting->number = '';
		$oShop_Price_Setting->posted = 0;
		$oShop_Price_Setting->description = Core::_('Shop.set_price_recount_sets');
		$oShop_Price_Setting->save();

		$oShop_Price_Setting->number = $oShop_Price_Setting->id;
		$oShop_Price_Setting->save();

		$limit = 100;
		$offset = 0;

		do {
			$oShop_Items = $this->Shop_Items;
			$oShop_Items->queryBuilder()
				->where('shop_items.shortcut_id', '=', 0)
				->where('shop_items.type', '=', 3)
				->limit($limit)
				->offset($offset);

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
				$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
				$oShop_Price_Setting_Item->shop_price_id = 0;
				$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
				$oShop_Price_Setting_Item->old_price = $oShop_Item->price;
				$oShop_Price_Setting_Item->new_price = $oShop_Item->getSetPrice();
				$oShop_Price_Setting_Item->save();
			}

			$offset += $limit;
		}
		while (count($aShop_Items));

		// Проводим
		$oShop_Price_Setting->post();

		Core_Event::notify($this->_modelName . '.onAfterRecountSets', $this);

		return $this;
	}

	/**
	 * Delete empty groups in UPLOAD path for shop
	 */
	public function deleteEmptyDirs()
	{
		Core_File::deleteEmptyDirs($this->getPath());
		return FALSE;
	}

	/**
	 * Get first shop's admin email
	 * @return string
	 */
	public function getFirstEmail()
	{
		$aEmails = trim($this->email) != ''
			? explode(',', $this->email)
			: array(EMAIL_TO);

		return trim($aEmails[0]);
	}

	/**
	 * Backend callback method
	 * @return float
	 */
	public function adminTransactionAmountBackend()
	{
		$siteuser_id = intval(Core_Array::getGet('siteuser_id'));

		return Core_Entity::factory('Siteuser', $siteuser_id)->getTransactionsAmount($this);
	}

	/**
	 * Show taxes in XML
	 * @var boolean
	 */
	protected $_showXmlTaxes = FALSE;

	/**
	 * Add taxes to XML
	 * @param boolean $showXmlTaxes
	 * @return self
	 */
	public function showXmlTaxes($showXmlTaxes = TRUE)
	{
		$this->_showXmlTaxes = $showXmlTaxes;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop.onBeforeRedeclaredGetStdObject
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
	 * @hostcms-event shop.onBeforeSelectShopWarehouses
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('http', '//' . Core_Array::get($_SERVER, 'SERVER_NAME'))
			->addXmlTag('url', $this->Structure->getPath())
			->addXmlTag('captcha_id', $this->use_captcha ? Core_Captcha::getCaptchaId() : 0);

		$this->shop_currency_id && $this->addEntity($this->Shop_Currency->clearEntities());
		$this->shop_measure_id && $this->addEntity($this->Shop_Measure->clearEntities());
		$this->shop_company_id && $this->addEntity($this->Shop_Company->clearEntities());

		$this->size_measure !== '' && $this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('size_measure')
				->addAttribute('id', $this->size_measure)
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('name')
						->value(Core::_('Shop.size_measure_' . $this->size_measure))
				)
		);

		// Warehouses
		$oShop_Warehouses = $this->Shop_Warehouses;

		Core_Event::notify($this->_modelName . '.onBeforeSelectShopWarehouses', $this, array($oShop_Warehouses));

		$aShop_Warehouses = $oShop_Warehouses->findAll();

		$this->addEntities($aShop_Warehouses);

		$this->_showXmlTaxes && $this->addEntities(Core_Entity::factory('Shop_Tax')->findAll());

		if ($this->_showXmlCounts)
		{
			$oShop_Items = $this->Shop_Items;
			$oShop_Items->queryBuilder()
				->where('shop_items.shop_group_id', '=', 0);
			$iCountItems = $oShop_Items->getCount();

			$aShop_Groups = $this->Shop_Groups->getByParentId(0, FALSE);
			$iCountGroups = count($aShop_Groups);

			$array = array(
				'items_count' => $iCountItems,
				'items_total_count' => $iCountItems,
				'subgroups_count' => $iCountGroups,
				'subgroups_total_count' => $iCountGroups
			);

			foreach ($aShop_Groups as $oShop_Group)
			{
				$array['items_total_count'] += $oShop_Group->items_total_count;
				$array['subgroups_total_count'] += $oShop_Group->subgroups_total_count;
			}

			$this
				->addXmlTag('items_count', $array['items_count'])
				->addXmlTag('items_total_count', $array['items_total_count'])
				->addXmlTag('subgroups_count', $array['subgroups_count'])
				->addXmlTag('subgroups_total_count', $array['subgroups_total_count']);
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		!$this->structure_id && Core_Html_Entity::factory('Span')
			->class('badge badge-darkorange badge-ico white')
			->add(Core_Html_Entity::factory('I')->class('fa fa-chain-broken'))
			->execute();

		$countShopGroups = $this->Shop_Groups->getCount();
		$countShopGroups && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-folder-open-o"></i> ' . $countShopGroups)
			->title(Core::_('Shop.all_groups_count', $countShopGroups))
			->execute();

		$countShopItems = $this->Shop_Items->getCount();
		$countShopItems && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-file-o"></i> ' . $countShopItems)
			->title(Core::_('Shop.all_items_count', $countShopItems))
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function pathBackend()
	{
		$this->structure_id && $this->Structure->pathBackend();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function rebuildBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$return = '';

		if ($this->filter)
		{
			$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'rebuildFilter', NULL, 1, $this->id, '');
			$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'rebuildFilter', NULL, 1, $this->id, '');

			$oShop_Filter_Controller = new Shop_Filter_Controller($this);
			$sTableName = $oShop_Filter_Controller->getTableName();

			$oQB = Core_QueryBuilder::select(array('COUNT(*)', 'count'))
				->from($sTableName);

			$aRow = $oQB->execute()->asAssoc()->current();

			$return = '<div style="position: relative"><a href="' . $href . '" onclick="' . $onclick . '"><i class="fa fa-refresh" title="' . $aRow['count'] . '"></i></a>';

			if ($aRow['count'] == 0)
			{
				ob_start();

				Core_Html_Entity::factory('I')
					->class('fa fa-exclamation-triangle darkorange')
					->execute();

				$return .= ob_get_clean();
			}
			$return .= '</div>';
		}

		return $return;
	}

	/**
	 * The position of watermark on the X axis
	 * @return string
	 */
	public function getWatermarkDefaultPositionX()
	{
		return $this->watermark_default_position_x;
	}

	/**
	 * The position of watermark on the Y axis
	 * @return string
	 */
	public function getWatermarkDefaultPositionY()
	{
		return $this->watermark_default_position_y;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_currency_idBackend()
	{
		return $this->shop_currency_id
			? htmlspecialchars($this->Shop_Currency->sign)
			: '';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_currency_idBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->Shop_Currency->id == 0 && Core_Html_Entity::factory('I')
			->class('fa fa-exclamation-triangle darkorange')
			->execute();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop.onBeforeGetRelatedSite
	 * @hostcms-event shop.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}