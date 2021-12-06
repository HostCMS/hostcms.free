<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Siteuser_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
 class Shop_Discount_Siteuser_Group_Model extends Core_Entity
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
		'shop_discount' => array(),
		'siteuser_group' => array(),
	);

	public function getObject(Shop_Discount_Model $oShop_Discount, $siteuser_group_id)
	{
		$oShop_Discount_Siteuser_Groups = Core_Entity::factory('Shop_Discount_Siteuser_Group');
		$oShop_Discount_Siteuser_Groups->queryBuilder()
			->where('shop_discount_id', '=', $oShop_Discount->id)
			->where('siteuser_group_id', '=', $siteuser_group_id);

		return $oShop_Discount_Siteuser_Groups->getFirst();
	}
}