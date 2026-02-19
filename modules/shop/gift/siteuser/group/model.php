<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Gift_Siteuser_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
 class Shop_Gift_Siteuser_Group_Model extends Core_Entity
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
		'shop_gift' => array(),
		'siteuser_group' => array(),
	);

	/**
	 * Get object
	 * @param Shop_Gift_Model $oShop_Gift
	 * @param int $siteuser_group_id
	 * @return void
	 */
	public function getObject(Shop_Gift_Model $oShop_Gift, $siteuser_group_id)
	{
		$oShop_Gift_Siteuser_Groups = Core_Entity::factory('Shop_Gift_Siteuser_Group');
		$oShop_Gift_Siteuser_Groups->queryBuilder()
			->where('shop_gift_id', '=', $oShop_Gift->id)
			->where('siteuser_group_id', '=', $siteuser_group_id);

		return $oShop_Gift_Siteuser_Groups->getFirst();
	}
}