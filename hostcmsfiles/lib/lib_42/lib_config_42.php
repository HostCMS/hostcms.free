<?php

if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

	if (!is_null($oSiteuser))
	{
		$Message_Controller_Show = new Message_Controller_Show($oSiteuser);

		$Message_Controller_Show
			->limit(Core_Array::get(Core_Page::instance()->libParams, 'itemsOnPage', 15))
			->url(Core_Entity::factory('Structure', Core_Page::instance()->structure->id)->getPath())
			->ajax(Core_Array::getPost('ajaxLoad', FALSE)) // параметр Ajax запроса
			->activity(Core_Array::getPost('activity', FALSE)) // параметр активности пользователя
			->parseUrl();

		$aErrors = array();

		// Создание топика
		if (!$Message_Controller_Show->topic && !is_null(Core_Array::getPost('login')))
		{
			$login = Core_Array::getPost('login', '', 'str');
			$subject = Core_Array::getPost('subject', '', 'str');
			$text = Core_Array::getPost('text', '', 'str');
			$result = $Message_Controller_Show->createTopic($login, $subject, $text);

			if ($result == 'wrong-login')
			{
				$aErrors[] = Core::factory('Core_Xml_Entity')
					->name('error')->value('Пользователя с таким логином не существует!');

				$Message_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('login')
						->value($login)
					)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('subject')
						->value($subject)
					)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('text')
						->value($text)
					);
			}
		}
		elseif (!is_null(Core_Array::getPost('text')))
		{
			$text = Core_Array::getPost('text', '', 'str');
			$result = $Message_Controller_Show->addMessage($text);

			if ($result == 'empty-text')
			{
				$aErrors[] = Core::factory('Core_Xml_Entity')
					->name('error')->value('Отсутствует текст сообщения!');
			}
			elseif ($result == 'wrong-topic')
			{
				$aErrors[] = Core::factory('Core_Xml_Entity')
					->name('error')->value('Не выбрана переписка!');
			}
		}

		$Message_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('errors')
				->addEntities($aErrors)
			);

		if (!is_null(Core_Array::getPost('ajaxLoad')))
		{
			$xslName = $Message_Controller_Show->topic && !$Message_Controller_Show->delete
				? Core_Array::get(Core_Page::instance()->libParams, 'messagesListXsl')
				: Core_Array::get(Core_Page::instance()->libParams, 'xsl');

			ob_start();

			$Message_Controller_Show
				->xsl(Core_Entity::factory('Xsl')->getByName($xslName))
				->show();

			Core::showJson(array('content' => ob_get_clean()));
		}

		Core_Page::instance()->object = $Message_Controller_Show;
	}
}