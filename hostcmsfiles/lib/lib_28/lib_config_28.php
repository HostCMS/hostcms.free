<?php

if (Core::moduleIsActive('siteuser'))
{
	$Core_Router_Route = new Core_Router_Route(rawurldecode($oCore_Page->structure->getPath()) . '({login}/)', array());
	$matches = $Core_Router_Route->applyPattern(Core::$url['path']);

	$login = Core_Array::get($matches, 'login');
	
	$oSiteuser = strlen($login)
		? Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByLogin($login)
		: NULL;
	
	if (!$oSiteuser || !$oSiteuser->active)
	{
		// Error 404
		$oCore_Page->error404();
		return;
	}

	// Empty siteuser
	//is_null($oSiteuser) && $oSiteuser = Core_Entity::factory('Siteuser');

	$Siteuser_Controller_Show = new Siteuser_Controller_Show(
		$oSiteuser
	);

	Core_Page::instance()->object = $Siteuser_Controller_Show;

	if ($oSiteuser->id)
	{
		Core_Page::instance()->title($oSiteuser->login);
		Core_Page::instance()->description($oSiteuser->login);
		Core_Page::instance()->keywords($oSiteuser->login);

		// AJAX add as friend
		if (!is_null(Core_Array::getGet('addFriend')))
		{
			$oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			// Пользователь авторизован
			if (!is_null($oCurrentSiteuser))
			{
				$oCurrentSiteuser->addFriend($oSiteuser);
			}

			Core::showJson('Added');
		}

		// AJAX delete friend
		if (!is_null(Core_Array::getGet('removeFriend')))
		{
			$oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			// Пользователь авторизован
			if (!is_null($oCurrentSiteuser))
			{
				$oCurrentSiteuser->removeFriend($oSiteuser);
			}

			Core::showJson('Removed');
		}

		// AJAX add into list
		if (!is_null(Core_Array::getGet('changeType')))
		{
			$oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			// Пользователь авторизован
			if (!is_null($oCurrentSiteuser) && $oSiteuser->id != $oCurrentSiteuser->id)
			{
				$type = intval(Core_Array::getGet('type'));

				if ($type == 0 || !is_null(Core_Entity::factory('Siteuser_Relationship_Type')->find($type)->id))
				{
					// Add as a friend
					$oCurrentSiteuser->addFriend($oSiteuser);

					$oFriends = $oCurrentSiteuser->Siteuser_Relationships;
					$oFriends->queryBuilder()
						->where('siteuser_relationships.recipient_siteuser_id', '=', $oSiteuser->id)
						->limit(1);
					$aFriends = $oFriends->findAll();

					// Add
					if(count($aFriends))
					{
						$oSiteuser_Relationship = $aFriends[0];
						$oSiteuser_Relationship->siteuser_relationship_type_id = $type;
						$oSiteuser_Relationship->save();
					}
				}
			}

			Core::showJson('Changed');
		}
	}
}