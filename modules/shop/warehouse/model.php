<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Model extends Core_Entity
{
	/**
	 * Callback property_id
	 * @var int
	 */
	public $items = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item' => array('through' => 'shop_warehouse_item'),
		'shop_warehouse_item' => array(),
		'shop_item_reserved' => array(),
		'shop_cart' => array(),
		'shop_warehouse_entry' => array(),
		'shop_warehouse_inventory' => array(),
		'shop_warehouse_incoming' => array(),
		'shop_warehouse_writeoff' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_country' => array(),
		'shop_country_location' => array(),
		'shop_country_location_city' => array(),
		'shop_country_location_city_area' => array(),
		'user' => array(),
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Change status of activity for warehouse
	 * @return self
	 */
	public function changeStatus()
	{
		if ($this->active)
		{
			if ($this->default)
			{
				throw new Core_Exception(Core::_('Shop_Warehouse.default_change_active_error'),
					array(), 0, $bShowDebugTrace = FALSE
				);
			}
			else
			{
				$this->active = 0;
			}
		}
		else
		{
			$this->active = 1;
		}

		return $this->save();
	}

	/**
	 * Switch default status
	 * @return self
	 */
	public function changeDefaultStatus()
	{
		$this->save();

		$oShop_Warehouses = $this->Shop->Shop_Warehouses;
		$oShop_Warehouses
			->queryBuilder()
			->where('shop_warehouses.default', '=', 1);

		$aShop_Warehouses = $oShop_Warehouses->findAll();

		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$oShop_Warehouse->default = 0;
			$oShop_Warehouse->update();
		}

		$this->default = 1;
		$this->active = 1;
		return $this->save();
	}

	/**
	 * Get default warehouse
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_warehouses.default', '=', 1)
			->limit(1);

		$aShop_Warehouses = $this->findAll($bCache);

		return isset($aShop_Warehouses[0])
			? $aShop_Warehouses[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_warehouse.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Carts->deleteAll(FALSE);
		$this->Shop_Warehouse_Items->deleteAll(FALSE);

		// Удаляем связи с зарезервированными, прямая связь
		$this->Shop_Item_Reserveds->deleteAll(FALSE);

		$this->Shop_Warehouse_Entries->deleteAll(FALSE);
		$this->Shop_Warehouse_Inventories->deleteAll(FALSE);
		$this->Shop_Warehouse_Incomings->deleteAll(FALSE);
		$this->Shop_Warehouse_Writeoffs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$queryBuilder = Core_QueryBuilder::select(array('SUM(count)', 'count'))
			->from('shop_warehouse_items')
			->where('shop_warehouse_items.shop_warehouse_id', '=', $this->id);

		$aResult = $queryBuilder->execute()->asAssoc()->current();

		$aResult['count'] && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value($aResult['count'])
			->title(Core::_('Shop_Warehouse.shop_items_count'))
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function itemsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Warehouse_Items->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value($count)
			->title($count)
			->execute();
	}

	/**
	 * Get rest
	 * @param $shop_item_id shop item id
	 * @param $dateTo date
	 * @return float
	 */
	public function getRest($shop_item_id, $dateTo = NULL)
	{
		$count = 0;

		$oShop_Warehouse_Entries = $this->Shop_Warehouse_Entries;
		$oShop_Warehouse_Entries->queryBuilder()
			->where('shop_warehouse_entries.shop_item_id', '=', $shop_item_id);

		if (!is_null($dateTo))
		{
			$oShop_Warehouse_Entries->queryBuilder()
				->where('shop_warehouse_entries.datetime', '<=', $dateTo);
		}

		$aShop_Warehouse_Entries = $oShop_Warehouse_Entries->findAll();

		foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
		{
			$type = $oShop_Warehouse_Entry->getDocumentType();

			if (!is_null($type))
			{
				switch ($type)
				{
					// Инвентаризация. Сброс к значению при инвентаризации.
					case 0:
						$count = $oShop_Warehouse_Entry->value;
					break;
					// Приход
					case 1:
						$count += $oShop_Warehouse_Entry->value;
					break;
					// Списание
					case 2:
						$count -= $oShop_Warehouse_Entry->value;
					break;
					// Пересортица
					case 3:
						// У списываемого товара value будет отрицательным
						$count += $oShop_Warehouse_Entry->value;
					break;
				}
			}
		}

		return floatval($count);
	}

	/**
	 * Set rest
	 * @param $shop_item_id shop item id
	 * @param $value value
	 * @return self
	 */
	public function setRest($shop_item_id, $value)
	{
		$oShop_Warehouse_Item = $this->Shop_Warehouse_Items->getByShop_item_id($shop_item_id);

		if (is_null($oShop_Warehouse_Item))
		{
			$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item');
			$oShop_Warehouse_Item->shop_warehouse_id = $this->id;
			$oShop_Warehouse_Item->shop_item_id = $shop_item_id;
		}

		$oShop_Warehouse_Item->count = $value;
		$oShop_Warehouse_Item->save();
	}
}