<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');
Core_Auth::authorization($sModule = 'shop');
$sAdminFormAction = '/admin/shop/order/property/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', 67);
$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));
$oPropertyDir = Core_Entity::factory('Property_Dir', Core_Array::getGet('property_dir_id', 0));

$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop_Order.show_list_of_properties_title', $oShop->name))
	->pageTitle(Core::_('Shop_Order.show_list_of_properties_title', $oShop->name));
$oAdmin_Form_Controller->addEntity(Admin_Form_Entity::factory('Menus')->add(Admin_Form_Entity::factory('Menu')
	->name(Core::_('Property.menu'))
	->icon('fa fa-gears')
	->add(Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->img('/admin/images/page_gear_add.gif')
		->href($oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0))
		->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0))))
	->add(Admin_Form_Entity::factory('Menu')
		->name(Core::_('Property_Dir.menu'))
		->icon('fa fa-folder-o')
		->add(Admin_Form_Entity::factory('Menu')
			->name(Core::_('Admin_Form.add'))
			->icon('fa fa-plus')
			->img('/admin/images/folder_gear_add.gif')
			->href($oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0))
			->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)))));
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php', NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php', NULL, NULL, ''))
);

if ($oShop->shop_dir_id)
{
	$oShopDir = Core_Entity::factory('Shop_Dir', $oShop->shop_dir_id);
	$aBreadcrumbs = array();
	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDir->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDir->id}"));
	} while ($oShopDir = $oShopDir->getParent());
	
	$aBreadcrumbs = array_reverse($aBreadcrumbs);
	
	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name($oShop->name)
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id=0"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id=0")));
	
if ($oShopGroup->id)
{
	$oShopGroupTmp = $oShopGroup;
	$aBreadcrumbs = array();
	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupTmp->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupTmp->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupTmp->id}"));
	} while ($oShopGroupTmp = $oShopGroupTmp->getParent());
	$aBreadcrumbs = array_reverse($aBreadcrumbs);
	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.show_order_title', $oShop->name, FALSE))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.property_title'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

if ($oPropertyDir->id)
{
	$oShopGroupTmp = $oPropertyDir;
	$aBreadcrumbs = array();
	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupTmp->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&property_dir_id={$oShopGroupTmp->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&property_dir_id={$oShopGroupTmp->id}"));
	} while ($oShopGroupTmp = $oShopGroupTmp->getParent());
	
	$aBreadcrumbs = array_reverse($aBreadcrumbs);
	
	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $oAdmin_Form->id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oProperty_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Property_Controller_Edit', $oAdmin_Form_Action
	);

	$oProperty_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	$oProperty_Controller_Edit->linkedObject(Core_Entity::factory('Shop_Order_Property_List', $oShop->id));

	$oAdmin_Form_Controller->addAction($oProperty_Controller_Edit);
}

$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $oAdmin_Form->id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	$oAdmin_Form_Controller->addAction($oControllerApply);
}

$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $oAdmin_Form->id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Property_Dir'));
$oAdmin_Form_Dataset
	->addCondition(array('select' => array('property_dirs.*')))
	->addCondition(array('join' => array('shop_order_property_dirs', 'shop_order_property_dirs.property_dir_id', '=', 'property_dirs.id')))
	->addCondition(array('where' =>array('parent_id', '=', $oPropertyDir->id)))
	->addCondition(array('where' =>array('shop_order_property_dirs.shop_id', '=', $oShop->id)))
	->changeField('name', 'type', 4)
	->changeField('name', 'link', "/admin/shop/order/property/index.php?shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&property_dir_id={id}")
	->changeField('name', 'onclick', "$.adminLoad({path: '/admin/shop/order/property/index.php', additionalParams: 'shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&property_dir_id={id}', windowId: '{windowId}'}); return false");

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Property'));
$oAdmin_Form_Dataset
	->addCondition(array('select' => array('properties.*')))
	->addCondition(array('join' => array('shop_order_properties', 'shop_order_properties.property_id', '=', 'properties.id')))
	->addCondition(array('where' =>array('shop_order_properties.shop_id', '=', $oShop->id)))
	->addCondition(array('where' =>array('property_dir_id', '=', $oPropertyDir->id)));

$oAdmin_Form_Dataset	
	->changeField('multiple', 'link', "/admin/shop/order/property/index.php?hostcms[action]=changeMultiple&hostcms[checked][{dataset_key}][{id}]=1&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&property_dir_id={property_dir_id}")
	->changeField('multiple', 'onclick', "$.adminLoad({path: '/admin/shop/order/property/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&property_dir_id={property_dir_id}', action: 'changeMultiple', windowId: '{windowId}'}); return false");	
	
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->execute();