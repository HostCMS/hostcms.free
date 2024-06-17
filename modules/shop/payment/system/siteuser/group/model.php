<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Payment_System_Siteuser_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
 class Shop_Payment_System_Siteuser_Group_Model extends Core_Entity
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
		'shop_payment_system' => array(),
		'siteuser_group' => array(),
	);

	public function getObject(Shop_Payment_System_Model $oShop_Payment_System, $siteuser_group_id)
	{
		$oShop_Payment_System_Siteuser_Groups = Core_Entity::factory('Shop_Payment_System_Siteuser_Group');
		$oShop_Payment_System_Siteuser_Groups->queryBuilder()
			->where('shop_payment_system_id', '=', $oShop_Payment_System->id)
			->where('siteuser_group_id', '=', $siteuser_group_id);

		return $oShop_Payment_System_Siteuser_Groups->getFirst();
	}
}