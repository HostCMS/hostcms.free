<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 365;
$sFormAction = '/admin/shop/discount/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0, 'int'));
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0, 'int'));
$oShop_Discount = Core_Entity::factory('Shop_Discount', Core_Array::getGet('shop_discount_id', 0, 'int'));
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Item_Discount.title', $oShop_Discount->name);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Хлебные крошки
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

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
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"));
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

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Discount.model_name'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/discount/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/discount/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_discount_id={$oShop_Discount->id}&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_discount_id={$oShop_Discount->id}&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Редактировать"
/*$oAdmin_Form_Action_Edit = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action_Edit && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oShop_Discount_Siteuser_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Discount_Siteuser_Item_Controller_Edit', $oAdmin_Form_Action_Edit
	);

	$oShop_Discount_Siteuser_Item_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	$oAdmin_Form_Controller->addAction($oShop_Discount_Siteuser_Item_Controller_Edit);
}*/

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие удаления
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('markDeleted');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'markDeleted')
{
	$oShop_Discount_Siteuser_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Discount_Siteuser_Item_Controller_Delete', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShop_Discount_Siteuser_Controller_Delete);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Item_Discount')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('shop_item_discounts.shop_discount_id', '=', $oShop_Discount->id)))
	->addCondition(array('where' => array('shop_item_discounts.shop_item_id', '>', 0)));

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->addExternalReplace('{shop_discount_id}', $oShop_Discount->id);

// Показ формы
$oAdmin_Form_Controller->execute();