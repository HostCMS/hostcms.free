<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop Module.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-03-02';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'shop';

	/**
	 * List of Schedule Actions
	 * @var array
	 */
	protected $_scheduleActions = array(
		0 => 'searchIndexItem',
		1 => 'searchIndexGroup',
		2 => 'searchUnindexItem',
		3 => 'recountShop',
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
		/**
		 * $_SESSION['search_block'] - номер блока индексации
		 * $_SESSION['last_limit'] - количество проиндексирвоанных последним блоком
		 */
		if (!isset($_SESSION['search_block']))
		{
			$_SESSION['search_block'] = 0;
		}

		if (!isset($_SESSION['last_limit']))
		{
			$_SESSION['last_limit'] = 0;
		}

		$limit_orig = $limit;

		$result = array();

		switch ($_SESSION['search_block'])
		{
			case 0:
				$aTmpResult = $this->indexingShopGroups($offset, $limit);

				$_SESSION['last_limit'] = count($aTmpResult);

				$result = array_merge($result, $aTmpResult);
				$count = count($result);

				if ($count < $limit_orig)
				{
					$_SESSION['search_block']++;
					$limit = $limit_orig - $count;
					$offset = 0;
				}
				else
				{
					return $result;
				}

			case 1:
				// Следующая индексация
				$aTmpResult = $this->indexingShopItems($offset, $limit);

				$_SESSION['last_limit'] = count($aTmpResult);

				$result = array_merge($result, $aTmpResult);
				$count = count($result);

				// Закончена индексация
				if ($count < $limit_orig)
				{
					$_SESSION['search_block']++;
					$limit = $limit_orig - $count;
					$offset = 0;
				}
				else
				{
					return $result;
				}

			case 2:
				// Следующая индексация
				$aTmpResult = $this->indexingShopSellers($offset, $limit);

				$_SESSION['last_limit'] = count($aTmpResult);

				$result = array_merge($result, $aTmpResult);
				$count = count($result);

				// Закончена индексация
				if ($count < $limit_orig)
				{
					$_SESSION['search_block']++;
					$limit = $limit_orig - $count;
					$offset = 0;
				}
				else
				{
					return $result;
				}
		}

		// По окончанию индексации сбрасываем сессии в 0
		$_SESSION['search_block'] = 0;

		return $result;
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
			->where('shop_groups.indexing', '=', 1)
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

		$oShopSeller = Core_Entity::factory('Shop_Seller');

		$oShopSeller
			->queryBuilder()
			->join('shops', 'shop_sellers.shop_id', '=', 'shops.id')
			->join('structures', 'shops.structure_id', '=', 'structures.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('shop_sellers.deleted', '=', 0)
			->where('shops.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('shop_sellers.id')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingShopSellers', $this, array($oShopSeller));

		$aShopSellers = $oShopSeller->findAll();

		$result = array();
		foreach ($aShopSellers as $oShopSeller)
		{
			$result[] = $oShopSeller->indexing();
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

					!is_null($oShop_Group->id) && $oSearch_Page->addEntity($oShop_Group);
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
							->showXmlSpecialprices(TRUE);

						$oShop_Item->shop_group_id
							&& $oSearch_Page->addEntity($oShop_Item->Shop_Group);

						Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oShop_Item));

						$oSearch_Page->addEntity($oShop_Item);
					}
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
					Core_Entity::factory('Shop', $entityId)->recount();
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
			)
		);
	}
}