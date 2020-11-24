<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Apply extends Admin_Form_Action_Controller_Type_Apply
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @hostcms-event Shop_Order_Controller_Apply.onBeforeExecute
	 * @hostcms-event Shop_Order_Controller_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		$oBefore = clone $this->_object;

		parent::execute($operation);

		if ($this->_object->shop_payment_system_id)
		{
			$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory(
				Core_Entity::factory('Shop_Payment_System', $this->_object->shop_payment_system_id)
			);

			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->shopOrder($this->_object)
					->shopOrderBeforeAction($oBefore)
					->changedOrder('apply');
			}
		}

		if ($this->_object->shop_order_status_id)
		{
			$this->_object->status_datetime = Core_Date::timestamp2sql(time());
			$this->_object->save();

			$this->_object->historyPushChangeStatus();
			$this->_object->notifyBotsChangeStatus();
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($this->_object));

		return $this;
	}
}