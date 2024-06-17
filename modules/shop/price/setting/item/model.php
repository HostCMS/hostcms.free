<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Setting_Item_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Price_Setting_Item_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_price_setting' => array(),
		'shop_price' => array(),
		'shop_item' => array()
	);

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
		'shop_price_setting_items.shop_price_id' => 'ASC',
	);

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_price_setting_item.onBeforeGetRelatedSite
	 * @hostcms-event shop_price_setting_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}