<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery_Siteuser_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
 class Shop_Delivery_Siteuser_Group_Model extends Core_Entity
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
		'shop_delivery' => array(),
		'siteuser_group' => array(),
	);

	public function getObject(Shop_Delivery_Model $oShop_Delivery, $siteuser_group_id)
	{
		$oShop_Delivery_Siteuser_Groups = Core_Entity::factory('Shop_Delivery_Siteuser_Group');
		$oShop_Delivery_Siteuser_Groups->queryBuilder()
			->where('shop_delivery_id', '=', $oShop_Delivery->id)
			->where('siteuser_group_id', '=', $siteuser_group_id);

		return $oShop_Delivery_Siteuser_Groups->getFirst();
	}
}