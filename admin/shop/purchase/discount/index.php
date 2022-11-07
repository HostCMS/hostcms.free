<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 73;
$sFormAction = '/admin/shop/purchase/discount/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));
$oShopDir = $oShop->Shop_Dir;

$oShop_Purchase_Discount_Dir = Core_Entity::factory('Shop_Purchase_Discount_Dir', Core_Array::getGet('shop_purchase_discount_dir_id', 0, 'int'));

$sFormTitle = Core::_('Shop_Purchase_Discount.order_discount_show_title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_move_dirs'))
	&& !is_null(Core_Array::getGet('queryString'))
	&& Core_Array::getGet('entity_id')
)
{
	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
	$entity_id = intval(Core_Array::getGet('entity_id'));
	$mode = intval(Core_Array::getGet('mode'));

	$oShop = Core_Entity::factory('Shop', $entity_id);

	$aExclude = strlen(Core_Array::getGet('exclude'))
		? json_decode(Core_Array::getGet('exclude'), TRUE)
		: array();

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Shop_Purchase_Discount.root') . ' [0]'
		);

		$oShop_Purchase_Discount_Dirs = $oShop->Shop_Purchase_Discount_Dirs;
		$oShop_Purchase_Discount_Dirs->queryBuilder()
			->limit(Core::$mainConfig['autocompleteItems']);

		switch ($mode)
		{
			// Вхождение
			case 0:
			default:
				$oShop_Purchase_Discount_Dirs->queryBuilder()->where('shop_purchase_discount_dirs.name', 'LIKE', '%' . str_replace(' ', '%', $sQuery) . '%');
			break;
			// Вхождение с начала
			case 1:
				$oShop_Purchase_Discount_Dirs->queryBuilder()->where('shop_purchase_discount_dirs.name', 'LIKE', $sQuery . '%');
			break;
			// Вхождение с конца
			case 2:
				$oShop_Purchase_Discount_Dirs->queryBuilder()->where('shop_purchase_discount_dirs.name', 'LIKE', '%' . $sQuery);
			break;
			// Точное вхождение
			case 3:
				$oShop_Purchase_Discount_Dirs->queryBuilder()->where('shop_purchase_discount_dirs.name', '=', $sQuery);
			break;
		}

		count($aExclude) && $oShop_Purchase_Discount_Dirs->queryBuilder()
			->where('shop_purchase_discount_dirs.id', 'NOT IN', $aExclude);

		$aShop_Purchase_Discount_Dirs = $oShop_Purchase_Discount_Dirs->findAll();

		foreach ($aShop_Purchase_Discount_Dirs as $oShop_Purchase_Discount_Dir)
		{
			$sParents = $oShop_Purchase_Discount_Dir->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oShop_Purchase_Discount_Dir->id,
				'label' => $sParents . ' [' . $oShop_Purchase_Discount_Dir->id . ']'
			);
		}
	}

	Core::showJson($aJSON);
}

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$additionalParams = "shop_purchase_discount_dir_id={$oShop_Purchase_Discount_Dir->id}";

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
Admin_Form_Entity::factory('Menu')
	->name(Core::_('Admin_Form.add'))
	->icon('fa fa-plus')
	->img('/admin/images/money_add.gif')
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
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Purchase_Discount_Dir.menu'))
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
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

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
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"));
	}while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

// Крошки по группам
if ($oShop_Purchase_Discount_Dir->id)
{
	$oShop_Purchase_Discount_Dir_Breadcrumbs = $oShop_Purchase_Discount_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShop_Purchase_Discount_Dir_Breadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/purchase/discount/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_purchase_discount_dir_id={$oShop_Purchase_Discount_Dir_Breadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/purchase/discount/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_purchase_discount_dir_id={$oShop_Purchase_Discount_Dir_Breadcrumbs->id}"));
	}
	while ($oShop_Purchase_Discount_Dir_Breadcrumbs = $oShop_Purchase_Discount_Dir_Breadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

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

// Действие "Редактировать"
$oAdmin_Form_Action_Edit = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)->Admin_Form_Actions->getByName('edit');
if ($oAdmin_Form_Action_Edit && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oEditController = Admin_Form_Action_Controller::factory(
		'Shop_Purchase_Discount_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$oEditController->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($oEditController);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)->Admin_Form_Actions->getByName('copy');
if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Перенести"
$oAdminFormActionMove = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$Admin_Form_Action_Controller_Type_Move = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Move', $oAdminFormActionMove
	);

	$Admin_Form_Action_Controller_Type_Move
		->title(Core::_('Shop_Purchase_Discount.move_dirs_title'))
		->selectCaption(Core::_('Shop_Purchase_Discount.move_items_dirs'))
		->value($oShop_Purchase_Discount_Dir->id)
		->autocompletePath('/admin/shop/purchase/discount/index.php?autocomplete=1&show_move_dirs=1')
		->autocompleteEntityId($oShop->id);

	$iCount = $oShop->Shop_Purchase_Discount_Dirs->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		$aExclude = array();

		$aChecked = $oAdmin_Form_Controller->getChecked();

		foreach ($aChecked as $datasetKey => $checkedItems)
		{
			// Exclude just dirs
			if ($datasetKey == 0)
			{
				foreach ($checkedItems as $key => $value)
				{
					$aExclude[] = $key;
				}
			}
		}

		$Admin_Form_Action_Controller_Type_Move
			// Список директорий генерируется другим контроллером
			->selectOptions(array(' … ') + Shop_Purchase_Discount_Controller_Edit::fillShopPurchaseDiscountDir($oShop->id, 0, $aExclude));
	}
	else
	{
		$Admin_Form_Action_Controller_Type_Move->autocomplete(TRUE);
	}

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Move);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Purchase_Discount_Dir')
);

$oAdmin_Form_Dataset
	->changeField('name', 'class', 'semi-bold')
	->addCondition(array('where' => array('parent_id', '=', $oShop_Purchase_Discount_Dir->id)))
	->addCondition(array('where' => array('shop_id', '=', $oShop->id)));

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Purchase_Discount')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('shop_purchase_discount_dir_id', '=', $oShop_Purchase_Discount_Dir->id)))
	->addCondition(array('where' => array('shop_id', '=', $oShop->id)))
	->changeField('active', 'link', "/admin/shop/purchase/discount/index.php?hostcms[action]=changeStatus&hostcms[checked][{dataset_key}][{id}]=1&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_purchase_discount_dir_id={$oShop_Purchase_Discount_Dir->id}")
	->changeField('active', 'onclick', "$.adminLoad({path: '/admin/shop/purchase/discount/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_purchase_discount_dir_id={$oShop_Purchase_Discount_Dir->id}', action: 'changeStatus', windowId: '{windowId}'}); return false");

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $oShopGroup->id);

$oAdmin_Form_Controller->execute();