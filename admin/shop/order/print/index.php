<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization('shop');

$sAdminFormAction = '/admin/shop/order/card/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->setUp()
	->path($sAdminFormAction);

//ob_start();
$oShopOrder = Core_Entity::factory('Shop_Order', Core_Array::getGet('shop_order_id', 0));

if(CURRENT_SITE == $oShopOrder->Shop->site_id)
{
	// HostCMS v. 5
	if (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5)
	{
		$handler_path = CMS_FOLDER . "hostcmsfiles/shop/pay/handler" . $oShopOrder->shop_payment_system_id . '.php';

		if (file_exists($handler_path))
		{
			include($handler_path);
			$handler = new system_of_pay_handler();
			$handler->PrintOrder($oShopOrder->id);
		}
	}
	else
	{
		$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory($oShopOrder->Shop_Payment_System);

		$oShop_Payment_System_Handler
			->shopOrder($oShopOrder)
			->printInvoice();
	}
}

/*$oAdmin_Answer = Core_Skin::instance()->answer();

$oAdmin_Answer
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->message('')
	->skin(FALSE)
	->execute();*/