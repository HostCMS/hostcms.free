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
$iAdmin_Form_Id = 277;
$sAdminFormAction = '/admin/shop/warehouse/cell/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Идентификатор магазина
$shop_id = intval(Core_Array::getGet('shop_id'));

// Идентификатор группы товаров
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

$parent_id = intval(Core_Array::getGet('parent_id', 0));

$shop_warehouse_id = intval(Core_Array::getGet('shop_warehouse_id', 0));

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse')->find($shop_warehouse_id);

// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

$oShop_Warehouse_Cell = Core_Entity::factory('Shop_Warehouse_Cell', $parent_id);

$sFormTitle = Core::_('Shop_Warehouse_Cell.title', $oShop_Warehouse_Cell->nameWithSeparator(), $oShop_Warehouse->name);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_parents'))
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$iShopWarehouseId = intval(Core_Array::getGet('shop_warehouse_id'));
	$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse', $iShopWarehouseId);

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Shop_Warehouse_Cell.root')
		);

		$oShop_Warehouse_Cells = $oShop_Warehouse->Shop_Warehouse_Cells;
		$oShop_Warehouse_Cells->queryBuilder()
			->where('shop_warehouse_cells.name', 'LIKE', '%' . $sQuery . '%')
			->clearOrderBy()
			->orderBy('shop_warehouse_cells.name', 'ASC')
			->limit(Core::$mainConfig['autocompleteItems']);

		$aShop_Warehouse_Cells = $oShop_Warehouse_Cells->findAll(FALSE);

		foreach ($aShop_Warehouse_Cells as $oShop_Warehouse_Cell)
		{
			$sParents = $oShop_Warehouse_Cell->nameWithSeparator();

			$aJSON[] = array(
				'id' => $oShop_Warehouse_Cell->id,
				'label' => $sParents
			);
		}
	}

	Core::showJson($aJSON);
}

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

// Представитель класса хлебных крошек
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath = '/admin/shop/index.php', NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, ''))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Добавляем крошки для групп магазинов
if ($oShopDir->id)
{
	$aBreadcrumbs = array();

	$oShopBreadCrumbDir = $oShopDir;

	do
	{
		$additionalParams = "shop_dir_id={$oShopBreadCrumbDir->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopBreadCrumbDir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	}
	while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на форму списка групп товаров и товаров
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	$sShopItemFormPath = '/admin/shop/item/index.php';

	do
	{
		$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroup->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	}
	while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Warehouse.main_menu_warehouses_list'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/index.php', NULL, NULL, $sAdditionalParams))
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_warehouse_id={$shop_warehouse_id}&shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);

if ($parent_id)
{
	$oParentCell = Core_Entity::factory('Shop_Warehouse_Cell')->find($parent_id);

	if (!is_null($oParentCell->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = "&parent_id={$oParentCell->id}&shop_warehouse_id={$shop_warehouse_id}&shop_id={$shop_id}&shop_group_id={$shop_group_id}";

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oParentCell->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oParentCell = $oParentCell->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Warehouse_Cell_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Warehouse_Cell_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Warehouse_Cell_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warehouse_Cell_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$Admin_Form_Action_Controller_Type_Apply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Apply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Warehouse_Cell')
);

$oAdmin_Form_Dataset->addCondition(
	array(
		'where' => array('shop_warehouse_id', '=', $shop_warehouse_id)
	)
)->addCondition(array('where' => array('parent_id', '=', $parent_id)));

$oAdmin_Form_Controller
	->addExternalReplace('{shop_warehouse_id}', $shop_warehouse_id)
	->addExternalReplace('{shop_group_id}', $shop_group_id)
	->addExternalReplace('{shop_id}', $shop_id)
	;

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

// Показ формы
$oAdmin_Form_Controller->execute();