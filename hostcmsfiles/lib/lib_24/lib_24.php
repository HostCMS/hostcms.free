<?php

// Привязывать сессию пользователя к IP
$attachSessionToIp = FALSE;

if (!Core::moduleIsActive('siteuser'))
{
	?>
	<h1>Клиенты</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="https://www.hostcms.ru/hostcms/modules/siteusers/">Клиенты</a>&raquo; доступен в редакциях &laquo;<a href="https://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="https://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return ;
}

/* Максимальное количество представителей или компаний у клиента */
$max_representatives = 3;

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
			$oProperty_Value->value(Core_Str::stripTags(Core_Str::toStr($value)));
			$oProperty_Value->save();
		break;
		case 8: // Date
			$date = Core_Str::toStr($value);
			$date = Core_Date::date2sql($date);
			$oProperty_Value->value($date);
			$oProperty_Value->save();
		break;
		case 9: // Datetime
			$datetime = Core_Str::toStr($value);
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
		$bPublic = Core_Array::getPost("{$prefix}_{$objectId}_{$type}_public{$oEntity->id}", '', 'bool');

		if (!is_null($directoryValue))
		{
			$oEntity->value = Core_Str::toStr($directoryValue);
			$oEntity->public = $bPublic;

			$fieldName != 'undefined'
				&& $oEntity->$fieldName = Core_Array::getPost("{$prefix}_{$objectId}_directory_{$type}_type{$oEntity->id}", 0, 'int');

			$oEntity->save();
		}
	}

	if (!is_null(Core_Array::getPost("{$prefix}_{$objectId}_{$type}")))
	{
		$aNewValues = Core_Array::getPost("{$prefix}_{$objectId}_{$type}");
		$aNewTypes = Core_Array::getPost("{$prefix}_{$objectId}_directory_{$type}_type");
		$aNewPublic = Core_Array::getPost("{$prefix}_{$objectId}_{$type}_public");

		foreach ($aNewValues as $key => $value)
		{
			if (strlen($value))
			{
				$oEntity = Core_Entity::factory($relationName);
				$oEntity->value = Core_Str::toStr($value);
				$oEntity->public = is_null(Core_Array::get($aNewPublic, $key)) ? 0 : 1;

				$fieldName != 'undefined'
					&& $oEntity->$fieldName = Core_Array::get($aNewTypes, $key, 0, 'int');

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
$delete_property_value = Core_Array::getGet('delete_property_value', 0, 'int');
if (!is_null($delete_property_value) && !$bNewUser)
{
	$aProperty_Values = $oSiteuser->getPropertyValues(FALSE);

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
			$oProperty_Value->delete();
		}
	}
}

// Обновление данных или регистрация нового пользователя
if (!is_null(Core_Array::getPost('apply')))
{
	$login = Core_Array::getPost('login', '', 'trim');
	$password = Core_Array::getPost('password', '', 'str');
	$email = Core_Array::getPost('email', '', 'trim');

	// Replace '/' to '-'
	$login = str_replace('/', '-', $login);

	$oSiteuser->login = $login;
	strlen($password) > 0 && $oSiteuser->password = Core_Hash::instance()->hash($password);
	$oSiteuser->email = $email;

	// Проверка корректности email
	if (Core_Valid::email($email))
	{
		// Проверка CSRF-токена
		if ($oSiteuser->id > 0 || $Siteuser_Controller_Show->checkCsrf(Core_Array::getPost('csrf_token', '', 'str')))
		{
			// Check captcha
			if ($oSiteuser->id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id', '', 'str'), Core_Array::getPost('captcha', '', 'str')))
			{
				// Пароль необязателен при обновлении данных о пользователе
				if (strlen($login) > 0 && ($oSiteuser->id > 0 || strlen($password) > 0) && strlen($email) > 0 && mb_strpos($login, 'http://') === FALSE)
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

							if ($bQuickRegistration || $password === Core_Array::getPost('password2', '', 'str'))
							{
								// Новому пользователю устанавливаем сразу активность при быстрой регистрации
								$bNewUser && $oSiteuser->active = $bQuickRegistration ? 1 : 0;

								// Antispam
								if (Core::moduleIsActive('antispam'))
								{
									$Antispam_Controller = new Antispam_Controller();
									$Antispam_Controller
										->addText($oSiteuser->login, 'login')
										->addText($oSiteuser->email, 'email');

									if (!is_null(Core_Array::getPost("company_name")))
									{
										$aSiteuserCompanies = Core_Array::getPost("company_name");
										$aSiteuserCompanyAddress = Core_Array::getPost("company_address");
										$aSiteuserCompanyCountry = Core_Array::getPost("company_country");
										$aSiteuserCompanyPostcode = Core_Array::getPost("company_postcode");
										$aSiteuserCompanyCity = Core_Array::getPost("company_city");

										foreach ($aSiteuserCompanies as $key => $sSiteuserCompanyName)
										{
											$Antispam_Controller
												->addText($sSiteuserCompanyName)
												->addText(Core_Array::get($aSiteuserCompanyAddress, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserCompanyCountry, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserCompanyPostcode, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserCompanyCity, $key, '', 'str'));
										}
									}

									if (!is_null(Core_Array::getPost("person_name")))
									{
										$aSiteuserPeopleNames = Core_Array::getPost("person_name");
										$aSiteuserPeopleSurnames = Core_Array::getPost("person_surname");
										$aSiteuserPeoplePatronymics = Core_Array::getPost("person_patronymic");
										$aSiteuserPeoplePostcodes = Core_Array::getPost("person_postcode");
										$aSiteuserPeopleCountries = Core_Array::getPost("person_country");
										$aSiteuserPeopleCities = Core_Array::getPost("person_city");
										$aSiteuserPeopleAddresses = Core_Array::getPost("person_address");

										foreach ($aSiteuserPeopleNames as $key => $sSiteuserPersonName)
										{
											$Antispam_Controller
												->addText($sSiteuserPersonName)
												->addText(Core_Array::get($aSiteuserPeopleSurnames, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserPeoplePatronymics, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserPeoplePostcodes, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserPeopleCountries, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserPeopleCities, $key, '', 'str'))
												->addText(Core_Array::get($aSiteuserPeopleAddresses, $key, '', 'str'));
										}
									}

									$bAntispamAnswer = $Antispam_Controller->execute();

									// Check e-mail
									if ($bAntispamAnswer)
									{
										$bAntispamAnswer = Antispam_Domain_Controller::checkEmail($oSiteuser->email);
									}
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
												is_null($oMaillist_Siteuser)
													&& $oMaillist_Siteuser = Core_Entity::factory('Maillist_Siteuser')->siteuser_id($oSiteuser->id)->maillist_id($oMaillists->id);

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
											$oSiteuser_Company->name = Core_Array::getPost("company_name{$oSiteuser_Company->id}", '', 'str');
											$oSiteuser_Company->save();

											$aFileData = Core_Array::getFiles("company_image{$oSiteuser_Company->id}", array());
											uploadImage($oSiteuser_Company, $aFileData);
										}

										if (!is_null(Core_Array::getPost("company_address{$oSiteuser_Company->id}")))
										{
											$aDirectory_Addresses = $oSiteuser_Company->Directory_Addresses->findAll();
											if (!isset($aDirectory_Addresses[0]))
											{
												$aDirectory_Addresses[0] = Core_Entity::factory('Directory_Address');
												$aDirectory_Addresses[0]->value = '';
												$oSiteuser_Company->add($aDirectory_Addresses[0]);
											}

											$aDirectory_Addresses[0]->postcode = Core_Array::getPost("company_postcode{$oSiteuser_Company->id}", '', 'str');
											$aDirectory_Addresses[0]->country = Core_Array::getPost("company_country{$oSiteuser_Company->id}", '', 'str');
											$aDirectory_Addresses[0]->city = Core_Array::getPost("company_city{$oSiteuser_Company->id}", '', 'str');
											$aDirectory_Addresses[0]->value = Core_Array::getPost("company_address{$oSiteuser_Company->id}", '', 'str');
											$aDirectory_Addresses[0]->save();
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
											$oSiteuser_Person->name = Core_Array::getPost("person_name{$oSiteuser_Person->id}", '', 'str');
											$oSiteuser_Person->surname = Core_Array::getPost("person_surname{$oSiteuser_Person->id}", '', 'str');
											$oSiteuser_Person->patronymic = Core_Array::getPost("person_patronymic{$oSiteuser_Person->id}", '', 'str');

											// $oSiteuser_Person->postcode = Core_Array::getPost("person_postcode{$oSiteuser_Person->id}", '', 'str');
											// $oSiteuser_Person->country = Core_Array::getPost("person_country{$oSiteuser_Person->id}", '', 'str');
											// $oSiteuser_Person->city = Core_Array::getPost("person_city{$oSiteuser_Person->id}", '', 'str');
											// $oSiteuser_Person->address = Core_Array::getPost("person_address{$oSiteuser_Person->id}", '', 'str');

											$oSiteuser_Person->save();

											$aFileData = Core_Array::getFiles("person_image{$oSiteuser_Person->id}", array());
											uploadImage($oSiteuser_Person, $aFileData);
										}

										if (!is_null(Core_Array::getPost("person_address{$oSiteuser_Person->id}")))
										{
											$aDirectory_Addresses = $oSiteuser_Person->Directory_Addresses->findAll();
											if (!isset($aDirectory_Addresses[0]))
											{
												$aDirectory_Addresses[0] = Core_Entity::factory('Directory_Address');
												$aDirectory_Addresses[0]->value = '';
												$oSiteuser_Person->add($aDirectory_Addresses[0]);
											}

											$aDirectory_Addresses[0]->postcode = Core_Array::getPost("person_postcode{$oSiteuser_Person->id}", '', 'str');
											$aDirectory_Addresses[0]->country = Core_Array::getPost("person_country{$oSiteuser_Person->id}", '', 'str');
											$aDirectory_Addresses[0]->city = Core_Array::getPost("person_city{$oSiteuser_Person->id}", '', 'str');
											$aDirectory_Addresses[0]->value = Core_Array::getPost("person_address{$oSiteuser_Person->id}", '', 'str');
											$aDirectory_Addresses[0]->save();
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
										//$aSiteuserCompanyPhone = Core_Array::getPost("company_0_phone");
										//$aSiteuserCompanyEmail = Core_Array::getPost("company_0_email");
										//$aSiteuserCompanySocial = Core_Array::getPost("company_0_social");
										//$aSiteuserCompanyMessenger = Core_Array::getPost("company_0_messenger");
										//$aSiteuserCompanyWebsite = Core_Array::getPost("company_0_website");

										foreach ($aSiteuserCompanies as $key => $sSiteuserCompanyName)
										{
											// Проверка на количество компаний у клиента
											if ($oSiteuser->Siteuser_Companies->getCount(FALSE) < $max_representatives)
											{
												if (strlen($sSiteuserCompanyName))
												{
													$oSiteuser_Company = Core_Entity::factory('Siteuser_Company');
													$oSiteuser_Company->name = Core_Str::toStr($sSiteuserCompanyName);
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

													$value = Core_Array::get($aSiteuserCompanyAddress, $key, '', 'str');
													if ($value != '')
													{
														$oDirectory_Address = Core_Entity::factory('Directory_Address');
														$oDirectory_Address->country = Core_Array::get($aSiteuserCompanyCountry, $key, '', 'str');
														$oDirectory_Address->postcode = Core_Array::get($aSiteuserCompanyPostcode, $key, '', 'str');
														$oDirectory_Address->city = Core_Array::get($aSiteuserCompanyCity, $key, '', 'str');
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

										//$aSiteuserPeoplePhone = Core_Array::getPost("person_0_phone");
										//$aSiteuserPeopleEmail = Core_Array::getPost("person_0_email");
										//$aSiteuserPeopleSocial = Core_Array::getPost("person_0_social");
										//$aSiteuserPeopleMessenger = Core_Array::getPost("person_0_messenger");
										//$aSiteuserPeopleWebsite = Core_Array::getPost("person_0_website");

										foreach ($aSiteuserPeopleNames as $key => $sSiteuserPersonName)
										{
											// Проверка на количество представителей у клиента
											if ($oSiteuser->Siteuser_People->getCount(FALSE) < $max_representatives)
											{
												if (strlen($sSiteuserPersonName))
												{
													$oSiteuser_Person = Core_Entity::factory('Siteuser_Person');
													$oSiteuser_Person->name = Core_Str::toStr($sSiteuserPersonName);
													$oSiteuser_Person->siteuser_id = $oSiteuser->id;

													$oSiteuser_Person->surname = Core_Array::get($aSiteuserPeopleSurnames, $key, '', 'str');
													$oSiteuser_Person->patronymic = Core_Array::get($aSiteuserPeoplePatronymics, $key, '', 'str');

													// $oSiteuser_Person->postcode = Core_Array::get($aSiteuserPeoplePostcodes, $key, '', 'str');
													// $oSiteuser_Person->country = Core_Array::get($aSiteuserPeopleCountries, $key, '', 'str');
													// $oSiteuser_Person->city = Core_Array::get($aSiteuserPeopleCities, $key, '', 'str');
													// $oSiteuser_Person->address = Core_Array::get($aSiteuserPeopleAddresses, $key, '', 'str');

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

													$value = Core_Array::get($aSiteuserPeopleAddresses, $key, '', 'str');
													if ($value != '')
													{
														$oDirectory_Address = Core_Entity::factory('Directory_Address');
														$oDirectory_Address->country = Core_Array::get($aSiteuserPeopleCountries, $key, '', 'str');
														$oDirectory_Address->postcode = Core_Array::get($aSiteuserPeoplePostcodes, $key, '', 'str');
														$oDirectory_Address->city = Core_Array::get($aSiteuserPeopleCities, $key, '', 'str');
														$oDirectory_Address->value = $value;
														$oSiteuser_Person->add($oDirectory_Address);
													}

													applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Phone');
													applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Email');
													applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Social');
													applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Messenger');
													applyDirectoryValues(0, $oSiteuser_Person, 'Directory_Website');
												}
											}
										}
									}

									// Регистрация нового пользователя
									if ($bNewUser)
									{
										if ($bQuickRegistration)
										{
											// Авторизуем зарегистрированного пользователя
											$oSiteuser
												// Не привязывать сессию пользователя к IP
												->attachSessionToIp($attachSessionToIp)
												->setCurrent();

											// Перенаправляем на страницу, с которой он пришел
											!is_null(Core_Array::getPost('location')) && $Siteuser_Controller_Show->go(
												Core_Array::getPost('location', '', 'str')
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

										// Спасибо за регистрацию
										$Siteuser_Controller_Show->addEntity(
											Core::factory('Core_Xml_Entity')
												->name('success_code')->value('successfulRegistration')
										);
									}
									else
									{
										// Ваши анкетные данные успешно изменены
										$Siteuser_Controller_Show->addEntity(
											Core::factory('Core_Xml_Entity')
												->name('success_code')->value('successfulUpdate')
										);
									}
								}
								else
								{
									// Пользователь не может быть зарегистрирован, запрещенные данные!
									$Siteuser_Controller_Show->addEntity(
										Core::factory('Core_Xml_Entity')
											->name('error_code')->value('antispam')
									);
									
									// Log action
									Core_Log::instance()->clear()
										->status(Core_Log::$MESSAGE)
										->write(Core::_('Siteuser.antispam'));
								}
							}
							else
							{
								// Повтор пароля введен неверно!
								$Siteuser_Controller_Show->addEntity(
									Core::factory('Core_Xml_Entity')
										->name('error_code')->value('repeatPasswordIncorrect')
								);
								
								// Log action
								Core_Log::instance()->clear()
									->status(Core_Log::$MESSAGE)
									->write(Core::_('Siteuser.repeatPasswordIncorrect'));
							}
						}
						else
						{
							// Пользователь с указанным электронным адресом зарегистрирован ранее!
							$Siteuser_Controller_Show->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('error_code')->value('userWithEmailAlreadyExists')
							);
							
							// Log action
							Core_Log::instance()->clear()
								->status(Core_Log::$MESSAGE)
								->write(Core::_('Siteuser.userWithEmailAlreadyExists'));
						}
					}
					else
					{
						// Пользователь с таким логином зарегистрирован ранее!
						$Siteuser_Controller_Show->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('error_code')->value('userWithLoginAlreadyExists')
						);
						
						// Log action
						Core_Log::instance()->clear()
							->status(Core_Log::$MESSAGE)
							->write(Core::_('Siteuser.userWithLoginAlreadyExists'));
					}
				}
				else
				{
					// Заполните, пожалуйста, все обязательные параметры!
					$Siteuser_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('error_code')->value('requiredFieldsNotFilled')
					);
					
					// Log action
					Core_Log::instance()->clear()
						->status(Core_Log::$MESSAGE)
						->write(Core::_('Siteuser.requiredFieldsNotFilled'));
				}
			}
			else
			{
				// Неправильно введен код подтверждения!
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error_code')->value('wrongCaptcha')
				);
				
				// Log action
				Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write(Core::_('Siteuser.wrongCaptcha'));
			}
		}
		else
		{
			// Форма устарела, обновите страницу и повторите вход!
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error_code')->value('wrongCsrf')
			);
			
			// Log action
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write(Core::_('Siteuser.wrongCsrf'));
		}
	}
	else
	{
		// Введен некорректный адрес электронной почты!
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('error_code')->value('wrongEmail')
		);
		
		// Log action
		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write(Core::_('Siteuser.wrongEmail'));
	}
}

// Флаг быстрой регистрации
if (!is_null(Core_Array::getRequest('fast')))
{
	$Siteuser_Controller_Show->fastRegistration(TRUE);
	$location = Core_Array::getRequest('location', '', 'str');
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