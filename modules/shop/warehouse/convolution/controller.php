<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Convolution_Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Warehouse_Convolution_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shopId',
		'limit',
		'shop_warehouse_id',
		'date',
		'position',
		'timeout',
	);

	protected $_aShop_Warehouse_InventoryIDs = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->position = 0;
		$this->timeout = 30;
	}

	protected $_currentShop_Warehouse_Inventory = NULL;
	protected $_currentShop_Warehouse_InventoryId = NULL;
	protected $_counter = 0;

	protected function _getShop_Warehouse_Inventory()
	{
		if (is_null($this->_currentShop_Warehouse_Inventory) || $this->_counter > $this->limit)
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory');
			$oShop_Warehouse_Inventory->shop_warehouse_id = $this->shop_warehouse_id;
			$oShop_Warehouse_Inventory->number = '';
			$oShop_Warehouse_Inventory->description = Core::_('Shop_Warehouse_Convolution.description', $this->date);
			$oShop_Warehouse_Inventory->datetime = Core_Date::date2sql($this->date);
			$oShop_Warehouse_Inventory->posted = 0;
			$oShop_Warehouse_Inventory->save();

			$oShop_Warehouse_Inventory->number = $oShop_Warehouse_Inventory->id;
			$oShop_Warehouse_Inventory->save();

			$this->_currentShop_Warehouse_Inventory = $oShop_Warehouse_Inventory;
			$this->_aShop_Warehouse_InventoryIDs[] = $this->_currentShop_Warehouse_InventoryId = $oShop_Warehouse_Inventory->id;
			$this->_counter = 0;
		}

		$this->_counter++;

		return $this->_currentShop_Warehouse_Inventory;
	}

	/**
	 * Executes the business logic.
	 * @return array
	 */
	public function execute()
	{
		if ($this->shop_warehouse_id > 0)
		{
			$timeout = Core::getmicrotime();

			$sDate = Core_Date::date2sql($this->date);

			$oShop = Core_Entity::factory('Shop', $this->shopId);

			$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse', $this->shop_warehouse_id);

			$oShop_Items = $oShop->Shop_Items;
			$oShop_Items
				->queryBuilder()
				->where('shop_items.shortcut_id', '=', 0)
				->limit($this->limit)
				->offset($this->position)
				->clearOrderBy()
				->orderBy('id', 'ASC');

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				$oShop_Warehouse_Item = $oShop_Warehouse->Shop_Warehouse_Items->getByShopItemId($oShop_Item->id, FALSE);
				if (!is_null($oShop_Warehouse_Item))
				{
					$oShop_Warehouse_Inventory = $this->_getShop_Warehouse_Inventory();

					$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
					$oShop_Warehouse_Inventory_Item->shop_warehouse_inventory_id = $oShop_Warehouse_Inventory->id;
					$oShop_Warehouse_Inventory_Item->shop_item_id = $oShop_Item->id;
					$oShop_Warehouse_Inventory_Item->count = $oShop_Warehouse_Item->count;
					$oShop_Warehouse_Inventory_Item->save();
				}

				$this->position++;

				if (Core::getmicrotime() - $timeout + 3 > $this->timeout)
				{
					break;
				}
			}

			if (count($aShop_Items) == 0)
			{
				return 'finish';
			}
		}
		else
		{
			return 'error';
		}

		return 'continue';
	}

	public function postNext()
	{
		if (count($this->_aShop_Warehouse_InventoryIDs))
		{
			$id = array_shift($this->_aShop_Warehouse_InventoryIDs);
			Core_Entity::factory('Shop_Warehouse_Inventory', $id)->post();

			return TRUE;
		}

		// Инвентаризация
		Core_QueryBuilder::update('shop_warehouse_inventories')
			->set('deleted', 1)
			->where('shop_warehouse_id', '=', $this->shop_warehouse_id)
			->where('deleted', '=', 0)
			->where('datetime', '<', Core_Date::date2sql($this->date))
			->execute();

		// Оприходование
		Core_QueryBuilder::update('shop_warehouse_incomings')
			->set('deleted', 1)
			->where('shop_warehouse_id', '=', $this->shop_warehouse_id)
			->where('deleted', '=', 0)
			->where('datetime', '<', Core_Date::date2sql($this->date))
			->execute();

		// Списание
		Core_QueryBuilder::update('shop_warehouse_writeoffs')
			->set('deleted', 1)
			->where('shop_warehouse_id', '=', $this->shop_warehouse_id)
			->where('deleted', '=', 0)
			->where('datetime', '<', Core_Date::date2sql($this->date))
			->execute();

		// Пересортица
		Core_QueryBuilder::update('shop_warehouse_regrades')
			->set('deleted', 1)
			->where('shop_warehouse_id', '=', $this->shop_warehouse_id)
			->where('deleted', '=', 0)
			->where('datetime', '<', Core_Date::date2sql($this->date))
			->execute();

		// Перемещение
		/*Core_QueryBuilder::update('shop_warehouse_movements')
			->set('deleted', 1)
			->open()
				->where('source_shop_warehouse_id', '=', $this->shop_warehouse_id)
				->setOr()
				->where('destination_shop_warehouse_id', '=', $this->shop_warehouse_id)
			->close()
			->where('deleted', '=', 0)
			->where('datetime', '<', Core_Date::date2sql($this->date))
			->execute();*/

		return FALSE;
	}

	/**
	 * Execute some routine before serialization
	 * @return array
	 */
	public function __sleep()
	{
		$this->_currentShop_Warehouse_Inventory = NULL;

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

		if ($this->_currentShop_Warehouse_InventoryId)
		{
			$this->_currentShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory', $this->_currentShop_Warehouse_InventoryId);
		}

		return $this;
	}
}