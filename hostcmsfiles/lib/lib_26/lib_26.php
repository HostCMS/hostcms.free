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
is_null($oSiteuser) && $oSiteuser = Core_Entity::factory('Siteuser');

$Siteuser_Controller_Restore_Password = new Siteuser_Controller_Restore_Password(
	$oSiteuser
);

$xslRestorePasswordXsl = Core_Array::get(Core_Page::instance()->libParams, 'xslRestorePasswordXsl');
$xslRestorePasswordMailXsl = Core_Array::get(Core_Page::instance()->libParams, 'xslRestorePasswordMailXsl');
$subject = Core_Array::get(Core_Page::instance()->libParams, 'subject', 'Восстановление пароля');

if (!is_null(Core_Array::getPost('apply')))
{
	// Проверка CSRF-токена
	if ($Siteuser_Controller_Restore_Password->checkCsrf(Core_Array::getPost('csrf_token', '', 'str')))
	{
		$login = Core_Array::getPost('login', '', 'str');
		$email = Core_Array::getPost('email', '', 'str');

		$oSiteuser = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByLoginAndEmail($login, $email);

		if (!is_null($oSiteuser) && $oSiteuser->active)
		{
			$Siteuser_Controller_Restore_Password
				->setEntity($oSiteuser)
				->subject($subject)
				->xslMail(
					Core_Entity::factory('Xsl')->getByName($xslRestorePasswordMailXsl)
				)
				->sendNewPassword();
		}
		else
		{
			$Siteuser_Controller_Restore_Password->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error_code')->value('wrongUser')
			);
		}
	}
	else
	{
		$Siteuser_Controller_Restore_Password->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('error_code')->value('wrongCsrf')
		);
	}
}

$Siteuser_Controller_Restore_Password
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslRestorePasswordXsl)
	)
	->show();