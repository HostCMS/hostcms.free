<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

$Shop_Controller_Show
	->limit($oShop->items_on_page)
	->parseUrl();

Core_Page::instance()->object = $Shop_Controller_Show;