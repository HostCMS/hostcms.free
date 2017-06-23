<?php 

if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();	
	
	// Если пользователь не авторизован
	if (is_null($oSiteuser))
	{
		header('Location: /users/');
		exit();
	}
}