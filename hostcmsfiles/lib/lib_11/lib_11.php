<?php

$Shop_Controller_Show = Core_Page::instance()->object;

$Shop_Controller_Show
   ->shopItems()
   ->queryBuilder()
   ->clearOrderBy()
   ->leftJoin('shop_groups', 'shop_groups.id', '=', 'shop_items.shop_group_id')
   ->where('shop_items.active', '=', 1)
   ->open()
   ->where('shop_groups.active', '=', 1)
   ->setOr()
   ->where('shop_groups.active', 'IS', NULL)
   ->where('shop_items.modification_id', '=', 0)
   ->close()
   ->clearOrderBy()
   ->orderBy('shop_items.shop_group_id')
   ->orderBy('shop_items.name');

$Shop_Controller_Show
	->shopGroups()
	->queryBuilder()
	->where('shop_groups.active', '=', 1)
	->clearOrderBy()
	->orderBy('shop_groups.id');

$xslName = Core_Array::get(Core_Page::instance()->libParams, 'xsl');

$Shop_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->groupsMode('all')
	->itemsProperties(TRUE)
	->group(FALSE)
	->show();