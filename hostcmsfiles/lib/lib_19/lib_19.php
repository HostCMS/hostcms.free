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

if (!is_null(Core_Array::getPost('anonymousmaillist')))
{
	// Register new siteuser
	if (is_null($oSiteuser->id))
	{
		$login = Core_Array::getPost('login', '', 'str');
		$email = Core_Array::getPost('email', '', 'str');

		$oSiteuser->login = $login;
		$oSiteuser->email = $email;
		$oSiteuser->name = $login;

		// Логин не начинается с http://
		if (strpos($login, 'http://') === FALSE && strlen($login) > 2)
		{
			if (Core_Valid::email($email))
			{
				if (is_null(Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByLogin($login)))
				{
					if (is_null(Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByEmail($email)))
					{
						$oSiteuser->password = Core_Hash::instance()->hash(Core_Password::get(rand(8, 12)));
						$oSiteuser->save();

						// Отправка письма
						$oSite_Alias = $oSiteuser->Site->getCurrentAlias();
						$Siteuser_Controller_Show->subject(
							Core::_('Siteuser.confirm_subject', !is_null($oSite_Alias) ? $oSite_Alias->alias_name_without_mask : '')
						)->sendConfirmationMail(
							Core_Entity::factory('Xsl')->getByName('ПисьмоПодтверждениеРегистрации')
						);

						$Siteuser_Controller_Show->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('message')->value('Пользователь успешно зарегистрирован и подписан на почтовые рассылки. Вам необходимо подтвердить регистрацию.')
						);

						// Помещаем пользователя в группу по умолчанию
						$oSiteuser_Group = $oSiteuser->Site->Siteuser_Groups->getDefault();
						!is_null($oSiteuser_Group) && $oSiteuser_Group->add($oSiteuser);
					}
					else
					{
						$Siteuser_Controller_Show->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('error')->value('Пользователь с таким e-mail уже зарегистрирован.')
						);
					}
				}
				else
				{
					$Siteuser_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error')->value('Пользователь с таким логином уже зарегистрирован.')
					);
				}
			}
			else
			{
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')->value('Введен некорректный e-mai')
				);
			}
		}
		else
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')->value('Недопустимый логин пользователя')
			);
		}

	}

	// Пользовать уже авторизован или зарегистрирован выше
	if (!is_null($oSiteuser->id))
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
}

$Siteuser_Controller_Show->xsl(
	Core_Entity::factory('Xsl')->getByName(
		Core_Array::get(Core_Page::instance()->libParams, 'xsl')
	)
)
->showMaillists(TRUE)
->show();