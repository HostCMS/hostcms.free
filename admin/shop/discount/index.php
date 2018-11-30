<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 72;
$sFormAction = '/admin/shop/discount/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', intval(Core_Array::getGet('shop_id', 0)));
$oShopGroup = Core_Entity::factory('Shop_Group', intval(Core_Array::getGet('shop_group_id', 0)));
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Discount.discount_add_form_link');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->img('/admin/images/money_add.gif')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref(
				$oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0
			)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax(
				$oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0
			)
		)
	)
;

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

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
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath()))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath()))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Редактировать"
$oAdmin_Form_Action_Edit = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action_Edit)
{
	$Shop_Discount_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Discount_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$Shop_Discount_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($Shop_Discount_Controller_Edit);
}

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

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Discount')
);

$oAdmin_Form_Dataset
	->changeField('active', 'link', '/admin/shop/discount/index.php?hostcms[action]=changeStatus&hostcms[checked][{dataset_key}][{id}]=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShopGroup->id)
	->changeField('active', 'onclick', "$.adminLoad({path: '/admin/shop/discount/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}', action: 'changeStatus', windowId: '{windowId}'}); return false")
	->addCondition(array('where' => array('shop_id', '=', $oShop->id)))
;

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();