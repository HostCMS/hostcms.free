<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Price_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Price_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
		'shop_price' => array()
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Get value of price for item by ID of price
	 * @param int $shop_price_id prive id
	 * @param boolean $bCache cache mode
	 * @return Shop_Item_Price_Model|NULL
	 */
	public function getByPriceId($shop_price_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_price_id', '=', $shop_price_id)
			->limit(1);

		$aShop_Item_Prices = $this->findAll($bCache);

		return isset($aShop_Item_Prices[0])
			? $aShop_Item_Prices[0]
			: NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item_price.onBeforeGetRelatedSite
	 * @hostcms-event shop_item_price.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}