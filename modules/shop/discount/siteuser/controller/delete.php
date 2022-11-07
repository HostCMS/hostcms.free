<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Siteuser_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discount_Siteuser_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		if ($this->_object->id && $this->_object->siteuser_id)
		{
			Core_QueryBuilder::delete('shop_item_discounts')
				->where('shop_item_discounts.siteuser_id', '=', $this->_object->siteuser_id)
				->execute();

			return $this;
		}
	}
}