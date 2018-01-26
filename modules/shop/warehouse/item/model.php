<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Item_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Item_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
		'shop_warehouse' => array(),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'user_id',
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_warehouse_item.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$oShop_Item_Reserveds = Core_Entity::factory('Shop_Item_Reserved');
		$oShop_Item_Reserveds->queryBuilder()
			->where('shop_item_id', '=', $this->shop_item_id)
			->where('shop_warehouse_id', '=', $this->shop_warehouse_id)
			->where('datetime', '>', Core_Date::timestamp2sql(time() - $this->Shop_Item->Shop->reserve_hours * 60 * 60));

		$aShop_Item_Reserveds = $oShop_Item_Reserveds->findAll();

		$reserved = 0;
		foreach ($aShop_Item_Reserveds as $oShop_Item_Reserved)
		{
			$reserved += $oShop_Item_Reserved->count;
		}

		$this
			->clearXmlTags()
			->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('reserved')
					->value($reserved)
			);

		return parent::getXml();
	}

	/**
	 * Get item count by item ID
	 * @param int $shop_item_id item ID
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getByShopItemId($shop_item_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_item_id', '=', $shop_item_id)
			->limit(1);

		$aShop_Warehouse_Items = $this->findAll($bCache);

		return isset($aShop_Warehouse_Items[0])
			? $aShop_Warehouse_Items[0]
			: NULL;
	}

	/**
	 * Get item count by warehouse ID
	 * @param int $shop_warehouse_id warehouse ID
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getByWarehouseId($shop_warehouse_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_warehouse_id', '=', $shop_warehouse_id)
			->limit(1);

		$aShop_Warehouse_Items = $this->findAll($bCache);

		return isset($aShop_Warehouse_Items[0])
			? $aShop_Warehouse_Items[0]
			: NULL;
	}
}