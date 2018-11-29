<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$oShopItemParent = Core_Entity::factory('Shop_Item', intval(Core_Array::getGet('shop_item_id', 0)));
$oShop = $oShopItemParent->Shop;
$oShopGroup = $oShopItemParent->Shop_Group;
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_("Shop_Item.item_modification_title", $oShopItemParent->name);

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path('/admin/shop/item/modification/create/index.php')
	->title(Core::_('Shop_Item.create_modification_title'));

// Хлебные крошки
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Shop_Item.create_modification_title'))
	;

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php'))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php'))
);

// Крошки по директориям магазинов
if ($oShopDir->id)
{
	$oShopDirBreadcrumbs = $oShopDir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oShopDirBreadcrumbs->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		));
	} while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
	->name($oShop->name)
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
);

// Крошки по группам товаров
if ($oShopGroup->id)
{
	$oShopGroupBreadcrumbs = $oShopGroup;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"));
	} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref("/admin/shop/item/modification/index.php", NULL, NULL, "shop_item_id={$oShopItemParent->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax("/admin/shop/item/modification/index.php", NULL, NULL, "shop_item_id={$oShopItemParent->id}"))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action('/admin/shop/item/modification/index.php')
		->enctype('multipart/form-data');

$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

$aProperties = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)
	->getPropertiesForGroup($oShopItemParent->shop_group_id);

Core_Event::notify('Shop_Item_Modification_Create.onAfterSelectProperties', $oAdmin_Form_Entity_Form, array($aProperties, $oShopItemParent->shop_group_id));

$eventResult = Core_Event::getLastReturn();

if (is_array($eventResult))
{
	$aProperties = $eventResult;
}
	
$aNamePattern = array();

foreach ($aProperties as $oProperty)
{
	// Если тип свойства - "Список" и модуль списков активен
	if ($oProperty->type == 3 && Core::moduleIsActive('list'))
	{
		$aNamePattern[] = sprintf("%s {P%s}", $oProperty->name, $oProperty->id);

		$aValues = $oProperty->List->getListItemsTree();

		$oAdmin_Form_Entity_Form->add(
			Admin_Form_Entity::factory('Div')->class('row')
				->add(
					Admin_Form_Entity::factory('Checkbox')
						->name("property_{$oProperty->id}")
						->caption(Core::_('Shop_Item.create_modification_property_enable', $oProperty->name, $oProperty->id))
				)
				->add(
					Admin_Form_Entity::factory('Select')
						->options($aValues)
						->multiple("multiple")
						->name("property{$oProperty->id}list[]")
						->style('width:300px;')
						->filter(FALSE)
				)
		);
	}
}

$aCurrencies = array(' … ');
$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();
foreach ($aShop_Currencies as $oShop_Currency)
{
	$aCurrencies[$oShop_Currency->id] = $oShop_Currency->name;
}

$aMeasures = array(' … ');
$aShop_Measures = Core_Entity::factory('Shop_Measure')->findAll();

foreach ($aShop_Measures as $oShop_Measure)
{
	$aMeasures[$oShop_Measure->id] = $oShop_Measure->name;
}

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Input')
		->name("price")
		->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2'))
		->caption(Core::_('Shop_Item.create_modification_price'))
		->value($oShopItemParent->price)
)->add(
	Admin_Form_Entity::factory('Select')
		->name('currency')
		->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2'))
		->caption(' ')
		->options($aCurrencies)
		->value($oShopItemParent->shop_currency_id)
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Select')
		->name('measure')
		->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2'))
		->options($aMeasures)
		->value($oShopItemParent->shop_measure_id)
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Input')
		->name('marking')
		->divAttr(array('class' => 'form-group col-xs-12'))
		->caption(Core::_('Shop_Item.create_modification_mark'))
		->value(sprintf("%s-{N}", $oShopItemParent->marking))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Input')
		->name('name')
		->caption(Core::_('Shop_Item.create_modification_name', $oShopItemParent->name, $oShopItemParent->name))
		->value(sprintf("%s %s", $oShopItemParent->name, implode(', ', $aNamePattern)))
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_main_properties')
		->caption(Core::_('Shop_Item.create_modification_copy_main_properties'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_seo')
		->caption(Core::_('Shop_Item.create_modification_copy_seo'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_export_import')
		->caption(Core::_('Shop_Item.create_modification_copy_export_import'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_prices_to_item')
		->caption(Core::_('Shop_Item.create_modification_copy_prices_to_item'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_specials_prices_to_item')
		->caption(Core::_('Shop_Item.create_modification_copy_specials_prices_to_item'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_tying_products')
		->caption(Core::_('Shop_Item.create_modification_copy_tying_products'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_external_property')
		->caption(Core::_('Shop_Item.create_modification_copy_external_property'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_tags')
		->caption(Core::_('Shop_Item.create_modification_copy_tags'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')->add(
	Admin_Form_Entity::factory('Checkbox')
		->name('copy_warehouse_count')
		->caption(Core::_('Shop_Item.create_modification_copy_warehouse_count'))
		->value(0)
		->divAttr(array('class' => 'form-group col-xs-12'))
))->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(Core::factory('Core_Html_Entity_Input')->type('hidden')->name('shop_item_id')->value($oShopItemParent->id))
);

$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item')->getByShop_item_id($oShopItemParent->id);
if (!is_null($oShop_Warehouse_Item))
{
	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(
			Core::factory('Core_Html_Entity_Input')->type('hidden')->name('count')->value($oShop_Warehouse_Item->count)
		)
	);
}

$oAdmin_Form_Entity_Form->add($oMainTab);

$oAdmin_Form_Entity_Form->add(
	Admin_Form_Entity::factory('Button')
	->name('create_modifications')
	->type('submit')
	->value(Core::_('Shop_Item.create_modification'))
	->class('applyButton btn btn-blue')
	->onclick($oAdmin_Form_Controller->getAdminSendForm('generateModifications', NULL, 'hostcms[checked][0][0]=1&shop_item_id=' . $oShopItemParent->id))
);

$oAdmin_Form_Entity_Form->execute();

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	//->content(iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", ob_get_clean()))
	->content(ob_get_clean())
	->title($sFormTitle)
	->execute();