<?php
// Прозрачная подписка на рассылку
if (!is_null(Core_Array::getPost('stealthSubscribe')))
{
	$aReturn = array(
		'success' => '',
		'error' => ''
	);

	if (Core::moduleIsActive('siteuser'))
	{
		$email = strval(Core_Array::getPost('email', ''));

		if (strlen($email) && Core_Valid::email($email))
		{
			$oSiteuser = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByEmail($email);

			if (is_null($oSiteuser))
			{
				// Antispam
				if (Core::moduleIsActive('antispam'))
				{
					$Antispam_Controller = new Antispam_Controller();
					$bAntispamAnswer = $Antispam_Controller
						->addText($email)
						->execute();

					// Check e-mail
					if ($bAntispamAnswer)
					{
						$bAntispamAnswer = Antispam_Domain_Controller::checkEmail($email);
					}
				}
				else
				{
					$bAntispamAnswer = TRUE;
				}

				if ($bAntispamAnswer)
				{
					$oSiteuser = Core_Entity::factory('Siteuser');
					$oSiteuser->login = str_replace('/', '-', $email);
					$oSiteuser->email = $email;
					$oSiteuser->site_id = CURRENT_SITE;
					$oSiteuser->password = Core_Guid::get();
					$oSiteuser->active = 1;
					$oSiteuser->save();

					// Внесение пользователя в группу по умолчанию
					$oSiteuser_Group = $oSiteuser->Site->Siteuser_Groups->getDefault();

					if (!is_null($oSiteuser_Group))
					{
						$oSiteuser_Group->add($oSiteuser);
					}

					// Почтовые рассылки
					if (Core::moduleIsActive('maillist'))
					{
						$aMaillists = $oSiteuser->getAllowedMaillists();

						foreach ($aMaillists as $oMaillist)
						{
							$oMaillist_Siteuser = $oSiteuser->Maillist_Siteusers->getByMaillist($oMaillist->id);

							// Пользователь подписан
							if (is_null($oMaillist_Siteuser))
							{
								// Пользователь не был подписан
								$oMaillist_Siteuser = Core_Entity::factory('Maillist_Siteuser')
									->siteuser_id($oSiteuser->id)
									->maillist_id($oMaillist->id)
									->type(0)
									->save();

							}
						}

						$aReturn['success'] = 'Пользователь подписан успешно!';
					}
					else
					{
						$aReturn['error'] = 'Модуль "Почтовые рассылки" отсутствует!';
					}
				}
			}
			else
			{
				$aReturn['error'] = 'Пользователь с указанным электронным адресом невозможно подписать на рассылку!';
			}
		}
		else
		{
			$aReturn['error'] = 'Введен некорректный электронный адрес!';
		}
	}
	else
	{
		$aReturn['error'] = 'Модуль "Пользователи сайта" отсутствует!';
	}

	Core::showJson($aReturn);
}