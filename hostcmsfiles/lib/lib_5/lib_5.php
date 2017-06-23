<?php 

$Structure_Controller_Show = new Structure_Controller_Show(
		Core_Entity::factory('Site', CURRENT_SITE)
	);
$Structure_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName(Core_Page::instance()->libParams['xsl'])
	)
	->parentId(intval(Core_Page::instance()->libParams['structureParentId']))
	// Показывать группы информационных систем в карте сайта
	->showInformationsystemGroups(Core_Page::instance()->libParams['showInformationsystemGroups'])
	// Показывать элементы информационных систем в карте сайта
	->showInformationsystemItems(Core_Page::instance()->libParams['showInformationsystemItems'])
	// Показывать группы магазина в карте сайта
	->showShopGroups(Core_Page::instance()->libParams['showShopGroups'])
	// Показывать товары магазина в карте сайта
	->showShopItems(Core_Page::instance()->libParams['showShopItems'])
	->show();