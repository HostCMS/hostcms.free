<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Item_Controller_Apply extends Admin_Form_Action_Controller_Type_Apply
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @hostcms-event Shop_Order_Item_Controller_Apply.onBeforeExecute
	 * @hostcms-event Shop_Order_Item_Controller_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		$oShop_Order = $this->_object->Shop_Order;

		$previuosObject = clone $this->_object;

		parent::execute($operation);

		if ($oShop_Order->posted)
		{
			$oShop_Order->posted = 0;
			$oShop_Order->post();
		}

		if ($previuosObject->shop_order_item_status_id != $this->_object->shop_order_item_status_id)
		{
			$this->_object->historyPushChangeItemStatus();
		}

		$oShop_Order->checkShopOrderItemStatuses();

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($this->_object));

		return $this;
	}
}