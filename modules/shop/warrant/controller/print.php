<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warrant_Controller_Print
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Warrant_Controller_Print extends Printlayout_Controller_Print
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
				$shop_warrant_id = key($aChecked[0]);

				$oShop_Warrant = Core_Entity::factory('Shop_Warrant')->getById($shop_warrant_id);

				if (!is_null($oShop_Warrant))
				{
					$this->_oPrintlayout_Controller
						->replace($oShop_Warrant->getPrintlayoutReplaces())
						->driver($oPrintlayout_Driver)
						->entity($oShop_Warrant);
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