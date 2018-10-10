<?php

if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

	if (!is_null($oSiteuser))
	{
		$oSite = $oSiteuser->Site;

		$Message_Controller_Show = new Message_Controller_Show($oSiteuser);

		$Message_Controller_Show
			->limit(Core_Array::get(Core_Page::instance()->libParams, 'itemsOnPage', 15))
			->url(Core_Entity::factory('Structure', Core_Page::instance()->structure->id)->getPath())
			->ajax(Core_Array::getPost('ajaxLoad', FALSE)) // параметр Ajax запроса
			->activity(Core_Array::getPost('activity', FALSE)) // параметр активности пользователя
			->parseUrl();

		$aErrors = array();

		if (Core_Array::getPost('text'))
		{
			$oMessage = Core_Entity::factory('Message');

			$allowable_tags = '<b><strong><i><em><br><p><u><strike><ul><ol><li>';
			$oMessage->text = nl2br(trim(Core_Str::stripTags(Core_Array::getPost('text'), $allowable_tags)));

			empty($oMessage->text) && $aErrors[] = Core::factory('Core_Xml_Entity')
				->name('error')->value('Отсутствует текст сообщения!');

			//$oMessage->datetime = Core_Date::timestamp2sql(time());
			$oMessage->siteuser_id = $oSiteuser->id;
		}

		if ($Message_Controller_Show->topic)
		{
			$oMessage_Topic = Core_Entity::factory('Message_Topic')->getById($Message_Controller_Show->topic);

			if ($oMessage_Topic && $oMessage_Topic->access($oSiteuser))
			{
				// При входе в тему пересчитываем количество
				is_null(Core_Array::getPost('ajaxLoad'))
					&& $oMessage_Topic->recount();
				
				$sPageTitle = $oMessage_Topic->subject;
			}
			else
			{
				$sPageTitle = 'Пользователь не имеет доступа к переписке!';

				$aErrors[] = Core::factory('Core_Xml_Entity')
					->name('error')->value($sPageTitle);
			}

			Core_Page::instance()->title($sPageTitle);
			Core_Page::instance()->description($sPageTitle);
			Core_Page::instance()->keywords($sPageTitle);
		}

		if (isset($oMessage))
		{
			if (!isset($oMessage_Topic))
			{
				$oMessage_Topic = Core_Entity::factory('Message_Topic');
				$oMessage_Topic->subject = Core_Str::stripTags(Core_Array::getPost('subject'));

				empty($oMessage_Topic->subject) && $oMessage_Topic->subject = Core::_('Message_Topic.no_subject');

				$oMessage_Topic->sender_siteuser_id = $oSiteuser->id;

				$oSiteuserRecipient = $oSite->Siteusers->getByLogin(trim(Core_Array::getPost('login')));

				if (!is_null($oSiteuserRecipient))
				{
					$oMessage_Topic->recipient_siteuser_id = $oSiteuserRecipient->id;
				}
				else
				{
					$aErrors[] = Core::factory('Core_Xml_Entity')
						->name('error')->value('Пользователя с таким логином не существует!');
				}
			}

			if (!count($aErrors))
			{
				// При добавлении сообщения увеличиваем количество непрочитанных
				$oMessage_Topic->sender_siteuser_id == $oSiteuser->id
					? $oMessage_Topic->count_recipient_unread += 1
					: $oMessage_Topic->count_sender_unread += 1;
					
				$oMessage_Topic->add($oMessage);
			}
		}

		$Message_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('errors')
				->addEntities($aErrors));

		if (!is_null(Core_Array::getPost('ajaxLoad')))
		{
			$xslName = $Message_Controller_Show->topic && !$Message_Controller_Show->delete
				? Core_Array::get(Core_Page::instance()->libParams, 'messagesListXsl')
				: Core_Array::get(Core_Page::instance()->libParams, 'xsl');

			$aArray = array();

			ob_start();

			$Message_Controller_Show
				->xsl(Core_Entity::factory('Xsl')->getByName($xslName))
				->show();

			$aArray['content'] = ob_get_clean();

			echo json_encode($aArray);
			exit();
		}

		Core_Page::instance()->object = $Message_Controller_Show;
	}
}