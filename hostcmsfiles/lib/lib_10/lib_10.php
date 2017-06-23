<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

$Shop_Controller_Show
	->limit(0)
	->group(FALSE)
	->xsl(
		Core_Entity::factory('Xsl')->getByName(
			Core_Array::get(Core_Page::instance()->libParams, 'xsl')
		)
	)
	->cache(FALSE)
	->itemsProperties(TRUE)
	->show();