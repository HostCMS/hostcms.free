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

$xsl_letter = Core_Array::get(Core_Page::instance()->libParams, 'xslRestorePasswordMailXsl');
$xslUserRegistration = Core_Array::get(Core_Page::instance()->libParams, 'userRegistrationXsl');

$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
$bNewUser = is_null($oSiteuser);
$bNewUser && $oSiteuser = Core_Entity::factory('Siteuser')->site_id(CURRENT_SITE);

$Siteuser_Controller_Show = new Siteuser_Controller_Show(
	$oSiteuser
);

// Удаления файла доп. св-ва
$delete_property = Core_Array::getGet('delete_property');
if (!is_null($delete_property) && !$bNewUser)
{
	$oSiteuser_Property_List = Core_Entity::factory('Siteuser_Property_List', $oSiteuser->site_id);

	$oProperty = Core_Entity::factory('Property', intval($delete_property));

	$aProperty_Values = $oProperty->getValues($oSiteuser->id);

	if (count($aProperty_Values))
	{
		$aProperty_Values[0]->setDir(
			$oSiteuser_Property_List->getDirPath($oSiteuser)
		);

		// Удаление файла большого изображения
		if ($aProperty_Values[0]->file)
		{
			$aProperty_Values[0]
				->deleteLargeFile()
				->deleteSmallFile();
		}
	}
}

// Обновление данных или регистрация нового пользователя
if (!is_null(Core_Array::getPost('apply')))
{
	$login = trim(strval(Core_Array::getPost('login')));
	$password = strval(Core_Array::getPost('password'));
	$email = trim(strval(Core_Array::getPost('email')));

	$oSiteuser->login = $login;
	strlen($password) > 0 && $oSiteuser->password = Core_Hash::instance()->hash($password);
	$oSiteuser->email = $email;
	$oSiteuser->name = Core_Str::stripTags(strval(Core_Array::getPost('name')));
	$oSiteuser->surname = Core_Str::stripTags(strval(Core_Array::getPost('surname')));
	$oSiteuser->patronymic = Core_Str::stripTags(strval(Core_Array::getPost('patronymic')));
	$oSiteuser->company = Core_Str::stripTags(strval(Core_Array::getPost('company')));
	$oSiteuser->phone = Core_Str::stripTags(strval(Core_Array::getPost('phone')));
	$oSiteuser->fax = Core_Str::stripTags(strval(Core_Array::getPost('fax')));
	$oSiteuser->website = Core_Str::stripTags(strval(Core_Array::getPost('website')));
	$oSiteuser->icq = Core_Str::stripTags(strval(Core_Array::getPost('icq')));
	$oSiteuser->country = Core_Str::stripTags(strval(Core_Array::getPost('country')));
	$oSiteuser->postcode = Core_Str::stripTags(strval(Core_Array::getPost('postcode')));
	$oSiteuser->city = Core_Str::stripTags(strval(Core_Array::getPost('city')));
	$oSiteuser->address = Core_Str::stripTags(strval(Core_Array::getPost('address')));

	// Проверка корректности email
	if (Core_Valid::email($email))
	{
		// Check captcha
		if ($oSiteuser->id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
		{
			// Пароль необязателен при обновлении данных о пользователе
			if (strlen($login) > 0 && ($oSiteuser->id > 0 || strlen($password) > 0) && strlen($email) > 0
			&& mb_strpos($login, 'http://') === FALSE)
			{
				// Проверка совпадения логина
				$oTmpSiteuser = $oSiteuser->Site->Siteusers->getByLogin($login);
				if (is_null($oTmpSiteuser) || $oTmpSiteuser->id == $oSiteuser->id)
				{
					// Проверка совпадения email
					$oTmpSiteuser = $oSiteuser->Site->Siteusers->getByEmail($email);
					if (is_null($oTmpSiteuser) || $oTmpSiteuser->id == $oSiteuser->id)
					{
						// При быстрой регистрации password2 не передается
						$bQuickRegistration = is_null(Core_Array::getPost('password2'));

						if ($bQuickRegistration || $password == Core_Array::getPost('password2'))
						{
							// Новому пользователю устанавливаем сразу активность при быстрой регистрации
							$bNewUser && $oSiteuser->active = $bQuickRegistration ? 1 : 0;

							$oSiteuser->save();

							if ($bNewUser)
							{
								// Внесение пользователя в группу по умолчанию
								$oSiteuser_Group = $oSiteuser->Site->Siteuser_Groups->getDefault();

								if (!is_null($oSiteuser_Group))
								{
									$oSiteuser_Group->add($oSiteuser);
								}
							}

							// Почтовые рассылки
							if (Core::moduleIsActive('maillist'))
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

							// Дополнительные свойства
							$oSiteuser_Property_List = Core_Entity::factory('Siteuser_Property_List', $oSiteuser->site_id);

							$aProperties = $oSiteuser_Property_List->Properties->findAll();
							foreach ($aProperties as $oProperty)
							{
								// Поле не скрытое
								if ($oProperty->type != 10)
								{
									$aProperty_Values = $oProperty->getValues($oSiteuser->id);
									count($aProperty_Values) == 0 && $aProperty_Values[0] = $oProperty->createNewValue($oSiteuser->id);

									// Дополнительные свойства
									switch ($oProperty->type)
									{
										case 0: // Int
										case 3: // List
										case 5: // Information system
											$aProperty_Values[0]->value(intval(Core_Array::getPost("property_{$oProperty->id}")));
											$aProperty_Values[0]->save();
										break;
										case 1: // String
										case 4: // Textarea
										case 6: // Wysiwyg
											$aProperty_Values[0]->value(Core_Str::stripTags(strval(Core_Array::getPost("property_{$oProperty->id}"))));
											$aProperty_Values[0]->save();
										break;
										case 8: // Date
											$date = strval(Core_Array::getPost("property_{$oProperty->id}"));
											$date = Core_Date::date2sql($date);
											$aProperty_Values[0]->value($date);
											$aProperty_Values[0]->save();
										break;
										case 9: // Datetime
											$datetime = strval(Core_Array::getPost("property_{$oProperty->id}"));
											$datetime = Core_Date::datetime2sql($datetime);
											$aProperty_Values[0]->value($datetime);
											$aProperty_Values[0]->save();
										break;
										case 2: // File
											$aFileData = Core_Array::getFiles("property_{$oProperty->id}");
											if (!is_null($aFileData))
											{
												$aProperty_Values[0]->setDir(
													$oSiteuser_Property_List->getDirPath($oSiteuser)
												);

												if (Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
												{
													// Удаление файла большого изображения
													if ($aProperty_Values[0]->file)
													{
														$aProperty_Values[0]
															->deleteLargeFile()
															->deleteSmallFile();
													}

													$aProperty_Values[0]->save();
													$aProperty_Values[0]->file = 'property_' . $aProperty_Values[0]->id . '.' . Core_File::getExtension($aFileData['name']);
													$aProperty_Values[0]->file_name = Core_Str::stripTags($aFileData['name']);

													try
													{
														$oSiteuser_Property_List->createPropertyDir($oSiteuser);

														// Resize image
														Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oSiteuser->Site->max_size_load_image, $oSiteuser->Site->max_size_load_image, $aProperty_Values[0]->getLargeFilePath());

														$aProperty_Values[0]->save();
													}
													catch (Exception $e) {
														Core_Message::show($e->getMessage(), 'error');
													};
												}
											}
										break;
										case 7: // Checkbox
											$aProperty_Values[0]->value(is_null(Core_Array::getPost("property_{$oProperty->id}")) ? 0 : 1);
											$aProperty_Values[0]->save();
										break;
									}
								}
							}

							// Регистрация нового пользователя
							if ($bNewUser)
							{
								if ($bQuickRegistration)
								{
									// Авторизуем зарегистрированного пользователя
									$oSiteuser->setCurrent();

									// Перенаправляем на страницу, с которой он пришел
									!is_null(Core_Array::getPost('location')) && $Siteuser_Controller_Show->go(
										strval(Core_Array::getPost('location'))
									);
								}

								// Отправка письма
								$oSite_Alias = $oSiteuser->Site->getCurrentAlias();
								$Siteuser_Controller_Show
									->setEntity($oSiteuser)
									->applyAffiliate(Core_Array::get($_COOKIE, 'affiliate_name'))
									->subject(
										Core::_('Siteuser.confirm_subject', !is_null($oSite_Alias) ? $oSite_Alias->alias_name_without_mask : '')
									)
									->sendConfirmationMail(Core_Entity::factory('Xsl')->getByName($xsl_letter));

								?>
								<h1>Спасибо за регистрацию</h1>
								<p>Для продолжения работы необходимо подтвердить регистрацию Ваших данных.
								В Ваш адрес отправлено письмо, содержащее ссылку для подтверждения регистрации.</p>
								<p>Если Ваш браузер поддерживает автоматическое перенаправление через 3 секунды Вы перейдете на страницу <a href="../">авторизации пользователя</a>.</p>
								<script type="text/javascript">setTimeout(function(){ location = '../' }, 3000);</script>
								<?php

								return;
							}
							else
							{
								?><h1>Ваши анкетные данные успешно изменены</h1>
								<p>Если Ваш браузер поддерживает автоматическое перенаправление через 3 секунды Вы перейдете в <a href="../">кабинет пользователя</a>.</p>
								<script type="text/javascript">setTimeout(function(){ location = '../' }, 3000);</script>
								<?php

								return;
							}
						}
						else
						{
							$Siteuser_Controller_Show->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('error')->value('Повтор пароля введен неверно!')
							);
						}
					}
					else
					{
						$Siteuser_Controller_Show->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('error')->value('Пользователь с указанным электронным адресом зарегистрирован ранее!')
						);
					}
				}
				else
				{
					$Siteuser_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error')->value('Пользователь с таким логином зарегистрирован ранее!')
					);
				}
			}
			else
			{
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')->value('Заполните, пожалуйста, все обязательные параметры!')
				);
			}
		}
		else
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')->value('Неправильно введен код подтверждения!')
			);
		}
	}
	else
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('error')->value('Введен некорректный электронный адрес!')
		);
	}
}

// Флаг быстрой регистрации
if (!is_null(Core_Array::getRequest('fast')))
{
	$Siteuser_Controller_Show->fastRegistration(TRUE);
	$location = Core_Array::getRequest('location');
	!is_null($location)
		&& strlen($location)
		&& $Siteuser_Controller_Show->location($location);
}

$Siteuser_Controller_Show->xsl(
	Core_Entity::factory('Xsl')->getByName($xslUserRegistration)
)
	->properties(TRUE)
	->showMaillists(TRUE)
	->show();