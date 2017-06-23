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

$Siteuser_Controller_Show = Core_Page::instance()->object;

$Siteuser_Controller_Show->xsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'userInfoXsl')
	)
)
->showFriends(TRUE)
->show();