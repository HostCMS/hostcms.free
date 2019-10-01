<?php

if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
	is_null($oSiteuser) && $oSiteuser = Core_Entity::factory('Siteuser');

	$Siteuser_Controller_Show = new Siteuser_Controller_Show(
		$oSiteuser
	);

	// Авторизация OAuth
	if(!is_null($oauth_provider = Core_Array::getGet('oauth_provider')))
	{
		try
		{
			Core_Session::start();
			Core_Array::set($_SESSION, 'oauth_provider', $oauth_provider);

			$sLocation = Core_Array::getGet('location');
			!is_null($sLocation) && Core_Array::set($_SESSION, 'oauth_location', $sLocation);

			$oSiteuser_Oauth_Controller = Siteuser_Oauth_Controller::factory($oauth_provider);
			if (is_null($oSiteuser_Oauth_Controller))
			{
				throw new Exception('Class does not exist');
			}
			Core_Array::set($_SESSION, 'oauth_data', $oSiteuser_Oauth_Controller->execute());
			die();
		}
		catch (Exception $e){}
	}

	$bTwitter = !is_null($oauth_token = Core_Array::getGet('oauth_token')) && !is_null($oauth_verifier = Core_Array::getGet('oauth_verifier'));

	// Встречаем ответ Вконтакте/Facebook/Одноклассники/Google+/Яндекс/Mail.ru/Twitter
	if(!is_null($code = Core_Array::getGet('code')) || $bTwitter)
	{
		Core_Session::start();
		$oauth_provider = Core_Array::get($_SESSION, 'oauth_provider');

		$oSiteuser_Oauth_Controller = Siteuser_Oauth_Controller::factory($oauth_provider);

		if(is_null($oSiteuser_Oauth_Controller))
		{
			throw new Exception('Class does not exist');
		}

		if ($bTwitter)
		{
			$oSiteuser_Oauth_Controller->oauth_token_secret = Core_Array::get($_SESSION, 'oauth_data');
			$oSiteuser_Oauth_Controller->oauth_token = $oauth_token;
			$oSiteuser_Oauth_Controller->oauth_verifier = $oauth_verifier;
		}
		else
		{
			$oSiteuser_Oauth_Controller->code = $code;
		}

		$aResult = $oSiteuser_Oauth_Controller->execute();

		if (is_null(Core_Array::get($aResult, 'error')))
		{
			if (!is_null($user_id = Core_Array::get($aResult, 'user_id')))
			{
				$oCurrentSiteuser = NULL;

				$oSiteuser_Identity_Provider = Core_Entity::factory('Siteuser_Identity_Provider', $oauth_provider);

				$oSiteuser = $oSiteuser_Identity_Provider->getSiteuserByIdentity($user_id);

				if (is_null($oSiteuser))
				{
					$oSite = Core_Entity::factory('Site', CURRENT_SITE);

					if(!is_null($user_login = Core_Array::get($aResult, 'login')))
					{
						// Replace '/' to '-'
						$user_login = str_replace('/', '-', $user_login);

						$oSiteuser = $oSite->Siteusers->getByLogin($user_login, FALSE);
						$sUserLogin = is_null($oSiteuser) ? $user_login : '';
					}
					else
					{
						$sUserLogin = '';
					}

					if(!is_null($user_email = Core_Array::get($aResult, 'email')))
					{
						$oSiteuser = $oSite->Siteusers->getByEmail($user_email, FALSE);
						$sUserEmail = is_null($oSiteuser) ? $user_email : '';
					}
					else
					{
						$sUserEmail = '';
					}

					// Create new siteuser
					$oSiteuser = Core_Entity::factory('Siteuser');
					$oSiteuser->login = Core_Str::stripTags($sUserLogin);
					$oSiteuser->password = Core_Hash::instance()->hash(Core_Password::get(12));
					$oSiteuser->email = $sUserEmail;
					$oSiteuser->name = Core_Str::stripTags(Core_Array::get($aResult, 'name', ''));
					$oSiteuser->surname = Core_Str::stripTags(Core_Array::get($aResult, 'surname', ''));
					$oSiteuser->company = Core_Str::stripTags(Core_Array::get($aResult, 'company', ''));
					$oSiteuser->save();

					if (!is_null($sPicture = Core_Array::get($aResult, 'picture')) && $sPicture != '')
					{
						// Ищем свойство аватара
						$oSiteuser_Property_List = Core_Entity::factory('Siteuser_Property_List', CURRENT_SITE);
						$oProperty = $oSiteuser_Property_List->Properties->getByname('Аватар', FALSE);

						if(!is_null($oProperty))
						{
							// Папка назначения
							$sDestinationFolder = $oSiteuser->getDirPath();

							// Файл-источник
							$sSourceFile = $sPicture;

							// Создаем папку назначения
							$oSiteuser->createDir();

							// Файл из WEB'а, создаем временный файл
							$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
							// Копируем содержимое WEB-файла в локальный временный файл
							file_put_contents($sTempFileName, file_get_contents($sSourceFile));

							// Файл-источник равен временному файлу
							$sSourceFile = $sTempFileName;

							switch(Core_Image::exifImagetype($sSourceFile))
							{
								case 1:
									$sExt = 'gif';
								break;
								case 2:
									$sExt = 'jpeg';
								break;
								case 3:
									$sExt = 'png';
								break;
								default:
									$sExt = 'jpeg';
								break;
							}

							$aPropertyValues = $oProperty->getValues($oSiteuser->id, FALSE);

							$oProperty_Value = count($aPropertyValues)
								? $aPropertyValues[0]
								: $oProperty->createNewValue($oSiteuser->id)->save();

							// Удаляем старое большое изображение
							if ($oProperty_Value->file != '')
							{
								try
								{
									Core_File::delete($sDestinationFolder . $oProperty_Value->file);
								} catch (Exception $e) {}
							}

							$sTargetFileName = "property_{$oProperty_Value->id}.{$sExt}";

							// Создаем массив параметров для загрузки картинок элементу
							$aPicturesParam = array();
							$aPicturesParam['large_image_isset'] = TRUE;
							$aPicturesParam['large_image_source'] = $sSourceFile;
							$aPicturesParam['large_image_name'] = "avatar.{$sExt}";
							$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
							$aPicturesParam['large_image_preserve_aspect_ratio'] = TRUE;
							$aPicturesParam['large_image_max_width'] = $oProperty->image_large_max_width;
							$aPicturesParam['large_image_max_height'] = $oProperty->image_large_max_height;
							$aPicturesParam['large_image_watermark'] = FALSE;
							$aPicturesParam['create_small_image_from_large'] = FALSE;

							try
							{
								$aResult = Core_File::adminUpload($aPicturesParam);
							}
							catch (Exception $exc)
							{
								Core_Message::show($exc->getMessage(), 'error');
								$aResult = array('large_image' => FALSE);
							}

							if ($aResult['large_image'])
							{
								$oProperty_Value->file = $sTargetFileName;
								$oProperty_Value->file_name = '';
							}

							$oProperty_Value->save();

							// Файл временный, подлежит удалению
							try
							{
								Core_File::delete($sSourceFile);
							} catch (Exception $e) {}
						}
					}

					if ($sUserLogin == '')
					{
						$oSiteuser->login = 'id' . $oSiteuser->id;
						$oSiteuser->save();
					}

					// Add siteuser's identity
					$oSiteuser_Identity = Core_Entity::factory('Siteuser_Identity');
					$oSiteuser_Identity->siteuser_identity_provider_id = $oauth_provider;
					$oSiteuser_Identity->identity = $user_id;
					$oSiteuser->add($oSiteuser_Identity);

					// Add into default group
					$oSiteuser_Group = $oSiteuser->Site->Siteuser_Groups->getDefault();
					if (!is_null($oSiteuser_Group))
					{
						$oSiteuser_Group->add($oSiteuser);
					}
					$oSiteuser->activate();

					// Только для нового пользователя
					$Siteuser_Controller_Show
						->setEntity($oSiteuser)
						->applyAffiliate(Core_Array::get($_COOKIE, 'affiliate_name'));
				}

				$oSiteuser->setCurrent();

				$oauth_location = Core_Array::get($_SESSION, 'oauth_location');
				!is_null($oauth_location) && $Siteuser_Controller_Show->go(strval($oauth_location));			
			}
		}
		else
		{
			$error = Core_Array::get($aResult, 'error');
			$error_description = Core_Array::get($aResult, 'error_description');
			throw new Exception("Error: {$error} - {$error_description}");
		}
	}

	// Авторизация по логину и паролю
	if (Core_Array::getPost('apply'))
	{
		$oSiteuser = $oSiteuser->Site->Siteusers->getByLoginAndPassword(
			strval(Core_Array::getPost('login')), strval(Core_Array::getPost('password'))
		);

		if (!is_null($oSiteuser))
		{
			if ($oSiteuser->active)
			{
				$expires = Core_Array::getPost('remember')
					? 2678400 // 31 день
					: 86400; // 1 день

				$oSiteuser->setCurrent($expires);

				// Change controller's siteuser
				$Siteuser_Controller_Show->setEntity($oSiteuser);

				// Location
				!is_null(Core_Array::getPost('location')) && $Siteuser_Controller_Show->go(
					strval(Core_Array::getPost('location'))
				);
			}
			else
			{
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')->value('Пользователь не активирован!')
					);
			}
		}
		else
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')->value('Введите корректный логин и пароль!')
			);
		}
	}

	// Авторизация по логину OpenID
	if (Core_Array::getPost('applyOpenIDLogin'))
	{
		$oSiteuser_OpenID_Controller = Siteuser_OpenID_Controller::instance();

		$iSiteuser_Identity_Provider = intval(Core_Array::getPost('identity_provider'));

		$oSiteuser_Identity_Provider = Core_Entity::factory('Siteuser_Identity_Provider')->find($iSiteuser_Identity_Provider);

		if (is_null($oSiteuser_Identity_Provider->id))
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value('Провайдер аутентификации не найден!')
			);
		}
		else
		{
			$sLogin = Core_Array::getPost('openid_login');

			$sIdentityURL = sprintf($oSiteuser_Identity_Provider->url, $sLogin);

			$oSiteuser_OpenID_Controller
				->setIdentityURL($sIdentityURL)
				->setTrustRoot('http://' . Core_Array::get($_SERVER, "HTTP_HOST"))
				->setRequiredFields(array('email', 'fullname'))
				->setOptionalFields(array('nickname', 'dob', 'gender', 'postcode', 'country', 'language', 'timezone'));

			if ($oSiteuser_OpenID_Controller->getOpenIDServer())
			{
				// Send Response from OpenID server to this script
				$oSiteuser_OpenID_Controller
					->setReturnURL(
						'http://' . Core_Array::get($_SERVER, "HTTP_HOST") . Core_Page::instance()->structure->getPath()
					)
					->redirect();
			}
			else
			{
				$aError = $oSiteuser_OpenID_Controller->GetError();
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('provider_error')->value($aError['description'])
				);
			}
		}
	}

	// Авторизация по OpenID
	if (Core_Array::getPost('applyOpenID'))
	{
		$oSiteuser_OpenID_Controller = Siteuser_OpenID_Controller::instance();

		$sIdentityURL = Core_Array::getPost('openid');

		$oSiteuser_OpenID_Controller
			->setIdentityURL($sIdentityURL)
			->setTrustRoot('http://' . Core_Array::get($_SERVER, "HTTP_HOST"))
			->setRequiredFields(array('email', 'fullname'))
			->setOptionalFields(array('nickname', 'dob', 'gender', 'postcode', 'country', 'language', 'timezone'));

		if ($oSiteuser_OpenID_Controller->getOpenIDServer())
		{
			// Send Response from OpenID server to this script
			$oSiteuser_OpenID_Controller
				->setReturnURL(
					'http://' . Core_Array::get($_SERVER, "HTTP_HOST") . Core_Page::instance()->structure->getPath()
				)
				->redirect();
		}
		else
		{
			$aError = $oSiteuser_OpenID_Controller->GetError();
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value($aError['description'])
			);
		}
	}

	// Данные от сервера OpenID
	if (Core_Array::getGet('openid_mode') == 'id_res')
	{
		$oSiteuser_OpenID_Controller = Siteuser_OpenID_Controller::instance();

		$bValidate = $oSiteuser_OpenID_Controller
			->setIdentityURL(Core_Array::getGet('openid_identity'))
			->validateWithServer();

		// VALID
		if ($bValidate)
		{
			$sIdentity = $oSiteuser_OpenID_Controller->getIdentityUrl();

			$oSite = Core_Entity::factory('Site', CURRENT_SITE);

			$oSiteusers = $oSite->Siteusers;
			$oSiteusers->queryBuilder()
				->select('siteusers.*')
				->join('siteuser_identities', 'siteuser_identities.siteuser_id', '=', 'siteusers.id')
				->where('siteuser_identities.identity', '=', $sIdentity)
				->limit(1);

			$aSiteusers = $oSiteusers->findAll(FALSE);

			if (!count($aSiteusers))
			{
				// Create new siteuser
				$oSiteuser = Core_Entity::factory('Siteuser');

				$nickname = trim(strval($oSiteuser_OpenID_Controller->getAttribute('nickname')));

				if (strlen($nickname) && !is_null($oSite->Siteusers->getByLogin($nickname)))
				{
					$nickname = '';
				}

				// Replace '/' to '-'
				$nickname = str_replace('/', '-', $nickname);

				$oSiteuser->login = $nickname;
				$oSiteuser->password = Core_Hash::instance()->hash(
					Core_Password::get(12)
				);
				$oSiteuser->email = trim(strval($oSiteuser_OpenID_Controller->getAttribute('email')));
				$oSiteuser->name = trim(strval($oSiteuser_OpenID_Controller->getAttribute('fullname')));
				$oSiteuser->save();

				if (!strlen($oSiteuser->login))
				{
					$oSiteuser->login = 'id' . $oSiteuser->id;
					$oSiteuser->save();
				}

				// Add siteuser's identity
				$oSiteuser_Identity = Core_Entity::factory('Siteuser_Identity');
				$oSiteuser_Identity->identity = $sIdentity;
				$oSiteuser->add($oSiteuser_Identity);

				// Add into default group
				$oSiteuser_Group = $oSiteuser->Site->Siteuser_Groups->getDefault();

				if (!is_null($oSiteuser_Group))
				{
					$oSiteuser_Group->add($oSiteuser);
				}

				$oSiteuser->activate();
			}
			else
			{
				$oSiteuser = $aSiteusers[0];
			}

			$oSiteuser->setCurrent();

			// Change controller's siteuser
			$Siteuser_Controller_Show->setEntity($oSiteuser);
		}
		elseif ($oSiteuser_OpenID_Controller->isError())
		{
			$aError = $oSiteuser_OpenID_Controller->GetError();
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value($aError['description'])
			);
		}
		else
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value('Ошибка проверки подписи! Повторите авторизацию.')
			);
		}
	}

	// Подтверждение регистрации пользователем
	if (Core_Array::getGet('accept'))
	{
		$oSiteuser = Core_Entity::factory('Siteuser')->getByGuid(strval(Core_Array::getGet('accept')));

		if (!is_null($oSiteuser))
		{
			$oSiteuser
				->activate()
				->setCurrent();
			$Siteuser_Controller_Show->setEntity($oSiteuser);
		}
	}

	// Отмена регистрации пользователем
	if (Core_Array::getGet('cancel'))
	{
		$oSiteuser = Core_Entity::factory('Siteuser')->getByGuid(strval(Core_Array::getGet('cancel')));

		if (!is_null($oSiteuser))
		{
			// Отменяем авторизацию текущего пользователя
			$oSiteuser
				->active(0)
				->save()
				->unsetCurrent();

			// Set empty siteuser
			$Siteuser_Controller_Show->setEntity(Core_Entity::factory('Siteuser'));
		}
	}

	// Пользователь выходит из кабинета
	if (Core_Array::getGet('action') == 'exit')
	{
		// Отменяем авторизацию текущего пользователя
		$oSiteuser->unsetCurrent();

		// Set empty siteuser
		$Siteuser_Controller_Show->setEntity(Core_Entity::factory('Siteuser'));
	}

	if (Core_Page::instance()->structure->getPath() != Core::$url['path'])
	{
		$Siteuser_Controller_Show->error404();
	}

	Core_Page::instance()->object = $Siteuser_Controller_Show;
}