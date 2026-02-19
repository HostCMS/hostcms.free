<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount_Siteuser_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
 class Shop_Purchase_Discount_Siteuser_Group_Model extends Core_Entity
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
		'shop_purchase_discount' => array(),
		'siteuser_group' => array(),
	);

	/**
	 * Get object
	 * @param Shop_Purchase_Discount_Model $oShop_Purchase_Discount
	 * @param int $siteuser_group_id
	 * @return void
	 */
	public function getObject(Shop_Purchase_Discount_Model $oShop_Purchase_Discount, $siteuser_group_id)
	{
		$oShop_Purchase_Discount_Siteuser_Groups = Core_Entity::factory('Shop_Purchase_Discount_Siteuser_Group');
		$oShop_Purchase_Discount_Siteuser_Groups->queryBuilder()
			->where('shop_purchase_discount_id', '=', $oShop_Purchase_Discount->id)
			->where('siteuser_group_id', '=', $siteuser_group_id);

		return $oShop_Purchase_Discount_Siteuser_Groups->getFirst();
	}
}