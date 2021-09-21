<?php

if (!Core::moduleIsActive('siteuser'))
{
	?>
	<h1>Клиенты</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="https://www.hostcms.ru/hostcms/modules/siteusers/">Клиенты</a>&raquo; доступен в редакциях &laquo;<a href="https://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="https://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return ;
}

$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

if (is_null($oSiteuser))
{
	?><h1>Вы не авторизованы!</h1>
	<p>Для просмотра заказов необходимо авторизироваться.</p>
	<?php
	return ;
}

$shop_id = intval(
	Core_Array::end(explode('/', trim(Core::$url['path'], '/')))
);

$oShop = Core_Entity::factory('Shop', $shop_id);

if ($oShop->site_id != $oSiteuser->site_id)
{
	throw new Core_Exception('Wrong shop. Access denied');
}

$amount = Core_Array::getPost('amount', 0, 'int');

if (Core_Array::getPost('apply') && $amount > 0)
{
	$oShop_Order = Core_Entity::factory('Shop_Order');
	$oShop_Order->shop_country_id = Core_Array::getPost('shop_country_id', 0, 'int');
	$oShop_Order->shop_country_location_id = Core_Array::getPost('shop_country_location_id', 0, 'int');
	$oShop_Order->shop_country_location_city_id = Core_Array::getPost('shop_country_location_city_id', 0, 'int');
	$oShop_Order->shop_country_location_city_area_id = Core_Array::getPost('shop_country_location_city_area_id', 0, 'int');
	$oShop_Order->postcode = Core_Str::stripTags(Core_Array::getPost('postcode', '', 'str'));
	$oShop_Order->address = Core_Str::stripTags(Core_Array::getPost('address', '', 'str'));
	$oShop_Order->surname = Core_Str::stripTags(Core_Array::getPost('surname', '', 'str'));
	$oShop_Order->name = Core_Str::stripTags(Core_Array::getPost('name', '', 'str'));
	$oShop_Order->patronymic = Core_Str::stripTags(Core_Array::getPost('patronymic', '', 'str'));
	$oShop_Order->company = Core_Str::stripTags(Core_Array::getPost('company', '', 'str'));
	$oShop_Order->phone = Core_Str::stripTags(Core_Array::getPost('phone', '', 'str'));
	$oShop_Order->fax = Core_Str::stripTags(Core_Array::getPost('fax', '', 'str'));
	$oShop_Order->email = Core_Str::stripTags(Core_Array::getPost('email', '', 'str'));
	$oShop_Order->description = Core_Str::stripTags(Core_Array::getPost('description', '', 'str'));
	$oShop_Order->shop_delivery_condition_id = 0;
	$oShop_Order->shop_payment_system_id = Core_Array::getPost('shop_payment_system_id', 0, 'int');
	$oShop_Order->shop_currency_id = $oShop->shop_currency_id;
	$oShop_Order->siteuser_id = $oSiteuser->id;
	$oShop->add($oShop_Order);

	// Set invoice
	$oShop_Order->invoice($oShop_Order->id)->save();

	$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
	$oShop_Order_Item->quantity = 1;
	$oShop_Order_Item->shop_item_id = 0;
	$oShop_Order_Item->shop_warehouse_id = 0;
	$oShop_Order_Item->price = $amount;
	$oShop_Order_Item->type = 2;
	$oShop_Order_Item->name = Core::_('Shop_Order_Item.replenishment_account', $oSiteuser->login, $oShop->name, FALSE);
	$oShop_Order->add($oShop_Order_Item);

	$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System);

	$oShop_Payment_System_Handler
		->shopOrder($oShop_Order)
		->printInvoice();
}
else
{
	$Shop_Address_Controller_Show = new Shop_Address_Controller_Show($oShop);

	// List of payment systems
	$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->getAllByActive(1);
	foreach ($aShop_Payment_Systems as $oShop_Payment_System)
	{
		$Shop_Address_Controller_Show->addEntity($oShop_Payment_System->clearEntities());
	}

	$Shop_Address_Controller_Show->xsl(
			Core_Entity::factory('Xsl')->getByName(
				Core_Array::get(Core_Page::instance()->libParams, 'deliveryAddressXsl')
			)
		)
		->show();
}