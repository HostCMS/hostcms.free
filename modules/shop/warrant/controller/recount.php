<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warrant_Controller_Recount.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warrant_Controller_Recount extends Admin_Form_Action_Controller
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
			$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
			$windowId = $modalWindowId ? $modalWindowId : $this->_Admin_Form_Controller->getWindowId();

			$amount = $this->_object->getShopDocumentRelatedAmount();

			ob_start();

			Core_Html_Entity::factory('Script')
				->value('$(function() {
					$("#' . $windowId . ' input[name = amount]").val("' . $amount . '");
					$("#' . $windowId . ' .amount-alert").addClass("hidden");
				})')
				->execute();

			$this->addMessage(ob_get_clean());

			return TRUE;
		}
	}
}