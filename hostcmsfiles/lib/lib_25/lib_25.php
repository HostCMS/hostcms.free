<?php

if (!Core::moduleIsActive('siteuser'))
{
	?><h1>Клиенты</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="https://www.hostcms.ru/hostcms/modules/siteusers/">Клиенты</a>&raquo; доступен в редакциях &laquo;<a href="https://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="https://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return;
}

$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

if (is_null($oSiteuser))
{
	?><h1>Вы не авторизованы!</h1>
	<p>Для просмотра заказов необходимо авторизироваться.</p>
	<?php
	return ;
}

if (!Core::moduleIsActive('shop'))
{
	?><h1>Мои заказы</h1>
	<p>Список заказов временно недоступен.</p>
	<?php
	return ;
}

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Order_Controller_Show = new Shop_Order_Controller_Show($oShop);

$Shop_Order_Controller_Show
	->limit(10)
	->parseUrl();

$xslName = Core_Array::get(Core_Page::instance()->libParams, 'orderXsl');

if (Core_Array::getGet('action', '', 'str') === 'cancel')
{
	$guid = Core_Array::getGet('guid', '', 'str');

	$oShop_Order = $oShop->Shop_Orders->getByGuid($guid);

	if ($oShop_Order)
	{
		// Аннулируем заказ, только если он еще не оплачен
		if (!$oShop_Order->paid)
		{
			$oShop_Order->changeStatusCanceled();
		}
		else
		{
			$Shop_Order_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')
					->value('Нельзя отменить заказ, так как он уже оплачен.')
			);
		}
	}
	else
	{
		$Shop_Order_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('error')
				->value('Информация о заказе не найдена.')
		);
	}
}
// Изменить платежную систему для заказа
elseif(Core_Array::getPost('change_payment_system'))
{
	$shop_order_id = Core_Array::getPost('shop_order_id', 0, 'int');
	$shop_payment_system_id = Core_Array::getPost('shop_payment_system_id', 0, 'int');

	$oShop_Order = $oShop->Shop_Orders->getById($shop_order_id);

	// Если заказ принадлежит пользователю и изменились данные о форме оплаты
	if ($oSiteuser->id == $oShop_Order->siteuser_id
	&& $oShop_Order->shop_payment_system_id != $shop_payment_system_id
	// и заказ еще не оплачен
	&& $oShop_Order->paid == 0)
	{
		$oShop_Payment_System = Core_Entity::factory('Shop_Payment_System', $shop_payment_system_id);
		if ($oShop_Payment_System->active)
		{
			$oShop_Order->shop_payment_system_id = $oShop_Payment_System->id;
			$oShop_Order->save();
		}
	}
}

$Shop_Order_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->itemsProperties(TRUE)
	->ordersPropertiesList(TRUE)
	->comments(TRUE)
	->commentsProperties(TRUE)
	->show();