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
		->name('show_comments')->value(Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1))
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_add_comments')->value(Core_Array::get(Core_Page::instance()->libParams, 'showAddComment', 2))
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
	if (Core_Array::getPost('add_comment') && Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1))
	{
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
		$oComment->parent_id = intval(Core_Array::getPost('parent_id', 0));
		$oComment->active = Core_Array::get(Core_Page::instance()->libParams, 'addedCommentActive', 1) == 1 ? 1 : 0;
		$oComment->author = Core_Str::stripTags(Core_Array::getPost('author'));
		$oComment->email = Core_Str::stripTags(Core_Array::getPost('email'));
		$oComment->phone = Core_Str::stripTags(Core_Array::getPost('phone'));
		$oComment->grade = intval(Core_Array::getPost('grade', 0));
		$oComment->subject = Core_Str::stripTags(Core_Array::getPost('subject'));
		$oComment->text = nl2br(Core_Str::stripTags(Core_Array::getPost('text'), $allowable_tags));
		$oComment->siteuser_id = $siteuser_id;

		$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);

		$oXmlCommentTag
			->addEntity($oComment)
			->addEntity($oInformationsystem_Item);

		if (is_null($oLastComment) || time() > Core_Date::sql2timestamp($oLastComment->datetime) + ADD_COMMENT_DELAY)
		{
			$oInformationsystem = $Informationsystem_Controller_Show->getEntity();

			if ($oInformationsystem->use_captcha == 0 || $siteuser_id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
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
					
				Core_Mail::instance()
					->to(EMAIL_TO)
					->from($aFrom[0])
					->header('Reply-To', Core_Valid::email($oComment->email)
						? $oComment->email
						: $aFrom[0]
					)
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
if ($Informationsystem_Controller_Show->group == 0)
{
	$Informationsystem_Controller_Show->group(FALSE);
}

$Informationsystem_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->itemsProperties(TRUE)
	->show();