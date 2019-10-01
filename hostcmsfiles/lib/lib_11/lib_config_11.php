<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

$path = Core_Page::instance()->structure->getPath();

$Shop_Controller_Show
	->pattern(rawurldecode($path) . '({path})(page-{page}/)')
	/*->pattern(rawurldecode($path) . '({path})({xls})(page-{page}/)')
	->patternExpressions(array(
		'xls' => 'xls/'
	))*/
	->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('path')
			->value($path)
	)
	->limit(500)
	->parseUrl();

// Excel
/*if (isset($Shop_Controller_Show->patternParams['xls']))
{
	die();
}*/
// /Excel

Core_Page::instance()->object = $Shop_Controller_Show;