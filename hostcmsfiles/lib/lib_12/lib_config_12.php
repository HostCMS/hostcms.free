<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

// $oCore_Out_File = new Core_Out_File();
// $oCore_Out_File->filePath(CMS_FOLDER . "yandexmarket.xml");

$Shop_Controller_YandexMarket = new Shop_Controller_YandexMarket($oShop);
// $Shop_Controller_YandexMarket->stdOut($oCore_Out_File);
$Shop_Controller_YandexMarket
	->token(Core_Array::get(Core_Page::instance()->libParams, 'token', ''))
	// ->outlets(TRUE)
	->parseUrl()
	// ->additionalImages(array('images'))
	->show();

exit();