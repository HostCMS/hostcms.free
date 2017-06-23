<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_YandexRealty = new Shop_Controller_YandexRealty($oShop);
$Shop_Controller_YandexRealty->show();

exit();