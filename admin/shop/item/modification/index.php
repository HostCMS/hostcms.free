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
$iAdmin_Form_Id = 81;
$sFormAction = '/admin/shop/item/modification/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_item_id = Core_Array::getRequest('shop_item_id', 0, 'int');

$oShopItemParent = Core_Entity::factory('Shop_Item', $shop_item_id);

if ($oShopItemParent->shortcut_id)
{
	$oShopItemParent = $oShopItemParent->Shop_Item;
}

if ($shop_item_id)
{
	$oShop = $oShopItemParent->Shop;
	$oShopGroup = $oShopItemParent->Shop_Group;
}
else
{
	$shop_id = Core_Array::getRequest('shop_id', 0, 'int');
	$shop_group_id = Core_Array::getRequest('shop_group_id', 0, 'int');

	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);
	$oShop = Core_Entity::factory('Shop', $shop_id);
}

$oShopDir = $oShop->Shop_Dir;

$sFormTitle = $shop_item_id
	? Core::_("Shop_Item.item_modification_title", $oShopItemParent->name, FALSE)
	: Core::_("Shop_Item.modifications_menu");

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

$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

if ($shop_item_id)
{
	// Элементы меню
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Item.show_groups_modification'))
			->icon('fa fa-code-fork')
			->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Shop_Item.item_modification_add_item'))
					->icon('fa fa-plus')
					->href($oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0))
					->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0))
			)
			->add
			(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Shop_Item.create_modification'))
					->icon('fa fa-magic')
					->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/modification/create/index.php', NULL, NULL, "shop_item_id={$oShopItemParent->id}"))
					->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/modification/create/index.php', NULL, NULL, "shop_item_id={$oShopItemParent->id}"))
			)
		)
	;
}

if ($oShopItemParent->id)
{
	$shop_group_id = !is_null($oShopItemParent->Shop_Group->id)
		? $oShopItemParent->Shop_Group->id
		: 0;

	$windowId = $oAdmin_Form_Controller->getWindowId();

	$href = "/admin/shop/item/index.php?shop_id={$oShop->id}&shop_group_id={$shop_group_id}&hostcms[checked][0][{$oShopItemParent->id}]=1";
	$onclick = "$.adminLoad({path: '/admin/shop/item/index.php', additionalParams: 'shop_id={$oShop->id}&shop_group_id={$shop_group_id}&hostcms[checked][1][{$oShopItemParent->id}]=1', action: 'edit', windowId: '{$windowId}'}); return false";

	$oAdmin_Form_Controller->addEntity(
		$oAdmin_Form_Controller->getTitleEditIcon($href, $onclick)
	);
}

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

$additionalParams = $shop_item_id
	? "shop_item_id={$oShopItemParent->id}"
	: "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Редактировать"
$oAdmin_Form_Action_Edit = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action_Edit && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Item_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$Shop_Item_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($Shop_Item_Controller_Edit);
}

// Действие "Применить"
$oAdmin_Form_Action_Apply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdmin_Form_Action_Apply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$Admin_Form_Action_Controller_Type_Apply = Admin_Form_Action_Controller::factory(
		'Shop_Item_Controller_Apply', $oAdmin_Form_Action_Apply
	);

	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Apply);
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

$oAdmin_Form_Action_generateModifications = $oAdmin_Form->Admin_Form_Actions->getByName('generateModifications');

if ($oAdmin_Form_Action_generateModifications && $oAdmin_Form_Controller->getAction() == 'generateModifications')
{
	$oShop_Item_Modification_Create_Controller = Admin_Form_Action_Controller::factory(
		'Shop_Item_Modification_Create_Controller', $oAdmin_Form_Action_generateModifications
	);

	$oAdmin_Form_Controller->addAction($oShop_Item_Modification_Create_Controller);
}

// Действие "Скидка"
$oAdminFormActionApplyDiscount = $oAdmin_Form->Admin_Form_Actions->getByName('apply_discount');

if ($oAdminFormActionApplyDiscount && $oAdmin_Form_Controller->getAction() == 'apply_discount')
{
	$Shop_Item_Controller_Apply_Discount = Admin_Form_Action_Controller::factory(
		'Shop_Item_Controller_Apply_Discount', $oAdminFormActionApplyDiscount
	);

	$Shop_Item_Controller_Apply_Discount
		->title(Core::_('Shop_Item.apply_discount_items_title'))
		->Shop($oShop);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Item_Controller_Apply_Discount);
}

// Действие "Удаление значения свойства"
$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('deletePropertyValue');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oDeletePropertyValueController = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAction
	);

	$oDeletePropertyValueController
		->linkedObject(array(
			Core_Entity::factory('Shop_Item_Property_List', $oShop->id)
		));

	$oAdmin_Form_Controller->addAction($oDeletePropertyValueController);
}

// Действие "Удаление файла большого изображения"
$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('deleteLargeImage');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteLargeImage')
{
	$oDeleteLargeImageController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteLargeImageController
		->methodName('deleteLargeImage')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oDeleteLargeImageController);
}

// Действие "Удаление файла малого изображения"
$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('deleteSmallImage');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteSmallImage')
{
	$oDeleteSmallImageController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteSmallImageController
		->methodName('deleteSmallImage')
		->divId(array('preview_small_image', 'delete_small_image'));

	$oAdmin_Form_Controller->addAction($oDeleteSmallImageController);
}

// Удаление сопутствующих товаров с вкладки
$oAdminFormActionDeleteAssociated = $oAdmin_Form->Admin_Form_Actions->getByName('deleteAssociated');

if ($oAdminFormActionDeleteAssociated && $oAdmin_Form_Controller->getAction() == 'deleteAssociated')
{
	$Shop_Item_Associated_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Item_Associated_Controller_Delete', $oAdminFormActionDeleteAssociated
	);

	$oAdmin_Form_Controller->addAction($Shop_Item_Associated_Controller_Delete);
}

// Удаление товаров из комплекта
$oAdminFormActionDeleteSet = $oAdmin_Form->Admin_Form_Actions->getByName('deleteSetItem');

if ($oAdminFormActionDeleteSet && $oAdmin_Form_Controller->getAction() == 'deleteSetItem')
{
	$Shop_Item_Set_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Item_Set_Controller_Delete', $oAdminFormActionDeleteSet
	);

	$oAdmin_Form_Controller->addAction($Shop_Item_Set_Controller_Delete);
}

// Действие "Изменить атрибуты"
$oAdminFormActionChangeAttribute = $oAdmin_Form->Admin_Form_Actions->getByName('change_attributes');

if ($oAdminFormActionChangeAttribute && $oAdmin_Form_Controller->getAction() == 'change_attributes')
{
	$oShop_Item_Controller_Change_Attribute = Admin_Form_Action_Controller::factory(
		'Shop_Item_Controller_Change_Attribute', $oAdminFormActionChangeAttribute
	);

	$oShop_Item_Controller_Change_Attribute
		->title(Core::_('Shop_Item.change_attributes_items_title'))
		->modifications(TRUE)
		->Shop($oShop);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShop_Item_Controller_Change_Attribute);
}

// Действие "Создать ярлык"
$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('shortcut');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'shortcut' && $oAdmin_Form_Action_Edit)
{
	$oShortcutController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Shortcut', $oAction
	);

	$oShortcutController
		->title(Core::_('Shop_Item.shortcut_creation_window_caption'))
		->selectCaption(Core::_('Shop_Item.add_item_shortcut_shop_groups_id'))
		->value($oShopGroup->id);

	$iCount = $oShop->Shop_Groups->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		$oShortcutController->selectOptions(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id));
	}
	else
	{
		$oShortcutController->autocomplete(TRUE);
	}

	$oAdmin_Form_Controller->addAction($oShortcutController);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Item')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('shop_items.*', array('shop_items.price', 'adminPrice'), array('SUM(shop_warehouse_items.count)', 'adminRest')))
	)
	->addCondition(
		array('leftJoin' => array('shop_warehouse_items', 'shop_items.id', '=', 'shop_warehouse_items.shop_item_id'))
	)
	->addCondition(array('groupBy' => array('shop_items.id')));

if ($shop_item_id)
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('modification_id', '=', $oShopItemParent->id))
	);
}
else
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('modification_id', '>', 0))
	);
}

// Change field type
if (Core_Entity::factory('Shop', $oShop->id)->Shop_Warehouses->getCount() == 1)
{
	$oAdmin_Form_Dataset->changeField('adminRest', 'type', 2);
}

// Change field type
$oAdmin_Form_Dataset->changeField('img', 'type', 10);

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->addExternalReplace('{shop_item_id}', $oShopItemParent->id);

// Показ формы
$oAdmin_Form_Controller->execute();