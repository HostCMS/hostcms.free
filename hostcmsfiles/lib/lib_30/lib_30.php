<?php

if (!Core::moduleIsActive('siteuser'))
{
	?>
	<h1>Пользователи сайта</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/users/">Пользователи сайта</a>&raquo; доступен в редакциях &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="http://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
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

if (!Core::moduleIsActive('shop'))
{
	?><h1>Мои заказы</h1>
	<p>Список заказов временно недоступен.</p>
	<?php
	return ;
}

$Siteuser_Controller_Account_Show = new Siteuser_Controller_Account_Show(
	$oSiteuser
);
$Siteuser_Controller_Account_Show->parseUrl();

$xslName = $Siteuser_Controller_Account_Show->shop
	? Core_Array::get(Core_Page::instance()->libParams, 'siteuserAccountTransactionsXsl')
	: Core_Array::get(Core_Page::instance()->libParams, 'siteuserAccountsXsl');

$Siteuser_Controller_Account_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->show();