<?php

if (!Core::moduleIsActive('maillist'))
{
	?>
	<h1>Почтовые рассылки</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="https://www.hostcms.ru/hostcms/modules/maillists/">Почтовые рассылки</a>&raquo; доступен в редакции &laquo;<a href="https://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo;.</p>
	<?php
	return ;
}

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
is_null($oSiteuser) && $oSiteuser = Core_Entity::factory('Siteuser')->site_id(CURRENT_SITE);

$Siteuser_Controller_Show = new Siteuser_Controller_Show(
	$oSiteuser
);

// Пользовать уже авторизован или зарегистрирован выше
if (!is_null($oSiteuser->id))
{
	if (!is_null(Core_Array::getPost('apply')))
	{
		$aMaillists = $oSiteuser->getAllowedMaillists();
		foreach ($aMaillists as $oMaillists)
		{
			$oMaillist_Siteuser = $oSiteuser->Maillist_Siteusers->getByMaillist($oMaillists->id);

			// Пользователь подписан
			if (Core_Array::getPost("maillist_{$oMaillists->id}"))
			{
				// Пользователь не был подписан
				is_null($oMaillist_Siteuser) && $oMaillist_Siteuser = Core_Entity::factory('Maillist_Siteuser')->siteuser_id($oSiteuser->id)->maillist_id($oMaillists->id);

				$oMaillist_Siteuser->type = Core_Array::getPost("type_{$oMaillists->id}") == 0 ? 0 : 1;
				$oMaillist_Siteuser->save();

			}
			elseif (!is_null($oMaillist_Siteuser))
			{
				// Отписываем пользователя от рассылки
				$oMaillist_Siteuser->delete();
			}
		}
	}

	$Siteuser_Controller_Show->xsl(
		Core_Entity::factory('Xsl')->getByName(
			Core_Array::get(Core_Page::instance()->libParams, 'xsl')
		)
	)
	->showMaillists(TRUE)
	->show();
}
else
{
	?>
	<h1>Страница рассылок недоступна</h1>
	<p>Для того, чтобы перейти на эту страницу, необходимо пройти авторизацию.</p>
	<p>Если Ваш браузер поддерживает автоматическое перенаправление через 3 секунды Вы перейдёте на страницу <a href="../">авторизации пользователя</a>. Если Вы не хотите ждать, перейдите по соответствующей ссылке.</p>
	<script type="text/javascript">setTimeout(function(){ location = '../' }, 3000);</script>
	<?php
}