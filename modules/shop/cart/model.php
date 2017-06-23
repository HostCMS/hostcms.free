<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Cart_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Cart_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'postpone' => 0,
		'quantity' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_item' => array(),
		'shop_warehouse' => array(),
		'siteuser' => array()
	);

	/**
	 * Get Shop_Cart by item $shop_item_id and site user $siteuser_id
	 * @param int $shop_item_id
	 * @param int $siteuser_id
	 * @param boolean $bCache
	 * @return Shop_Cart_Model|NULL
	 */
	public function getByShopItemIdAndSiteuserId($shop_item_id, $siteuser_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_item_id', '=', $shop_item_id)
			->where('siteuser_id', '=', $siteuser_id)
			->limit(1);

		$aShopCarts = $this->findAll($bCache);

		return isset($aShopCarts[0]) ? $aShopCarts[0] : NULL;
	}

	/**
	 * Get Shop_Cart by site user $siteuser_id
	 * @param int $siteuser_id
	 * @param boolean $bCache
	 * @return array
	 */
	public function getBySiteuserId($siteuser_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('siteuser_id', '=', $siteuser_id);

		return $this->findAll($bCache);
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Show properties in XML
	 * @param boolean $showXmlProperties
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE)
	{
		$this->_showXmlProperties = $showXmlProperties;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_cart.onBeforeRedeclaredGetXml
	 * @hostcms-event shop_cart.onBeforeAddShopItem
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$oShop_Item = $this->Shop_Item
			->clearEntities()
			->showXmlWarehousesItems(TRUE)
			->showXmlBonuses(TRUE)
			->showXmlProperties($this->_showXmlProperties)
			->cartQuantity($this->quantity);

		// Parent item for modification
		if ($this->Shop_Item->modification_id)
		{
			$oModification = Core_Entity::factory('Shop_Item')->find($this->Shop_Item->modification_id);
			!is_null($oModification->id) && $oShop_Item->addEntity(
				$oModification->showXmlProperties($this->_showXmlProperties)
			);
		}

		Core_Event::notify($this->_modelName . '.onBeforeAddShopItem', $this, array($oShop_Item));

		$this->clearXmlTags()
			->addEntity($oShop_Item);

		return parent::getXml();
	}
}