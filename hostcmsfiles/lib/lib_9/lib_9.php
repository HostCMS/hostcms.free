<?php

$Shop_Seller_Controller_Show = Core_Page::instance()->object;

$xslName = $Shop_Seller_Controller_Show->seller
	? Core_Array::get(Core_Page::instance()->libParams, 'xsl')
	: Core_Array::get(Core_Page::instance()->libParams, 'listXsl');

$Shop_Seller_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->show();