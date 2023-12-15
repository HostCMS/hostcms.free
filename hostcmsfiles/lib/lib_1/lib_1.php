<?php

$Informationsystem_Controller_Show = Core_Page::instance()->object;

$xslName = $Informationsystem_Controller_Show->item
	? Core_Array::get(Core_Page::instance()->libParams, 'informationsystemItemXsl')
	: Core_Array::get(Core_Page::instance()->libParams, 'informationsystemXsl');

$Informationsystem_Controller_Show->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('ОтображатьСсылкуНаАрхив')->value(0)
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('ОтображатьСсылкиНаСледующиеСтраницы')->value(1)
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('ТекущаяГруппа')->value($Informationsystem_Controller_Show->group)
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_comments')->value(
			Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1, 'int')
		)
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_add_comments')->value(
			Core_Array::get(Core_Page::instance()->libParams, 'showAddComment', 2, 'int')
		)
);

$Informationsystem_Controller_Show
	->tags(TRUE)
	->comments(TRUE);

if ($Informationsystem_Controller_Show->item == 0)
{
	$Informationsystem_Controller_Show->itemsForbiddenTags(array('text'));
}
else
{
	if (Core_Array::getPost('add_comment') && Core_Array::get(Core_Page::instance()->libParams, 'showAddComment') != 0)
	{
		$Informationsystem_Controller_Show->cache(FALSE);

		$oInformationsystem = $Informationsystem_Controller_Show->getEntity();

		$oLastComment = Core_Entity::factory('Comment')->getLastCommentByIp(
			Core_Array::get($_SERVER, 'REMOTE_ADDR')
		);

		$oXmlCommentTag = Core::factory('Core_Xml_Entity')
			->name('document');

		$siteuser_id = 0;
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$siteuser_id = $oSiteuser->id;
			}
		}

		$oComment = Core_Entity::factory('Comment');

		$allowable_tags = '<b><strong><i><em><br><p><u><strike><ul><ol><li>';
		$oComment->parent_id = Core_Array::getPost('parent_id', 0, 'int');
		$oComment->active = Core_Array::get(Core_Page::instance()->libParams, 'addedCommentActive', 1) == 1 ? 1 : 0;
		$oComment->author = Core_Str::stripTags(Core_Array::getPost('author', '', 'str'));
		$oComment->email = Core_Str::stripTags(Core_Array::getPost('email', '', 'str'));
		$oComment->phone = Core_Str::stripTags(Core_Array::getPost('phone', '', 'str'));
		$oComment->grade = Core_Array::getPost('grade', 0, 'int');
		$oComment->subject = Core_Str::stripTags(Core_Array::getPost('subject', '', 'str'));
		$oComment->text = nl2br(Core_Str::stripTags(Core_Array::getPost('text', '', 'str'), $allowable_tags));
		$oComment->siteuser_id = $siteuser_id;

		$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);

		$oXmlCommentTag
			->addEntity($oComment)
			->addEntity($oInformationsystem_Item);

		if (is_null($oLastComment) || time() > Core_Date::sql2timestamp($oLastComment->datetime) + ADD_COMMENT_DELAY)
		{
			if ($oInformationsystem->use_captcha == 0 || $siteuser_id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id', '', 'str'), Core_Array::getPost('captcha', '', 'str')))
			{
				// Antispam
				if (Core::moduleIsActive('antispam'))
				{
					$Antispam_Controller = new Antispam_Controller();
					$bAntispamAnswer = $Antispam_Controller
						->addText($oComment->author, 'author')
						->addText($oComment->email, 'email')
						->addText($oComment->phone, 'phone')
						->addText($oComment->subject, 'subject')
						->addText($oComment->text, 'text')
						->execute();

					// Check e-mail
					if ($bAntispamAnswer)
					{
						$bAntispamAnswer = Antispam_Domain_Controller::checkEmail($oComment->email);
					}
				}
				else
				{
					$bAntispamAnswer = TRUE;
				}

				if ($bAntispamAnswer)
				{
					$oComment->save();

					$oComment
						->dateFormat($oInformationsystem->format_date)
						->dateTimeFormat($oInformationsystem->format_datetime);

					$oInformationsystem_Item->add($oComment)->clearCache();

					$oXmlCommentTag->addEntity($oInformationsystem);

					// Отправка письма администратору
					$sText = Xsl_Processor::instance()
						->xml($oXmlCommentTag->getXml())
						->xsl(Core_Entity::factory('Xsl')->getByName(Core_Array::get(Core_Page::instance()->libParams, 'addCommentAdminMailXsl')))
						->process();

					$aFrom = array_map('trim', explode(',', EMAIL_TO));

					$oCore_Mail_Driver = Core_Mail::instance()
						->to(EMAIL_TO)
						->from($aFrom[0])
						->header('Reply-To', Core_Valid::email($oComment->email)
							? $oComment->email
							: $aFrom[0])
						->subject(Core::_('Informationsystem.comment_mail_subject'))
						->message(trim($sText))
						->contentType(Core_Array::get(Core_Page::instance()->libParams, 'commentMailNoticeType', 0) == 0
							? 'text/plain'
							: 'text/html'
						)
						->send();
				}
				else
				{
					$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
						->name('error_antispam')->value(1)
					);

					$oComment->text = Core_Str::br2nl($oComment->text);
					$Informationsystem_Controller_Show->addEntity($oComment);
				}
			}
			else
			{
				$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
					->name('error_captcha')->value(1)
				);

				$oComment->text = Core_Str::br2nl($oComment->text);
				$Informationsystem_Controller_Show->addEntity($oComment);
			}
		}
		else
		{
			$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
				->name('error_time')->value(1)
			);

			$oComment->text = Core_Str::br2nl($oComment->text);
			$Informationsystem_Controller_Show->addEntity($oComment);
		}

		// Дополнительные свойства
		$oInformationsystem_Comment_Property_List = Core_Entity::factory('Informationsystem_Comment_Property_List', $oInformationsystem->id);

		$aProperties = $oInformationsystem_Comment_Property_List->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			// Поле не скрытое
			if ($oProperty->type != 10)
			{
				$sFieldName = "property_{$oProperty->id}";

				$value = $oProperty->type == 2
					? Core_Array::getFiles($sFieldName)
					: Core_Array::getPost($sFieldName, '', 'str');

				$oProperty_Value = $oProperty->createNewValue($oComment->id);

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
							if (Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
							{
								$oProperty_Value->setDir($oComment->getPath());
								$oProperty_Value->save();
								$oProperty_Value->file = 'property_' . $oProperty_Value->id . '.' . Core_File::getExtension($aFileData['name']);
								$oProperty_Value->file_name = Core_Str::stripTags($aFileData['name']);

								try
								{
									$oComment->createDir();

									// Resize image
									Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oProperty_Value->Property->image_large_max_width, $oProperty_Value->Property->image_large_max_height, $oProperty_Value->getLargeFilePath());

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
		}

		// Результат добавления комментария
		$xsl_result = Xsl_Processor::instance()
			->xml($oXmlCommentTag->getXml())
			->xsl(Core_Entity::factory('Xsl')->getByName(
				Core_Array::get(Core_Page::instance()->libParams, 'addCommentNoticeXsl'))
			)
			->process();

		$Informationsystem_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('message')->value($xsl_result)
		);
	}
}

// В корне выводим из всех групп
/*if ($Informationsystem_Controller_Show->group == 0)
{
	$Informationsystem_Controller_Show->group(FALSE);
}*/

$Informationsystem_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->itemsProperties(TRUE)
	//->commentsProperties(TRUE)
	->show();