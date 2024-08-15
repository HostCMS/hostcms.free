<?php

$Shop_Controller_Show = Core_Page::instance()->object;

$xslName = $Shop_Controller_Show->item
	? Core_Array::get(Core_Page::instance()->libParams, 'shopItemXsl')
	: Core_Array::get(Core_Page::instance()->libParams, 'shopXsl');

$Shop_Controller_Show->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('ТекущаяГруппа')->value(
			intval(is_array($Shop_Controller_Show->group)
				? Core_Array::first($Shop_Controller_Show->group)
				: $Shop_Controller_Show->group
			)
		)
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_comments')->value(Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1))
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_add_comments')->value(Core_Array::get(Core_Page::instance()->libParams, 'showAddComment', 2))
);

$Shop_Controller_Show->tags(TRUE);

if ($Shop_Controller_Show->item == 0)
{
	$Shop_Controller_Show->itemsForbiddenTags(array('text'));

	// Producers
	if (Core_Array::getGet('producer_id'))
	{
		$iProducerId = Core_Array::getGet('producer_id', 0, 'int');
		$Shop_Controller_Show->producer($iProducerId);
	}

	if (Core_Array::getGet('filter') || Core_Array::getGet('sorting'))
	{
		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('filter')->value(1)
		);

		// Sorting
		$sorting = Core_Array::getGet('sorting', 0, 'int');

		($sorting == 1 || $sorting == 2)
			&& $Shop_Controller_Show->orderBy('absolute_price', $sorting == 1 ? 'ASC' : 'DESC');

		$sorting == 3 && $Shop_Controller_Show->orderBy('shop_items.name', 'ASC');

		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('sorting')->value($sorting)
		);

		// Prices
		$Shop_Controller_Show->setFilterPricesConditions($_GET);

		// Additional properties
		$Shop_Controller_Show->setFilterPropertiesConditions($_GET);
	}
}
else
{
	$Shop_Controller_Show
		->associatedItems(TRUE)
		->modifications(TRUE)
		->comments(TRUE)
		->tabs(TRUE);

	if (Core_Array::getPost('add_comment') && Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1))
	{
		$oShop = $Shop_Controller_Show->getEntity();

		$Shop_Controller_Show->cache(FALSE);

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
		$oComment->active = $oShop->comment_active;
		$oComment->author = Core_Str::stripTags(Core_Array::getPost('author', '', 'str'));
		$oComment->email = Core_Str::stripTags(Core_Array::getPost('email', '', 'str'));
		$oComment->phone = Core_Str::stripTags(Core_Array::getPost('phone', '', 'str'));
		$oComment->grade = Core_Array::getPost('grade', 0, 'int');
		$oComment->subject = Core_Str::stripTags(Core_Array::getPost('subject', '', 'str'));
		$oComment->text = nl2br(Core_Str::stripTags(Core_Array::getPost('text', '', 'str'), $allowable_tags));
		$oComment->siteuser_id = $siteuser_id;

		$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

		$oXmlCommentTag
			->addEntity($oComment)
			->addEntity($oShop_Item);

		if (is_null($oLastComment) || time() > Core_Date::sql2timestamp($oLastComment->datetime) + ADD_COMMENT_DELAY)
		{
			if ($oShop->use_captcha == 0 || $siteuser_id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id', '', 'str'), Core_Array::getPost('captcha', '', 'str')))
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
						->dateFormat($oShop->format_date)
						->dateTimeFormat($oShop->format_datetime);

					$oShop_Item->add($oComment)->clearCache();

					$oXmlCommentTag->addEntity($oShop);

					// Отправка письма администратору
					$sText = Xsl_Processor::instance()
						->xml($oXmlCommentTag->getXml())
						->xsl(
							Core_Entity::factory('Xsl')
								->getByName(Core_Array::get(Core_Page::instance()->libParams, 'addCommentAdminMailXsl'))
						)
						->process();

					$aFrom = array_map('trim', explode(',', EMAIL_TO));

					Core_Mail::instance()
						->to(EMAIL_TO)
						->from($aFrom[0])
						->header('Reply-To', Core_Valid::email($oComment->email)
							? $oComment->email
							: $aFrom[0]
						)
						->subject(Core::_('Shop.comment_mail_subject'))
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
					$Shop_Controller_Show->addEntity($oComment);
				}
			}
			else
			{
				$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
					->name('error_captcha')->value(1)
				);

				$oComment->text = Core_Str::br2nl($oComment->text);
				$Shop_Controller_Show->addEntity($oComment);
			}
		}
		else
		{
			$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
				->name('error_time')->value(1)
			);

			$oComment->text = Core_Str::br2nl($oComment->text);
			$Shop_Controller_Show->addEntity($oComment);
		}

		// Дополнительные свойства
		$oShop_Comment_Property_List = Core_Entity::factory('Shop_Comment_Property_List', $oShop->id);

		$aProperties = $oShop_Comment_Property_List->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			// Поле не скрытое
			if ($oProperty->type != 10)
			{
				$sFieldName = "property_{$oProperty->id}";

				$value = $oProperty->type == 2
					? Core_Array::getFiles($sFieldName)
					: Core_Array::getPost($sFieldName);

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

		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('message')->value($xsl_result)
		);
	}
}

// В корне выводим из всех групп
/*if ($Shop_Controller_Show->group == 0 || !is_null($Shop_Controller_Show->tag))
{
	$Shop_Controller_Show->group(FALSE)->forbidSelectModifications();
}*/

//$Shop_Controller_Show->itemsForbiddenTags(array('shop_producer'));

$Shop_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->addMinMaxWidth()
	->addMinMaxLength()
	//->addAllowedTags('/shop', array('name'))
	//->addAllowedTags('/shop/shop_item', array('name'))
	//->addAllowedTags('/shop/shop_group', array('name'))
	->show();