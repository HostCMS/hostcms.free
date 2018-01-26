<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$download_digital_file = Core_Array::getGet('download_digital_file');
if ($download_digital_file)
{
	$oShop_Item_Digital = Core_Entity::factory('Shop_Item_Digital')->find($download_digital_file);

	// Проверяем, принадлежит ли товар текущему магазину и доступен пользователю
	if ($oShop_Item_Digital->id
	&& $oShop_Item_Digital->Shop_Item->Shop->site_id == CURRENT_SITE)
	{
		$file_path = $oShop_Item_Digital->getFullFilePath();

		if (is_file($file_path))
		{
			Core_File::download($file_path, $oShop_Item_Digital->filename);
			exit ();
		}
	}
}


// Код формы
$iAdmin_Form_Id = 132;
$sFormAction = '/admin/shop/item/digital/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShopItemParent = Core_Entity::factory('Shop_Item', intval(Core_Array::getGet('shop_item_id', 0)));
$oShop = $oShopItemParent->Shop;
$oShopGroup = $oShopItemParent->modification_id ? $oShopItemParent->Modification->Shop_Group : $oShopItemParent->Shop_Group;
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_("Shop_Item_Digital.shop_eitems_form_title", $oShopItemParent->name);

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

$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item_Digital.shop_eitems_menu_title'))
		->icon('fa fa-file-text-o')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item_Digital.eitems_show_add_new_link'))
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

if ($oShopItemParent->modification_id)
{
	$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(html_entity_decode(Core::_("Shop_Item.item_modification_title", $oShopItemParent->Modification->name)))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/modification/index.php', NULL, NULL, "shop_item_id={$oShopItemParent->Modification->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/modification/index.php', NULL, NULL, "shop_item_id={$oShopItemParent->Modification->id}")));
}

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_item_id={$oShopItemParent->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_item_id={$oShopItemParent->id}"))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Редактировать"
$oAdmin_Form_Action_Edit = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action_Edit)
{
	$Shop_Item_Digital_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Item_Digital_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$Shop_Item_Digital_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($Shop_Item_Digital_Controller_Edit);
}

// Действие "Применить"
$oAdmin_Form_Action_Apply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
->Admin_Form_Actions
->getByName('apply');

if ($oAdmin_Form_Action_Apply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$Admin_Form_Action_Controller_Type_Apply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdmin_Form_Action_Apply
	);

	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Apply);
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

// Действие "Удаление файла"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteFile');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteFile')
{
	$oDeleteFileController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteFileController
		->methodName('deleteFile')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oDeleteFileController);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Item_Digital')
);

$oAdmin_Form_Dataset->addCondition(array('where' => array('shop_item_id', '=', $oShopItemParent->id)));

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

//$oAdmin_Form_Controller->addExternalReplace('{shop_item_id}', $oShopItemParent->id);

// Показ формы
$oAdmin_Form_Controller->execute();