<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Movement_Controller_Print
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Movement_Controller_Print extends Printlayout_Controller_Print
{
	protected function _prepare()
	{
		$oPrintlayout = Core_Entity::factory('Printlayout')->getById($this->printlayout);

		$this->_oPrintlayout_Controller = new Printlayout_Controller($oPrintlayout);

		if (!is_null($oPrintlayout))
		{
			$driver_id = Core_Array::getPost('driver_id');

			$oPrintlayout_Driver = Core_Entity::factory('Printlayout_Driver', $driver_id);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			if (isset($aChecked[0]))
			{
				$shop_warehouse_movement_id = key($aChecked[0]);

				$oShop_Warehouse_Movement = Core_Entity::factory('Shop_Warehouse_Movement')->getById($shop_warehouse_movement_id);

				if (!is_null($oShop_Warehouse_Movement))
				{
					$this->_oPrintlayout_Controller = new Printlayout_Controller($oPrintlayout);
					$this->_oPrintlayout_Controller
						->replace($oShop_Warehouse_Movement->getPrintlayoutReplaces())
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warehouse_Movement);
				}
			}
		}

		return $this;
	}

	protected function _print()
	{
		$this->_oPrintlayout_Controller->execute()->downloadFile();

		exit();
	}
}