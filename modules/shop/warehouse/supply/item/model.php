<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Supply_Item_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Supply_Item_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_warehouse_supply_items.id' => 'ASC',
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_warehouse_supply' => array(),
		'shop_item' => array(),
	);

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_supply_item.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_supply_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}