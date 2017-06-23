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

/* Переходим в кабинет пользователя, если siteuser авторизирован */
if (!is_null($oSiteuser) && $oSiteuser->id)
{
	$Siteuser_Controller_Show = new Siteuser_Controller_Show(
		$oSiteuser
	);

	$Siteuser_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('item')
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('name')->value('Код приглашения')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('path')->value('info/')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/code.png')
			)
	)
	->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('item')
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('name')->value('Структура приглашенных')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('path')->value('invites/')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/structure.png')
			)
	)
	->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('item')
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('name')->value('Бонусы')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('path')->value('bonuses/')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/bonuses.png')
			)
	);

	$Siteuser_Controller_Show->xsl(
		Core_Entity::factory('Xsl')->getByName(
			Core_Array::get(Core_Page::instance()->libParams, 'personalAreaXsl')
		)
	)
	->show();
}
else
{
	?><p>Пользователь не найден!</p><?php
}
