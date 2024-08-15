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
$iAdmin_Form_Id = 72;
$sFormAction = '/admin/shop/item/discount/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

if ($oShopItem->shortcut_id)
{
	$oShopItem = $oShopItem->Shop_Item;
}

$oShopGroup = $oShopItem->modification_id ? $oShopItem->Modification->Shop_Group  : $oShopItem->Shop_Group;

$oShop = $oShopItem->Shop;
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Discount.show_item_discount_title', $oShopItem->name, FALSE);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Discount.show_groups_discount'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
	/* Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Discount.show_groups_discount'))
		->icon('fa fa-money')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->icon('fa fa-plus')
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
		) */
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Bonus.bonus'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
	/* Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Bonus.bonus'))
		->icon('fa fa-star')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref(
						$oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0
					)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax(
						$oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0
					)
				)
		) */
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

// Если товар - модификация, значит мы пришли из формы списка модификаций,
// добавляем соответствующую крошку
if ($oShopItem->modification_id)
{
	// Крошка на текущую форму
	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_("Shop_Item.item_modification_title", $oShopItem->Modification->name, FALSE))
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/modification/index.php', NULL, NULL, "shop_item_id={$oShopItem->Modification->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/modification/index.php', NULL, NULL, "shop_item_id={$oShopItem->Modification->id}"))
	);
}

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_item_id={$oShopItem->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_item_id={$oShopItem->id}"))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Редактировать"
$oAdmin_Form_Action_Edit = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action_Edit && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Item_Discount_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Item_Discount_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$Shop_Item_Discount_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($Shop_Item_Discount_Controller_Edit);
}

// Действие "Удалить"
$oAdmin_Form_Action_MarkDeleted = $oAdmin_Form->Admin_Form_Actions->getByName('markDeleted');

if ($oAdmin_Form_Action_MarkDeleted && $oAdmin_Form_Controller->getAction() == 'markDeleted')
{
	$Shop_Item_Discount_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Item_Discount_Controller_Delete', $oAdmin_Form_Action_MarkDeleted
	);
	$oAdmin_Form_Controller->addAction($Shop_Item_Discount_Controller_Delete);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Источник данных 0 - Скидки
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Discount')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('shop_discounts.*'))
	)
	->addCondition(
		array('join' => array('shop_item_discounts', 'shop_discounts.id', '=', 'shop_item_discounts.shop_discount_id'))
	)
	->addCondition(array('where' => array('shop_item_discounts.shop_item_id', '=', $oShopItem->id)))
	->addCondition(array('where' => array('shop_item_discounts.siteuser_id', '=', 0)));

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $oShopGroup->id ? $oShopGroup->id : 0);

// Источник данных 1 - Бонусы
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Bonus')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('shop_bonuses.*'))
	)
	->addCondition(
		array('join' => array('shop_item_bonuses', 'shop_bonuses.id', '=', 'shop_item_bonuses.shop_bonus_id'))
	)
	->addCondition(array('where' => array('shop_item_bonuses.shop_item_id', '=', $oShopItem->id)));

$oAdmin_Form_Dataset->changeField('name', 'type', 1);

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->deleteAdminFormActionById(1334);
$oAdmin_Form_Controller->deleteAdminFormActionById(1338);

// Показ формы
$oAdmin_Form_Controller->execute();