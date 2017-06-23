<?php

if (Core::moduleIsActive('search'))
{
	$Search_Controller_Show = Core_Page::instance()->object;

	$Search_Controller_Show
		->xsl(
			Core_Entity::factory('Xsl')->getByName(Core_Page::instance()->libParams['xsl'])
		)
		->show();
}
else
{
	?>
	<h1>Поиск</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/search/">Поиск по сайту</a>&raquo; доступен в редакциях &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo;, &laquo;<a href="http://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;, &laquo;<a href="http://www.hostcms.ru/hostcms/editions/small-business/">Малый бизнес</a>&raquo; и &laquo;<a href="http://www.hostcms.ru/hostcms/editions/my-site/">Мой сайт</a>&raquo;.</p>
	<?php
}