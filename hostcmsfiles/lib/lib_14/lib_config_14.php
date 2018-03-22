<?php

// Page doesn't accept subpages, 404 error
$oCore_Page = Core_Page::instance();
if ($oCore_Page->structure->getPath() != Core::$url['path'])
{
	$oCore_Page->error404();
}
else
{
	$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Page::instance()->libParams['informationsystemId']);

	$Informationsystem_Controller_Rss_Show = new Informationsystem_Controller_Rss_Show($oInformationsystem);

	$Informationsystem_Controller_Rss_Show
		->offset(Core_Page::instance()->libParams['begin'])
		->limit(Core_Page::instance()->libParams['count'])
		// Экспорт в Яндекс.Новости
		->yandex(Core_Page::instance()->libParams['yandexFullText'])
		// Выгрузка для Яндекс.Турбо
		//->turbo(TRUE)
		// Счетчик системы статистики для учета посещаемости Турбо-страниц
		/*->channelEntities(
			array(
				array('name' => 'yandex:analytics', 'attributes' => array('type' => 'Yandex', 'id' => '00000000'))
			)
		)*/
		->group(Core_Page::instance()->libParams['informationGroupId'] == 0
			? FALSE
			: Core_Page::instance()->libParams['informationGroupId']
		)
		->stripTags(Core_Page::instance()->libParams['stripTags']);

	if (Core_Page::instance()->libParams['rssTitle'])
	{
		$Informationsystem_Controller_Rss_Show
			->title(Core_Page::instance()->libParams['rssTitle']);
	}

	if (Core_Page::instance()->libParams['rssDescription'])
	{
		$Informationsystem_Controller_Rss_Show
			->description(Core_Page::instance()->libParams['rssDescription']);
	}

	if (Core_Page::instance()->libParams['rssUrl'])
	{
		$Informationsystem_Controller_Rss_Show
			->link(Core_Page::instance()->libParams['rssUrl']);
	}

	if (Core_Page::instance()->libParams['rssImage'])
	{
		$oSiteAlias = $oInformationsystem->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$Informationsystem_Controller_Rss_Show->image(array(
				'url' => Core_Page::instance()->libParams['rssImage'],
				'title' => $oInformationsystem->name,
				'link' => 'http://' . $oSiteAlias->name . '/'
			));
			
			if (Core_Page::instance()->libParams['yandexFullText'])
			{
				$Informationsystem_Controller_Rss_Show->channelEntities = array_merge(
					$Informationsystem_Controller_Rss_Show->channelEntities,
					array(
						array(
							'name' => 'yandex:logo',
							'value' => Core_Page::instance()->libParams['rssImage']
						),
						array(
							'name' => 'yandex:logo',
							'value' => Core_Page::instance()->libParams['rssImage'],
							'attributes' => array('type' => 'square')
						)
					)
				);
			}
		}
	}

	$Informationsystem_Controller_Rss_Show->show();

	exit();
}