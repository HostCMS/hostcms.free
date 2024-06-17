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
$iAdmin_Form_Id = 63;
$sAdminFormAction = '/admin/shop/order/status/index.php';

// Идентификатор родительской группы
$shop_id = Core_Array::getGet('shop_id', 0, 'int');

$oShop = Core_Entity::factory('Shop', intval(Core_Array::getRequest('shop_id', 0)));
$oShop_Group = Core_Entity::factory('Shop_Group', intval(Core_Array::getRequest('shop_group_id', 0)));

$iShopDirId = Core_Array::getGet('shop_dir_id', 0, 'int');
$parent_id = Core_Array::getGet('parent_id', 0, 'int');

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop_Order_Status.title'))
	->pageTitle(Core::_('Shop_Order_Status.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref($sNextFormPath = '/admin/shop/index.php', NULL, NULL, ''))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sNextFormPath, NULL, NULL, ''))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($iShopDirId)
{
	// Далее генерируем цепочку хлебных крошек от текущей группы к корневой
	$oShopDir = Core_Entity::factory('Shop_Dir')->find($iShopDirId);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	do
	{
		$additionalParams = 'shop_dir_id=' . intval($oShopDir->id);

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sNextFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sNextFormPath, NULL, NULL, $additionalParams));
	} while ($oShopDir = $oShopDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
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
if ($oShop_Group->id)
{
	$oShopGroupBreadcrumbs = $oShop_Group;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShop_Group->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShop_Group->id}"));
	} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop_Order_Status.title'))
	->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParam = "&shop_dir_id={$iShopDirId}&shop_id={$shop_id}&shop_group_id={$oShop_Group->id}"))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParam)));

if ($parent_id)
{
	$oParentStatus = Core_Entity::factory('Shop_Order_Status')->find($parent_id);

	if (!is_null($oParentStatus->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = "&shop_id={$shop_id}&shop_dir_id={$iShopDirId}&shop_group_id={$oShop_Group->id}&parent_id={$oParentStatus->id}";

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oParentStatus->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oParentStatus = $oParentStatus->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$ShopOrderStatus_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Status_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$ShopOrderStatus_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($ShopOrderStatus_Controller_Edit);
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
	Core_Entity::factory('Shop_Order_Status')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('shop_id', '=', $shop_id)))
	->addCondition(array('where' => array('parent_id', '=', $parent_id)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addExternalReplace('{shop_id}', $shop_id);
$oAdmin_Form_Controller->addExternalReplace('{shop_dir_id}', $iShopDirId);
$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $oShop_Group->id);

// Показ формы
$oAdmin_Form_Controller->execute();