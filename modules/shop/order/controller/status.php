<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Controller_Status.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Status extends Admin_Form_Action_Controller
{

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */

	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			$shopOrderStatusId = Core_Array::getRequest('shopOrderStatusId');

			if (is_null($shopOrderStatusId))
			{
				throw new Core_Exception("shopOrderStatusId is NULL");
			}

			if ($shopOrderStatusId)
			{
				$oShop_Order_Status = Core_Entity::factory('Shop_Order_Status')->find(intval($shopOrderStatusId));

				if (!is_null($oShop_Order_Status->id))
				{
					$shopOrderStatusId = $oShop_Order_Status->id;
				}
				else
				{
					throw new Core_Exception("shopOrderStatusId is unknown");
				}
			}
			else
			{
				// Без статуса
				$shopOrderStatusId = 0;

				$sNewFromStatusName = Core::_('Shop_Order.notStatus');
			}

			$oShop_Order = $this->_object;
			$oShop_Order->shop_order_status_id = $shopOrderStatusId;
			$oShop_Order->save();

			return TRUE;
		}
	}
}