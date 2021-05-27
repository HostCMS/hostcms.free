<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

if (Core_Array::getRequest('compare'))
{
	// Запрещаем индексацию страницы избранного
	Core_Page::instance()->response
		->header('X-Robots-Tag', 'none');

	$compare = Core_Array::getRequest('compare');
	!is_array($compare) && $compare = array($compare);

	$oShop_Compare_Controller = Shop_Compare_Controller::instance();

	foreach ($compare as $key => $shop_item_id)
	{
		$oShop_Compare_Controller
			->clear()
			->shop_item_id(intval($shop_item_id))
			->add();
	}
}

// Ajax
if (Core_Array::getRequest('_', FALSE)
	&& (Core_Array::getRequest('compare') || Core_Array::getRequest('loadCompare')))
{
	ob_start();

	// Краткое избранное
	$Shop_Compare_Controller_Show = new Shop_Compare_Controller_Show(
		$oShop
	);
	$Shop_Compare_Controller_Show
		->xsl(
			Core_Entity::factory('Xsl')->getByName(
				Core_Array::get(Core_Page::instance()->libParams, 'littleCompareXsl')
			)
		)
		->show();

	echo json_encode(ob_get_clean());
	exit();
}

$Shop_Compare_Controller_Show = new Shop_Compare_Controller_Show($oShop);

Core_Page::instance()->object = $Shop_Compare_Controller_Show;