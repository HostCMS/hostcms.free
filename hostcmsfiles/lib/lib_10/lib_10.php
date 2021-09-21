<?php

$Shop_Compare_Controller_Show = Core_Page::instance()->object;

$oShop = $Shop_Compare_Controller_Show->getEntity();

$Shop_Compare_Controller_Show = new Shop_Compare_Controller_Show(
	$oShop
);

$xslName = Core_Array::get(Core_Page::instance()->libParams, 'xsl');

$Shop_Compare_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->limit(2)
	->show();