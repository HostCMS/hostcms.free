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

if (!is_null($oSiteuser))
{
	$Siteuser_Controller_Show = new Siteuser_Controller_Show(
		$oSiteuser
	);

	$date_from = Core_Array::getPost('date_from', date('d.m.Y', strtotime('-30 days')));
	!is_null($date_from) && $date_from = Core_Date::date2sql($date_from);

	$date_to = Core_Array::getPost('date_to', date('d.m.Y'));
	!is_null($date_to) && $date_to = Core_Date::date2sql($date_to);

	$Siteuser_Controller_Show->xsl(
			Core_Entity::factory('Xsl')->getByName(
				Core_Array::get(Core_Page::instance()->libParams, 'xsl')
			)
		)
		->showAffiliats(TRUE)
		->showAffiliatsTree(TRUE)
		->dateFrom($date_from)
		->dateTo($date_to)
		->show();
}
else
{
	?><p>Пользователь не найден!</p><?php
}