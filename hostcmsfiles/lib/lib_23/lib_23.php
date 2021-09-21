<?php

if (!Core::moduleIsActive('siteuser'))
{
	?>
	<h1>Клиенты</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="https://www.hostcms.ru/hostcms/modules/siteusers/">Клиенты</a>&raquo; доступен в редакциях &laquo;<a href="https://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="https://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return ;
}

$Siteuser_Controller_Show = Core_Page::instance()->object;

$xslUserAuthorization = Core_Array::get(Core_Page::instance()->libParams, 'userAuthorizationXsl');

$oSiteuser = $Siteuser_Controller_Show->getEntity();

if ($oSiteuser->id)
{
	$Siteuser_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('item')
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('name')->value('Личная информация')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('path')->value('registration/')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/info.png')
			)
	);

	if (Core::moduleIsActive('maillist'))
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('name')->value('Почтовые рассылки')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('path')->value('maillist/')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/maillist.png')
				)
		);
	}

	if (Core::moduleIsActive('helpdesk'))
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('name')->value('Служба техподдержки')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('path')->value('helpdesk/')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/helpdesk.png')
				)
		);
	}

	if (Core::moduleIsActive('shop'))
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('name')->value('Мои заказы')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('path')->value('order/')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/order.png')
				)
		);

		if (Core::moduleIsActive('siteuser'))
		{
			$oAffiliate_Plans = Core_Entity::factory('Site', CURRENT_SITE)->Affiliate_Plans;

			$aSiteuserGroupId = array();

			$oSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
			foreach ($oSiteuser_Groups as $oSiteuser_Group)
			{
				$aSiteuserGroupId[] = $oSiteuser_Group->id;
			}

			if (count($aSiteuserGroupId))
			{
				$oAffiliate_Plans->queryBuilder()
					->where('siteuser_group_id', 'IN', $aSiteuserGroupId);

				$aAffiliate_Plans = $oAffiliate_Plans->findAll();

				if (count($aAffiliate_Plans))
				{
					$Siteuser_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('item')
							->addEntity(
								Core::factory('Core_Xml_Entity')->name('name')->value('Партнерские программы')
							)
							->addEntity(
								Core::factory('Core_Xml_Entity')->name('path')->value('affiliats/')
							)
							->addEntity(
								Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/partner.png')
							)
					);
				}
			}
		}
	}

	if (Core::moduleIsActive('siteuser'))
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('name')->value('Лицевой счет')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('path')->value('account/')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/account.png')
				)
		);
	}

	if (Core::moduleIsActive('shop'))
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('name')->value('Мои объявления')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('path')->value('my_advertisement/')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/bulletin-board.png')
				)
		);
	}

	if (Core::moduleIsActive('message'))
	{
		$Siteuser_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('name')->value('Мои сообщения')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('path')->value('my_messages/')
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/message.png')
				)
		);
	}

	$Siteuser_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('item')
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('name')->value('Выход')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('path')->value('?action=exit')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('image')->value('/images/user/exit.png')
			)
	);
}

$Siteuser_Controller_Show->xsl(
		Core_Entity::factory('Xsl')->getByName($xslUserAuthorization)
	)
	->showGroups(TRUE)
	// ->showDiscountcards(TRUE)
	->show();