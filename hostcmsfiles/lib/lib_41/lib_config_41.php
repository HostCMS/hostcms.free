<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Producer_Controller_Show = new Shop_Producer_Controller_Show($oShop);

$Shop_Producer_Controller_Show
	->limit(Core_Array::get(Core_Page::instance()->libParams, 'itemsOnPage'))
	->parseUrl();

if ($Shop_Producer_Controller_Show->producer)
{
	$oShop_Producer = Core_Entity::factory('Shop_Producer', $Shop_Producer_Controller_Show->producer);
	Core_Page::instance()->title($oShop_Producer->seo_title
		? $oShop_Producer->seo_title
		: $oShop_Producer->name);
	Core_Page::instance()->description($oShop_Producer->seo_description
		? $oShop_Producer->seo_description
		: $oShop_Producer->name);
	Core_Page::instance()->keywords($oShop_Producer->seo_keywords
		? $oShop_Producer->seo_keywords
		: $oShop_Producer->name);
}

Core_Page::instance()->object = $Shop_Producer_Controller_Show;