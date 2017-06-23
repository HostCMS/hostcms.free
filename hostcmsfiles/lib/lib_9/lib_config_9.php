<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Seller_Controller_Show = new Shop_Seller_Controller_Show($oShop);

$Shop_Seller_Controller_Show
	->limit(Core_Array::get(Core_Page::instance()->libParams, 'itemsOnPage'))
	->parseUrl();

if ($Shop_Seller_Controller_Show->seller)
{
	$oShop_Seller = Core_Entity::factory('Shop_Seller', $Shop_Seller_Controller_Show->seller);
	Core_Page::instance()->title($oShop_Seller->name);
	Core_Page::instance()->description($oShop_Seller->name);
	Core_Page::instance()->keywords($oShop_Seller->name);
}

Core_Page::instance()->object = $Shop_Seller_Controller_Show;