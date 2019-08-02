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

$oCompanyXmlEntity = Core::factory('Core_Xml_Entity')->name('company');
$oPersonXmlEntity = Core::factory('Core_Xml_Entity')->name('person');

$Siteuser_Controller_Show
	->addEntity($oCompanyXmlEntity)
	->addEntity($oPersonXmlEntity);

foreach ($_POST as $key => $value)
{
	if (strpos($key, 'company_') === 0)
	{
		$oCompanyXmlEntity->addEntity(
			Core::factory('Core_Xml_Entity')
				->name($key)
				->value(isset($value[0]) ? $value[0] : '')
		);
	}
	elseif (strpos($key, 'person_') === 0)
	{
		$oPersonXmlEntity->addEntity(
			Core::factory('Core_Xml_Entity')
				->name($key)
				->value(isset($value[0]) ? $value[0] : '')
		);
	}
}

/* Добавление свойств */
function addPropertyValue($oSiteuser, $oProperty, $oProperty_Value, $value)
{
	switch ($oProperty->type)
	{
		case 0: // Int
		case 3: // List
		case 5: // Information system
			$oProperty_Value->value(intval($value));
			$oProperty_Value->save();
		break;
		case 1: // String
		case 4: // Textarea
		case 6: // Wysiwyg
			$oProperty_Value->value(Core_Str::stripTags(strval($value)));
			$oProperty_Value->save();
		break;
		case 8: // Date
			$date = strval($value);
			$date = Core_Date::date2sql($date);
			$oProperty_Value->value($date);
			$oProperty_Value->save();
		break;
		case 9: // Datetime
			$datetime = strval($value);
			$datetime = Core_Date::datetime2sql($datetime);
			$oProperty_Value->value($datetime);
			$oProperty_Value->save();
		break;
		case 2: // File
			$aFileData = $value;

			if (!is_null($aFileData))
			{
				$oProperty_Value->setDir($oSiteuser->getDirPath());

				if (Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
				{
					// Удаление файла большого изображения
					if ($oProperty_Value->file)
					{
						$oProperty_Value
							->deleteLargeFile()
							->deleteSmallFile();
					}

					$oProperty_Value->save();
					$oProperty_Value->file = 'property_' . $oProperty_Value->id . '.' . Core_File::getExtension($aFileData['name']);
					$oProperty_Value->file_name = Core_Str::stripTags($aFileData['name']);

					try
					{
						$oSiteuser->createDir();

						// Resize image
						Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oSiteuser->Site->max_size_load_image, $oSiteuser->Site->max_size_load_image, $oProperty_Value->getLargeFilePath());

						$oProperty_Value->save();
					}
					catch (Exception $e) {
						Core_Message::show($e->getMessage(), 'error');
					};
				}
			}
		break;
		case 7: // Checkbox
			$oProperty_Value->value(is_null($value) ? 0 : 1);
			$oProperty_Value->save();
		break;
	}
}

/* Применение значений справочников */
function applyDirectoryValues($objectId, $oObject, $relationName)
{
	switch (get_class($oObject))
	{
		case 'Siteuser_Person_Model':
			$prefix = 'person';
		break;
		case 'Siteuser_Company_Model':
			$prefix = 'company';
		break;
		default:
			$prefix = 'undefined';
	}

	switch ($relationName)
	{
		case 'Directory_Email':
			$type = 'email';
			$fieldName = "directory_{$type}_type_id";
		break;
		case 'Directory_Phone':
			$type = 'phone';
			$fieldName = "directory_{$type}_type_id";
		break;
		case 'Directory_Social':
			$type = 'social';
			$fieldName = "directory_{$type}_type_id";
		break;
		case 'Directory_Messenger':
			$type = 'messenger';
			$fieldName = "directory_{$type}_type_id";
		break;
		case 'Directory_Website':
			$type = 'website';
			$fieldName = 'undefined';
		break;
		default:
			$type = 'undefined';
			$fieldName = 'undefined';
	}

	$pluralRelationName = Core_Inflection::getPlural($relationName);

	$aEntities = $oObject->$pluralRelationName->findAll();

	foreach ($aEntities as $oEntity)
	{
		$directoryValue = Core_Array::getPost("{$prefix}_{$objectId}_{$type}{$oEntity->id}");
		$bPublic = (boolean)Core_Array::getPost("{$prefix}_{$objectId}_{$type}_public{$oEntity->id}");

		if (!is_null($directoryValue))
		{
			$oEntity->value = strval($directoryValue);
			$oEntity->public = $bPublic;

			$fieldName != 'undefined'
				&& $oEntity->$fieldName = intval(Core_Array::getPost("{$prefix}_{$objectId}_directory_{$type}_type{$oEntity->id}"));

			$oEntity->save();
		}
	}

	if (!is_null(Core_Array::getPost("{$prefix}_{$objectId}_{$type}")))
	{
		$aNewValues = Core_Array::getPost("{$prefix}_{$objectId}_{$type}");
		$aNewTypes = Core_Array::getPost("{$prefix}_{$objectId}_directory_{$type}_type");
		$aNewPublic = Core_Array::getPost("{$prefix}_{$objectId}_{$type}_public");

		foreach($aNewValues as $key => $value)
		{
			if (strlen($value))
			{
				$oEntity = Core_Entity::factory($relationName);
				$oEntity->value = strval($value);
				$oEntity->public = is_null(Core_Array::get($aNewPublic, $key)) ? 0 : 1;

				$fieldName != 'undefined'
					&& $oEntity->$fieldName = intval(Core_Array::get($aNewTypes, $key));

				$oObject->add($oEntity);
			}
		}
	}
}

function uploadImage($oObject, $aFileData)
{
	if (isset($aFileData['name']) && Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
	{
		switch (get_class($oObject))
		{
			case 'Siteuser_Person_Model':
				$aConfig = Core_Config::instance()->get('siteuser_person_config', array()) + array(
					'max_height' => 130,
					'max_width' => 130
				);
			break;
			case 'Siteuser_Company_Model':
				$aConfig = Core_Config::instance()->get('siteuser_company_config', array()) + array(
					'max_height' => 130,
					'max_width' => 130
				);
			break;
			default:
				$aConfig = array(
					'max_height' => 130,
					'max_width' => 130
				);
		}

		$oObject->deleteImageFile();

		$oObject->image = 'logo.' . Core_File::getExtension($aFileData['name']);

		try
		{
			$oObject->createDir();

			// Resize image
			Core_Image::instance()->resizeImage($aFileData['tmp_name'], $aConfig['max_width'], $aConfig['max_height'], $oObject->getImageFilePath());

			$oObject->save();
		}
		catch (Exception $e) {};
	}
}

// Удаления файла доп. св-ва
$delete_property_value = Core_Array::getGet('delete_property_value');
if (!is_null($delete_property_value) && !$bNewUser)
{
	$aProperty_Values = $oSiteuser->getPropertyValues();

	foreach ($aProperty_Values as $oProperty_Value)
	{
		if ($oProperty_Value->id == intval($delete_property_value)
			&& $oProperty_Value->Property->type == 2)
		{
			$oProperty_Value->setDir($oSiteuser->getDirPath());

			// Удаление файла большого изображения
			if ($oProperty_Value->file)
			{
				$oProperty_Value
					->deleteLargeFile()
					->deleteSmallFile();
			}
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
	// $oSiteuser->name = Core_Str::stripTags(strval(Core_Array::getPost('name')));
	// $oSiteuser->surname = Core_Str::stripTags(strval(Core_Array::getPost('surname')));
	// $oSiteuser->patronymic = Core_Str::stripTags(strval(Core_Array::getPost('patronymic')));
	// $oSiteuser->company = Core_Str::stripTags(strval(Core_Array::getPost('company')));
	// $oSiteuser->phone = Core_Str::stripTags(strval(Core_Array::getPost('phone')));
	// $oSiteuser->fax = Core_Str::stripTags(strval(Core_Array::getPost('fax')));
	// $oSiteuser->website = Core_Str::stripTags(strval(Core_Array::getPost('website')));
	// $oSiteuser->icq = Core_Str::stripTags(strval(Core_Array::getPost('icq')));
	// $oSiteuser->country = Core_Str::stripTags(strval(Core_Array::getPost('country')));
	// $oSiteuser->postcode = Core_Str::stripTags(strval(Core_Array::getPost('postcode')));
	// $oSiteuser->city = Core_Str::stripTags(strval(Core_Array::getPost('city')));
	// $oSiteuser->address = Core_Str::stripTags(strval(Core_Array::getPost('address')));

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

							// Antispam
							if (Core::moduleIsActive('antispam'))
							{
								$Antispam_Controller = new Antispam_Controller();
								$bAntispamAnswer = $Antispam_Controller
									->addText($oSiteuser->login)
									->addText($oSiteuser->email)
									->execute();
							}
							else
							{
								$bAntispamAnswer = TRUE;
							}

							if ($bAntispamAnswer)
							{
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

								// Дополнительные свойства, существующие значения
								$aProperty_Values = $oSiteuser->getPropertyValues();
								foreach ($aProperty_Values as $oProperty_Value)
								{
									$oProperty = $oProperty_Value->Property;

									if ($oProperty->type != 10)
									{
										$sFieldName = "property_{$oProperty->id}_{$oProperty_Value->id}";

										$value = $oProperty->type == 2
											? Core_Array::getFiles($sFieldName)
											: Core_Array::getPost($sFieldName);

										addPropertyValue($oSiteuser, $oProperty, $oProperty_Value, $value);
									}
								}

								// Дополнительные свойства, новые значения
								$oSiteuser_Property_List = Core_Entity::factory('Siteuser_Property_List', $oSiteuser->site_id);

								$aProperties = $oSiteuser_Property_List->Properties->findAll();
								foreach ($aProperties as $oProperty)
								{
									// Поле не скрытое
									if ($oProperty->type != 10)
									{
										$sFieldName = "property_{$oProperty->id}";

										if ($oProperty->type == 2)
										{
											$aValues = Core_Array::getFiles($sFieldName);

											$mTmpValue = array();

											if (isset($aValues['name']))
											{
												foreach ($aValues['name'] as $key => $value)
												{
													$mTmpValue[$key] = array(
														"name" => isset($aValues['name']) ? Core_Array::get($aValues['name'], $key) : NULL,
														"type" => isset($aValues['type']) ? Core_Array::get($aValues['type'], $key) : NULL,
														"tmp_name" => isset($aValues['tmp_name']) ? Core_Array::get($aValues['tmp_name'], $key) : NULL,
														"error" => isset($aValues['error']) ? Core_Array::get($aValues['error'], $key) : NULL,
														"size" => isset($aValues['size']) ? Core_Array::get($aValues['size'], $key) : NULL
													);
												}
											}

											$aValues = $mTmpValue;
										}
										else
										{
											$aValues = Core_Array::getPost($sFieldName);
										}

										if (is_array($aValues))
										{
											foreach ($aValues as $key => $value)
											{
												$oProperty_Value = $oProperty->createNewValue($oSiteuser->id);
												addPropertyValue($oSiteuser, $oProperty, $oProperty_Value, $value);
											}
										}
									}
								}

								// Companies
								$aSiteuser_Companies = $oSiteuser->Siteuser_Companies->findAll(FALSE);
								foreach ($aSiteuser_Companies as $oSiteuser_Company)
								{
									if (!is_null(Core_Array::getPost("company_name{$oSiteuser_Company->id}")))
									{
										$oSiteuser_Company->name = strval(Core_Array::getPost("company_name{$oSiteuser_Company->id}"));
										$oSiteuser_Company->save();

										$aFileData = Core_Array::getFiles("company_image{$oSiteuser_Company->id}", array());
										uploadImage($oSiteuser_Company, $aFileData);
									}

									$aDirectory_Addresses = $oSiteuser_Company->Directory_Addresses->findAll();
									foreach ($aDirectory_Addresses as $oDirectory_Address)
									{
										if (!is_null(Core_Array::getPost("company_address{$oSiteuser_Company->id}")))
										{
											$oDirectory_Address->postcode = strval(Core_Array::getPost("company_postcode{$oSiteuser_Company->id}"));
											$oDirectory_Address->country = strval(Core_Array::getPost("company_country{$oSiteuser_Company->id}"));
											$oDirectory_Address->city = strval(Core_Array::getPost("company_city{$oSiteuser_Company->id}"));
											$oDirectory_Address->value = strval(Core_Array::getPost("company_address{$oSiteuser_Company->id}"));
											$oDirectory_Address->save();
										}
									}

									applyDirectoryValues($oSiteuser_Company->id, $oSiteuser_Company, 'Directory_Phone');
									applyDirectoryValues($oSiteuser_Company->id, $oSiteuser_Company, 'Directory_Email');
									applyDirectoryValues($oSiteuser_Company->id, $oSiteuser_Company, 'Directory_Social');
									applyDirectoryValues($oSiteuser_Company->id, $oSiteuser_Company, 'Directory_Messenger');
									applyDirectoryValues($oSiteuser_Company->id, $oSiteuser_Company, 'Directory_Website');
								}

								// People
								$aSiteuser_People = $oSiteuser->Siteuser_People->findAll(FALSE);
								foreach ($aSiteuser_People as $oSiteuser_Person)
								{
									if (!is_null(Core_Array::getPost("person_name{$oSiteuser_Person->id}")))
									{
										$oSiteuser_Person->name = strval(Core_Array::getPost("person_name{$oSiteuser_Person->id}"));
										$oSiteuser_Person->surname = strval(Core_Array::getPost("person_surname{$oSiteuser_Person->id}"));
										$oSiteuser_Person->patronymic = strval(Core_Array::getPost("person_patronymic{$oSiteuser_Person->id}"));
										$oSiteuser_Person->postcode = strval(Core_Array::getPost("person_postcode{$oSiteuser_Person->id}"));
										$oSiteuser_Person->country = strval(Core_Array::getPost("person_country{$oSiteuser_Person->id}"));
										$oSiteuser_Person->city = strval(Core_Array::getPost("person_city{$oSiteuser_Person->id}"));
										$oSiteuser_Person->address = strval(Core_Array::getPost("person_address{$oSiteuser_Person->id}"));
										$oSiteuser_Person->save();

										$aFileData = Core_Array::getFiles("person_image{$oSiteuser_Person->id}", array());
										uploadImage($oSiteuser_Person, $aFileData);
									}

									applyDirectoryValues($oSiteuser_Person->id, $oSiteuser_Person, 'Directory_Phone');
									applyDirectoryValues($oSiteuser_Person->id, $oSiteuser_Person, 'Directory_Email');
									applyDirectoryValues($oSiteuser_Person->id, $oSiteuser_Person, 'Directory_Social');
									applyDirectoryValues($oSiteuser_Person->id, $oSiteuser_Person, 'Directory_Messenger');
									applyDirectoryValues($oSiteuser_Person->id, $oSiteuser_Person, 'Directory_Website');
								}

								// Новые блоки Компаний
								if (!is_null(Core_Array::getPost("company_name")))
								{
									$aSiteuserCompanies = Core_Array::getPost("company_name");
									$aSiteuserCompanyAddress = Core_Array::getPost("company_address");
									$aSiteuserCompanyCountry = Core_Array::getPost("company_country");
									$aSiteuserCompanyPostcode = Core_Array::getPost("company_postcode");
									$aSiteuserCompanyCity = Core_Array::getPost("company_city");
									$aSiteuserCompanyPhone = Core_Array::getPost("company_0_phone");
									$aSiteuserCompanyEmail = Core_Array::getPost("company_0_email");
									$aSiteuserCompanySocial = Core_Array::getPost("company_0_social");
									$aSiteuserCompanyMessenger = Core_Array::getPost("company_0_messenger");
									$aSiteuserCompanyWebsite = Core_Array::getPost("company_0_website");

									foreach ($aSiteuserCompanies as $key => $sSiteuserCompanyName)
									{
										if (strlen($sSiteuserCompanyName))
										{
											$oSiteuser_Company = Core_Entity::factory('Siteuser_Company');
											$oSiteuser_Company->name = strval($sSiteuserCompanyName);
											$oSiteuser_Company->siteuser_id = $oSiteuser->id;
											$oSiteuser_Company->save();

											$aFileData = Core_Array::getFiles("company_image", array());

											if (isset($aFileData['name'][$key]))
											{
												$aTmpFile = array(
													"name" => isset($aFileData['name']) ? Core_Array::get($aFileData['name'], $key) : NULL,
													"type" => isset($aFileData['type']) ? Core_Array::get($aFileData['type'], $key) : NULL,
													"tmp_name" => isset($aFileData['tmp_name']) ? Core_Array::get($aFileData['tmp_name'], $key) : NULL,
													"error" => isset($aFileData['error']) ? Core_Array::get($aFileData['error'], $key) : NULL,
													"size" => isset($aFileData['size']) ? Core_Array::get($aFileData['size'], $key) : NULL
												);

												uploadImage($oSiteuser_Company, $aTmpFile);
											}

											$value = strval(Core_Array::get($aSiteuserCompanyAddress, $key));
											if ($value != '')
											{
												$oDirectory_Address = Core_Entity::factory('Directory_Address');
												$oDirectory_Address->country = strval(Core_Array::get($aSiteuserCompanyCountry, $key));
												$oDirectory_Address->postcode = strval(Core_Array::get($aSiteuserCompanyPostcode, $key));
												$oDirectory_Address->city = strval(Core_Array::get($aSiteuserCompanyCity, $key));
												$oDirectory_Address->value = $value;
												$oSiteuser_Company->add($oDirectory_Address);
											}

											applyDirectoryValues(0, $oSiteuser_Company, 'Directory_Phone');
											applyDirectoryValues(0, $oSiteuser_Company, 'Directory_Email');
											applyDirectoryValues(0, $oSiteuser_Company, 'Directory_Social');
											applyDirectoryValues(0, $oSiteuser_Company, 'Directory_Messenger');
											applyDirectoryValues(0, $oSiteuser_Company, 'Directory_Website');
										}
									}
								}

								// Новые блоки Персон
								if (!is_null(Core_Array::getPost("person_name")))
								{
									$aSiteuserPeopleNames = Core_Array::getPost("person_name");
									$aSiteuserPeopleSurnames = Core_Array::getPost("person_surname");
									$aSiteuserPeoplePatronymics = Core_Array::getPost("person_patronymic");
									$aSiteuserPeoplePostcodes = Core_Array::getPost("person_postcode");
									$aSiteuserPeopleCountries = Core_Array::getPost("person_country");
									$aSiteuserPeopleCities = Core_Array::getPost("person_city");
									$aSiteuserPeopleAddresses = Core_Array::getPost("person_address");

									$aSiteuserPeoplePhone = Core_Array::getPost("person_0_phone");
									$aSiteuserPeopleEmail = Core_Array::getPost("person_0_email");
									$aSiteuserPeopleSocial = Core_Array::getPost("person_0_social");
									$aSiteuserPeopleMessenger = Core_Array::getPost("person_0_messenger");
									$aSiteuserPeopleWebsite = Core_Array::getPost("person_0_website");

									foreach ($aSiteuserPeopleNames as $key => $sSiteuserPersonName)
									{
										if (strlen($sSiteuserPersonName))
										{
											$oSiteuser_Person = Core_Entity::factory('Siteuser_Person');
											$oSiteuser_Person->name = strval($sSiteuserPersonName);
											$oSiteuser_Person->siteuser_id = $oSiteuser->id;

											$oSiteuser_Person->surname = strval(Core_Array::get($aSiteuserPeopleSurnames, $key));
											$oSiteuser_Person->patronymic = strval(Core_Array::get($aSiteuserPeoplePatronymics, $key));
											$oSiteuser_Person->postcode = strval(Core_Array::get($aSiteuserPeoplePostcodes, $key));
											$oSiteuser_Person->country = strval(Core_Array::get($aSiteuserPeopleCountries, $key));
											$oSiteuser_Person->city = strval(Core_Array::get($aSiteuserPeopleCities, $key));
											$oSiteuser_Person->address = strval(Core_Array::get($aSiteuserPeopleAddresses, $key));
											$oSiteuser_Person->save();

											$aFileData = Core_Array::getFiles("person_image", array());

											if (isset($aFileData['name'][$key]))
											{
												$aTmpFile = array(
													"name" => isset($aFileData['name']) ? Core_Array::get($aFileData['name'], $key) : NULL,
													"type" => isset($aFileData['type']) ? Core_Array::get($aFileData['type'], $key) : NULL,
													"tmp_name" => isset($aFileData['tmp_name']) ? Core_Array::get($aFileData['tmp_name'], $key) : NULL,
													"error" => isset($aFileData['error']) ? Core_Array::get($aFileData['error'], $key) : NULL,
													"size" => isset($aFileData['size']) ? Core_Array::get($aFileData['size'], $key) : NULL
												);

												uploadImage($oSiteuser_Person, $aTmpFile);
											}

											applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Phone');
											applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Email');
											applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Social');
											applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Messenger');
											applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Website');
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
										->name('error')->value('Пользователь не может быть зарегистрирован!')
								);
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