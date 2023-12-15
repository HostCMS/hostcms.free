<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop Module.
 *
 * Типы документов:
 * 0 - Shop_Warehouse_Inventory_Model
 * 1 - Shop_Warehouse_Incoming_Model
 * 2 - Shop_Warehouse_Writeoff_Model
 * 3 - Shop_Warehouse_Regrade_Model
 * 4 - Shop_Warehouse_Movement_Model
 * 5 - Shop_Order_Model
 * 6 - Shop_Warehouse_Purchaseorder_Model
 * 7 - Shop_Warehouse_Invoice_Model
 * 8 - Shop_Warehouse_Supply
 * 9 - Shop_Warehouse_Purchasereturn
 * 10 - Shop_Price_Setting_Model
 * 30 - Shop_Warrant_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2023-07-17';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'shop';

	/**
	 * Get List of Schedule Actions
	 * @return array
	 */
	public function getScheduleActions()
	{
		return array(
			0 => array(
				'name' => 'searchIndexItem',
				'entityCaption' => Core::_('Shop.searchIndexItem')
			),
			1 => array(
				'name' => 'searchIndexGroup',
				'entityCaption' => Core::_('Shop.searchIndexGroup')
			),
			2 => array(
				'name' => 'searchUnindexItem',
				'entityCaption' => Core::_('Shop.searchUnindexItem')
			),
			3 => array(
				'name' => 'recountShop',
				'entityCaption' => Core::_('Shop.shop_id')
			),
			4 => array(
				'name' => 'rebuildFastfilter',
				'entityCaption' => Core::_('Shop.shop_id')
			),
			5 => array(
				'name' => 'unsetApplyPurchaseDiscounts',
				'entityCaption' => Core::_('Shop.shop_id')
			),
			6 => array(
				'name' => 'setApplyPurchaseDiscounts',
				'entityCaption' => Core::_('Shop.shop_id')
			),
			7 => array(
				'name' => 'recountSets',
				'entityCaption' => Core::_('Shop.shop_id')
			),
			8 => array(
				'name' => 'updateCurrency',
				'entityCaption' => Core::_('Shop.updateCurrency')
			),
			9 => array(
				'name' => 'checkShopOrderStatusDeadline',
				'entityCaption' => Core::_('Shop.shop_id')
			)
		);
	}

	protected $_options = array(
		'itemEditWarehouseLimit' => array(
			'type' => 'int',
			'default' => 20
		),
		'smallImagePrefix' => array(
			'type' => 'string',
			'default' => 'small_'
		),
		'itemLargeImage' => array(
			'type' => 'string',
			'default' => 'item_%d.%s'
		),
		'itemSmallImage' => array(
			'type' => 'string',
			'default' => 'small_item_%d.%s'
		),
		'groupLargeImage' => array(
			'type' => 'string',
			'default' => 'group_%d.%s'
		),
		'groupSmallImage' => array(
			'type' => 'string',
			'default' => 'small_group_%d.%s'
		),
		'shop_item_card_xsl' => array(
			'type' => 'string',
			'default' => 'ЦенникиТоваров'
		),
	);

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 40,
				'block' => 0,
				'ico' => 'fa fa-shopping-cart',
				'name' => Core::_('Shop.menu'),
				'href' => "/admin/shop/index.php",
				'onclick' => "$.adminLoad({path: '/admin/shop/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Функция обратного вызова для поисковой индексации данных модуля
	 *
	 * @param $offset
	 * @param $limit
	 * @return array
	 */
	public function indexing($offset, $limit)
	{
		if (!isset($_SESSION['search_block']))
		{
			$_SESSION['search_block'] = 0;
		}

		$initialLimit = $limit;

		$aPages = array();

		$currentStepCount = 0;

		switch ($_SESSION['search_block'])
		{
			case 0:
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write("indexingShopGroups({$offset}, {$limit})");

				$aPages = $this->indexingShopGroups($offset, $limit);

				$currentStepCount = count($aPages);

				if ($currentStepCount < $initialLimit)
				{
					// Next block
					$_SESSION['search_block']++;
					$limit = $initialLimit - $currentStepCount;
					$offset = 0;
				}
				else
				{
					break;
				}

			case 1:
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write("indexingShopItems({$offset}, {$limit})");

				$aTmpResult = $this->indexingShopItems($offset, $limit);

				$aPages = array_merge($aPages, $aTmpResult);

				$count = count($aPages);

				if ($count < $initialLimit)
				{
					// Next block
					$_SESSION['search_block']++;
					$limit = $initialLimit - $count;
					$offset = 0;
				}
				else
				{
					$currentStepCount = count($aTmpResult);
					break;
				}

			case 2:
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write("indexingShopSellers({$offset}, {$limit})");

				$aTmpResult = $this->indexingShopSellers($offset, $limit);

				$aPages = array_merge($aPages, $aTmpResult);

				$count = count($aPages);

				if ($count < $initialLimit)
				{
					// Next block
					$_SESSION['search_block']++;
					$limit = $initialLimit - $count;
					$offset = 0;
				}
				else
				{
					$currentStepCount = count($aTmpResult);
					break;
				}

			case 3:
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write("indexingShopProducers({$offset}, {$limit})");

				$aTmpResult = $this->indexingShopProducers($offset, $limit);

				$aPages = array_merge($aPages, $aTmpResult);

				$count = count($aPages);

				if ($count < $initialLimit)
				{
					// Next block
					$_SESSION['search_block']++;
					$limit = $initialLimit - $count;
					$offset = 0;
				}
				else
				{
					$currentStepCount = count($aTmpResult);
					break;
				}

			case 4:
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write("indexingShopFilterSeos({$offset}, {$limit})");

				$aTmpResult = $this->indexingShopFilterSeos($offset, $limit);

				$currentStepCount = count($aTmpResult);

				$aPages = array_merge($aPages, $aTmpResult);
		}

		return array('pages' => $aPages, 'indexed' => $currentStepCount, 'finished' => count($aPages) < $initialLimit);
	}

	/**
	 * Индексация групп
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Shop_Module.indexingShopGroups
	 */
	public function indexingShopGroups($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oShopGroup = Core_Entity::factory('Shop_Group');
		$oShopGroup
			->queryBuilder()
			->straightJoin()
			->join('shops', 'shop_groups.shop_id', '=', 'shops.id')
			->join('structures', 'shops.structure_id', '=', 'structures.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('structures.shortcut_id', '=', 0)
			->where('shop_groups.indexing', '=', 1)
			->where('shop_groups.shortcut_id', '=', 0)
			->where('shop_groups.active', '=', 1)
			->where('shop_groups.deleted', '=', 0)
			->where('shops.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('shop_groups.id', 'DESC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingShopGroups', $this, array($oShopGroup));

		$aShopGroups = $oShopGroup->findAll(FALSE);

		$result = array();
		foreach ($aShopGroups as $oShopGroup)
		{
			$result[] = $oShopGroup->indexing();
		}

		return $result;
	}

	/**
	 * Индексация товаров
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Shop_Module.indexingShopItems
	 */
	public function indexingShopItems($offset, $limit)
	{
		$limit = intval($limit);
		$offset = intval($offset);

		$dateTime = Core_Date::timestamp2sql(time());

		$oShopItem = Core_Entity::factory('Shop_Item');

		$oShopItem
			->queryBuilder()
			->straightJoin()
			->join('shops', 'shop_items.shop_id', '=', 'shops.id')
			->join('structures', 'shops.structure_id', '=', 'structures.id')
			->leftJoin('shop_groups', 'shop_items.shop_group_id', '=', 'shop_groups.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('structures.shortcut_id', '=', 0)
			->where('shop_items.indexing', '=', 1)
			->where('shop_items.shortcut_id', '=', 0)
			->where('shop_items.active', '=', 1)
			->where('shop_items.deleted', '=', 0)
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
			->close()
			->setAnd()
			->open()
				->where('shop_groups.id', 'IS', NULL)
				->setOr()
				->where('shop_groups.active', '=', 1)
				->where('shop_groups.indexing', '=', 1)
			->close()
			->where('shops.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('shop_items.id', 'DESC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingShopItems', $this, array($oShopItem));

		$aShopItems = $oShopItem->findAll(FALSE);

		$result = array();

		foreach ($aShopItems as $oShopItem)
		{
			$result[] = $oShopItem->indexing();
		}

		return $result;
	}

	/**
	 * Индексация продавцов
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Shop_Module.indexingShopSellers
	 */
	public function indexingShopSellers($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oShop_Sellers = Core_Entity::factory('Shop_Seller');

		$oShop_Sellers
			->queryBuilder()
			->join('shops', 'shop_sellers.shop_id', '=', 'shops.id')
			->join('structures', 'shops.structure_id', '=', 'structures.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('structures.shortcut_id', '=', 0)
			->where('shop_sellers.deleted', '=', 0)
			->where('shops.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('shop_sellers.id')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingShopSellers', $this, array($oShop_Sellers));

		$aShop_Sellers = $oShop_Sellers->findAll(FALSE);

		$result = array();
		foreach ($aShop_Sellers as $oShop_Seller)
		{
			$result[] = $oShop_Seller->indexing();
		}

		return $result;
	}

	/**
	 * Индексация производителей
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Shop_Module.indexingShopProducers
	 */
	public function indexingShopProducers($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oShop_Producers = Core_Entity::factory('Shop_Producer');

		$oShop_Producers
			->queryBuilder()
			->join('shops', 'shop_producers.shop_id', '=', 'shops.id')
			->join('structures', 'shops.structure_id', '=', 'structures.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('structures.shortcut_id', '=', 0)
			->where('shop_producers.indexing', '=', 1)
			->where('shop_producers.active', '=', 1)
			->where('shop_producers.deleted', '=', 0)
			->where('shops.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('shop_producers.id')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingShopProducers', $this, array($oShop_Producers));

		$aShop_Producers = $oShop_Producers->findAll(FALSE);

		$result = array();
		foreach ($aShop_Producers as $oShop_Producer)
		{
			$result[] = $oShop_Producer->indexing();
		}

		return $result;
	}

	/**
	 * Индексация seo-фильтров
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Shop_Module.indexingShopFilterSeos
	 */
	public function indexingShopFilterSeos($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oShop_Filter_Seos = Core_Entity::factory('Shop_Filter_Seo');
		$oShop_Filter_Seos
			->queryBuilder()
			->join('shops', 'shop_filter_seos.shop_id', '=', 'shops.id')
			->where('shop_filter_seos.deleted', '=', 0)
			->where('shop_filter_seos.active', '=', 1)
			->where('shop_filter_seos.indexing', '=', 1)
			->where('shops.deleted', '=', 0)
			->clearOrderBy()
			->orderBy('shop_filter_seos.id')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingShopFilterSeos', $this, array($oShop_Filter_Seos));

		$aShop_Filter_Seos = $oShop_Filter_Seos->findAll(FALSE);

		$result = array();
		foreach ($aShop_Filter_Seos as $oShop_Filter_Seo)
		{
			$result[] = $oShop_Filter_Seo->indexing();
		}

		return $result;
	}

	/**
	 * Cache
	 * @var array
	 */
	protected $_cacheSearchCallbackGroupProperties = array();

	/**
	 * Search callback function
	 * @param Search_Page_Model $oSearch_Page
	 * @return self
	 * @hostcms-event Shop_Module.searchCallback
	 */
	public function searchCallback($oSearch_Page)
	{
		if ($oSearch_Page->module_value_id)
		{
			switch ($oSearch_Page->module_value_type)
			{
				case 1: // Группы
					$oShop_Group = Core_Entity::factory('Shop_Group')->find($oSearch_Page->module_value_id);

					Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oShop_Group));

					if (!is_null($oShop_Group->id))
					{
						$oSearch_Page->addEntity($oShop_Group);

						// Structure node
						if ($oShop_Group->Shop->structure_id)
						{
							$oSearch_Page->addEntity($oShop_Group->Shop->Structure);
						}
					}
				break;
				case 2: // Товары
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($oSearch_Page->module_value_id);

					if (!is_null($oShop_Item->id))
					{
						if ($oShop_Item->shop_group_id)
						{
							if (!isset($this->_cacheSearchCallbackGroupProperties[$oShop_Item->shop_group_id]))
							{
								$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop_Item->Shop->id);

								$showXmlProperties = array();

								$aProperties = $oShop_Item_Property_List->getPropertiesForGroup($oShop_Item->shop_group_id);
								foreach ($aProperties as $oProperty)
								{
									$showXmlProperties[] = $oProperty->id;
								}

								$this->_cacheSearchCallbackGroupProperties[$oShop_Item->shop_group_id] = count($showXmlProperties) ? $showXmlProperties : FALSE;
							}

							$showXmlProperties = $this->_cacheSearchCallbackGroupProperties[$oShop_Item->shop_group_id];
						}
						else
						{
							$showXmlProperties = TRUE;
						}

						$oShop_Item
							->showXmlComments(TRUE)
							->showXmlProperties($showXmlProperties)
							->showXmlSpecialprices(TRUE)
							->showXmlCommentsRating(TRUE);

						$oShop_Item->shop_group_id
							&& $oSearch_Page->addEntity($oShop_Item->Shop_Group);

						$oShop_Item->Shop->shop_currency_id
							&& $oSearch_Page->addEntity($oShop_Item->Shop->Shop_Currency);

						Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oShop_Item));

						$oSearch_Page->addEntity($oShop_Item);

						// Structure node
						if ($oShop_Item->Shop->structure_id)
						{
							$oSearch_Page->addEntity($oShop_Item->Shop->Structure);
						}
					}
				break;
				case 3: // Продавцы
					$oShop_Seller = Core_Entity::factory('Shop_Seller')->find($oSearch_Page->module_value_id);

					Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oShop_Seller));

					!is_null($oShop_Seller->id) && $oSearch_Page->addEntity($oShop_Seller);
				break;
				case 4: // SEO-фильтры
					$oShop_Filter_Seo = Core_Entity::factory('Shop_Filter_Seo')->find($oSearch_Page->module_value_id);

					Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oShop_Filter_Seo));

					!is_null($oShop_Filter_Seo->id) && $oSearch_Page->addEntity($oShop_Filter_Seo);
				break;
			}
		}

		return $this;
	}

	/**
	 * Backend search callback function
	 * @param Search_Page_Model $oSearch_Page
	 * @return array 'href' and 'onclick'
	 */
	public function backendSearchCallback($oSearch_Page)
	{
		$href = $onclick = NULL;

		$iAdmin_Form_Id = 65;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/admin/shop/item/index.php';

		if ($oSearch_Page->module_value_id)
		{
			switch ($oSearch_Page->module_value_type)
			{
				case 1: // Группы магазина
					$oShop_Group = Core_Entity::factory('Shop_Group')->find($oSearch_Page->module_value_id);

					if (!is_null($oShop_Group->id))
					{
						$additionalParams = "shop_id={$oShop_Group->Shop->id}&shop_group_id={$oShop_Group->id}";
						$href = $oAdmin_Form_Controller->getAdminLoadHref($sPath, NULL, NULL, $additionalParams);
						$onclick = $oAdmin_Form_Controller->getAdminLoadAjax($sPath, NULL, NULL, $additionalParams);
					}
				break;
				case 2: // Товары магазина
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($oSearch_Page->module_value_id);

					if (!is_null($oShop_Item->id))
					{
						$additionalParams = "shop_id={$oShop_Item->Shop->id}&shop_group_id={$oShop_Item->shop_group_id}";
						$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 1, $oShop_Item->id, $additionalParams);
						$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 1, $oShop_Item->id, $additionalParams);
					}
				break;
			}
		}

		return array(
			'icon' => 'fa-shopping-cart',
			'href' => $href,
			'onclick' => $onclick
		);
	}

	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
		if ($entityId)
		{
			switch ($action)
			{
				// Index item
				case 0:
					Core_Entity::factory('Shop_Item', $entityId)->index()->clearCache();
				break;
				// Index group
				case 1:
					Core_Entity::factory('Shop_Group', $entityId)->index()->clearCache();
				break;
				// Unindex item
				case 2:
					Core_Entity::factory('Shop_Item', $entityId)->unindex()->clearCache();
				break;
				// Recount shop
				case 3:
					if (!$entityId)
					{
						throw new Core_Exception('callSchedule:: entityId expected as shop_id, fill in the form', array(), 0, FALSE);
					}

					Core_Entity::factory('Shop', $entityId)->recount();
				break;
				// Rebuild fastfilter
				case 4:
					$oShop = Core_Entity::factory('Shop', $entityId);

					// Groups
					$Shop_Filter_Group_Controller = new Shop_Filter_Group_Controller($oShop);
					$Shop_Filter_Group_Controller
						->dropTable()
						->createTable()
						->rebuild();

					// Items
					$oShop_Filter_Controller = new Shop_Filter_Controller($oShop);
					$oShop_Filter_Controller
						->dropTable()
						->createTable();

					$limit = 1000;
					$position = 0;

					do {
						$oShop_Items = $oShop->Shop_Items;
						$oShop_Items->queryBuilder()
							->where('shop_items.active', '=', 1)
							->limit($limit)
							->offset($position)
							->clearOrderBy()
							->orderBy('shop_items.id');

						$aShop_Items = $oShop_Items->findAll(FALSE);

						foreach ($aShop_Items as $oShop_Item)
						{
							$oShop_Filter_Controller->fill($oShop_Item);
						}

						$position += $limit;
					}
					while(count($aShop_Items));
				break;
				case 5:
					// Не применять скидки от суммы заказа и карты к товарам со скидками

					$dayFieldName = 'day' . date('N');
					$time = time();
					$sDatetime = Core_Date::timestamp2sql(time());

					Core_QueryBuilder::update('shop_items')
						->set('apply_purchase_discount', 0)
						->join('shop_item_discounts', 'shop_item_discounts.shop_item_id', '=', 'shop_items.id')
						->join('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id')
						->where('shop_items.shop_id', '=', $entityId)
						->where('shop_items.deleted', '=', 0)
						->where('shop_discounts.active', '=', 1)
						->where('shop_discounts.deleted', '=', 0)
						->where('shop_discounts.start_datetime', '<=', $sDatetime)
						->where('shop_discounts.end_datetime', '>=', $sDatetime)
						->where('shop_discounts.start_time', '<=', date('H:i:s', $time))
						->where('shop_discounts.end_time', '>=', date('H:i:s', $time))
						->where('shop_discounts.' . $dayFieldName, '=', 1)
						->execute();
				break;
				case 6:
					// Применять скидки от суммы заказа и карты к товарам без скидкок

					$dayFieldName = 'day' . date('N');
					$time = time();
					$sDatetime = Core_Date::timestamp2sql(time());

					Core_QueryBuilder::update('shop_items')
						->set('apply_purchase_discount', 1)
						->leftJoin('shop_item_discounts', 'shop_item_discounts.shop_item_id', '=', 'shop_items.id')
						->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id',
						array(
							array('AND' => array('shop_discounts.active', '=', 1)),
							array('AND' => array('shop_discounts.deleted', '=', 0)),
							array('AND' => array('shop_discounts.start_datetime', '<=', $sDatetime)),
							array('AND' => array('shop_discounts.end_datetime', '>=', $sDatetime)),
							array('AND' => array('shop_discounts.start_time', '<=', date('H:i:s', $time))),
							array('AND' => array('shop_discounts.end_time', '>=', date('H:i:s', $time))),
							array('AND' => array('shop_discounts.' . $dayFieldName, '=', 1))
						))
						->where('shop_items.shop_id', '=', $entityId)
						->where('shop_items.deleted', '=', 0)
						->where('shop_discounts.id', 'IS', NULL)
						->execute();
				break;
				// Recount sets
				case 7:
					Core_Entity::factory('Shop', $entityId)->recountSets();
				break;
				// update currencies
				case 8:
					$oShop_Currency_Driver = Shop_Currency_Driver::instance($entityId);
					$oShop_Currency_Driver->execute();
				break;
				case 9:
					$oShop = Core_Entity::factory('Shop', $entityId);

					$limit = 1000;
					$position = 0;

					do {
						$oShop_Orders = $oShop->Shop_Orders;
						$oShop_Orders->queryBuilder()
							->where('shop_orders.shop_order_status_deadline', '!=', '0000-00-00 00:00:00')
							->limit($limit)
							->offset($position)
							->clearOrderBy()
							->orderBy('shop_orders.id');

						$aShop_Orders = $oShop_Orders->findAll(FALSE);

						foreach ($aShop_Orders as $oShop_Order)
						{
							$oShop_Order->checkShopOrderStatusDeadline();
						}

						$position += $limit;
					}
					while(count($aShop_Orders));
				break;
			}
		}
	}

	/**
	 * Get Notification Design
	 * @param int $type
	 * @param int $entityId
	 * @return array
	 */
	public function getNotificationDesign($type, $entityId)
	{
		// Идентификатор формы "Оформленные заказы"
		$iAdmin_Form_Id = 75;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

		// Контроллер формы
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
		$oAdmin_Form_Controller
			->path('/admin/shop/order/index.php')
			->window('id_content');

		switch ($type)
		{
			case 1: // Новый заказ
				$sIconIco = "fa-shopping-basket";
				$sIconColor = "white";
				$sBackgroundColor = "bg-azure";
				$sNotificationColor = 'azure';
			break;
			case 2: // Оплата
				$sIconIco = "fa-money";
				$sIconColor = "white";
				$sBackgroundColor = "bg-palegreen";
				$sNotificationColor = 'palegreen';
			break;
			default:
				$sIconIco = "fa-info";
				$sIconColor = "white";
				$sBackgroundColor = "bg-themeprimary";
				$sNotificationColor = 'info';
		}

		$oShop = Core_Entity::factory('Shop_Order', $entityId)->Shop;

		return array(
			'icon' => array(
				'ico' => "fa {$sIconIco}",
				'color' => $sIconColor,
				'background-color' => $sBackgroundColor
			),
			'notification' => array(
				'ico' => $sIconIco,
				'background-color' => $sNotificationColor
			),
			'href' => $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, $entityId, "shop_id={$oShop->id}"),
			'onclick' => $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, $entityId, "shop_id={$oShop->id}"),
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			),
			'site' => htmlspecialchars($oShop->Site->name) . ' [' . $oShop->Site->id . ']'
		);
	}

	/**
	 * Get Module's Printlayouts
	 * @return array
	 */
	public function getPrintlayouts()
	{
		return array(
			array(
				'dir' => Core::_('Shop_Order.orders'),
				'items' => array(
					5 => array('name' => Core::_('Shop_Order.shops_link_order'))
				)
			),
			array(
				'dir' => Core::_('Shop_Warehouse.warehouse'),
				'items' => array(
					0 => array('name' => Core::_('Shop_Warehouse_Inventory.title')),
					1 => array('name' => Core::_('Shop_Warehouse_Incoming.title')),
					2 => array('name' => Core::_('Shop_Warehouse_Writeoff.title')),
					3 => array('name' => Core::_('Shop_Warehouse_Regrade.title')),
					4 => array('name' => Core::_('Shop_Warehouse_Movement.title')),
					6 => array('name' => Core::_('Shop_Warehouse_Purchaseorder.title')),
					7 => array('name' => Core::_('Shop_Warehouse_Invoice.title')),
					8 => array('name' => Core::_('Shop_Warehouse_Supply.title')),
					9 => array('name' => Core::_('Shop_Warehouse_Purchasereturn.title')),
				)
			),
			array(
				'dir' => Core::_('Shop_Warrant.title'),
				'items' => array(
					30 => array('name' => Core::_('Shop_Warrant.type0')),
					31 => array('name' => Core::_('Shop_Warrant.type1')),
					32 => array('name' => Core::_('Shop_Warrant.type2')),
					33 => array('name' => Core::_('Shop_Warrant.type3')),
				)
			),
			array(
				'dir' => Core::_('Shop_Price.show_prices_title'),
				'items' => array(
					10 => array('name' => Core::_('Shop_Price_Setting.title')),
				)
			),
			array(
				'dir' => Core::_('Shop_Item.model_name'),
				'items' => array(
					60 => array('name' => Core::_('Shop_Item.item_cards')),
				)
			)
		);
	}

	/**
	 * Report tabs array
	 * @var array
	 */
	protected $_reports = array(
		'ordersCost' => array('Shop_Report_Controller', 'ordersCost'),
		'ordersPaid' => array('Shop_Report_Controller', 'ordersPaid'),
		'popularItems' => array('Shop_Report_Controller', 'popularItems'),
		'popularProducers' => array('Shop_Report_Controller', 'popularProducers')
	);

	/**
	 * Module's webhooks
	 * @var array
	 */
	protected $_webhooks = array(
		'onShopOrderPaid', 'onShopOrderCancelPaid', 'onShopOrderCanceled', 'onShopOrderUncanceled', 'onShopOrderChangeStatus'
	);
}